<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineOrder extends Model
{
    protected $fillable = [
        'order_number', 'first_name', 'last_name', 'email', 'phone',
        'address', 'city', 'pickup_branch_id', 'payment_method', 'notes',
        'subtotal', 'tax_amount', 'total_amount', 'status',
        'payment_reference', 'fulfilled_by', 'fulfilled_at',
    ];

    protected $casts = [
        'subtotal'     => 'decimal:2',
        'tax_amount'   => 'decimal:2',
        'total_amount' => 'decimal:2',
        'fulfilled_at' => 'datetime',
    ];

    public function items()    { return $this->hasMany(OnlineOrderItem::class); }
    public function branch()   { return $this->belongsTo(Branch::class, 'pickup_branch_id'); }
    public function fulfiller(){ return $this->belongsTo(Employee::class, 'fulfilled_by'); }

    public function getFullNameAttribute() { return "{$this->first_name} {$this->last_name}"; }

    public function getStatusLabelAttribute() {
        return match($this->status) {
            'pending'    => 'Pending',
            'confirmed'  => 'Confirmed',
            'processing' => 'Processing',
            'ready'      => 'Ready for Pickup',
            'completed'  => 'Completed',
            'cancelled'  => 'Cancelled',
            default      => ucfirst($this->status),
        };
    }
}
