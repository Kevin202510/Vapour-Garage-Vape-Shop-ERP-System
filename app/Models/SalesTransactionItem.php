<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTransactionItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'discount_id',
        'quantity',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'subtotal',
    ];

    protected $casts = [
        'unit_price'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'subtotal'        => 'decimal:2',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(SalesTransaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }
}
