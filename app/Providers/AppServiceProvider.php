<?php

namespace App\Providers;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\BranchInventoryRepositoryInterface;
use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Repositories\DepartmentRepositoryInterface;
use App\Contracts\Repositories\DiscountRepositoryInterface;
use App\Contracts\Repositories\EmployeeRepositoryInterface;
use App\Contracts\Repositories\PayrollRepositoryInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Repositories\ProductCategoryRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\PurchaseOrderRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\SalesTransactionRepositoryInterface;
use App\Contracts\Repositories\StockTransferRepositoryInterface;
use App\Contracts\Repositories\SupplierRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Repositories\AttendanceRepository;
use App\Repositories\AuditLogRepository;
use App\Repositories\BranchInventoryRepository;
use App\Repositories\BranchRepository;
use App\Repositories\CustomerRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\DiscountRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\PayrollRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\ProductCategoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\PurchaseOrderRepository;
use App\Repositories\RoleRepository;
use App\Repositories\SalesTransactionRepository;
use App\Repositories\StockTransferRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\UserRepository;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All repository interface → concrete class bindings.
     * Laravel resolves these automatically via the IoC container.
     * To swap an implementation (e.g. for testing), change the value here.
     */
    public array $bindings = [
        // Auth / Users
        UserRepositoryInterface::class              => UserRepository::class,
        RoleRepositoryInterface::class               => RoleRepository::class,
        PermissionRepositoryInterface::class         => PermissionRepository::class,

        // Organisation
        BranchRepositoryInterface::class            => BranchRepository::class,
        DepartmentRepositoryInterface::class        => DepartmentRepository::class,

        // HR
        EmployeeRepositoryInterface::class          => EmployeeRepository::class,
        AttendanceRepositoryInterface::class        => AttendanceRepository::class,
        PayrollRepositoryInterface::class           => PayrollRepository::class,

        // Catalogue
        ProductCategoryRepositoryInterface::class   => ProductCategoryRepository::class,
        ProductRepositoryInterface::class           => ProductRepository::class,
        SupplierRepositoryInterface::class          => SupplierRepository::class,

        // Inventory & transfers
        BranchInventoryRepositoryInterface::class   => BranchInventoryRepository::class,
        StockTransferRepositoryInterface::class     => StockTransferRepository::class,

        // Commerce
        CustomerRepositoryInterface::class          => CustomerRepository::class,
        DiscountRepositoryInterface::class          => DiscountRepository::class,
        SalesTransactionRepositoryInterface::class  => SalesTransactionRepository::class,
        PurchaseOrderRepositoryInterface::class     => PurchaseOrderRepository::class,

        // System
        AuditLogRepositoryInterface::class          => AuditLogRepository::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
         if (request()->header('x-forwarded-proto') === 'https') {
            URL::forceScheme('https');
        }
        Paginator::defaultView('vendor.pagination.simple');
        Paginator::defaultSimpleView('vendor.pagination.simple');
        View::composer('layouts.sidebar', function ($view) {
        $view->with([
            'lowStockCount' => DB::table('branch_inventory')
                ->join('products', 'branch_inventory.product_id', '=', 'products.id')
                ->whereRaw('(branch_inventory.quantity_on_hand - branch_inventory.quantity_reserved) <= products.reorder_level')
                ->where('products.is_active', true)
                ->count(),
            'pendingTransfers' => DB::table('stock_transfers')
                ->where('status', 'pending')
                ->count(),
        ]);
    });
    }
}
