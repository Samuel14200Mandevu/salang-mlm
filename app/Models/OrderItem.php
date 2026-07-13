<?php
// app/Models/OrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'package_id',
        'name',
        'sku',
        'quantity',
        'price',
        'total',
        'pv_value',
        'bv_value',
        'options',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'total' => 'decimal:2',
        'pv_value' => 'integer',
        'bv_value' => 'integer',
        'options' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function getFormattedTotalAttribute()
    {
        return '$' . number_format($this->total, 2);
    }

    public function getTotalPVAttribute(): int
    {
        return $this->pv_value * $this->quantity;
    }

    public function getTotalBVAttribute(): int
    {
        return $this->bv_value * $this->quantity;
    }

    public function isProduct()
    {
        return !is_null($this->product_id);
    }

    public function isPackage()
    {
        return !is_null($this->package_id);
    }
}