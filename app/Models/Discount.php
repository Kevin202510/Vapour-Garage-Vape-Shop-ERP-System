<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'value',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'value'      => 'decimal:2',
        'valid_from' => 'date',
        'valid_until'=> 'date',
        'is_active'  => 'boolean',
    ];

    public function isCurrentlyValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }
        $today = now()->toDateString();
        if ($this->valid_from && $this->valid_from->gt(now())) {
            return false;
        }
        if ($this->valid_until && $this->valid_until->lt(now())) {
            return false;
        }
        return true;
    }

    public function computeDiscount(float $amount): float
    {
        return match ($this->type) {
            'percentage'   => round($amount * $this->value / 100, 2),
            'fixed_amount' => min($this->value, $amount),
            default        => 0,
        };
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(SalesTransactionItem::class);
    }
}
