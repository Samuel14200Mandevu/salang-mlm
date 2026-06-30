<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'cost',
        'stock',
        'sku',
        'category',
        'image',
        'gallery',
        'is_active',
        'is_featured',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'gallery' => 'array',
        'metadata' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function getStockLabelAttribute()
    {
        if ($this->stock > 10) {
            return '<span class="text-green-600">En stock</span>';
        } elseif ($this->stock > 0) {
            return '<span class="text-yellow-600">Stock faible</span>';
        }
        return '<span class="text-red-600">Rupture</span>';
    }

    public function isInStock()
    {
        return $this->stock > 0;
    }
}
