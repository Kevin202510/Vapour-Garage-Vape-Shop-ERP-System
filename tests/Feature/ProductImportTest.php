<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    private function userWithCreateProducts(): User
    {
        Permission::firstOrCreate(['name' => 'create products', 'guard_name' => 'sanctum']);
        $branch = Branch::factory()->create();
        $user   = User::factory()->create(['branch_id' => $branch->id]);
        $user->givePermissionTo('create products');

        return $user;
    }

    private function csvUpload(string $content, string $filename = 'import.csv'): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'import') . '.csv';
        file_put_contents($path, $content);

        return new UploadedFile($path, $filename, 'text/csv', null, true);
    }

    public function test_a_user_without_permission_cannot_import(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('products.import.form'))->assertForbidden();
        $this->actingAs($user)->get(route('products.import.template'))->assertForbidden();
    }

    public function test_the_template_downloads_as_csv_with_the_expected_headers(): void
    {
        $user = $this->userWithCreateProducts();

        $response = $this->actingAs($user)->get(route('products.import.template'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('SKU,Name,Description,Category,Supplier', $response->streamedContent());
    }

    public function test_it_creates_updates_and_skips_rows_from_a_csv_upload(): void
    {
        $user     = $this->userWithCreateProducts();
        $category = ProductCategory::factory()->create(['name' => 'Disposables']);
        $supplier = Supplier::factory()->create(['company_name' => 'Acme Vape Supply']);
        Branch::factory()->count(2)->create(['is_active' => true]);

        $existing = Product::factory()->create(['sku' => 'EXISTING-SKU-01', 'name' => 'Old Name']);

        $csv = "SKU,Name,Description,Category,Supplier,Unit Cost,Selling Price,Tax Rate (%),Unit of Measure,Reorder Level,Age Restricted (Yes/No),Active (Yes/No),On Sale (Yes/No),Sale Price,Wholesale (Yes/No),Min Order Qty\n"
            . "NEW-SKU-01,Brand New Product,A description,Disposables,Acme Vape Supply,350.00,599.00,12,pc,10,Yes,Yes,No,,No,\n"
            . "EXISTING-SKU-01,Updated Name,,,,100,150,,,,,,,,,\n"
            . ",Missing SKU Row,,,,10,20,,,,,,,,,\n"
            . "BAD-CATEGORY-ROW,Has Bad Category,,Not A Real Category,,10,20,,,,,,,,,\n";

        $response = $this->actingAs($user)
            ->post(route('products.import'), ['file' => $this->csvUpload($csv)]);

        $response->assertRedirect(route('products.import.form'));
        $response->assertSessionHas('importResult', function ($result) {
            return $result['created'] === 1 && $result['updated'] === 1 && $result['skipped'] === 2;
        });

        $created = Product::where('sku', 'NEW-SKU-01')->first();
        $this->assertNotNull($created);
        $this->assertSame('Brand New Product', $created->name);
        $this->assertSame($category->id, $created->category_id);
        $this->assertSame($supplier->id, $created->supplier_id);
        $this->assertSame('pc', $created->unit_of_measure);
        $this->assertSame(
            BranchInventory::where('product_id', $created->id)->count(),
            Branch::where('is_active', true)->count()
        );

        $this->assertSame('Updated Name', $existing->fresh()->name);

        $this->assertDatabaseMissing('products', ['sku' => '']);
        $this->assertDatabaseMissing('products', ['sku' => 'BAD-CATEGORY-ROW']);

        $this->assertDatabaseHas('audit_logs', ['table_name' => 'products', 'action' => 'create']);
    }

    public function test_re_importing_the_same_sku_restores_a_soft_deleted_product(): void
    {
        $user    = $this->userWithCreateProducts();
        $product = Product::factory()->create(['sku' => 'DELETED-SKU-01']);
        $product->delete();
        $this->assertTrue($product->fresh()->trashed());

        $csv = "SKU,Name,Unit Cost,Selling Price\n"
            . "DELETED-SKU-01,Revived Product,100,150\n";

        $this->actingAs($user)->post(route('products.import'), ['file' => $this->csvUpload($csv)]);

        $product->refresh();
        $this->assertFalse($product->trashed());
        $this->assertSame('Revived Product', $product->name);
    }

    public function test_it_imports_a_real_xlsx_file(): void
    {
        $user = $this->userWithCreateProducts();
        Branch::factory()->create(['is_active' => true]);

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->fromArray([
            ['SKU', 'Name', 'Unit Cost', 'Selling Price'],
            ['XLSX-SKU-01', 'Excel Imported Product', '400', '750'],
        ], null, 'A1');

        $path = tempnam(sys_get_temp_dir(), 'import') . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $file = new UploadedFile($path, 'import.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $response = $this->actingAs($user)->post(route('products.import'), ['file' => $file]);

        $response->assertSessionHas('importResult', fn ($result) => $result['created'] === 1);
        $this->assertDatabaseHas('products', ['sku' => 'XLSX-SKU-01', 'name' => 'Excel Imported Product']);
    }

    public function test_an_empty_file_is_rejected_with_a_clear_error(): void
    {
        $user = $this->userWithCreateProducts();

        $response = $this->actingAs($user)
            ->post(route('products.import'), ['file' => $this->csvUpload("SKU,Name,Unit Cost,Selling Price\n")]);

        $response->assertSessionHasErrors('file');
    }
}
