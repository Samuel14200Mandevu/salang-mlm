<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public const EXPIRY_ALERT_DAYS = 90;

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
        'min_stock',
        'expires_at',
        'sku',
        'ean',
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
        'min_stock' => 'integer',
        'expires_at' => 'date',
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

    public function scopeOutOfStock($query)
    {
        return $query->where('stock', '<=', 0);
    }

    /** Stock > 0 et ≤ seuil min (à commander). */
    public function scopeBelowMinStock($query)
    {
        return $query->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'min_stock');
    }

    public function scopeExpiringSoon($query, ?int $withinDays = null)
    {
        $days = $withinDays ?? self::EXPIRY_ALERT_DAYS;
        $today = now()->startOfDay();

        return $query->whereNotNull('expires_at')
            ->whereDate('expires_at', '>=', $today)
            ->whereDate('expires_at', '<=', $today->copy()->addDays($days));
    }

    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->whereDate('expires_at', '<', now()->startOfDay());
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

    public function minStockThreshold(): int
    {
        $min = (int) ($this->min_stock ?? 10);

        return max(0, $min);
    }

    public function isOutOfStock(): bool
    {
        return (int) $this->stock <= 0;
    }

    public function isBelowMinStock(): bool
    {
        return ! $this->isOutOfStock() && (int) $this->stock <= $this->minStockThreshold();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->lt(now()->startOfDay());
    }

    public function isExpiringSoon(?int $withinDays = null): bool
    {
        if ($this->expires_at === null || $this->isExpired()) {
            return false;
        }

        $days = $withinDays ?? self::EXPIRY_ALERT_DAYS;

        return $this->expires_at->lte(now()->startOfDay()->addDays($days));
    }

    public function stockValue(): float
    {
        return round((float) $this->stock * (float) ($this->cost ?? 0), 2);
    }

    /**
     * Statut pharmacie (aligné kit Excel) : PÉRIME / PÉRIME BIENTÔT / RUPTURE / À COMMANDER / OK.
     */
    public function pharmacyStatus(): string
    {
        if ($this->isExpired()) {
            return 'expired';
        }
        if ($this->isExpiringSoon()) {
            return 'expiring';
        }
        if ($this->isOutOfStock()) {
            return 'out';
        }
        if ($this->isBelowMinStock()) {
            return 'reorder';
        }

        return 'ok';
    }

    public function pharmacyStatusLabel(): string
    {
        return match ($this->pharmacyStatus()) {
            'expired' => 'Périmé',
            'expiring' => 'Périme bientôt',
            'out' => 'Rupture',
            'reorder' => 'À commander',
            default => 'OK',
        };
    }

    public function pharmacyStatusBadgeClass(): string
    {
        return match ($this->pharmacyStatus()) {
            'expired', 'out' => 'badge-danger',
            'expiring', 'reorder' => 'badge-warning',
            default => 'badge-success',
        };
    }

    public function getStockLabelAttribute()
    {
        return match ($this->pharmacyStatus()) {
            'expired' => 'Périmé',
            'expiring' => 'Périme bientôt',
            'out' => 'Rupture de stock',
            'reorder' => 'Stock faible',
            default => 'En stock',
        };
    }

    public function getStockStatusClassAttribute()
    {
        return $this->pharmacyStatusBadgeClass();
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

    /**
     * Prix catalogue selon la portion (1/2 uniquement si half_price/half_pv renseignés).
     */
    public function priceForPortion(?string $portion): float
    {
        if ($portion === 'half' && $this->hasHalfOption()) {
            return round((float) $this->half_price, 2);
        }

        return round((float) ($this->full_price ?? $this->price), 2);
    }

    /**
     * PV catalogue selon la portion.
     */
    public function pvForPortion(?string $portion): int
    {
        if ($portion === 'half' && $this->hasHalfOption()) {
            return (int) $this->half_pv;
        }

        return (int) ($this->full_pv ?? $this->pv_value ?? 0);
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

    // Code-barres (Code 128 = SKU)
    public function getBarcodeUrlAttribute()
    {
        if (isset($this->metadata['barcode_svg'])) {
            return asset('storage/'.$this->metadata['barcode_svg']);
        }

        return null;
    }

    public function getBarcodeBase64Attribute()
    {
        return $this->metadata['barcode_base64'] ?? null;
    }

    /** @deprecated Utiliser barcode_url */
    public function getQrCodeUrlAttribute()
    {
        return $this->barcode_url
            ?? (isset($this->metadata['qr_code_svg']) ? asset('storage/'.$this->metadata['qr_code_svg']) : null);
    }

    /** @deprecated Utiliser barcode_base64 */
    public function getQrCodeBase64Attribute()
    {
        return $this->barcode_base64 ?? ($this->metadata['qr_base64'] ?? null);
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

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class)->latest();
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