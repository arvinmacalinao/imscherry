<?php

use App\Livewire\ScanCart;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Livewire\Scan\ReturnedScan;
use App\Http\Livewire\Scan\CancelledScan;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScanLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\Order\OrderController;
use App\Http\Controllers\Order\DueOrderController;
use App\Http\Controllers\Product\ProductController;
use App\Http\Controllers\Order\OrderImportController;
use App\Http\Controllers\Purchase\PurchaseController;
use App\Http\Controllers\Order\OrderPendingController;
use App\Http\Controllers\ProductTransactionController;
use App\Http\Controllers\Order\OrderCompleteController;
use App\Http\Controllers\Quotation\QuotationController;
use App\Http\Controllers\Dashboards\DashboardController;
use App\Http\Controllers\Product\ProductExportController;
use App\Http\Controllers\Product\ProductImportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('php/', function () {
    return phpinfo();
});

Route::get('/', function () {
    return view('auth/login');
});

Route::middleware(['auth', 'role:accounting'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    });

Route::middleware(['auth'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    // Route::get('/', [DashboardController::class, 'index'])->name('dashboard');ad

    // User Management
    // Route::resource('/users', UserController::class); //->except(['show']);
    // Route::put('/user/change-password/{username}', [UserController::class, 'updatePassword'])->name('users.updatePassword');

     Route::middleware(['role:admin'])->group(function () {
        Route::resource('/users', UserController::class);
        Route::put('/user/change-password/{username}', [UserController::class, 'updatePassword'])->name('users.updatePassword');
    });

    Route::post('/orders/download-multiple', [OrderController::class, 'downloadMultipleInvoices'])
    ->name('orders.downloadMultiple');



    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/settings', [ProfileController::class, 'settings'])->name('profile.settings');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('/quotations', QuotationController::class);
    Route::resource('/customers', CustomerController::class);
    Route::resource('/suppliers', SupplierController::class);
    Route::resource('/categories', CategoryController::class);
    Route::resource('/units', UnitController::class);

    // Route Products
    Route::get('/products/import', [ProductImportController::class, 'create'])->name('products.import.view');
    Route::post('/products/import', [ProductImportController::class, 'store'])->name('products.import.store');
    Route::get('/products/export', [ProductExportController::class, 'create'])->name('products.export.store');
    Route::resource('/products', ProductController::class);

   

    // Route Orders
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/pending', OrderPendingController::class)->name('orders.pending');
    Route::get('/orders/complete', OrderCompleteController::class)->name('orders.complete');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('orders.create');
    // Route::any('/orders/delete/{order_id}/', [OrderController::class, 'delete'])->name('orders.delete');
    Route::post('/orders/store', [OrderController::class, 'store'])->name('orders.store');

    Route::get('/order/import', [OrderImportController::class, 'create'])->name('orders.import.view');
    Route::post('/order/import', [OrderImportController::class, 'store'])->name('orders.import.store');

    Route::post('/invoice/create', [InvoiceController::class, 'create'])->name('invoice.create');

    // SHOW ORDER
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/cancel/{order}', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::get('/orders/pending/{order}', [OrderController::class, 'pending'])->name('orders.pending');
    Route::put('/orders/update/{order}', [OrderController::class, 'update'])->name('orders.update');

    // DUES
    Route::get('/due/orders/', [DueOrderController::class, 'index'])->name('due.index');
    Route::get('/due/order/view/{order}', [DueOrderController::class, 'show'])->name('due.show');
    Route::get('/due/order/edit/{order}', [DueOrderController::class, 'edit'])->name('due.edit');
    Route::put('/due/order/update/{order}', [DueOrderController::class, 'update'])->name('due.update');

    // TODO: Remove from OrderController
    Route::get('/orders/details/{order_id}/download', [OrderController::class, 'downloadInvoice'])->name('order.downloadInvoice');
    // Route::post('/orders/export-summary', [OrderController::class, 'exportOrderSummary'])->name('orders.exportSummary');


    // Route Purchases
    Route::get('/purchases/approved', [PurchaseController::class, 'approvedPurchases'])->name('purchases.approvedPurchases');
    Route::get('/purchases/report', [PurchaseController::class, 'dailyPurchaseReport'])->name('purchases.dailyPurchaseReport');
    Route::get('/purchases/report/export', [PurchaseController::class, 'getPurchaseReport'])->name('purchases.getPurchaseReport');
    Route::post('/purchases/report/export', [PurchaseController::class, 'exportPurchaseReport'])->name('purchases.exportPurchaseReport');

    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/purchases', [PurchaseController::class, 'store'])->name('purchases.store');

    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    Route::get('/purchases/{purchase}/edit', [PurchaseController::class, 'edit'])->name('purchases.edit');
    Route::put('/purchases/{purchase}/edit', [PurchaseController::class, 'update'])->name('purchases.update');
    Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.delete');

    Route::get('/warehouse/scan', [ScanController::class, 'showWarehouseScanPage'])->name('warehouse.scan.page');
    Route::post('/warehouse/scan', [ScanController::class, 'processProductPull'])->name('warehouse.scan.process');
    Route::post('/warehouse/scan/confirm', [ScanController::class, 'confirmProductPulls'])->name('warehouse.scan.confirm');
    Route::post('/warehouse/scan/remove/{rowId}', [ScanController::class, 'removeProductFromPullList'])->name('warehouse.scan.remove');

    Route::get('warehouse/report', [ReportController::class, 'warehouse'])->name('warehouse.report');
    Route::get('sales/report', [ReportController::class, 'sales'])->name('sales.report');
    Route::get('cancel/report', [ReportController::class, 'cancel'])->name('cancel.report');
    Route::get('return/report', [ReportController::class, 'return'])->name('return.report');
    Route::get('customer/report', [ReportController::class, 'customer'])->name('customer.report');
    Route::get('/warehouse/export', [ReportController::class, 'export_warehouse'])->name('warehouse.export');
    Route::get('/sales/export', [ReportController::class, 'export_sales'])->name('sales.export');
    Route::get('/customer/export', [ReportController::class, 'export_customer'])->name('customer.export');
    Route::get('/cancel/export', [ReportController::class, 'export_cancel'])->name('cancel.export');
    Route::get('/return/export', [ReportController::class, 'export_return'])->name('return.export');

    Route::prefix('reports')->group(function () {
    Route::get('/categories', [ReportController::class, 'categories'])
        ->name('reports.categories');
    });

    Route::put('/orders/{order}/qc-done', [OrderController::class, 'qcDone'])
    ->name('orders.qcDone');

    //return to warehouse 
    Route::put('/order-details/{detail}/return',
        [OrderController::class, 'returnToWarehouse'])
        ->name('details.return');
    //for claims
    Route::put('/order-details/{detail}/claim',
    [OrderController::class, 'forClaims'])
    ->name('details.claim');


    Route::get('/scanned-items', function () {
        return view('scan.index'); // only wrapper blade
    })->name('scanlogs.index');


    Route::prefix('order')->group(function () {
    foreach (['ship', 'cancelled', 'return'] as $type) {
        Route::get("/scan_{$type}", [ScanController::class, "scan_{$type}"])
            ->name("order.scan_page.{$type}");
        Route::post("/scan_{$type}", [ScanController::class, 'scan_process'])
            ->name("order.scan_{$type}");
    }
});

    Route::post('/remove/{type}/{rowId}', [ScanController::class, 'removeFromCart'])->name('order.remove');
    Route::post('/confirm/{type}', [ScanController::class, 'confirm_scans'])->name('order.confirm');


    Route::prefix('transactions')->name('transactions.')->group(function () {
    
        Route::get('/', [ProductTransactionController::class, 'index'])
            ->name('index');
        
        Route::get('/create', [ProductTransactionController::class, 'create'])
            ->name('create');
        
        Route::post('/store', [ProductTransactionController::class, 'store'])
            ->name('store');
        
        Route::get('/{batch}', [ProductTransactionController::class, 'show'])
            ->name('show');
    });
});



require __DIR__.'/auth.php';

Route::get('test/', function (){
//    return view('test');
    return view('orders.create');
});
