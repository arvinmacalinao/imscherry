<?php

namespace App\Http\Controllers\Order;

use Exception;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Platform;
use App\Models\ShopName;
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
            'shopNames' => ShopName::with('platform')->get(),
        ] );
    }

    public function store(Request $request)
{   
    $request->validate([
        // 'shop_name_id' => 'required|exists:shopNames,id',
        'file' => 'required|mimetypes:text/plain,text/csv,application/csv,application/vnd.ms-excel,application/octet-stream,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ]);
    
     // Get the shop and derive platform
    $shop = ShopName::findOrFail($request->shop_name_id);
    $shopnameId = $shop->id;

    $platformId = $shop->platform_id;  

    if ($platformId == 1) { // Shopee
    $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $row_limit   = $sheet->getHighestDataRow();
            $row_range   = range(2, $row_limit); // skip header row

            $ordersData = [];

            // Step 1: Collect rows by orderNumber
            foreach ($row_range as $row) {
                $orderNumber      = $sheet->getCell('A' . $row)->getValue(); // Order ID
                if (!$orderNumber) {
                    continue; // skip empty rows
                }

                $ordersData[$orderNumber][] = [
                    'trackingNumber'   => $sheet->getCell('E' . $row)->getValue(),
                    'shippingOption'   => $sheet->getCell('F' . $row)->getValue(),
                    'orderDate'        => $sheet->getCell('J' . $row)->getValue(),
                    'productName'      => $sheet->getCell('M' . $row)->getValue(),
                    'skuReferenceNo'   => $sheet->getCell('N' . $row)->getValue(),
                    'quantity'         => (int) $sheet->getCell('R' . $row)->getValue(),
                    'prodsubtotal'     => (float) $sheet->getCell('T' . $row)->getValue(),
                    'grandTotal'       => (float) $sheet->getCell('AN' . $row)->getValue(),
                    'estimatedShipFee' => (float) $sheet->getCell('AO' . $row)->getValue(),
                    'customerName'     => $sheet->getCell('BE' . $row)->getValue(),
                    'deliveryAddress'  => $sheet->getCell('AS' . $row)->getValue(),
                ];
            }

            // Step 2: Process each order group
            foreach ($ordersData as $orderNumber => $items) {
                // Skip if already exists
                $order = Order::where('order_number', $orderNumber)
                    ->where('platform_id', $platformId)
                    ->first();

                if (!$order) {
                
                   $prefix = $shop->invoice_prefix;
                    // --- Find last invoice for this prefix ---
                    $lastInvoice = Order::withTrashed()
                        ->where('invoice_no', 'like', $prefix . '%')
                        ->orderBy('id', 'desc')
                            ->value('invoice_no');

                    // Extract last 6 digits
                    if ($lastInvoice) {
                        // Get numeric suffix
                        $lastSeq = intval(substr($lastInvoice, strlen($prefix)));
                        $nextSeq = str_pad($lastSeq + 1, 6, '0', STR_PAD_LEFT);
                    } else {
                        // First invoice for this prefix
                        $nextSeq = "000001";
                    }

                    // Build invoice number (no hyphen)
                    $invoiceNo = $prefix . $nextSeq;

                    // Aggregate totals
                    $totalProducts = array_sum(array_column($items, 'quantity'));
                    $grandTotal    = array_sum(array_column($items, 'grandTotal'));
                    $shipFee       = $items[0]['estimatedShipFee'] ?? 0;

                    $order = Order::create([
                        'order_number'        => $orderNumber,
                        'shop_name_id'        => $shopnameId,
                        'invoice_no'          => $invoiceNo,
                        'order_date'          => $items[0]['orderDate'],
                        'total_products'      => $totalProducts,
                        'shipping_fee'        => $shipFee,
                        'total'               => $grandTotal,
                        'platform_id'         => $platformId,
                        'status_id'           => 1, // imported
                        'customer_name'       => $items[0]['customerName'],
                        'shipping_address'    => $items[0]['deliveryAddress'],
                        'tracking_number'     => $items[0]['trackingNumber'],
                        'courier'             => $items[0]['shippingOption'],
                        'platform_created_at' => $items[0]['orderDate'],
                        'created_by'          => Auth::id(),
                    ]);
                }

                // Insert order details
                foreach ($items as $item) {
                    $product = Product::where('sku', $item['skuReferenceNo'])->first();

                    if (!$product) {
                        continue;
                    }

                    OrderDetails::create([
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'sku'        => $item['skuReferenceNo'],
                        'name'       => $item['productName'],
                        'quantity'   => $item['quantity'],
                        'price'      => $product->price,
                    ]);
                }
                Customer::firstOrCreate(
                    [
                        'platform_id' => $platformId,
                        'name'        => $items[0]['customerName'], // use first item’s customer
                    ],
                    [
                        // any extra default fields you want to set on creation
                        'created_by' => Auth::id(),
                    ]
                );
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

        $ordersData = []; // collect grouped orders

        foreach ($row_range as $row) {
            $orderNumber    = $sheet->getCell('M' . $row)->getValue(); // Order ID
            $trackingNumber = $sheet->getCell('BG' . $row)->getValue(); 
            $shippingOption = $sheet->getCell('BI' . $row)->getValue();
            
            $orderDateRaw = $sheet->getCell('I' . $row)->getValue(); // "10 Jul 2025 09:42"
            $orderDate    = null;
                    
            if ($orderDateRaw) {
                try {
                    $orderDate = Carbon::createFromFormat('d M Y H:i', $orderDateRaw)
                        ->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    // fallback if parsing fails
                    $orderDate = now();
                }
            }

            $productName    = $sheet->getCell('AZ' . $row)->getValue(); 
            $skuReferenceNo = $sheet->getCell('F' . $row)->getValue(); 

            $unitPrice = (float) $sheet->getCell('AV' . $row)->getValue(); 
            $paidPrice = (float) $sheet->getCell('AU' . $row)->getValue(); 
            $quantity  = $unitPrice > 0 ? intval(round($paidPrice / $unitPrice)) : 1;

            $estimatedShipFee = (float) $sheet->getCell('AX' . $row)->getValue(); 
            $customerName     = $sheet->getCell('Q' . $row)->getValue(); 
            $deliveryAddress  = $sheet->getCell('AS' . $row)->getValue(); 

            if (!$orderNumber) {
                continue; // skip empty rows
            }

            // init order container
            if (!isset($ordersData[$orderNumber])) {
                $ordersData[$orderNumber] = [
                    'order_number'   => $orderNumber,
                    'order_date'     => $orderDate,
                    'tracking'       => $trackingNumber,
                    'courier'        => $shippingOption,
                    'customer_name'  => $customerName,
                    'address'        => $deliveryAddress,
                    'ship_fee'       => $estimatedShipFee,
                    'total_products' => 0,
                    'grand_total'    => 0,
                    'details'        => [],
                ];
            }

            // add product to order details
            $ordersData[$orderNumber]['details'][] = [
                'sku'      => $skuReferenceNo,
                'name'     => $productName,
                'quantity' => $quantity,
                'price'    => $unitPrice,
            ];

            // accumulate totals
            $ordersData[$orderNumber]['total_products'] += $quantity;
            $ordersData[$orderNumber]['grand_total']    += $paidPrice;
        }

        foreach ($ordersData as $orderNumber => $data) {
            // check if order exists
            $order = Order::where('order_number', $orderNumber)
                ->where('platform_id', $platformId)
                ->first();

            if (!$order) {
                // generate invoice
                $lastInvoice = Order::withTrashed()->where('platform_id', $platformId)
                    ->orderBy('id', 'desc')
                    ->value('invoice_no');

                if ($lastInvoice) {
                    preg_match('/(\d+)$/', $lastInvoice, $matches);
                    $nextNumber = str_pad(((int)$matches[1]) + 1, 7, '0', STR_PAD_LEFT);
                } else {
                    $nextNumber = str_pad(1, 7, '0', STR_PAD_LEFT);
                }
                $invoiceNo = strtoupper("LAZADA-" . $nextNumber);

                $order = Order::create([
                    'order_number'     => $data['order_number'],
                    'invoice_no'       => $invoiceNo,
                    'order_date'       => $data['order_date'],
                    'total_products'   => $data['total_products'],
                    'shipping_fee'     => $data['ship_fee'],
                    'total'            => $data['grand_total'],
                    'platform_id'      => $platformId,
                    'status_id'        => 1,
                    'customer_name'    => $data['customer_name'],
                    'shipping_address' => $data['address'],
                    'tracking_number'  => $data['tracking'],
                    'courier'          => $data['courier'],
                    'platform_created_at' => $data['order_date'],
                    'created_by'       => Auth::id(),
                ]);
            }

            // save order details
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

            Customer::firstOrCreate(
                [
                    'platform_id' => $platformId,
                    'name'        => $customerName,
                ],
                [
                    'created_by' => Auth::id(),
                ]
            );
        }

        } catch (Throwable $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
    if ($platformId == 3) { // tiktok
    $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $row_limit   = $sheet->getHighestDataRow();
            $row_range   = range(2, $row_limit); // skip header row

            $ordersData = [];

            // Step 1: Collect rows by orderNumber
            foreach ($row_range as $row) {
                $orderNumber      = $sheet->getCell('A' . $row)->getValue(); // Order ID
                if (!$orderNumber) {
                    continue; // skip empty rows
                }

                $ordersData[$orderNumber][] = [
                    'trackingNumber'   => $sheet->getCell('AZ' . $row)->getValue(),
                    'shippingOption'   => $sheet->getCell('AK' . $row)->getValue(),
                    'orderDate'        => $sheet->getCell('Y' . $row)->getValue(),
                    'productName'      => $sheet->getCell('H' . $row)->getValue(),
                    'skuReferenceNo'   => $sheet->getCell('G' . $row)->getValue(),
                    'quantity'         => (int) $sheet->getCell('J' . $row)->getValue(),
                    'prodsubtotal'     => (float) $sheet->getCell('P' . $row)->getValue(),
                    'grandTotal'       => (float) $sheet->getCell('W' . $row)->getValue(),
                    'estimatedShipFee' => (float) $sheet->getCell('Q' . $row)->getValue(),
                    'customerName'     => $sheet->getCell('AN' . $row)->getValue(),
                    'deliveryAddress'  => $sheet->getCell('AU' . $row)->getValue(),
                ];
            }

            // Step 2: Process each order group
            foreach ($ordersData as $orderNumber => $items) {
                // Skip if already exists
                $order = Order::where('order_number', $orderNumber)
                    ->where('platform_id', $platformId)
                    ->first();

                if (!$order) {
                    // Generate invoice number
                    $lastInvoice = Order::withTrashed()->where('platform_id', $platformId)
                        ->orderBy('id', 'desc')
                        ->value('invoice_no');

                    if ($lastInvoice) {
                        preg_match('/(\d+)$/', $lastInvoice, $matches);
                        $nextNumber = str_pad(((int)$matches[1]) + 1, 7, '0', STR_PAD_LEFT);
                    } else {
                        $nextNumber = str_pad(1, 7, '0', STR_PAD_LEFT);
                    }

                    $invoiceNo = strtoupper("TIKTOK-" . $nextNumber);

                    // Aggregate totals
                    $totalProducts = array_sum(array_column($items, 'quantity'));
                    $grandTotal    = array_sum(array_column($items, 'grandTotal'));
                    $shipFee       = $items[0]['estimatedShipFee'] ?? 0;

                    $platformDate = $items[0]['orderDate'] 
                                 ? Carbon::createFromFormat('m/d/Y h:i:s A', trim($items[0]['orderDate']))->format('Y-m-d H:i:s') 
                                 : null;

                    $order = Order::create([
                        'order_number'        => $orderNumber,
                        'invoice_no'          => $invoiceNo,
                        'order_date'          => $platformDate,
                        'total_products'      => $totalProducts,
                        'shipping_fee'        => $shipFee,
                        'total'               => $grandTotal,
                        'platform_id'         => $platformId,
                        'status_id'           => 1, // default "processed"
                        'customer_name'       => $items[0]['customerName'],
                        'shipping_address'    => $items[0]['deliveryAddress'],
                        'tracking_number'     => $items[0]['trackingNumber'],
                        'courier'             => $items[0]['shippingOption'],
                        'platform_created_at' => $platformDate,
                        'created_by'          => Auth::id(),
                    ]);
                }

                // Insert order details
                foreach ($items as $item) {
                    $product = Product::firstOrCreate(
                        ['sku' => $item['skuReferenceNo']],
                        [
                            'name'       => $item['productName'],
                            'created_by' => Auth::id(),
                        ]
                    );

                    OrderDetails::create([
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'sku'        => $item['skuReferenceNo'],
                        'name'       => $item['productName'],
                        'quantity'   => $item['quantity'],
                        'price'      => $product->price,
                    ]);
                }
                Customer::firstOrCreate(
                    [
                        'platform_id' => $platformId,
                        'name'        => $items[0]['customerName'], // use first item’s customer
                    ],
                    [
                        // any extra default fields you want to set on creation
                        'created_by' => Auth::id(),
                    ]
                );
            }

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    if ($platformId == 15) { // Zalora
    $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet       = $spreadsheet->getActiveSheet();
            $row_limit   = $sheet->getHighestDataRow();
            $row_range   = range(2, $row_limit); // skip header row

            $ordersData = [];

            // Step 1: Collect rows by orderNumber
            foreach ($row_range as $row) {
                $orderNumber      = $sheet->getCell('G' . $row)->getValue(); // Order ID
                if (!$orderNumber) {
                    continue; // skip empty rows
                }

                $ordersData[$orderNumber][] = [
                    'trackingNumber'   => $sheet->getCell('E' . $row)->getValue(),
                    'shippingOption'   => $sheet->getCell('F' . $row)->getValue(),
                    'orderDate'        => $sheet->getCell('J' . $row)->getValue(),
                    'productName'      => $sheet->getCell('M' . $row)->getValue(),
                    'skuReferenceNo'   => $sheet->getCell('N' . $row)->getValue(),
                    'quantity'         => (int) $sheet->getCell('R' . $row)->getValue(),
                    'prodsubtotal'     => (float) $sheet->getCell('T' . $row)->getValue(),
                    'grandTotal'       => (float) $sheet->getCell('AN' . $row)->getValue(),
                    'estimatedShipFee' => (float) $sheet->getCell('AO' . $row)->getValue(),
                    'customerName'     => $sheet->getCell('BE' . $row)->getValue(),
                    'deliveryAddress'  => $sheet->getCell('AS' . $row)->getValue(),
                ];
            }

            // Step 2: Process each order group
            foreach ($ordersData as $orderNumber => $items) {
                // Skip if already exists
                $order = Order::where('order_number', $orderNumber)
                    ->where('platform_id', $platformId)
                    ->first();

                if (!$order) {
                    // Generate invoice number
                    $lastInvoice = Order::withTrashed()->where('platform_id', $platformId)
                        ->orderBy('id', 'desc')
                        ->value('invoice_no');

                    if ($lastInvoice) {
                        preg_match('/(\d+)$/', $lastInvoice, $matches);
                        $nextNumber = str_pad(((int)$matches[1]) + 1, 7, '0', STR_PAD_LEFT);
                    } else {
                        $nextNumber = str_pad(1, 7, '0', STR_PAD_LEFT);
                    }

                    $invoiceNo = strtoupper("SHOPEE-" . $nextNumber);

                    // Aggregate totals
                    $totalProducts = array_sum(array_column($items, 'quantity'));
                    $grandTotal    = array_sum(array_column($items, 'grandTotal'));
                    $shipFee       = $items[0]['estimatedShipFee'] ?? 0;

                    $order = Order::create([
                        'order_number'        => $orderNumber,
                        'invoice_no'          => $invoiceNo,
                        'order_date'          => $items[0]['orderDate'],
                        'total_products'      => $totalProducts,
                        'shipping_fee'        => $shipFee,
                        'total'               => $grandTotal,
                        'platform_id'         => $platformId,
                        'status_id'           => 1, // default "processed"
                        'customer_name'       => $items[0]['customerName'],
                        'shipping_address'    => $items[0]['deliveryAddress'],
                        'tracking_number'     => $items[0]['trackingNumber'],
                        'courier'             => $items[0]['shippingOption'],
                        'platform_created_at' => $items[0]['orderDate'],
                        'created_by'          => Auth::id(),
                    ]);
                }

                // Insert order details
                foreach ($items as $item) {
                    $product = Product::firstOrCreate(
                        ['sku' => $item['skuReferenceNo']],
                        [
                            'name'       => $item['productName'],
                            'created_by' => Auth::id(),
                        ]
                    );

                    OrderDetails::create([
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'sku'        => $item['skuReferenceNo'],
                        'name'       => $item['productName'],
                        'quantity'   => $item['quantity'],
                        'price'      => $product->price,
                    ]);
                }
                Customer::firstOrCreate(
                    [
                        'platform_id' => $platformId,
                        'name'        => $items[0]['customerName'], // use first item’s customer
                    ],
                    [
                        // any extra default fields you want to set on creation
                        'created_by' => Auth::id(),
                    ]
                );
            }

        } catch (Throwable $e) {
            return redirect()
                ->back()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }
    // add another platform validation

       return redirect()
            ->route('orders.index')
            ->with('success', 'Orders imported successfully!');
    }


}
