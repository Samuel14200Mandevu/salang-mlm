<?php
// app/Models/Product.php

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
        'pv_value',
        'bv_value',
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
        'pv_value' => 'integer',
        'bv_value' => 'integer',
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

    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function getStockLabelAttribute()
    {
        if ($this->stock > 10) {
            return 'In Stock';
        } elseif ($this->stock > 0) {
            return 'Low Stock';
        }
        return 'Out of Stock';
    }

    public function getStockStatusClassAttribute()
    {
        if ($this->stock > 10) {
            return 'text-green-600';
        } elseif ($this->stock > 0) {
            return 'text-yellow-600';
        }
        return 'text-red-600';
    }

    public function isInStock()
    {
        return $this->stock > 0;
    }

    public function getProfitAttribute()
    {
        if ($this->cost) {
            return $this->price - $this->cost;
        }
        return null;
    }

    public function getProfitMarginAttribute()
    {
        if ($this->cost && $this->cost > 0) {
            return (($this->price - $this->cost) / $this->price) * 100;
        }
        return null;
    }
}