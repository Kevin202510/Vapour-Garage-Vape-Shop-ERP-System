<?php


use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\Web\BranchWebController;
use App\Http\Controllers\Web\CustomerWebController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EmployeeWebController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\InventoryWebController;
use App\Http\Controllers\Web\PayrollWebController;
use App\Http\Controllers\Web\PermissionWebController;
use App\Http\Controllers\Web\PosWebController;
use App\Http\Controllers\Web\ProductWebController;
use App\Http\Controllers\Web\ProfileWebController;
use App\Http\Controllers\Web\PurchasingWebController;
use App\Http\Controllers\Web\RoleWebController;
use App\Http\Controllers\Web\SalesWebController;
use App\Http\Controllers\Web\TransferWebController;
use App\Http\Controllers\Web\UserWebController;
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

    // My Profile (self-service, no permission gate — always the caller's own data)
    Route::get('/my-profile', [ProfileWebController::class, 'show'])->name('profile.mine');

    // Point of Sale
    Route::get('/pos', [PosWebController::class, 'index'])->name('pos.index');

    // Products
    Route::get('products/export',        [ProductWebController::class, 'export'])->name('products.export');
    Route::post('products/{id}/archive', [ProductWebController::class, 'archive'])->name('products.archive');
    Route::post('products/{id}/restore', [ProductWebController::class, 'restore'])->name('products.restore');
    Route::resource('products', ProductWebController::class);

    // Inventory
    Route::get('/inventory',        [InventoryWebController::class, 'index'])->name('inventory.index');
    Route::get('/inventory/export', [InventoryWebController::class, 'export'])->name('inventory.export');
    Route::post('/inventory/adjust', [InventoryWebController::class, 'adjust'])->name('inventory.adjust');

    // Stock Transfers
    Route::prefix('transfers')->name('transfers.')->group(function () {
        Route::get('/',                    [TransferWebController::class, 'index'])->name('index');
        Route::get('/export',              [TransferWebController::class, 'export'])->name('export');
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
        Route::get('/export',                  [PurchasingWebController::class, 'export'])->name('export');
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
    Route::get('suppliers/export', [\App\Http\Controllers\Web\SupplierWebController::class, 'export'])->name('suppliers.export');
    Route::resource('suppliers', \App\Http\Controllers\Web\SupplierWebController::class);

    // Customers
    Route::get('customers/export', [CustomerWebController::class, 'export'])->name('customers.export');
    Route::post('customers/{customer}/add-points',    [CustomerWebController::class, 'addPoints'])->name('customers.add-points');
    Route::post('customers/{customer}/deduct-points', [CustomerWebController::class, 'deductPoints'])->name('customers.deduct-points');
    Route::resource('customers', CustomerWebController::class);

    // Employees
    Route::get('employees/export', [EmployeeWebController::class, 'export'])->name('employees.export');
    Route::resource('employees', EmployeeWebController::class);

    // Payroll
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/',                 [PayrollWebController::class, 'index'])->name('index');
        Route::get('/export',           [PayrollWebController::class, 'export'])->name('export');
        Route::get('/mine',             [PayrollWebController::class, 'mine'])->name('mine');
        Route::get('/{id}',            [PayrollWebController::class, 'show'])->name('show');
        Route::post('/generate',        [PayrollWebController::class, 'generate'])->name('generate');
        Route::post('/{id}/approve',    [PayrollWebController::class, 'approve'])->name('approve');
        Route::post('/{id}/release',    [PayrollWebController::class, 'release'])->name('release');
        Route::delete('/{id}',          [PayrollWebController::class, 'destroy'])->name('destroy');
    });

    // Branches
    Route::resource('branches', BranchWebController::class);

    // Users
    Route::post('users/{id}/activate',   [UserWebController::class, 'activate'])->name('users.activate');
    Route::post('users/{id}/deactivate', [UserWebController::class, 'deactivate'])->name('users.deactivate');
    Route::resource('users', UserWebController::class);

    // Roles & Permissions
    Route::resource('roles', RoleWebController::class)->except(['show']);
    Route::get('permissions', [PermissionWebController::class, 'index'])->name('permissions.index');

});

// ── Public shop routes (no auth required) ────────────────────────────────────
use App\Http\Controllers\Shop\ShopController;

// Public, unauthenticated surface — every route gets a generous baseline
// throttle against scraping/DoS, with tighter limits on the endpoints that
// write data, send email, or could be used to enumerate orders.
Route::prefix('shop')->name('shop.')->middleware('throttle:120,1')->group(function () {
    Route::get('/',                          [ShopController::class, 'index'])->name('index');
    Route::get('/product/{id}',              [ShopController::class, 'product'])->name('product');
    Route::get('/cart',                      [ShopController::class, 'cart'])->name('cart');
    Route::post('/cart/{id}',               [ShopController::class, 'addToCart'])->middleware('throttle:30,1')->name('cart.add');
    Route::patch('/cart/{id}',              [ShopController::class, 'updateCart'])->middleware('throttle:30,1')->name('cart.update');
    Route::delete('/cart/{id}',             [ShopController::class, 'removeFromCart'])->middleware('throttle:30,1')->name('cart.remove');
    Route::get('/checkout',                  [ShopController::class, 'checkout'])->name('checkout');
    // Creates a real order and queues an email — the highest-value target
    // for spam/abuse on this public surface.
    Route::post('/checkout',                 [ShopController::class, 'placeOrder'])->middleware('throttle:5,1')->name('order.place');
    Route::get('/order/{orderNumber}',       [ShopController::class, 'confirmation'])->name('confirmation');
    // order_number + email lookup — throttle to slow down enumeration.
    Route::get('/track',                     [ShopController::class, 'trackOrder'])->middleware('throttle:10,1')->name('track');
});

// ── Online Orders (ERP staff) ──────────────────────────────────────────────
use App\Http\Controllers\Web\OnlineOrderWebController;

Route::middleware(['auth', 'verified'])->prefix('online-orders')->name('online-orders.')->group(function () {
    Route::get('/',                [OnlineOrderWebController::class, 'index'])->name('index');
    Route::get('/export',          [OnlineOrderWebController::class, 'export'])->name('export');
    Route::get('/{onlineOrder}',   [OnlineOrderWebController::class, 'show'])->name('show');
    Route::patch('/{onlineOrder}', [OnlineOrderWebController::class, 'update'])->name('update');
});
