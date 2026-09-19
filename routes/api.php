<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DiscountController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\SalesTransactionController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| VapeHub ERP — API Routes
|--------------------------------------------------------------------------
| All routes prefixed with /api via bootstrap/app.php.
| Every protected route requires a valid Sanctum token.
| Permissions are enforced inside each controller via $this->authorize().
|
| Quick reference:
|   Auth          POST /api/auth/login   →  get token
|   Auth          GET  /api/auth/me      →  current user + roles
|
*/

// ── Public ────────────────────────────────────────────────────────────────────
Route::prefix('auth')->name('auth.')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->name('login');
});

// ── Authenticated ─────────────────────────────────────────────────────────────
// throttle:120,1 is a per-user ceiling (Laravel's default ThrottleRequests
// keys by authenticated user ID here, since auth:sanctum resolves one) —
// defense-in-depth against a leaked token or a runaway client, not a
// substitute for the tighter limits on specific sensitive endpoints.
Route::middleware(['auth:sanctum', 'throttle:120,1'])->name('api.')->group(function () {

    // ── Auth session ──────────────────────────────────────────────────────────
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::get('me',           [AuthController::class, 'me'])->name('me');
        Route::post('logout',      [AuthController::class, 'logout'])->name('logout');
        Route::post('logout-all',  [AuthController::class, 'logoutAll'])->name('logout-all');
        Route::post('refresh',     [AuthController::class, 'refresh'])->name('refresh');
    });

    // ── Users & RBAC ──────────────────────────────────────────────────────────
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/',                    [UserController::class, 'index'])->name('index');
        Route::post('/',                   [UserController::class, 'store'])->name('store');
        Route::get('/{user}',              [UserController::class, 'show'])->name('show');
        Route::put('/{user}',              [UserController::class, 'update'])->name('update');
        Route::delete('/{user}',           [UserController::class, 'destroy'])->name('destroy');
        Route::post('/{user}/restore',     [UserController::class, 'restore'])->name('restore');
        Route::post('/{user}/activate',    [UserController::class, 'activate'])->name('activate');
        Route::post('/{user}/deactivate',  [UserController::class, 'deactivate'])->name('deactivate');
        Route::put('/{user}/roles',        [UserController::class, 'syncRoles'])->name('sync-roles');
        Route::put('/{user}/permissions',  [UserController::class, 'syncPermissions'])->name('sync-permissions');
    });

    // ── Branches ──────────────────────────────────────────────────────────────
    Route::prefix('branches')->name('branches.')->group(function () {
        Route::get('/',               [BranchController::class, 'index'])->name('index');
        Route::post('/',              [BranchController::class, 'store'])->name('store');
        Route::get('/{id}',           [BranchController::class, 'show'])->name('show');
        Route::put('/{id}',           [BranchController::class, 'update'])->name('update');
        Route::delete('/{id}',        [BranchController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore',  [BranchController::class, 'restore'])->name('restore');
    });

    // ── Departments ───────────────────────────────────────────────────────────
    Route::prefix('departments')->name('departments.')->group(function () {
        Route::get('/',       [DepartmentController::class, 'index'])->name('index');
        Route::post('/',      [DepartmentController::class, 'store'])->name('store');
        Route::get('/{id}',   [DepartmentController::class, 'show'])->name('show');
        Route::put('/{id}',   [DepartmentController::class, 'update'])->name('update');
        Route::delete('/{id}',[DepartmentController::class, 'destroy'])->name('destroy');
    });

    // ── Employees ─────────────────────────────────────────────────────────────
    Route::prefix('employees')->name('employees.')->group(function () {
        Route::get('/',               [EmployeeController::class, 'index'])->name('index');
        Route::post('/',              [EmployeeController::class, 'store'])->name('store');
        Route::get('/{id}',           [EmployeeController::class, 'show'])->name('show');
        Route::put('/{id}',           [EmployeeController::class, 'update'])->name('update');
        Route::delete('/{id}',        [EmployeeController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore',  [EmployeeController::class, 'restore'])->name('restore');
    });

    // ── Attendance ────────────────────────────────────────────────────────────
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/',            [AttendanceController::class, 'index'])->name('index');
        Route::post('/clock-in',   [AttendanceController::class, 'clockIn'])->name('clock-in');
        Route::post('/clock-out',  [AttendanceController::class, 'clockOut'])->name('clock-out');
        Route::post('/manual',     [AttendanceController::class, 'manualEntry'])->name('manual');
        Route::get('/summary',     [AttendanceController::class, 'summary'])->name('summary');
        Route::put('/{id}',        [AttendanceController::class, 'update'])->name('update');
        Route::delete('/{id}',     [AttendanceController::class, 'destroy'])->name('destroy');
    });

    // ── Payroll ───────────────────────────────────────────────────────────────
    Route::prefix('payroll')->name('payroll.')->group(function () {
        Route::get('/',                           [PayrollController::class, 'index'])->name('index');
        Route::post('/generate',                  [PayrollController::class, 'generate'])->name('generate');
        Route::get('/employee/{employeeId}/slips',[PayrollController::class, 'employeeSlips'])->name('employee-slips');
        Route::get('/{id}',                       [PayrollController::class, 'show'])->name('show');
        Route::post('/{id}/approve',              [PayrollController::class, 'approve'])->name('approve');
        Route::post('/{id}/release',              [PayrollController::class, 'release'])->name('release');
        Route::delete('/{id}',                    [PayrollController::class, 'destroy'])->name('destroy');
    });

    // ── Suppliers ─────────────────────────────────────────────────────────────
    Route::prefix('suppliers')->name('suppliers.')->group(function () {
        Route::get('/',               [SupplierController::class, 'index'])->name('index');
        Route::post('/',              [SupplierController::class, 'store'])->name('store');
        Route::get('/{id}',           [SupplierController::class, 'show'])->name('show');
        Route::put('/{id}',           [SupplierController::class, 'update'])->name('update');
        Route::delete('/{id}',        [SupplierController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore',  [SupplierController::class, 'restore'])->name('restore');
    });

    // ── Product Categories ────────────────────────────────────────────────────
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/',       [ProductCategoryController::class, 'index'])->name('index');
        Route::post('/',      [ProductCategoryController::class, 'store'])->name('store');
        Route::get('/{id}',   [ProductCategoryController::class, 'show'])->name('show');
        Route::put('/{id}',   [ProductCategoryController::class, 'update'])->name('update');
        Route::delete('/{id}',[ProductCategoryController::class, 'destroy'])->name('destroy');
    });

    // ── Products ──────────────────────────────────────────────────────────────
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/',               [ProductController::class, 'index'])->name('index');
        Route::post('/',              [ProductController::class, 'store'])->name('store');
        Route::get('/low-stock',      [ProductController::class, 'lowStock'])->name('low-stock');
        Route::get('/{id}',           [ProductController::class, 'show'])->name('show');
        Route::put('/{id}',           [ProductController::class, 'update'])->name('update');
        Route::delete('/{id}',        [ProductController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore',  [ProductController::class, 'restore'])->name('restore');
    });

    // ── Inventory & Stock Transfers ───────────────────────────────────────────
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/',                          [InventoryController::class, 'index'])->name('index');
        Route::get('/low-stock',                 [InventoryController::class, 'lowStock'])->name('low-stock');
        Route::post('/adjust',                   [InventoryController::class, 'adjust'])->name('adjust');

        // Stock transfers
        Route::post('/transfers',                [InventoryController::class, 'requestTransfer'])->name('transfers.store');
        Route::post('/transfers/{id}/approve',   [InventoryController::class, 'approveTransfer'])->name('transfers.approve');
        Route::post('/transfers/{id}/receive',   [InventoryController::class, 'receiveTransfer'])->name('transfers.receive');
        Route::post('/transfers/{id}/reject',    [InventoryController::class, 'rejectTransfer'])->name('transfers.reject');
    });

    // ── Customers ─────────────────────────────────────────────────────────────
    Route::prefix('customers')->name('customers.')->group(function () {
        Route::get('/',                     [CustomerController::class, 'index'])->name('index');
        Route::post('/',                    [CustomerController::class, 'store'])->name('store');
        Route::get('/{id}',                 [CustomerController::class, 'show'])->name('show');
        Route::put('/{id}',                 [CustomerController::class, 'update'])->name('update');
        Route::delete('/{id}',              [CustomerController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore',        [CustomerController::class, 'restore'])->name('restore');
        Route::post('/{id}/add-points',     [CustomerController::class, 'addPoints'])->name('add-points');
    });

    // ── Discounts ─────────────────────────────────────────────────────────────
    Route::prefix('discounts')->name('discounts.')->group(function () {
        Route::get('/',       [DiscountController::class, 'index'])->name('index');
        Route::post('/',      [DiscountController::class, 'store'])->name('store');
        Route::get('/{id}',   [DiscountController::class, 'show'])->name('show');
        Route::put('/{id}',   [DiscountController::class, 'update'])->name('update');
        Route::delete('/{id}',[DiscountController::class, 'destroy'])->name('destroy');
    });

    // ── Sales / POS ───────────────────────────────────────────────────────────
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/',                   [SalesTransactionController::class, 'index'])->name('index');
        Route::post('/',                  [SalesTransactionController::class, 'store'])->name('store');
        Route::get('/daily-summary',      [SalesTransactionController::class, 'dailySummary'])->name('daily-summary');
        Route::get('/{id}',               [SalesTransactionController::class, 'show'])->name('show');
        Route::post('/{id}/void',         [SalesTransactionController::class, 'void'])->name('void');
    });

    // ── Purchase Orders ───────────────────────────────────────────────────────
    Route::prefix('purchase-orders')->name('purchase-orders.')->group(function () {
        Route::get('/',                [PurchaseOrderController::class, 'index'])->name('index');
        Route::post('/',               [PurchaseOrderController::class, 'store'])->name('store');
        Route::get('/{id}',            [PurchaseOrderController::class, 'show'])->name('show');
        Route::post('/{id}/approve',   [PurchaseOrderController::class, 'approve'])->name('approve');
        Route::post('/{id}/receive',   [PurchaseOrderController::class, 'receive'])->name('receive');
        Route::post('/{id}/cancel',    [PurchaseOrderController::class, 'cancel'])->name('cancel');
        Route::delete('/{id}',         [PurchaseOrderController::class, 'destroy'])->name('destroy');
    });

});
