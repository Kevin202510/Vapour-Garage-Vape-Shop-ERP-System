<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds in dependency order.
     *
     * Order matters:
     *  1. Branches               — anchor for everything
     *  2. Departments            — needed by employees
     *  3. Employees              — needed by transactions, POs, transfers, payroll
     *  4. Suppliers + Products   — needed by inventory, POs, transactions
     *  5. Inventory              — branch × product stock levels
     *  6. Customers              — needed by sales transactions
     *  7. Discounts              — needed by sales transaction items
     *  8. Sales Transactions     — depends on branches, employees, customers, products
     *  9. Purchase Orders        — depends on branches, employees, suppliers, products
     * 10. Stock Transfers        — depends on branches, employees, products
     * 11. Attendance + Payroll   — depends on employees
     */
    public function run(): void
    {
        $this->call([
            BranchSeeder::class,
            DepartmentSeeder::class,
            // EmployeeSeeder::class,
            // SupplierAndProductSeeder::class,
            // InventorySeeder::class,
            // CustomerSeeder::class,
            DiscountSeeder::class,
            // SalesTransactionSeeder::class,
            // OnlineOrderSeeder::class,
            // PurchaseOrderSeeder::class,
            // StockTransferSeeder::class,
            // AttendanceAndPayrollSeeder::class,
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            AdminUserSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ VapeHub ERP database seeded successfully.');
    }
}
