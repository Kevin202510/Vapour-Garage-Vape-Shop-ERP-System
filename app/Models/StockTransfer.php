<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number',
        'from_branch_id',
        'to_branch_id',
        'requested_by',
        'approved_by',
        'status',
        'notes',
        'rejection_reason',
        'requested_at',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at'  => 'datetime',
        'completed_at' => 'datetime',
    ];

    public static function generateTransferNumber(): string
    {
        $date  = now()->format('Ymd');
        $count = static::whereDate('requested_at', now()->toDateString())
                        ->lockForUpdate()
                        ->count();
        return 'ST-' . $date . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }

    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class, 'transfer_id');
    }
}
