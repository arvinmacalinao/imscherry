<?php

namespace App\Http\Controllers\Order;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetails;
use App\Models\Platform;
use App\Models\Product;
use App\Models\ShopName;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Throwable;

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

            Log::info('Shopee import started');

            try {

                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet       = $spreadsheet->getActiveSheet();

                // =========================
                // VALIDATE HEADERS
                // =========================
                $requiredHeaders = [
                    'A' => 'Order ID',
                    'M' => 'Product Name',
                    'N' => 'SKU Reference No',
                    'Q' => 'Deal Price',
                    'R' => 'Quantity'
                ];

                foreach ($requiredHeaders as $column => $expected) {

                    $value = trim($sheet->getCell($column.'1')->getValue());

                    Log::info('Header check', [
                        'column' => $column,
                        'expected' => $expected,
                        'found' => $value
                    ]);

                    if (stripos($value, $expected) === false) {
                        return redirect()
                            ->back()
                            ->with('error', 'Invalid Shopee file uploaded.');
                    }
                }

                $row_limit = $sheet->getHighestDataRow();
                $row_range = range(2, $row_limit);

                $ordersData = [];

                // =========================
                // STEP 1 — COLLECT DATA
                // =========================
                foreach ($row_range as $row) {

                    $orderNumber = $sheet->getCell('A' . $row)->getValue();
                    if (!$orderNumber) continue;

                    $ordersData[$orderNumber][] = [
                        'trackingNumber'   => $sheet->getCell('E' . $row)->getValue(),
                        'shippingOption'   => $sheet->getCell('F' . $row)->getValue(),
                        'orderDate'        => $sheet->getCell('J' . $row)->getValue(),
                        'productName'      => $sheet->getCell('M' . $row)->getValue(),
                        'skuReferenceNo'   => $sheet->getCell('N' . $row)->getValue(),
                        'unitPrice'        => (float) $sheet->getCell('Q' . $row)->getValue(),
                        'quantity'         => (int) $sheet->getCell('R' . $row)->getValue(),
                        'estimatedShipFee' => (float) $sheet->getCell('AO' . $row)->getValue(),
                        'customerName'     => $sheet->getCell('BE' . $row)->getValue(),
                        'deliveryAddress'  => $sheet->getCell('AS' . $row)->getValue(),
                    ];
                }

                Log::info('Collected orders', ['count' => count($ordersData)]);

                // =========================
                // PRELOAD DATA
                // =========================
                Log::info('Preloading product SKUs');
                $products = Product::pluck('id', 'sku');

                Log::info('Preloading existing orders');
                $existingOrders = Order::where('platform_id', $platformId)
                    ->pluck('id', 'order_number');

                // =========================
                // TRACK RESULTS
                // =========================
                $skippedOrders   = [];
                $duplicateOrders = [];
                $missingSkus     = [];
                $importedOrders  = 0;

                // =========================
                // STEP 2 — PROCESS ORDERS
                // =========================
                foreach ($ordersData as $orderNumber => $items) {

                    Log::info('Processing order', ['orderNumber' => $orderNumber]);

                    // =========================
                    // CHECK DUPLICATE ORDER
                    // =========================
                    if (isset($existingOrders[$orderNumber])) {

                        Log::warning('Duplicate order skipped', [
                            'orderNumber' => $orderNumber
                        ]);

                        $skippedOrders[]   = $orderNumber;
                        $duplicateOrders[] = $orderNumber;

                        continue;
                    }

                    // =========================
                    // VALIDATE SKUs
                    // =========================
                    $invalidSku = false;

                    foreach ($items as $item) {

                        if (!isset($products[$item['skuReferenceNo']])) {

                            Log::warning('Product not found', [
                                'order' => $orderNumber,
                                'sku'   => $item['skuReferenceNo']
                            ]);

                            $invalidSku = true;
                            $missingSkus[] = $item['skuReferenceNo'];
                        }
                    }

                    if ($invalidSku) {

                        Log::warning('Order skipped due to missing SKU', [
                            'orderNumber' => $orderNumber
                        ]);

                        $skippedOrders[] = $orderNumber;

                        continue;
                    }

                    // =========================
                    // CREATE ORDER
                    // =========================
                    Log::info('Creating new order');

                    $prefix = $shop->invoice_prefix;

                    $lastInvoice = Order::withTrashed()
                        ->where('invoice_no', 'like', $prefix . '%')
                        ->orderBy('id', 'desc')
                        ->value('invoice_no');

                    if ($lastInvoice) {
                        $lastSeq = intval(substr($lastInvoice, strlen($prefix)));
                        $nextSeq = str_pad($lastSeq + 1, 6, '0', STR_PAD_LEFT);
                    } else {
                        $nextSeq = "000001";
                    }

                    $invoiceNo = $prefix . $nextSeq;

                    $totalProducts = array_sum(array_column($items, 'quantity'));

                    $grandTotal = 0;
                    foreach ($items as $it) {
                        $grandTotal += $it['unitPrice'] * $it['quantity'];
                    }

                    $shipFee = $items[0]['estimatedShipFee'] ?? 0;

                    $order = Order::create([
                        'order_number'        => $orderNumber,
                        'shop_name_id'        => $shopnameId,
                        'invoice_no'          => $invoiceNo,
                        'order_date'          => $items[0]['orderDate'],
                        'total_products'      => $totalProducts,
                        'shipping_fee'        => $shipFee,
                        'total'               => $grandTotal,
                        'platform_id'         => $platformId,
                        'status_id'           => 1,
                        'customer_name'       => $items[0]['customerName'],
                        'shipping_address'    => $items[0]['deliveryAddress'],
                        'tracking_number'     => $items[0]['trackingNumber'],
                        'courier'             => $items[0]['shippingOption'],
                        'platform_created_at' => $items[0]['orderDate'],
                        'created_by'          => Auth::id(),
                    ]);

                    Log::info('Order created', ['order_id' => $order->id]);

                    $order->statusLogs()->create([
                        'status_id' => 1,
                        'acted_by'  => auth()->id(),
                        'remarks'   => 'Imported Shopee Order Successfully',
                    ]);

                    // =========================
                    // INSERT DETAILS (SAFE)
                    // =========================
                    foreach ($items as $item) {

                        $productId = $products[$item['skuReferenceNo']];

                        OrderDetails::create([
                            'order_id'   => $order->id,
                            'product_id' => $productId,
                            'sku'        => $item['skuReferenceNo'],
                            'name'       => $item['productName'],
                            'quantity'   => $item['quantity'],
                            'price'      => $item['unitPrice'],
                        ]);
                    }

                    // =========================
                    // CUSTOMER RECORD
                    // =========================
                    Customer::firstOrCreate(
                        [
                            'platform_id' => $platformId,
                            'name'        => $items[0]['customerName'],
                        ],
                        [
                            'created_by' => Auth::id(),
                        ]
                    );

                    $importedOrders++;
                }

                Log::info('Shopee import finished', [
                    'imported' => $importedOrders,
                    'skipped'  => count($skippedOrders),
                    'duplicates' => count($duplicateOrders),
                    'missing_skus' => array_unique($missingSkus)
                ]);

                return redirect()
                    ->route('orders.index')
                    ->with('success',
                        "Shopee Import Completed. Imported: {$importedOrders}, Skipped: ".count($skippedOrders).", Duplicates: ".count($duplicateOrders)
                    )
                    ->with('skipped_orders', $skippedOrders)
                    ->with('duplicate_orders', $duplicateOrders)
                    ->with('missing_skus', array_unique($missingSkus));

            } catch (Throwable $e) {

                Log::error('Shopee import error', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);

                return redirect()
                    ->back()
                    ->with('error', 'Error: ' . $e->getMessage());
            }
        }

        if ($platformId == 3) { // TikTok

            Log::info('TikTok import started');

            $file = $request->file('file');

            try {

                Log::info('Loading spreadsheet');

                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet       = $spreadsheet->getActiveSheet();
                $row_limit   = $sheet->getHighestDataRow();
                $row_range   = range(2, $row_limit);

                $ordersData = [];

                // =========================
                // STEP 1 — COLLECT DATA
                // =========================
                foreach ($row_range as $row) {

                    $orderNumber = $sheet->getCell('A' . $row)->getValue();
                    if (!$orderNumber) continue;

                    $subtotalBeforeDiscount = (float) $sheet->getCell('M' . $row)->getValue();
                    $sellerDiscount         = (float) $sheet->getCell('O' . $row)->getValue();
                    $quantity               = (int)   $sheet->getCell('J' . $row)->getValue();

                    $netSubtotal = $subtotalBeforeDiscount - $sellerDiscount;

                    $unitPrice = $quantity > 0
                        ? $netSubtotal / $quantity
                        : 0;

                    $ordersData[$orderNumber][] = [
                        'trackingNumber'   => $sheet->getCell('AI' . $row)->getValue(),
                        'shippingOption'   => $sheet->getCell('AK' . $row)->getValue(),
                        'orderDate'        => $sheet->getCell('Y' . $row)->getValue(),
                        'productName'      => $sheet->getCell('H' . $row)->getValue(),
                        'skuReferenceNo'   => $sheet->getCell('F' . $row)->getValue(),
                        'quantity'         => $quantity,
                        'unitPrice'        => $unitPrice,
                        'lineTotal'        => $netSubtotal,
                        'estimatedShipFee' => (float) $sheet->getCell('Q' . $row)->getValue(),
                        'customerName'     => $sheet->getCell('AN' . $row)->getValue(),
                        'deliveryAddress'  => $sheet->getCell('AR' . $row)->getValue(),
                        'paymenttype'      => $sheet->getCell('AW' . $row)->getValue(),
                    ];
                }

                Log::info('Collected orders', ['count' => count($ordersData)]);

                // =========================
                // PRELOAD DATA
                // =========================
                Log::info('Preloading product SKUs');
                $products = Product::pluck('id', 'sku');

                Log::info('Preloading existing orders');
                $existingOrders = Order::where('platform_id', $platformId)
                    ->pluck('id', 'order_number');

                // =========================
                // TRACK RESULTS
                // =========================
                $skippedOrders   = [];
                $duplicateOrders = [];
                $missingSkus     = [];
                $importedOrders  = 0;

                // =========================
                // STEP 2 — PROCESS ORDERS
                // =========================
                foreach ($ordersData as $orderNumber => $items) {

                    Log::info('Processing order', ['orderNumber' => $orderNumber]);

                    // =========================
                    // DUPLICATE CHECK
                    // =========================
                    if (isset($existingOrders[$orderNumber])) {

                        Log::warning('Duplicate order skipped', [
                            'orderNumber' => $orderNumber
                        ]);

                        $skippedOrders[]   = $orderNumber;
                        $duplicateOrders[] = $orderNumber;

                        continue;
                    }

                    // =========================
                    // SKU VALIDATION
                    // =========================
                    $invalidSku = false;

                    foreach ($items as $item) {

                        if (!isset($products[$item['skuReferenceNo']])) {

                            Log::warning('Product not found', [
                                'order' => $orderNumber,
                                'sku'   => $item['skuReferenceNo']
                            ]);

                            $invalidSku = true;
                            $missingSkus[] = $item['skuReferenceNo'];
                        }
                    }

                    if ($invalidSku) {

                        Log::warning('Order skipped due to missing SKU', [
                            'orderNumber' => $orderNumber
                        ]);

                        $skippedOrders[] = $orderNumber;

                        continue;
                    }

                    // =========================
                    // CREATE ORDER
                    // =========================
                    Log::info('Creating new order');

                    $prefix = $shop->invoice_prefix;

                    $lastInvoice = Order::withTrashed()
                        ->where('invoice_no', 'like', $prefix . '%')
                        ->orderBy('id', 'desc')
                        ->value('invoice_no');

                    $nextSeq = $lastInvoice
                        ? str_pad(intval(substr($lastInvoice, strlen($prefix))) + 1, 6, '0', STR_PAD_LEFT)
                        : "000001";

                    $invoiceNo = $prefix . $nextSeq;

                    $totalProducts = array_sum(array_column($items, 'quantity'));

                    $grandTotal = 0;
                    foreach ($items as $it) {
                        $grandTotal += $it['lineTotal'];
                    }

                    $shipFee = $items[0]['estimatedShipFee'] ?? 0;

                    $order = Order::create([
                        'order_number'        => $orderNumber,
                        'shop_name_id'        => $shopnameId,
                        'invoice_no'          => $invoiceNo,
                        'order_date'          => $items[0]['orderDate'],
                        'total_products'      => $totalProducts,
                        'shipping_fee'        => $shipFee,
                        'total'               => $grandTotal,
                        'platform_id'         => $platformId,
                        'status_id'           => 1,
                        'customer_name'       => $items[0]['customerName'],
                        'shipping_address'    => $items[0]['deliveryAddress'],
                        'tracking_number'     => $items[0]['trackingNumber'],
                        'courier'             => $items[0]['shippingOption'],
                        'platform_created_at' => Carbon::parse($items[0]['orderDate']),
                        'created_by'          => Auth::id(),
                        'payment_type'        => $items[0]['paymenttype'],
                    ]);

                    Log::info('Order created', ['order_id' => $order->id]);

                    $order->statusLogs()->create([
                        'status_id' => 1,
                        'acted_by'  => auth()->id(),
                        'remarks'   => 'Imported Order Successfully',
                    ]);

                    // =========================
                    // INSERT DETAILS
                    // =========================
                    foreach ($items as $item) {

                        $productId = $products[$item['skuReferenceNo']];

                        OrderDetails::create([
                            'order_id'   => $order->id,
                            'product_id' => $productId,
                            'sku'        => $item['skuReferenceNo'],
                            'name'       => $item['productName'],
                            'quantity'   => $item['quantity'],
                            'price'      => $item['unitPrice'],
                        ]);
                    }

                    // =========================
                    // CUSTOMER
                    // =========================
                    Customer::firstOrCreate(
                        [
                            'platform_id' => $platformId,
                            'name'        => $items[0]['customerName'],
                        ],
                        [
                            'created_by' => Auth::id(),
                        ]
                    );

                    $importedOrders++;
                }

                Log::info('TikTok import finished', [
                    'imported' => $importedOrders,
                    'skipped'  => count($skippedOrders),
                    'duplicates' => count($duplicateOrders),
                    'missing_skus' => array_unique($missingSkus)
                ]);

                return redirect()
                    ->route('orders.index')
                    ->with('success',
                        "TikTok Import Completed. Imported: {$importedOrders}, Skipped: ".count($skippedOrders).", Duplicates: ".count($duplicateOrders)
                    )
                    ->with('skipped_orders', $skippedOrders)
                    ->with('duplicate_orders', $duplicateOrders)
                    ->with('missing_skus', array_unique($missingSkus));

            } catch (Throwable $e) {

                Log::error('TikTok import error', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);

                return back()->with('error', $e->getMessage());
            }
        }

        if ($platformId == 2) { // Lazada

            Log::info('Lazada import started');

            $file = $request->file('file');

            try {

                // -------------------------
                // LOAD SPREADSHEET
                // -------------------------
                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet       = $spreadsheet->getActiveSheet();
                $row_limit   = $sheet->getHighestDataRow();
                $row_range   = range(2, $row_limit);

                $ordersData = [];

                // =========================
                // STEP 1 — COLLECT ROWS
                // =========================
                foreach ($row_range as $row) {

                    $orderNumber = trim($sheet->getCell('M' . $row)->getValue());
                    if (!$orderNumber) continue;

                    $unitPrice = (float) $sheet->getCell('AV' . $row)->getValue();

                    $ordersData[$orderNumber][] = [
                        'trackingNumber'   => trim($sheet->getCell('BG' . $row)->getValue()),
                        'shippingOption'   => trim($sheet->getCell('BC' . $row)->getValue()),
                        'orderDate'        => $sheet->getCell('I' . $row)->getValue(),
                        'productName'      => trim($sheet->getCell('AZ' . $row)->getValue()),
                        'skuReferenceNo'   => trim($sheet->getCell('F' . $row)->getValue()),
                        'quantity'         => 1, // Lazada: 1 row = 1 qty
                        'unitPrice'        => $unitPrice,
                        'lineTotal'        => $unitPrice,
                        'shipFee'          => (float) $sheet->getCell('AX' . $row)->getValue(),
                        'customerName'     => trim($sheet->getCell('Q' . $row)->getValue()),
                        'deliveryAddress'  => trim($sheet->getCell('AR' . $row)->getValue()),
                        'paymentType'      => trim($sheet->getCell('AT' . $row)->getValue()),
                    ];
                }

                Log::info('Collected Lazada orders', ['count' => count($ordersData)]);

                // =========================
                // PRELOAD DATA
                // =========================
                Log::info('Preloading product SKUs');
                $products = Product::pluck('id', 'sku');

                Log::info('Preloading existing orders');
                $existingOrders = Order::where('platform_id', $platformId)
                    ->pluck('id', 'order_number');

                // =========================
                // TRACK RESULTS
                // =========================
                $skippedOrders   = [];
                $duplicateOrders = [];
                $missingSkus     = [];
                $importedOrders  = 0;

                // =========================
                // STEP 2 — PROCESS ORDERS
                // =========================
                foreach ($ordersData as $orderNumber => $items) {

                    // Lazada splits by shipment
                    $shipments = collect($items)->groupBy('trackingNumber');
                    $totalShipments = $shipments->count();

                    $index = 0;

                    foreach ($shipments as $trackingNumber => $shipmentItems) {

                        // -------------------------
                        // SUFFIX ORDER NUMBER
                        // -------------------------
                        if ($totalShipments > 1) {
                            $suffix = chr(65 + $index); // A, B, C
                            $finalOrderNumber = "{$orderNumber}-{$suffix}";
                        } else {
                            $finalOrderNumber = $orderNumber;
                        }

                        // -------------------------
                        // DUPLICATE CHECK
                        // -------------------------
                        if (isset($existingOrders[$finalOrderNumber])) {

                            Log::warning('Duplicate order skipped', [
                                'orderNumber' => $finalOrderNumber
                            ]);

                            $skippedOrders[]   = $finalOrderNumber;
                            $duplicateOrders[] = $finalOrderNumber;

                            $index++;
                            continue;
                        }

                        // -------------------------
                        // SKU VALIDATION
                        // -------------------------
                        $invalidSku = false;

                        foreach ($shipmentItems as $item) {

                            if (!isset($products[$item['skuReferenceNo']])) {

                                Log::warning('Product not found', [
                                    'order' => $finalOrderNumber,
                                    'sku'   => $item['skuReferenceNo']
                                ]);

                                $invalidSku = true;
                                $missingSkus[] = $item['skuReferenceNo'];
                            }
                        }

                        if ($invalidSku) {

                            Log::warning('Shipment skipped due to missing SKU', [
                                'orderNumber' => $finalOrderNumber
                            ]);

                            $skippedOrders[] = $finalOrderNumber;

                            $index++;
                            continue;
                        }

                        // -------------------------
                        // INVOICE NUMBER
                        // -------------------------
                        $prefix = $shop->invoice_prefix;

                        $lastInvoice = Order::withTrashed()
                            ->where('invoice_no', 'like', $prefix . '%')
                            ->orderBy('id', 'desc')
                            ->value('invoice_no');

                        $nextSeq = $lastInvoice
                            ? str_pad(intval(substr($lastInvoice, strlen($prefix))) + 1, 6, '0', STR_PAD_LEFT)
                            : "000001";

                        $invoiceNo = $prefix . $nextSeq;

                        // -------------------------
                        // AGGREGATES (FIXED)
                        // -------------------------
                        $totalProducts = collect($shipmentItems)->sum('quantity'); // ✅ correct
                        $grandTotal    = collect($shipmentItems)->sum('lineTotal');
                        $shipFee       = collect($shipmentItems)->sum('shipFee');

                        // -------------------------
                        // CREATE ORDER
                        // -------------------------
                        $order = Order::create([
                            'order_number'        => $finalOrderNumber,
                            'shop_name_id'        => $shopnameId,
                            'invoice_no'          => $invoiceNo,
                            'order_date'          => $shipmentItems[0]['orderDate'],
                            'total_products'      => $totalProducts,
                            'shipping_fee'        => $shipFee,
                            'total'               => $grandTotal,
                            'platform_id'         => $platformId,
                            'status_id'           => 1,
                            'customer_name'       => $shipmentItems[0]['customerName'],
                            'shipping_address'    => $shipmentItems[0]['deliveryAddress'],
                            'tracking_number'     => $trackingNumber,
                            'courier'             => $shipmentItems[0]['shippingOption'],
                            'platform_created_at' => Carbon::parse($shipmentItems[0]['orderDate']),
                            'created_by'          => Auth::id(),
                            'payment_type'        => $shipmentItems[0]['paymentType'],
                        ]);

                        Log::info('Order created', ['order_id' => $order->id]);

                        // STATUS LOG
                        $order->statusLogs()->create([
                            'status_id' => 1,
                            'acted_by'  => auth()->id(),
                            'remarks'   => 'Imported Lazada Order',
                        ]);

                        // -------------------------
                        // GROUP BY SKU (CORRECT)
                        // -------------------------
                        $groupedItems = collect($shipmentItems)->groupBy('skuReferenceNo');

                        foreach ($groupedItems as $sku => $group) {

                            $productId = $products[$sku];

                            $quantity = $group->count(); // row count = qty
                            $total    = $group->sum('lineTotal');

                            OrderDetails::create([
                                'order_id'   => $order->id,
                                'product_id' => $productId,
                                'sku'        => $sku,
                                'name'       => $group->first()['productName'],
                                'quantity'   => $quantity,
                                'price'      => $quantity > 0 ? $total / $quantity : 0,
                            ]);
                        }

                        // -------------------------
                        // CUSTOMER
                        // -------------------------
                        Customer::firstOrCreate(
                            [
                                'platform_id' => $platformId,
                                'name'        => $shipmentItems[0]['customerName'],
                            ],
                            [
                                'created_by' => Auth::id(),
                            ]
                        );

                        $importedOrders++;
                        $index++;
                    }
                }

                Log::info('Lazada import finished', [
                    'imported' => $importedOrders,
                    'skipped'  => count($skippedOrders),
                    'duplicates' => count($duplicateOrders),
                    'missing_skus' => array_unique($missingSkus)
                ]);

                return redirect()
                    ->route('orders.index')
                    ->with('success',
                        "Lazada Import Completed. Imported: {$importedOrders}, Skipped: ".count($skippedOrders).", Duplicates: ".count($duplicateOrders)
                    )
                    ->with('skipped_orders', $skippedOrders)
                    ->with('duplicate_orders', $duplicateOrders)
                    ->with('missing_skus', array_unique($missingSkus));

            } catch (Throwable $e) {

                Log::error('Lazada import error', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);

                return back()->with('error', $e->getMessage());
            }
        }

        if ($platformId == 6) { // Edamama

            Log::info('edamama import started');

            $file = $request->file('file');

            try {

                Log::info('Loading spreadsheet');

                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet       = $spreadsheet->getActiveSheet();
                $row_limit   = $sheet->getHighestDataRow();
                $row_range   = range(2, $row_limit);

                $ordersData = [];

                // =========================
                // STEP 1 — COLLECT DATA
                // =========================
                foreach ($row_range as $row) {

                    $orderNumber = $sheet->getCell('G' . $row)->getValue();
                    if (!$orderNumber) continue;

                    $ordersData[$orderNumber][] = [
                        'trackingNumber'   => $sheet->getCell('A' . $row)->getValue(),
                        'orderDate'        => $sheet->getCell('C' . $row)->getValue(),
                        'productName'      => $sheet->getCell('E' . $row)->getValue(),
                        'skuReferenceNo'   => $sheet->getCell('O' . $row)->getValue(),
                        'quantity'         => (int) $sheet->getCell('J' . $row)->getValue(),
                        'prodsubtotal'     => (float) $sheet->getCell('L' . $row)->getValue(),
                        'grandTotal'       => (float) $sheet->getCell('W' . $row)->getValue(),
                        'estimatedShipFee' => (float) $sheet->getCell('Q' . $row)->getValue(),
                        'customerName'     => $sheet->getCell('H' . $row)->getValue(),
                        'deliveryAddress'  => $sheet->getCell('I' . $row)->getValue(),
                    ];
                }

                Log::info('Collected orders', ['count' => count($ordersData)]);

                // =========================
                // PRELOAD DATA
                // =========================
                Log::info('Preloading product SKUs');
                $products = Product::pluck('id', 'sku');

                Log::info('Preloading existing orders');
                $existingOrders = Order::where('platform_id', $platformId)
                    ->pluck('id', 'order_number');

                // =========================
                // TRACK RESULTS
                // =========================
                $skippedOrders   = [];
                $duplicateOrders = [];
                $missingSkus     = [];
                $importedOrders  = 0;

                // =========================
                // STEP 2 — PROCESS ORDERS
                // =========================
                foreach ($ordersData as $orderNumber => $items) {

                    Log::info('Processing order', ['orderNumber' => $orderNumber]);

                    // =========================
                    // DUPLICATE CHECK
                    // =========================
                    if (isset($existingOrders[$orderNumber])) {

                        Log::warning('Duplicate order skipped', [
                            'orderNumber' => $orderNumber
                        ]);

                        $skippedOrders[]   = $orderNumber;
                        $duplicateOrders[] = $orderNumber;

                        continue;
                    }

                    // =========================
                    // SKU VALIDATION
                    // =========================
                    $invalidSku = false;

                    foreach ($items as $item) {

                        if (!isset($products[$item['skuReferenceNo']])) {

                            Log::warning('Product not found', [
                                'order' => $orderNumber,
                                'sku'   => $item['skuReferenceNo']
                            ]);

                            $invalidSku = true;
                            $missingSkus[] = $item['skuReferenceNo'];
                        }
                    }

                    if ($invalidSku) {

                        Log::warning('Order skipped due to missing SKU', [
                            'orderNumber' => $orderNumber
                        ]);

                        $skippedOrders[] = $orderNumber;

                        continue;
                    }

                    // =========================
                    // CREATE ORDER
                    // =========================
                    Log::info('Creating new order');

                    $prefix = $shop->invoice_prefix;

                    $lastInvoice = Order::withTrashed()
                        ->where('invoice_no', 'like', $prefix . '%')
                        ->orderBy('id', 'desc')
                        ->value('invoice_no');

                    $nextSeq = $lastInvoice
                        ? str_pad(intval(substr($lastInvoice, strlen($prefix))) + 1, 6, '0', STR_PAD_LEFT)
                        : "000001";

                    $invoiceNo = $prefix . $nextSeq;

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
                        'status_id'           => 1,
                        'customer_name'       => $items[0]['customerName'],
                        'shipping_address'    => $items[0]['deliveryAddress'],
                        'tracking_number'     => $items[0]['trackingNumber'],
                        'platform_created_at' => Carbon::parse($items[0]['orderDate']),
                        'created_by'          => Auth::id(),
                    ]);

                    Log::info('Order created', ['order_id' => $order->id]);

                    $order->statusLogs()->create([
                        'status_id' => 1,
                        'acted_by'  => auth()->id(),
                        'remarks'   => 'Imported Order Successfully',
                    ]);

                    // =========================
                    // INSERT DETAILS
                    // =========================
                    foreach ($items as $item) {

                        $productId = $products[$item['skuReferenceNo']];

                        OrderDetails::create([
                            'order_id'   => $order->id,
                            'product_id' => $productId,
                            'sku'        => $item['skuReferenceNo'],
                            'name'       => $item['productName'],
                            'quantity'   => $item['quantity'],
                            'price'      => $item['prodsubtotal'] / max($item['quantity'], 1),
                        ]);
                    }

                    // =========================
                    // CUSTOMER
                    // =========================
                    Customer::firstOrCreate(
                        [
                            'platform_id' => $platformId,
                            'name'        => $items[0]['customerName'],
                        ],
                        [
                            'created_by' => Auth::id(),
                        ]
                    );

                    $importedOrders++;
                }

                Log::info('edamama import finished', [
                    'imported' => $importedOrders,
                    'skipped'  => count($skippedOrders),
                    'duplicates' => count($duplicateOrders),
                    'missing_skus' => array_unique($missingSkus)
                ]);

                return redirect()
                    ->route('orders.index')
                    ->with('success',
                        "Edamama Import Completed. Imported: {$importedOrders}, Skipped: ".count($skippedOrders).", Duplicates: ".count($duplicateOrders)
                    )
                    ->with('skipped_orders', $skippedOrders)
                    ->with('duplicate_orders', $duplicateOrders)
                    ->with('missing_skus', array_unique($missingSkus));

            } catch (Throwable $e) {

                Log::error('edamama import error', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);

                return back()->with('error', $e->getMessage());
            }
        }

        if ($platformId == 4) { // Shopify

            Log::info('Shopify import started');

            $file = $request->file('file');

            try {

                Log::info('Loading spreadsheet');

                $spreadsheet = IOFactory::load($file->getRealPath());
                $sheet       = $spreadsheet->getActiveSheet();
                $row_limit   = $sheet->getHighestDataRow();
                $row_range   = range(2, $row_limit);

                $ordersData = [];

                // =========================
                // SHOPIFY CARRY-FORWARD STATE
                // =========================
                $currentOrderNumber = null;
                $currentOrderDate   = null;
                $currentCustomer    = null;
                $currentAddress     = null;
                $currentTracking    = null;

                // =========================
                // STEP 1 — COLLECT DATA
                // =========================
                foreach ($row_range as $row) {

                    $orderNumber = $sheet->getCell('A' . $row)->getValue(); // Name
                    $orderDate   = $sheet->getCell('P' . $row)->getValue(); // Created at
                    $customer    = $sheet->getCell('AB' . $row)->getValue(); // Billing Name
                    $address     = $sheet->getCell('AC' . $row)->getValue(); // Billing Street
                    $tracking    = null; // Shopify usually doesn't include tracking here

                    // =========================
                    // CARRY-FORWARD LOGIC
                    // =========================
                    if ($orderNumber) $currentOrderNumber = $orderNumber;
                    if ($orderDate)   $currentOrderDate   = $orderDate;
                    if ($customer)    $currentCustomer    = $customer;
                    if ($address)     $currentAddress     = $address;

                    $orderNumber = $currentOrderNumber;
                    $orderDate   = $currentOrderDate;
                    $customer    = $currentCustomer;
                    $address     = $currentAddress;

                    if (!$orderNumber) continue;

                    $ordersData[$orderNumber][] = [
                        'trackingNumber'   => $tracking,
                        'orderDate'        => $orderDate,
                        'productName'      => $sheet->getCell('R' . $row)->getValue(),
                        'skuReferenceNo'   => $sheet->getCell('U' . $row)->getValue(),
                        'quantity'         => (int) $sheet->getCell('Q' . $row)->getValue(),
                        'unitPrice'        => (float) $sheet->getCell('S' . $row)->getValue(),
                        'estimatedShipFee' => (float) $sheet->getCell('J' . $row)->getValue(),
                        'customerName'     => $customer,
                        'deliveryAddress'  => $address,
                        'lineTotal'        => (float) $sheet->getCell('S' . $row)->getValue() * (int) $sheet->getCell('Q' . $row)->getValue(),
                    ];
                }

                Log::info('Collected Shopify orders', ['count' => count($ordersData)]);

                // =========================
                // PRELOAD DATA
                // =========================
                Log::info('Preloading product SKUs');
                $products = Product::pluck('id', 'sku');

                Log::info('Preloading existing orders');
                $existingOrders = Order::where('platform_id', $platformId)
                    ->pluck('id', 'order_number');

                // =========================
                // TRACK RESULTS
                // =========================
                $skippedOrders   = [];
                $duplicateOrders = [];
                $missingSkus     = [];
                $importedOrders  = 0;

                // =========================
                // STEP 2 — PROCESS ORDERS
                // =========================
                foreach ($ordersData as $orderNumber => $items) {

                    Log::info('Processing order', ['orderNumber' => $orderNumber]);

                    // =========================
                    // DUPLICATE CHECK
                    // =========================
                    if (isset($existingOrders[$orderNumber])) {

                        Log::warning('Duplicate order skipped', [
                            'orderNumber' => $orderNumber
                        ]);

                        $skippedOrders[]   = $orderNumber;
                        $duplicateOrders[] = $orderNumber;

                        continue;
                    }

                    // =========================
                    // SKU VALIDATION
                    // =========================
                    $invalidSku = false;

                    foreach ($items as $item) {

                        if (!isset($products[$item['skuReferenceNo']])) {

                            Log::warning('Product not found', [
                                'order' => $orderNumber,
                                'sku'   => $item['skuReferenceNo']
                            ]);

                            $invalidSku = true;
                            $missingSkus[] = $item['skuReferenceNo'];
                        }
                    }

                    if ($invalidSku) {

                        Log::warning('Order skipped due to missing SKU', [
                            'orderNumber' => $orderNumber
                        ]);

                        $skippedOrders[] = $orderNumber;

                        continue;
                    }

                    // =========================
                    // CREATE ORDER
                    // =========================
                    Log::info('Creating new order');

                    $prefix = $shop->invoice_prefix;

                    $lastInvoice = Order::withTrashed()
                        ->where('invoice_no', 'like', $prefix . '%')
                        ->orderBy('id', 'desc')
                        ->value('invoice_no');

                    $nextSeq = $lastInvoice
                        ? str_pad(intval(substr($lastInvoice, strlen($prefix))) + 1, 6, '0', STR_PAD_LEFT)
                        : "000001";

                    $invoiceNo = $prefix . $nextSeq;

                    $totalProducts = array_sum(array_column($items, 'quantity'));

                    $grandTotal = 0;
                    foreach ($items as $it) {
                        $grandTotal += $it['lineTotal'];
                    }

                    $shipFee = $items[0]['estimatedShipFee'] ?? 0;

                    $order = Order::create([
                        'order_number'        => $orderNumber,
                        'shop_name_id'        => $shopnameId,
                        'invoice_no'          => $invoiceNo,
                        'order_date'          => $items[0]['orderDate'],
                        'total_products'      => $totalProducts,
                        'shipping_fee'        => $shipFee,
                        'total'               => $grandTotal,
                        'platform_id'         => $platformId,
                        'status_id'           => 1,
                        'customer_name'       => $items[0]['customerName'],
                        'shipping_address'    => $items[0]['deliveryAddress'],
                        'tracking_number'     => null,
                        'courier'             => null,
                        'platform_created_at' => Carbon::parse($items[0]['orderDate']),
                        'created_by'          => Auth::id(),
                    ]);

                    Log::info('Order created', ['order_id' => $order->id]);

                    $order->statusLogs()->create([
                        'status_id' => 1,
                        'acted_by'  => auth()->id(),
                        'remarks'   => 'Imported Shopify Order Successfully',
                    ]);

                    // =========================
                    // INSERT DETAILS
                    // =========================
                    foreach ($items as $item) {

                        $productId = $products[$item['skuReferenceNo']];

                        OrderDetails::create([
                            'order_id'   => $order->id,
                            'product_id' => $productId,
                            'sku'        => $item['skuReferenceNo'],
                            'name'       => $item['productName'],
                            'quantity'   => $item['quantity'],
                            'price'      => $item['unitPrice'],
                        ]);
                    }

                    // =========================
                    // CUSTOMER
                    // =========================
                    Customer::firstOrCreate(
                        [
                            'platform_id' => $platformId,
                            'name'        => $items[0]['customerName'],
                        ],
                        [
                            'created_by' => Auth::id(),
                        ]
                    );

                    $importedOrders++;
                }

                Log::info('Shopify import finished', [
                    'imported' => $importedOrders,
                    'skipped'  => count($skippedOrders),
                    'duplicates' => count($duplicateOrders),
                    'missing_skus' => array_unique($missingSkus)
                ]);

                return redirect()
                    ->route('orders.index')
                    ->with('success',
                        "Shopify Import Completed. Imported: {$importedOrders}, Skipped: ".count($skippedOrders).", Duplicates: ".count($duplicateOrders)
                    )
                    ->with('skipped_orders', $skippedOrders)
                    ->with('duplicate_orders', $duplicateOrders)
                    ->with('missing_skus', array_unique($missingSkus));

            } catch (Throwable $e) {

                Log::error('Shopify import error', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);

                return back()->with('error', $e->getMessage());
            }
        }

       return redirect()
            ->route('orders.index')
            ->with('success', 'Orders imported successfully!');

    }
}
