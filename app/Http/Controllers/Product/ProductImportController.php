<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductRestockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ProductImportController extends Controller
{
    /** Errors listed on screen; the rest are summarised as "... and N more" */
    private const MAX_ERRORS_SHOWN = 30;

    public function create()
    {
        return view('products.import');
    }

    /**
     * Columns: A = Name, B = Category, C = SKU, D = Quantity, E = Price (row 1 is the header).
     *
     * The whole file is checked first and saved in one transaction: either every row is
     * imported or nothing is, so a corrected file can simply be imported again.
     * An existing SKU is a restock (its quantity is added); a new SKU creates the product.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx',
        ]);

        try {
            $sheet = IOFactory::load($request->file('file')->getRealPath())->getActiveSheet();
        } catch (Throwable $e) {
            Log::warning('Product import: unreadable file', ['error' => $e->getMessage()]);

            return back()->with('error', 'The file could not be read. Please upload a valid Excel file (.xlsx or .xls).');
        }

        [$rows, $errors] = $this->readRows($sheet);

        if ($errors) {
            $shown = array_slice($errors, 0, self::MAX_ERRORS_SHOWN);
            if (count($errors) > count($shown)) {
                $shown[] = '... and ' . (count($errors) - count($shown)) . ' more.';
            }

            return back()
                ->with('error', 'Nothing was imported. Please fix these rows and import the file again.')
                ->withErrors($shown);
        }

        if (! $rows) {
            return back()->with('error', 'No products found in the file (rows need a SKU in column C).');
        }

        try {
            [$created, $restocked, $units] = DB::transaction(fn () => $this->saveRows($rows));
        } catch (Throwable $e) {
            Log::error('Product import failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Nothing was imported: ' . $e->getMessage());
        }

        return redirect()
            ->route('products.index')
            ->with('success', "Products imported: {$created} new, {$restocked} restocked (+{$units} units).");
    }

    /**
     * Read and check every row without saving anything.
     *
     * @return array{0: array<int, array>, 1: string[]} [rows keyed by row number, error messages]
     */
    private function readRows($sheet): array
    {
        $categories = Category::pluck('id', 'slug');
        $rows = [];
        $errors = [];
        $skuRows = [];

        for ($row = 2; $row <= $sheet->getHighestDataRow(); $row++) {
            $sku = trim((string) $sheet->getCell('C' . $row)->getValue());
            if ($sku === '') {
                continue; // rows without SKU are skipped, as before
            }

            $name = trim((string) $sheet->getCell('A' . $row)->getValue());
            $categoryName = trim((string) $sheet->getCell('B' . $row)->getValue());
            $quantity = $sheet->getCell('D' . $row)->getCalculatedValue();
            $price = $sheet->getCell('E' . $row)->getCalculatedValue();

            if ($name === '') {
                $errors[] = "Row {$row}: product name (column A) is empty.";
            }

            $categoryId = $categories[Str::slug($categoryName)] ?? null;
            if ($categoryName === '') {
                $errors[] = "Row {$row}: category (column B) is empty.";
            } elseif (! $categoryId) {
                $errors[] = "Row {$row}: category \"{$categoryName}\" does not exist. Add it on the Categories page first.";
            }

            if ($quantity !== null && $quantity !== '' && (! is_numeric($quantity) || (int) $quantity != $quantity || $quantity < 0)) {
                $errors[] = "Row {$row}: quantity (column D) must be a whole number of 0 or more, got \"{$quantity}\".";
            }

            if ($price !== null && $price !== '' && (! is_numeric($price) || $price < 0)) {
                $errors[] = "Row {$row}: price (column E) must be a number, got \"{$price}\".";
            }

            if (isset($skuRows[$sku])) {
                $errors[] = "Row {$row}: SKU {$sku} is already in row {$skuRows[$sku]}. Put each SKU on one row only.";
            }
            $skuRows[$sku] = $row;

            $rows[$row] = [
                'name'        => $name,
                'sku'         => $sku,
                'category_id' => $categoryId,
                'quantity'    => (int) $quantity,
                'price'       => (float) $price,
            ];
        }

        return [$rows, $errors];
    }

    /**
     * Save the checked rows (called inside a transaction).
     *
     * @return array{0: int, 1: int, 2: int} [new products, restocked products, units added]
     */
    private function saveRows(array $rows): array
    {
        $created = $restocked = $units = 0;

        foreach ($rows as $data) {
            $product = Product::where('sku', $data['sku'])->lockForUpdate()->first();

            if ($product) {
                // existing SKU: restock
                $oldQty = (int) $product->quantity;

                $product->update([
                    'name'        => $data['name'],
                    'quantity'    => $oldQty + $data['quantity'],
                    'price'       => $data['price'],
                    'category_id' => $data['category_id'],
                ]);

                ProductRestockLog::record($product, $oldQty, $oldQty + $data['quantity'], 'import');
                $restocked++;
            } else {
                $product = Product::create([
                    'name'        => $data['name'],
                    'sku'         => $data['sku'],
                    'quantity'    => $data['quantity'],
                    'price'       => $data['price'],
                    'category_id' => $data['category_id'],
                    'created_by'  => auth()->id(),
                ]);

                ProductRestockLog::record($product, 0, $data['quantity'], 'import');
                $created++;
            }

            $units += $data['quantity'];
        }

        return [$created, $restocked, $units];
    }
}
