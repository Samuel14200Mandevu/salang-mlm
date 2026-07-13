<?php
// app/Models/Package.php

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
        if ($this->benefits) {
            return is_array($this->benefits) ? implode(', ', $this->benefits) : $this->benefits;
        }
        
        $defaultBenefits = [
            1 => ['Commission up to 30%', 'Shop access', 'Unlimited referrals'],
            2 => ['Commission up to 30%', 'Shop access', 'Unlimited referrals', 'Referral bonus'],
            3 => ['Commission up to 30%', 'Shop access', 'Unlimited referrals', 'Referral bonus', 'Training included'],
            4 => ['Commission up to 30%', 'Shop access', 'Unlimited referrals', 'Referral bonus', 'Training included', 'Priority support'],
            5 => ['Commission up to 30%', 'Shop access', 'Unlimited referrals', 'Referral bonus', 'Training included', 'Priority support', 'Exclusive events'],
        ];
        
        return implode(', ', $defaultBenefits[$this->id] ?? ['Commission up to 30%', 'Shop access']);
    }

    public function getIconAttribute()
    {
        $icons = [
            1 => 'rocket',
            2 => 'silver',
            3 => 'bronze',
            4 => 'gold',
            5 => 'diamond',
        ];
        return $icons[$this->id] ?? 'package';
    }

    public function getColorAttribute()
    {
        $colors = [
            1 => 'primary',
            2 => 'info',
            3 => 'warning',
            4 => 'purple',
            5 => 'success',
        ];
        return $colors[$this->id] ?? 'primary';
    }
}