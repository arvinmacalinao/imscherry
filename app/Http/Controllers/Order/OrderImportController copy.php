<?php

namespace App\Http\Controllers\Order;

use Exception;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Platform;
use App\Models\OrderDetails;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class OrderImportController extends Controller
{
    public function create()
    {
        return view('orders.import', [
            'platforms' => Platform::all(),
        ] );
    }

    public function store(Request $request)
{
    $request->validate([
        'platform_id' => 'required|exists:platforms,id',
        'file' => 'required|mimes:xlsx,csv'
    ]);

    $platformId = $request->platform_id;

    if ($platformId == 1) { // Shopee

   
        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $row_limit   = $sheet->getHighestDataRow();
            $row_range   = range(2, $row_limit); // skip header row

            foreach ($row_range as $row) {
                // Map Shopee columns
                $orderNumber      = $sheet->getCell('A' . $row)->getValue(); // Order ID
                $trackingNumber   = $sheet->getCell('E' . $row)->getValue(); // Tracking Number
                $shippingOption   = $sheet->getCell('F' . $row)->getValue(); // Shipping Option
                $orderDate        = $sheet->getCell('J' . $row)->getValue(); // Order Creation Date
                $productName      = $sheet->getCell('M' . $row)->getValue(); // Product Name
                $skuReferenceNo   = $sheet->getCell('N' . $row)->getValue(); // SKU
                $quantity         = (int) $sheet->getCell('R' . $row)->getValue(); // Quantity
                $totalProducts    = (int) $sheet->getCell('Y' . $row)->getValue(); // Number of items
                $prodsubtotal     = (float) $sheet->getCell('T' . $row)->getValue(); // Grand Total
                $grandTotal       = (float) $sheet->getCell('AN' . $row)->getValue(); // Grand Total
                $estimatedShipFee = (float) $sheet->getCell('AO' . $row)->getValue(); // Estimated Shipping Fee
                $customerName     = $sheet->getCell('BE' . $row)->getValue(); // Receiver Name
                $deliveryAddress  = $sheet->getCell('AS' . $row)->getValue(); // Delivery Address
    

                if (!$orderNumber) {
                    continue; // skip empty rows
                }

                // Check if order already exists
                $order = Order::where('order_number', $orderNumber)
                    ->where('platform_id', 1) // Shopee
                    ->first();

                if (!$order) {
                    // Generate invoice number
                    $lastInvoice = Order::where('platform_id', 1)
                        ->orderBy('id', 'desc')
                        ->value('invoice_no');

                    if ($lastInvoice) {
                        preg_match('/(\d+)$/', $lastInvoice, $matches);
                        $nextNumber = str_pad(((int)$matches[1]) + 1, 7, '0', STR_PAD_LEFT);
                    } else {
                        $nextNumber = str_pad(1, 7, '0', STR_PAD_LEFT);
                    }

                    $invoiceNo = strtoupper("SHOPEE-" . $nextNumber);

                    // Create Order
                    $order = Order::create([
                        'order_number'        => $orderNumber,
                        'invoice_no'          => $invoiceNo,
                        'order_date'          => $orderDate,
                        'total_products'      => $totalProducts,
                        'shipping_fee'        => $estimatedShipFee,
                        'total'               => $grandTotal,
                        'platform_id'         => $platformId,
                        'status_id'           => 1, // default "processed"
                        'customer_name'       => $customerName,
                        'shipping_address'    => $deliveryAddress,
                        'tracking_number'     => $trackingNumber,
                        'courier'             => $shippingOption,
                        'platform_created_at' => $orderDate,
                        'created_by'          => Auth::id(),
                    ]);
                }

                // Find or create product by SKU
                $product = Product::firstOrCreate(
                    ['sku' => $skuReferenceNo],
                    [
                        'name'       => $productName,
                        'created_by' => Auth::id(),
                    ]
                );

                // Insert Order Detail
                OrderDetails::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'sku'        => $skuReferenceNo,
                    'name'       => $productName,
                    'quantity'   => $quantity,
                    'price'      => $product->price,
                ]);
                
            }

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }
        else if ($platformId == 2) {
        $file = $request->file('file');
        
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $row_limit   = $sheet->getHighestDataRow();
            $row_range   = range(2, $row_limit); // skip header row
        
            $ordersData = []; // grouped orders
        
            foreach ($row_range as $row) {
                $orderNumber    = trim($sheet->getCell('M' . $row)->getValue()); // Lazada Order Number
                $trackingNumber = trim($sheet->getCell('BG' . $row)->getValue());
                $shippingOption = trim($sheet->getCell('BI' . $row)->getValue());
            
                $orderDateRaw = $sheet->getCell('I' . $row)->getValue(); // e.g. "08 Sep 2025 14:25"
                $orderDate = null;
            
                if ($orderDateRaw) {
                    try {
                        $orderDate = Carbon::createFromFormat('d M Y H:i', $orderDateRaw)
                            ->format('Y-m-d H:i:s');
                    } catch (Exception $e) {
                        $orderDate = now();
                    }
                }
            
                $productName    = trim($sheet->getCell('AZ' . $row)->getValue());
                $skuReferenceNo = trim($sheet->getCell('F' . $row)->getValue());
            
                $unitPrice      = (float) $sheet->getCell('AV' . $row)->getValue();
                $paidPrice      = (float) $sheet->getCell('AU' . $row)->getValue();
                $shipFee        = (float) $sheet->getCell('AX' . $row)->getValue();
                $customerName   = trim($sheet->getCell('Q' . $row)->getValue());
                $deliveryAddr   = trim($sheet->getCell('AS' . $row)->getValue());
            
                if (!$orderNumber) {
                    continue;
                }
            
                // Initialize order container
                if (!isset($ordersData[$orderNumber])) {
                    $ordersData[$orderNumber] = [
                        'order_number'   => $orderNumber,
                        'order_date'     => $orderDate,
                        'tracking'       => $trackingNumber,
                        'courier'        => $shippingOption,
                        'customer_name'  => $customerName,
                        'address'        => $deliveryAddr,
                        'ship_fee'       => $shipFee,
                        'total_products' => 0,
                        'grand_total'    => 0,
                        'details'        => [],
                    ];
                }
            
                // Group identical SKUs under same order
                $key = $skuReferenceNo . '|' . $unitPrice;
            
                if (!isset($ordersData[$orderNumber]['details'][$key])) {
                    $ordersData[$orderNumber]['details'][$key] = [
                        'sku'      => $skuReferenceNo,
                        'name'     => $productName,
                        'quantity' => 1, // each row = 1 item
                        'price'    => $unitPrice,
                        'paid'     => $paidPrice,
                    ];
                } else {
                    $ordersData[$orderNumber]['details'][$key]['quantity']++;
                    $ordersData[$orderNumber]['details'][$key]['paid'] += $paidPrice;
                }
            
                // Accumulate totals
                $ordersData[$orderNumber]['total_products']++;
                $ordersData[$orderNumber]['grand_total'] += $paidPrice;
            }
        
            // === Save to DB ===
            foreach ($ordersData as $orderNumber => $data) {
                $order = Order::where('order_number', $orderNumber)
                    ->where('platform_id', $platformId)
                    ->first();
            
                if (!$order) {
                    // Generate invoice number
                    $lastInvoice = Order::where('platform_id', $platformId)
                        ->orderByDesc('id')
                        ->value('invoice_no');
                
                    if ($lastInvoice && preg_match('/(\d+)$/', $lastInvoice, $matches)) {
                        $nextNumber = str_pad(((int)$matches[1]) + 1, 7, '0', STR_PAD_LEFT);
                    } else {
                        $nextNumber = str_pad(1, 7, '0', STR_PAD_LEFT);
                    }
                
                    $invoiceNo = strtoupper("LAZADA-" . $nextNumber);
                
                    $order = Order::create([
                        'order_number'        => $data['order_number'],
                        'invoice_no'          => $invoiceNo,
                        'order_date'          => $data['order_date'],
                        'total_products'      => $data['total_products'],
                        'shipping_fee'        => $data['ship_fee'],
                        'total'               => $data['grand_total'],
                        'platform_id'         => $platformId,
                        'status_id'           => 1,
                        'customer_name'       => $data['customer_name'],
                        'shipping_address'    => $data['address'],
                        'tracking_number'     => $data['tracking'],
                        'courier'             => $data['courier'],
                        'platform_created_at' => $data['order_date'],
                        'created_by'          => Auth::id(),
                    ]);
                }
            
                // Save products
                foreach ($data['details'] as $detail) {
                    $product = Product::firstOrCreate(
                        ['sku' => $detail['sku']],
                        [
                            'name'       => $detail['name'],
                            'created_by' => Auth::id(),
                        ]
                    );
                
                    OrderDetails::create([
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'sku'        => $detail['sku'],
                        'name'       => $detail['name'],
                        'quantity'   => $detail['quantity'],
                        'price'      => $detail['price'],
                    ]);
                }
            
                // Save customer (fixed the $items bug)
                Customer::firstOrCreate(
                    [
                        'platform_id' => $platformId,
                        'name'        => $data['customer_name'],
                    ],
                    [
                        'created_by' => Auth::id(),
                    ]
                );
            }
        
            return redirect()->back()->with('success', 'Lazada orders imported successfully.');
        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    // else if ($platformId == 2) {
    // $file = $request->file('file');

    // try {
    //     $spreadsheet = IOFactory::load($file->getRealPath());
    //     $sheet       = $spreadsheet->getActiveSheet();
    //     $row_limit   = $sheet->getHighestDataRow();
    //     $row_range   = range(2, $row_limit); // skip header row

    //     $ordersData = []; // collect grouped orders

    //     foreach ($row_range as $row) {
    //         $orderNumber    = $sheet->getCell('M' . $row)->getValue(); // Order ID
    //         $trackingNumber = $sheet->getCell('BG' . $row)->getValue(); 
    //         $shippingOption = $sheet->getCell('BI' . $row)->getValue();
            
    //         $orderDateRaw = $sheet->getCell('I' . $row)->getValue(); // "10 Jul 2025 09:42"
    //         $orderDate    = null;
                    
    //         if ($orderDateRaw) {
    //             try {
    //                 $orderDate = Carbon::createFromFormat('d M Y H:i', $orderDateRaw)
    //                     ->format('Y-m-d H:i:s');
    //             } catch (Exception $e) {
    //                 // fallback if parsing fails
    //                 $orderDate = now();
    //             }
    //         }

    //         $productName    = $sheet->getCell('AZ' . $row)->getValue(); 
    //         $skuReferenceNo = $sheet->getCell('F' . $row)->getValue(); 

    //         $unitPrice = (float) $sheet->getCell('AV' . $row)->getValue(); 
    //         $paidPrice = (float) $sheet->getCell('AU' . $row)->getValue(); 
    //         $quantity  = $unitPrice > 0 ? intval(round($paidPrice / $unitPrice)) : 1;

    //         $estimatedShipFee = (float) $sheet->getCell('AX' . $row)->getValue(); 
    //         $customerName     = $sheet->getCell('Q' . $row)->getValue(); 
    //         $deliveryAddress  = $sheet->getCell('AS' . $row)->getValue(); 

    //         if (!$orderNumber) {
    //             continue; // skip empty rows
    //         }

    //         // init order container
    //         if (!isset($ordersData[$orderNumber])) {
    //             $ordersData[$orderNumber] = [
    //                 'order_number'   => $orderNumber,
    //                 'order_date'     => $orderDate,
    //                 'tracking'       => $trackingNumber,
    //                 'courier'        => $shippingOption,
    //                 'customer_name'  => $customerName,
    //                 'address'        => $deliveryAddress,
    //                 'ship_fee'       => $estimatedShipFee,
    //                 'total_products' => 0,
    //                 'grand_total'    => 0,
    //                 'details'        => [],
    //             ];
    //         }

    //         // add product to order details
    //         $ordersData[$orderNumber]['details'][] = [
    //             'sku'      => $skuReferenceNo,
    //             'name'     => $productName,
    //             'quantity' => $quantity,
    //             'price'    => $unitPrice,
    //         ];

    //         // accumulate totals
    //         $ordersData[$orderNumber]['total_products'] += $quantity;
    //         $ordersData[$orderNumber]['grand_total']    += $paidPrice;
    //     }

    //     foreach ($ordersData as $orderNumber => $data) {
    //         // check if order exists
    //         $order = Order::where('order_number', $orderNumber)
    //             ->where('platform_id', $platformId)
    //             ->first();

    //         if (!$order) {
    //             // generate invoice
    //             $lastInvoice = Order::where('platform_id', $platformId)
    //                 ->orderBy('id', 'desc')
    //                 ->value('invoice_no');

    //             if ($lastInvoice) {
    //                 preg_match('/(\d+)$/', $lastInvoice, $matches);
    //                 $nextNumber = str_pad(((int)$matches[1]) + 1, 7, '0', STR_PAD_LEFT);
    //             } else {
    //                 $nextNumber = str_pad(1, 7, '0', STR_PAD_LEFT);
    //             }
    //             $invoiceNo = strtoupper("LAZADA-" . $nextNumber);

    //             $order = Order::create([
    //                 'order_number'     => $data['order_number'],
    //                 'invoice_no'       => $invoiceNo,
    //                 'order_date'       => $data['order_date'],
    //                 'total_products'   => $data['total_products'],
    //                 'shipping_fee'     => $data['ship_fee'],
    //                 'total'            => $data['grand_total'],
    //                 'platform_id'      => $platformId,
    //                 'status_id'        => 1,
    //                 'customer_name'    => $data['customer_name'],
    //                 'shipping_address' => $data['address'],
    //                 'tracking_number'  => $data['tracking'],
    //                 'courier'          => $data['courier'],
    //                 'platform_created_at' => $data['order_date'],
    //                 'created_by'       => Auth::id(),
    //             ]);
    //         }

    //         // save order details
    //         foreach ($data['details'] as $detail) {
    //             $product = Product::firstOrCreate(
    //                 ['sku' => $detail['sku']],
    //                 [
    //                     'name'       => $detail['name'],
    //                     'created_by' => Auth::id(),
    //                 ]
    //             );

    //             OrderDetails::create([
    //                 'order_id'   => $order->id,
    //                 'product_id' => $product->id,
    //                 'sku'        => $detail['sku'],
    //                 'name'       => $detail['name'],
    //                 'quantity'   => $detail['quantity'],
    //                 'price'      => $detail['price'],
    //             ]);
    //         }

    //         Customer::firstOrCreate(
    //             [
    //                 'platform_id' => $platformId,
    //                 'name'        => $items[0]['customer_name'], // use first item’s customer
    //             ],
    //             [
    //                 // any extra default fields you want to set on creation
    //                 'created_by' => Auth::id(),
    //             ]
    //         );
    //     }

    //     } catch (Throwable $e) {
    //         return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    //     }
    // }
    // add another platform validation

       return redirect()
            ->route('orders.index')
            ->with('success', 'Orders imported successfully!');
    }


}
