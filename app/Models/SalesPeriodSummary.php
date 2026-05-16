<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesPeriodSummary extends Model
{
    protected $fillable = [
        'period_type', 'period_label', 'date_from', 'date_to', 'branch_id',
        'total_revenue', 'total_tax', 'total_discounts',
        'total_transactions', 'total_items_sold', 'avg_basket',
        'voided_transactions', 'voided_amount',
        'payment_breakdown',
        'top_product_name', 'top_product_revenue',
        'closed_by', 'closed_by_name', 'closed_at',
    ];

    protected $casts = [
        'date_from'           => 'date',
        'date_to'             => 'date',
        'payment_breakdown'   => 'array',
        'closed_at'           => 'datetime',
        'total_revenue'       => 'decimal:2',
        'total_tax'           => 'decimal:2',
        'total_discounts'     => 'decimal:2',
        'avg_basket'          => 'decimal:2',
        'voided_amount'       => 'decimal:2',
        'top_product_revenue' => 'decimal:2',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
