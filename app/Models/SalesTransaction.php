<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'branch_id',
        'cashier_id',
        'customer_id',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'amount_tendered',
        'change_amount',
        'payment_method',
        'status',
        'void_reason',
        'transaction_date',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'amount_tendered'  => 'decimal:2',
        'change_amount'    => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    public static function generateTransactionNumber(int $branchId): string
    {
        $prefix = 'TXN-' . str_pad($branchId, 2, '0', STR_PAD_LEFT);
        $date   = now()->format('Ymd');
        $last   = static::where('transaction_number', 'like', "{$prefix}-{$date}-%")
                        ->lockForUpdate()
                        ->count();
        return "{$prefix}-{$date}-" . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'cashier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesTransactionItem::class, 'transaction_id');
    }
}
