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
        if ($this->benefits) {
            return is_array($this->benefits) ? implode(', ', $this->benefits) : $this->benefits;
        }
        
        // Bénéfices par défaut selon le package
        $defaultBenefits = [
            1 => ['Commission jusqu\'à 30%', 'Accès à la boutique', 'Parrainage illimité'],
            2 => ['Commission jusqu\'à 30%', 'Accès à la boutique', 'Parrainage illimité', 'Bonus de parrainage'],
            3 => ['Commission jusqu\'à 30%', 'Accès à la boutique', 'Parrainage illimité', 'Bonus de parrainage', 'Formation incluse'],
            4 => ['Commission jusqu\'à 30%', 'Accès à la boutique', 'Parrainage illimité', 'Bonus de parrainage', 'Formation incluse', 'Support prioritaire'],
            5 => ['Commission jusqu\'à 30%', 'Accès à la boutique', 'Parrainage illimité', 'Bonus de parrainage', 'Formation incluse', 'Support prioritaire', 'Événements exclusifs'],
        ];
        
        return implode(', ', $defaultBenefits[$this->id] ?? ['Commission jusqu\'à 30%', 'Accès à la boutique']);
    }

    public function getIconAttribute()
    {
        $icons = [
            1 => '🚀',
            2 => '🥈',
            3 => '🥉',
            4 => '🥇',
            5 => '💎',
        ];
        return $icons[$this->id] ?? '📦';
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