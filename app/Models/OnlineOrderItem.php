<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineOrderItem extends Model
{
    protected $fillable = ['online_order_id', 'product_id', 'product_name', 'sku', 'quantity', 'unit_price', 'subtotal'];
    protected $casts    = ['unit_price' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function product() { return $this->belongsTo(Product::class); }
    public function order()   { return $this->belongsTo(OnlineOrder::class); }
}
