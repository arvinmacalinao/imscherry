<?php

namespace App\Http\Controllers\Product;

use Exception;
use Throwable;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\ProductRestockLog;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;

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
            $row_range    = range(2, $row_limit);

            foreach ($row_range as $row) {
                $name        = $sheet->getCell('A' . $row)->getValue();
                $category_id = $sheet->getCell('B' . $row)->getValue();
                $sku         = $sheet->getCell('C' . $row)->getValue();
                $quantity    = (int) $sheet->getCell('D' . $row)->getValue();
                $price       = (float) $sheet->getCell('E' . $row)->getValue();

                if (!$sku) {
                    continue; // skip if no SKU
                }

                // Check if product exists
                $product = Product::where('sku', $sku)->first();

                if ($product) {
                    $oldQty = $product->quantity;
                    $newQty = $oldQty + $quantity;

                    // Update product
                    $product->update([
                        'quantity' => $newQty,
                        'price'    => $price, // optional: update price too
                        'category_id' => $category_id,
                    ]);

                    // Log restock
                    ProductRestockLog::create([
                        'product_id'     => $product->id,
                        'old_quantity'   => $oldQty,
                        'added_quantity' => $quantity,
                        'new_quantity'   => $newQty,
                        'user_id'        => auth()->id(), // track who did the import
                    ]);

                } else {
                    // Create new product
                    $product = Product::create([
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
            return redirect()
                ->route('products.index')
                ->with('error', 'Error: ' . $e->getMessage());
        }

        return redirect()
            ->route('products.index')
            ->with('success', 'Products imported successfully!');
    }
}
