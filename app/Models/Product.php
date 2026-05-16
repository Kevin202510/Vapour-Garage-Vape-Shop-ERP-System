<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku', 'name', 'description', 'image_path',
        'category_id', 'supplier_id',
        'unit_cost', 'selling_price', 'tax_rate',
        'unit_of_measure', 'reorder_level',
        'requires_age_verification', 'is_active',
        'is_new_arrival', 'is_best_seller', 'is_hot',
        'is_on_sale', 'sale_price',
        'is_wholesale', 'min_order_qty',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path ? Storage::url($this->image_path) : null;
    }

    protected $casts = [
        'unit_cost'                => 'decimal:2',
        'selling_price'            => 'decimal:2',
        'sale_price'               => 'decimal:2',
        'tax_rate'                 => 'decimal:2',
        'requires_age_verification'=> 'boolean',
        'is_active'                => 'boolean',
        'is_new_arrival'           => 'boolean',
        'is_best_seller'           => 'boolean',
        'is_hot'                   => 'boolean',
        'is_on_sale'               => 'boolean',
        'is_wholesale'             => 'boolean',
    ];

    public function getDiscountPercentAttribute(): int
    {
        if (! $this->is_on_sale || ! $this->sale_price || $this->selling_price <= 0) return 0;
        return (int) round((1 - $this->sale_price / $this->selling_price) * 100);
    }

    public function getPriceWithTaxAttribute(): float
    {
        return round($this->selling_price * (1 + $this->tax_rate / 100), 2);
    }

    public function getMarginAttribute(): float
    {
        return $this->selling_price > 0
            ? round((($this->selling_price - $this->unit_cost) / $this->selling_price) * 100, 2)
            : 0;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(BranchInventory::class);
    }

    public function salesItems(): HasMany
    {
        return $this->hasMany(SalesTransactionItem::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function transferItems(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }
}
