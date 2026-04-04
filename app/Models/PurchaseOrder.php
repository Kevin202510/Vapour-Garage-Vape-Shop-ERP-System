<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'branch_id',
        'created_by',
        'approved_by',
        'status',
        'total_amount',
        'order_date',
        'expected_date',
        'received_date',
        'notes',
    ];

    protected $casts = [
        'total_amount'  => 'decimal:2',
        'order_date'    => 'date',
        'expected_date' => 'date',
        'received_date' => 'date',
    ];

    public static function generatePoNumber(int $branchId): string
    {
        $prefix = 'PO-' . str_pad($branchId, 2, '0', STR_PAD_LEFT);
        $year   = now()->format('Y');
        $count  = static::where('po_number', 'like', "{$prefix}-{$year}-%")->count();
        return "{$prefix}-{$year}-" . str_pad($count + 1, 5, '0', STR_PAD_LEFT);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'po_id');
    }
}
