<?php


use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\Web\BranchWebController;
use App\Http\Controllers\Web\CustomerWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EmployeeWebController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\InventoryWebController;
use App\Http\Controllers\Web\PayrollWebController;
use App\Http\Controllers\Web\PosWebController;
use App\Http\Controllers\Web\ProductWebController;
use App\Http\Controllers\Web\PurchasingWebController;
use App\Http\Controllers\Web\SalesWebController;
use App\Http\Controllers\Web\TransferWebController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\SalesPeriodController;

/*
|--------------------------------------------------------------------------
| Web Routes — VapeHub ERP
|--------------------------------------------------------------------------
| All routes require authentication via Sanctum/Session.
| Role/permission checks are enforced inside controllers and
| service methods via $this->authorize().
|
| Route naming conventions:
|   dashboard, pos.index
|   {resource}.index | .create | .store | .show | .edit | .update | .destroy
|   {resource}.restore  (soft-delete restore)
|   payroll.generate | payroll.approve | payroll.release
|   transfers.approve | transfers.reject | transfers.receive
|   purchasing.approve | purchasing.receive-form | purchasing.receive | purchasing.cancel
|--------------------------------------------------------------------------
*/

// Public landing
Route::get('/home', [HomeController::class, 'index'])->name('landing');

// Root redirect
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect('/home');
});
 
Route::post('/chatbot', [ChatBotController::class, 'chat'])->middleware('throttle:20,1');

// ── Auth routes (provided by Laravel Breeze / Fortify / Sanctum) ──────────
require __DIR__ . '/auth.php';

// ── Authenticated web routes ───────────────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {

    // Branch switcher (admin / super_admin only)
    Route::post('/switch-branch', function (\Illuminate\Http\Request $request) {
        if (! auth()->user()->isSuperAdmin()) abort(403);
        if ($request->input('branch_id') === 'all') {
            // Store 0 as an explicit "All Branches" sentinel.
            // Forgetting the key would cause activeBranchId() to fall back to
            // the user's assigned branch_id instead of returning null.
            session(['active_branch_id' => 0]);
        } else {
            $request->validate(['branch_id' => 'required|exists:branches,id']);
            session(['active_branch_id' => (int) $request->branch_id]);
        }
        return back();
    })->name('branch.switch');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [DashboardController::class, 'export'])->name('dashboard.export');

    // Point of Sale
    Route::get('/pos', [PosWebController::class, 'index'])->name('pos.index');

    // Products
    Route::post('products/{id}/archive', [ProductWebController::class, 'archive'])->name('products.archive');
    Route::post('products/{id}/restore', [ProductWebController::class, 'restore'])->name('products.restore');
    Route::resource('products', ProductWebController::class);

    // Inventory
    Route::get('/inventory',        [InventoryWebController::class, 'index'])->name('inventory.index');
    Route::post('/inventory/adjust', [InventoryWebController::class, 'adjust'])->name('inventory.adjust');

    // Stock Transfers
    Route::prefix('transfers')->name('transfers.')->group(function () {
        Route::get('/',                    [TransferWebController::class, 'index'])->name('index');
        Route::get('/{id}',               [TransferWebController::class, 'show'])->name('show');
        Route::post('/',                   [TransferWebController::class, 'store'])->name('store');
        Route::post('/{id}/approve',       [TransferWebController::class, 'approve'])->name('approve');
        Route::post('/{id}/dispatch',      [TransferWebController::class, 'dispatch'])->name('dispatch');
        Route::post('/{id}/reject',        [TransferWebController::class, 'reject'])->name('reject');
        Route::post('/{id}/receive',       [TransferWebController::class, 'receive'])->name('receive');
        Route::post('/{id}/cancel',        [TransferWebController::class, 'cancel'])->name('cancel');
    });

    // Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/',             [SalesWebController::class, 'index'])->name('index');
        Route::get('/export',       [SalesWebController::class, 'export'])->name('export');
        Route::get('/{id}',        [SalesWebController::class, 'show'])->name('show');
        Route::post('/{id}/void',  [SalesWebController::class, 'void'])->name('void');
    });

    // Sales Period Closing (Reset Schedule)
    Route::prefix('sales-periods')->name('sales-periods.')->group(function () {
        Route::get('/',      [SalesPeriodController::class, 'index'])->name('index');
        Route::post('/close', [SalesPeriodController::class, 'close'])->name('close');
    });

    // Purchasing (Purchase Orders)
    Route::prefix('purchasing')->name('purchasing.')->group(function () {
        Route::get('/',                        [PurchasingWebController::class, 'index'])->name('index');
        Route::get('/create',                  [PurchasingWebController::class, 'create'])->name('create');
        Route::post('/',                       [PurchasingWebController::class, 'store'])->name('store');
        Route::get('/{id}',                    [PurchasingWebController::class, 'show'])->name('show');
        Route::post('/{id}/approve',           [PurchasingWebController::class, 'approve'])->name('approve');
        Route::get('/{id}/receive',            [PurchasingWebController::class, 'receiveForm'])->name('receive-form');
        Route::post('/{id}/receive',           [PurchasingWebController::class, 'receive'])->name('receive');
        Route::post('/{id}/cancel',            [PurchasingWebController::class, 'cancel'])->name('cancel');
    });

    // Product Categories
    Route::resource('categories', \App\Http\Controllers\Web\CategoryWebController::class)->except(['show']);

    // Suppliers
    Route::resource('suppliers', \App\Http\Controllers\Web\SupplierWebController::class);

    // Customers
    Route::post('customers/{customer}/add-points',    [CustomerWebController::class, 'addPoints'])->name('customers.add-points');
    Route::post('customers/{customer}/deduct-points', [CustomerWebController::class, 'deductPoints'])->name('customers.deduct-points');
    Route::resource('customers', CustomerWebController::class);

    // Employees
    Route::resource('employees', EmployeeWebController::class);

    // Payroll
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/',                 [PayrollWebController::class, 'index'])->name('index');
        Route::get('/{id}',            [PayrollWebController::class, 'show'])->name('show');
        Route::post('/generate',        [PayrollWebController::class, 'generate'])->name('generate');
        Route::post('/{id}/approve',    [PayrollWebController::class, 'approve'])->name('approve');
        Route::post('/{id}/release',    [PayrollWebController::class, 'release'])->name('release');
        Route::delete('/{id}',          [PayrollWebController::class, 'destroy'])->name('destroy');
    });

    // Branches
    Route::resource('branches', BranchWebController::class);

});

// ── Public shop routes (no auth required) ────────────────────────────────────
use App\Http\Controllers\Shop\ShopController;

Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/',                          [ShopController::class, 'index'])->name('index');
    Route::get('/product/{id}',              [ShopController::class, 'product'])->name('product');
    Route::get('/cart',                      [ShopController::class, 'cart'])->name('cart');
    Route::post('/cart/{id}',               [ShopController::class, 'addToCart'])->name('cart.add');
    Route::patch('/cart/{id}',              [ShopController::class, 'updateCart'])->name('cart.update');
    Route::delete('/cart/{id}',             [ShopController::class, 'removeFromCart'])->name('cart.remove');
    Route::get('/checkout',                  [ShopController::class, 'checkout'])->name('checkout');
    Route::post('/checkout',                 [ShopController::class, 'placeOrder'])->name('order.place');
    Route::get('/order/{orderNumber}',       [ShopController::class, 'confirmation'])->name('confirmation');
    Route::get('/track',                     [ShopController::class, 'trackOrder'])->name('track');
});

// ── Online Orders (ERP staff) ──────────────────────────────────────────────
use App\Http\Controllers\Web\OnlineOrderWebController;

Route::middleware(['auth', 'verified'])->prefix('online-orders')->name('online-orders.')->group(function () {
    Route::get('/',                [OnlineOrderWebController::class, 'index'])->name('index');
    Route::get('/{onlineOrder}',   [OnlineOrderWebController::class, 'show'])->name('show');
    Route::patch('/{onlineOrder}', [OnlineOrderWebController::class, 'update'])->name('update');
});
