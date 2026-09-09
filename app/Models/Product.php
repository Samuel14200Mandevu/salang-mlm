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
        'half_price',
        'half_pv',
        'full_price',
        'full_pv',
        'pv_value',
        'cost',
        'stock',
        'sku',
        'category',
        'unit',
        'packaging',
        'dosage',
        'image',
        'gallery',
        'is_active',
        'is_featured',
        'metadata',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'half_price' => 'decimal:2',
        'full_price' => 'decimal:2',
        'pv_value' => 'integer',
        'half_pv' => 'integer',
        'full_pv' => 'integer',
        'cost' => 'decimal:2',
        'stock' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'gallery' => 'array',
        'metadata' => 'array',
    ];

    // Suppression de bv_value des casts car il n'existe pas

    public function getRouteKeyName()
    {
        return 'slug';
    }

    // Scopes
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

    // Accesseurs
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function getDisplayPriceAttribute()
    {
        return $this->full_price ?? $this->price;
    }

    public function getDisplayPvAttribute()
    {
        return $this->full_pv ?? $this->pv_value;
    }

    public function getStockLabelAttribute()
    {
        if ($this->stock > 10) {
            return 'En stock';
        } elseif ($this->stock > 0) {
            return 'Stock faible';
        }
        return 'Rupture de stock';
    }

    public function getStockStatusClassAttribute()
    {
        if ($this->stock > 10) {
            return 'badge-success';
        } elseif ($this->stock > 0) {
            return 'badge-warning';
        }
        return 'badge-danger';
    }

    // Méthodes
    public function isInStock()
    {
        return $this->stock > 0;
    }

    public function hasHalfOption(): bool
    {
        return !is_null($this->half_price) && !is_null($this->half_pv);
    }

    public function hasFullOption(): bool
    {
        return !is_null($this->full_price) && !is_null($this->full_pv);
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

    // QR Code
    public function getQrCodeUrlAttribute()
    {
        if (isset($this->metadata['qr_code_svg'])) {
            return asset('storage/' . $this->metadata['qr_code_svg']);
        }
        return null;
    }

    public function getQrCodeBase64Attribute()
    {
        return $this->metadata['qr_base64'] ?? null;
    }

    // Relations
    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isInWishlist($userId)
    {
        return $this->wishlist()->where('user_id', $userId)->exists();
    }

    public function getWishlistCountAttribute()
    {
        return $this->wishlist()->count();
    }

    public function getCartCountAttribute()
    {
        return $this->cart()->count();
    }
}