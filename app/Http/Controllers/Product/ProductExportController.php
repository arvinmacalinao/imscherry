<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class ProductExportController extends Controller
{
    public function create()
    {
        $products = Product::all()->sortBy('product_name');

        $product_array [] = array(
            'Product Name',
            'Category',
            'SKU Code',
            'Stock',
            'Selling Price',
        );

        foreach($products as $product)
        {
            $product_array[] = array(
                'Product Name' => $product->name,
                'Category Id' => $product->category->name,
                'SKU Code' => $product->sku,
                'Stock' => $product->quantity,
                'Selling Price' =>$product->price,
            );
        }

        $this->store($product_array);
    }

    public function store($products)
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '4000M');

        try {
            $spreadSheet = new Spreadsheet();
            $spreadSheet->getActiveSheet()->getDefaultColumnDimension()->setWidth(20);
            $spreadSheet->getActiveSheet()->fromArray($products);
            $Excel_writer = new Xls($spreadSheet);
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="products.xls"');
            header('Cache-Control: max-age=0');
            ob_end_clean();
            $Excel_writer->save('php://output');
            exit();
        } catch (Exception $e) {
            return;
        }
    }
}
