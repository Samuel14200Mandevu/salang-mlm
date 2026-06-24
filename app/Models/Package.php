<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'price',
        'pv_value',
        'bv_value',
        'commission_rate',
        'description',
        'benefits',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'pv_value' => 'integer',
        'bv_value' => 'integer',
        'commission_rate' => 'decimal:2',
        'benefits' => 'array',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function getBenefitListAttribute()
    {
        return $this->benefits ? implode(', ', $this->benefits) : '';
    }
}
