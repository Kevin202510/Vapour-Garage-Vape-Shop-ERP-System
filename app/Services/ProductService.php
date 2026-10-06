<?php

namespace App\Services;

use App\Contracts\Repositories\AuditLogRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ProductService
{
    /**
     * Spreadsheet/CSV import column headers → canonical field keys. Header
     * matching is case-insensitive and strips any "(...)" annotation, so
     * "Tax Rate (%)" and "tax rate" both resolve to 'tax_rate'.
     */
    private const IMPORT_COLUMNS = [
        'sku'             => 'sku',
        'name'            => 'name',
        'description'     => 'description',
        'category'        => 'category',
        'supplier'        => 'supplier',
        'unit cost'       => 'unit_cost',
        'selling price'   => 'selling_price',
        'tax rate'        => 'tax_rate',
        'unit of measure' => 'unit_of_measure',
        'reorder level'   => 'reorder_level',
        'age restricted'  => 'requires_age_verification',
        'active'          => 'is_active',
        'on sale'         => 'is_on_sale',
        'sale price'      => 'sale_price',
        'wholesale'       => 'is_wholesale',
        'min order qty'   => 'min_order_qty',
    ];

    public function __construct(
        protected ProductRepositoryInterface  $repo,
        protected AuditLogRepositoryInterface $auditRepo,
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repo->paginate($perPage, $filters);
    }

    public function findOrFail(int $id): Product
    {
        return $this->repo->findById($id) ?? throw new ModelNotFoundException("Product [{$id}] not found.");
    }

    public function findBySkuOrFail(string $sku): Product
    {
        return $this->repo->findBySku($sku) ?? throw new ModelNotFoundException("Product SKU [{$sku}] not found.");
    }

    public function create(array $data): Product
    {
        $this->guardDuplicateSku($data['sku']);
        $product = $this->repo->create($data);

        // Create a zero-stock inventory record for every active branch so the
        // product appears in the inventory list and POS immediately after creation.
        Branch::where('is_active', true)->each(function (Branch $branch) use ($product) {
            BranchInventory::firstOrCreate(
                ['branch_id' => $branch->id, 'product_id' => $product->id],
                ['quantity_on_hand' => 0, 'quantity_reserved' => 0]
            );
        });

        return $product;
    }

    public function update(int $id, array $data): Product
    {
        $product = $this->findOrFail($id);

        if (isset($data['sku']) && $data['sku'] !== $product->sku) {
            $this->guardDuplicateSku($data['sku'], $id);
        }

        return $this->repo->update($product, $data);
    }

    public function delete(int $id): bool
    {
        return $this->repo->delete($this->findOrFail($id));
    }

    public function restore(int $id): bool
    {
        return $this->repo->restore($id);
    }

    public function getLowStock(int $branchId): Collection
    {
        return $this->repo->getLowStock($branchId);
    }

    private function guardDuplicateSku(string $sku, ?int $excludeId = null): void
    {
        $exists = Product::where('sku', $sku)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->withTrashed()
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages(['sku' => ['This SKU already exists.']]);
        }
    }

    // -------------------------------------------------------------------------
    // Bulk import (CSV / Excel)
    // -------------------------------------------------------------------------

    public static function importTemplateHeaders(): array
    {
        return [
            'SKU', 'Name', 'Description', 'Category', 'Supplier',
            'Unit Cost', 'Selling Price', 'Tax Rate (%)', 'Unit of Measure', 'Reorder Level',
            'Age Restricted (Yes/No)', 'Active (Yes/No)', 'On Sale (Yes/No)', 'Sale Price',
            'Wholesale (Yes/No)', 'Min Order Qty',
        ];
    }

    public static function importTemplateExampleRow(): array
    {
        return [
            'VG-001', 'Sample Disposable Vape 5000 Puffs', 'Fruit mix flavor, rechargeable.',
            'Disposables', 'Acme Vape Supply', '350.00', '599.00', '12', 'pc', '10',
            'Yes', 'Yes', 'No', '', 'No', '',
        ];
    }

    /**
     * Resolve an uploaded file's header cell to a canonical field key, or
     * null if it isn't a recognised import column (such a column is simply
     * ignored rather than rejecting the whole file).
     */
    public static function normalizeImportHeader(string $header): ?string
    {
        $clean = strtolower(trim(preg_replace('/\(.*?\)/', '', $header) ?? $header));
        $clean = preg_replace('/\s+/', ' ', $clean) ?? $clean;

        return self::IMPORT_COLUMNS[$clean] ?? null;
    }

    /**
     * Import products from parsed rows (array of [canonical_field => raw value]).
     * A row whose SKU already exists (including soft-deleted) updates that
     * product — and restores it if it was archived-deleted — otherwise a new
     * product is created. Rows are processed independently so one bad row
     * doesn't abort the rest of the file; failures are collected and returned.
     *
     * @param array<int, array<string, mixed>> $rows
     * @return array{created: int, updated: int, skipped: int, errors: array<int, array{row: int, message: string}>}
     */
    public function import(array $rows, ?int $actingEmployeeId = null, ?string $ipAddress = null): array
    {
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors  = [];

        foreach ($rows as $i => $row) {
            $rowNum = $i + 2; // +1 for 0-index, +1 for the header row

            try {
                $data = $this->mapImportRow($row);

                $existing = Product::withTrashed()->where('sku', $data['sku'])->first();

                if ($existing) {
                    if ($existing->trashed()) {
                        $existing->restore();
                    }
                    $this->repo->update($existing, $data);
                    $updated++;
                } else {
                    $this->create($data);
                    $created++;
                }
            } catch (ValidationException $e) {
                $skipped++;
                if (count($errors) < 100) {
                    $errors[] = ['row' => $rowNum, 'message' => implode(' ', collect($e->errors())->flatten()->all())];
                }
            } catch (\Throwable $e) {
                $skipped++;
                if (count($errors) < 100) {
                    $errors[] = ['row' => $rowNum, 'message' => $e->getMessage()];
                }
            }
        }

        $this->auditRepo->log(
            tableName:  'products',
            recordId:   0,
            action:     'create',
            newValues:  ['created' => $created, 'updated' => $updated, 'skipped' => $skipped],
            employeeId: $actingEmployeeId,
            ipAddress:  $ipAddress,
        );

        return compact('created', 'updated', 'skipped', 'errors');
    }

    /**
     * @throws ValidationException
     */
    private function mapImportRow(array $row): array
    {
        $sku  = $this->blankToNull($row['sku'] ?? null);
        $name = $this->blankToNull($row['name'] ?? null);

        if (! $sku || ! $name) {
            throw ValidationException::withMessages(['row' => ['SKU and Name are required.']]);
        }

        $unitCost     = $row['unit_cost'] ?? null;
        $sellingPrice = $row['selling_price'] ?? null;

        if (! is_numeric($unitCost) || ! is_numeric($sellingPrice)) {
            throw ValidationException::withMessages(['row' => ['Unit Cost and Selling Price must be numeric.']]);
        }

        $data = [
            'sku'                        => $sku,
            'name'                       => $name,
            'description'                => $this->blankToNull($row['description'] ?? null),
            'unit_cost'                  => round((float) $unitCost, 2),
            'selling_price'              => round((float) $sellingPrice, 2),
            'tax_rate'                   => is_numeric($row['tax_rate'] ?? null) ? round((float) $row['tax_rate'], 2) : 0,
            'unit_of_measure'            => $this->blankToNull($row['unit_of_measure'] ?? null) ?? 'pcs',
            'reorder_level'              => is_numeric($row['reorder_level'] ?? null) ? (int) $row['reorder_level'] : 0,
            'requires_age_verification'  => $this->toBool($row['requires_age_verification'] ?? null),
            'is_active'                  => $this->toBool($row['is_active'] ?? null, true),
            'is_on_sale'                 => $this->toBool($row['is_on_sale'] ?? null),
            'is_wholesale'               => $this->toBool($row['is_wholesale'] ?? null),
            'sale_price'                 => is_numeric($row['sale_price'] ?? null) ? round((float) $row['sale_price'], 2) : null,
            'min_order_qty'              => is_numeric($row['min_order_qty'] ?? null) ? (int) $row['min_order_qty'] : null,
        ];

        if (! $data['is_on_sale'])   $data['sale_price']    = null;
        if (! $data['is_wholesale']) $data['min_order_qty'] = null;

        if ($categoryName = $this->blankToNull($row['category'] ?? null)) {
            $category = ProductCategory::where('name', $categoryName)->first();
            if (! $category) {
                throw ValidationException::withMessages(['row' => ["Category \"{$categoryName}\" not found."]]);
            }
            $data['category_id'] = $category->id;
        }

        if ($supplierName = $this->blankToNull($row['supplier'] ?? null)) {
            $supplier = Supplier::where('company_name', $supplierName)->first();
            if (! $supplier) {
                throw ValidationException::withMessages(['row' => ["Supplier \"{$supplierName}\" not found."]]);
            }
            $data['supplier_id'] = $supplier->id;
        }

        return $data;
    }

    private function blankToNull(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function toBool(mixed $value, bool $default = false): bool
    {
        if ($value === null || trim((string) $value) === '') {
            return $default;
        }
        return in_array(strtolower(trim((string) $value)), ['yes', 'y', 'true', '1', 'active'], true);
    }
}
