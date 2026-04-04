<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierAndProductSeeder extends Seeder
{
    public function run(): void
    {
        // --- Suppliers ---
        $suppliers = [
            ['company_name' => 'VapePH Distribution Inc.',  'contact_person' => 'Marco Reyes',   'email' => 'marco@vapeph.com',    'phone' => '02-8123-4567', 'tin' => '123-456-789-000'],
            ['company_name' => 'CloudNine Trading Co.',     'contact_person' => 'Ana Santos',    'email' => 'ana@cloudnine.ph',    'phone' => '02-8234-5678', 'tin' => '234-567-890-000'],
            ['company_name' => 'NicVault Wholesale',        'contact_person' => 'Jose Cruz',     'email' => 'jose@nicvault.com',   'phone' => '02-8345-6789', 'tin' => '345-678-901-000'],
            ['company_name' => 'PuffPro Supply Chain',      'contact_person' => 'Lena Garcia',   'email' => 'lena@puffpro.ph',    'phone' => '02-8456-7890', 'tin' => '456-789-012-000'],
        ];

        $createdSuppliers = [];
        foreach ($suppliers as $s) {
            $createdSuppliers[] = Supplier::create(array_merge($s, ['is_active' => true]));
        }

        // --- Categories (parent → children) ---
        $catDevices     = ProductCategory::create(['name' => 'Devices',       'description' => 'Vape hardware and mods']);
        $catDisposables = ProductCategory::create(['name' => 'Disposables',   'description' => 'Single-use vapes', 'parent_category_id' => $catDevices->id]);
        $catMods        = ProductCategory::create(['name' => 'Mods & Kits',   'description' => 'Box mods and starter kits', 'parent_category_id' => $catDevices->id]);
        $catPods        = ProductCategory::create(['name' => 'Pod Systems',   'description' => 'Closed and open pod devices', 'parent_category_id' => $catDevices->id]);
        $catJuice       = ProductCategory::create(['name' => 'E-Liquids',     'description' => 'Freebase and salt nic juices']);
        $catSaltNic     = ProductCategory::create(['name' => 'Salt Nic',      'description' => '30-50mg salt nicotine', 'parent_category_id' => $catJuice->id]);
        $catFreebase    = ProductCategory::create(['name' => 'Freebase',      'description' => '0-6mg freebase juice', 'parent_category_id' => $catJuice->id]);
        $catCoils       = ProductCategory::create(['name' => 'Coils & Parts', 'description' => 'Replacement coils and pods']);
        $catAccessories = ProductCategory::create(['name' => 'Accessories',   'description' => 'Cables, cotton, tools']);

        // --- Products ---
        $products = [
            // Disposables
            ['sku' => 'DIS-001', 'name' => 'Elfbar BC5000 - Watermelon Ice',    'category_id' => $catDisposables->id, 'supplier_id' => $createdSuppliers[0]->id, 'unit_cost' => 180, 'selling_price' => 350,  'reorder_level' => 20, 'uom' => 'pcs'],
            ['sku' => 'DIS-002', 'name' => 'Elfbar BC5000 - Blue Razz Ice',     'category_id' => $catDisposables->id, 'supplier_id' => $createdSuppliers[0]->id, 'unit_cost' => 180, 'selling_price' => 350,  'reorder_level' => 20, 'uom' => 'pcs'],
            ['sku' => 'DIS-003', 'name' => 'Lost Mary 3000 - Strawberry Mango', 'category_id' => $catDisposables->id, 'supplier_id' => $createdSuppliers[1]->id, 'unit_cost' => 140, 'selling_price' => 280,  'reorder_level' => 20, 'uom' => 'pcs'],
            ['sku' => 'DIS-004', 'name' => 'HQD Cuvie Slick 6000',              'category_id' => $catDisposables->id, 'supplier_id' => $createdSuppliers[1]->id, 'unit_cost' => 160, 'selling_price' => 310,  'reorder_level' => 15, 'uom' => 'pcs'],

            // Mods & Kits
            ['sku' => 'MOD-001', 'name' => 'Vaporesso XROS 4 Pod Kit',          'category_id' => $catPods->id,  'supplier_id' => $createdSuppliers[2]->id, 'unit_cost' => 650,  'selling_price' => 1200, 'reorder_level' => 10, 'uom' => 'pcs'],
            ['sku' => 'MOD-002', 'name' => 'Voopoo Drag S Pro Box Mod Kit',     'category_id' => $catMods->id,  'supplier_id' => $createdSuppliers[2]->id, 'unit_cost' => 1500, 'selling_price' => 2800, 'reorder_level' => 5,  'uom' => 'pcs'],
            ['sku' => 'MOD-003', 'name' => 'Geekvape Aegis Legend 3 200W',      'category_id' => $catMods->id,  'supplier_id' => $createdSuppliers[3]->id, 'unit_cost' => 2000, 'selling_price' => 3800, 'reorder_level' => 5,  'uom' => 'pcs'],
            ['sku' => 'MOD-004', 'name' => 'Smok Nord 5 Pod Mod',               'category_id' => $catPods->id,  'supplier_id' => $createdSuppliers[0]->id, 'unit_cost' => 750,  'selling_price' => 1400, 'reorder_level' => 8,  'uom' => 'pcs'],

            // Salt Nic
            ['sku' => 'SLT-001', 'name' => 'Naked 100 Salt - Lava Flow 30mg',   'category_id' => $catSaltNic->id, 'supplier_id' => $createdSuppliers[1]->id, 'unit_cost' => 200, 'selling_price' => 420, 'reorder_level' => 15, 'uom' => 'bottle'],
            ['sku' => 'SLT-002', 'name' => 'Pachamama Salt - Fuji Apple 50mg',  'category_id' => $catSaltNic->id, 'supplier_id' => $createdSuppliers[1]->id, 'unit_cost' => 210, 'selling_price' => 430, 'reorder_level' => 15, 'uom' => 'bottle'],
            ['sku' => 'SLT-003', 'name' => 'Saltbae50 - Strawberry Cream',      'category_id' => $catSaltNic->id, 'supplier_id' => $createdSuppliers[2]->id, 'unit_cost' => 180, 'selling_price' => 380, 'reorder_level' => 10, 'uom' => 'bottle'],

            // Freebase Juices
            ['sku' => 'JCE-001', 'name' => 'Cosmic Fog - Milk & Honey 60ml',    'category_id' => $catFreebase->id, 'supplier_id' => $createdSuppliers[3]->id, 'unit_cost' => 280, 'selling_price' => 550, 'reorder_level' => 10, 'uom' => 'bottle'],
            ['sku' => 'JCE-002', 'name' => 'Nasty Juice - Trap Queen 60ml',     'category_id' => $catFreebase->id, 'supplier_id' => $createdSuppliers[3]->id, 'unit_cost' => 260, 'selling_price' => 520, 'reorder_level' => 10, 'uom' => 'bottle'],

            // Coils & Parts
            ['sku' => 'COL-001', 'name' => 'Vaporesso GTX Coil 0.4ohm (5pcs)', 'category_id' => $catCoils->id, 'supplier_id' => $createdSuppliers[2]->id, 'unit_cost' => 90,  'selling_price' => 180, 'reorder_level' => 20, 'uom' => 'pack'],
            ['sku' => 'COL-002', 'name' => 'Voopoo PnP-VM6 Coil 0.15ohm',      'category_id' => $catCoils->id, 'supplier_id' => $createdSuppliers[2]->id, 'unit_cost' => 80,  'selling_price' => 160, 'reorder_level' => 20, 'uom' => 'pcs'],
            ['sku' => 'COL-003', 'name' => 'Smok Nord 4 RPM 2 Pod Replacement', 'category_id' => $catCoils->id, 'supplier_id' => $createdSuppliers[0]->id, 'unit_cost' => 130, 'selling_price' => 260, 'reorder_level' => 15, 'uom' => 'pack'],

            // Accessories
            ['sku' => 'ACC-001', 'name' => 'USB-C Charging Cable 1m',           'category_id' => $catAccessories->id, 'supplier_id' => $createdSuppliers[3]->id, 'unit_cost' => 45,  'selling_price' => 99,  'reorder_level' => 25, 'uom' => 'pcs'],
            ['sku' => 'ACC-002', 'name' => 'Organic Cotton Pads (20pcs)',        'category_id' => $catAccessories->id, 'supplier_id' => $createdSuppliers[3]->id, 'unit_cost' => 35,  'selling_price' => 80,  'reorder_level' => 20, 'uom' => 'pack'],
            ['sku' => 'ACC-003', 'name' => 'Drip Tip 510 Mouthpiece Wide Bore', 'category_id' => $catAccessories->id, 'supplier_id' => $createdSuppliers[1]->id, 'unit_cost' => 60,  'selling_price' => 130, 'reorder_level' => 15, 'uom' => 'pcs'],
            ['sku' => 'ACC-004', 'name' => '18650 Battery Wrap (10pcs)',         'category_id' => $catAccessories->id, 'supplier_id' => $createdSuppliers[0]->id, 'unit_cost' => 30,  'selling_price' => 70,  'reorder_level' => 20, 'uom' => 'pack'],
        ];

        foreach ($products as $p) {
            Product::create([
                'sku'                       => $p['sku'],
                'name'                      => $p['name'],
                'description'               => null,
                'category_id'               => $p['category_id'],
                'supplier_id'               => $p['supplier_id'],
                'unit_cost'                 => $p['unit_cost'],
                'selling_price'             => $p['selling_price'],
                'tax_rate'                  => 12.00,
                'unit_of_measure'           => $p['uom'],
                'reorder_level'             => $p['reorder_level'],
                'requires_age_verification' => ! str_starts_with($p['sku'], 'ACC'),
                'is_active'                 => true,
            ]);
        }

        $this->command->info('✔ ' . count($createdSuppliers) . ' suppliers and ' . count($products) . ' products seeded.');
    }
}
