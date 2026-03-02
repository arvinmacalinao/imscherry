<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductRestockLog;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

class ProductImportController extends Controller
{
    public function create()
    {
        return view('products.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xls,xlsx',
        ]);

        $the_file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($the_file->getRealPath());
            $sheet        = $spreadsheet->getActiveSheet();
            $row_limit    = $sheet->getHighestDataRow();
            $row_range    = range(2, $row_limit); // Skip header

            foreach ($row_range as $row) {

                $name         = trim($sheet->getCell('A' . $row)->getValue());
                $categoryName = trim($sheet->getCell('B' . $row)->getValue());
                $sku          = trim($sheet->getCell('C' . $row)->getValue());
                $quantity     = (int) $sheet->getCell('D' . $row)->getValue();
                $price        = (float) $sheet->getCell('E' . $row)->getValue();

                if (!$sku) {
                    continue; // Skip rows without SKU
                }

                $slug = Str::slug($categoryName);

                $category = Category::where('slug', $slug)->first();

                if (!$category) {
                    throw new Exception("Category not found: {$categoryName} (Row {$row})");
                }

                $category_id = $category->id;

                $product = Product::where('sku', $sku)->first();

                if ($product) {

                    // Existing product → restock
                    $oldQty = $product->quantity;
                    $newQty = $oldQty + $quantity;

                    $product->update([
                        'name'        => $name,
                        'quantity'    => $newQty,
                        'price'       => $price,
                        'category_id' => $category_id,
                    ]);

                    // Log restock
                    ProductRestockLog::create([
                        'product_id'     => $product->id,
                        'old_quantity'   => $oldQty,
                        'added_quantity' => $quantity,
                        'new_quantity'   => $newQty,
                        'user_id'        => auth()->id(),
                    ]);

                } else {

                    // New product
                    Product::create([
                        'name'        => $name,
                        'sku'         => $sku,
                        'quantity'    => $quantity,
                        'price'       => $price,
                        'category_id' => $category_id,
                        'created_by'  => auth()->id(),
                    ]);
                }
            }

        } catch (Throwable $e) {
            dd($e->getMessage());
            return redirect()
                ->route('products.index')
                ->with('error', 'Import failed: ' . $e->getMessage());
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Products imported successfully!');
    }
}
