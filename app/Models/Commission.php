<?php
// app/Models/Commission.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_user_id',
        'commission_period_id',
        'period',
        'type',
        'amount',
        'percentage',
        'description',
        'notes',
        'order_id',
        'package_id',
        'product_id',
        'generation',
        'calculation_type',
        'status',
        'paid_at',
        'approved_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'paid_at' => 'datetime',
        'approved_at' => 'datetime',
        'generation' => 'integer',
    ];

    // ============================================================
    // RELATIONS
    // ============================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function period()
    {
        return $this->belongsTo(CommissionPeriod::class, 'commission_period_id');
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeDirect($query)
    {
        return $query->where('type', 'direct');
    }

    public function scopeIndirect($query)
    {
        return $query->where('type', 'indirect');
    }

    public function scopeLeadership($query)
    {
        return $query->where('type', 'leadership');
    }

    public function scopeRetail($query)
    {
        return $query->where('type', 'retail');
    }

    public function scopeSponsor($query)
    {
        return $query->where('type', 'sponsor');
    }

    public function scopeGlobal($query)
    {
        return $query->where('type', 'global');
    }

    public function scopeConsumer($query)
    {
        return $query->where('type', 'consumer');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeFromUser($query, $userId)
    {
        return $query->where('from_user_id', $userId);
    }

    public function scopeForPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeForPackage($query, $packageId)
    {
        return $query->where('package_id', $packageId);
    }

    public function scopeForProduct($query, $productId)
    {
        return $query->where('product_id', $productId);
    }

    // ============================================================
    // ACCESSEURS
    // ============================================================

    public function getTypeLabelAttribute()
    {
        $labels = [
            'sponsor' => 'Sponsor Bonus',
            'direct' => 'Direct Bonus',
            'indirect' => 'Indirect Bonus',
            'leadership' => 'Leadership Bonus',
            'retail' => 'Retail Profit',
            'global' => 'Global Bonus',
            'consumer' => 'Consumer Bonus',
        ];
        return $labels[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'En attente',
            'paid' => 'Payé',
            'approved' => 'Approuvé',
            'cancelled' => 'Annulé',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'paid' => 'success',
            'approved' => 'success',
            'cancelled' => 'danger',
        ];
        return $colors[$this->status] ?? 'neutral';
    }

    public function getTypeColorAttribute()
    {
        $colors = [
            'sponsor' => 'primary',
            'direct' => 'blue',
            'indirect' => 'green',
            'leadership' => 'purple',
            'retail' => 'orange',
            'global' => 'gold',
            'consumer' => 'teal',
        ];
        return $colors[$this->type] ?? 'gray';
    }

    public function getTypeIconAttribute()
    {
        $icons = [
            'sponsor' => '👤',
            'direct' => '⬇️',
            'indirect' => '↘️',
            'leadership' => '⭐',
            'retail' => '🛒',
            'global' => '🌍',
            'consumer' => '👥',
        ];
        return $icons[$this->type] ?? '📊';
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getItemNameAttribute()
    {
        if ($this->package_id && $this->package) {
            return $this->package->name;
        }
        
        if ($this->product_id && $this->product) {
            return $this->product->name;
        }
        
        return 'N/A';
    }

    public function getItemTypeAttribute()
    {
        if ($this->package_id) {
            return 'package';
        }
        
        if ($this->product_id) {
            return 'product';
        }
        
        return 'unknown';
    }

    public function getItemTypeLabelAttribute()
    {
        if ($this->package_id) {
            return '📦 Package';
        }
        
        if ($this->product_id) {
            return '🛒 Produit';
        }
        
        return 'N/A';
    }

    public function getDescriptionWithDetailsAttribute()
    {
        $desc = $this->description;
        
        if ($this->fromUser) {
            $desc .= " (de " . $this->fromUser->name . ")";
        }
        
        if ($this->generation) {
            $desc .= " - Génération " . $this->generation;
        }
        
        if ($this->package_id && $this->package) {
            $desc .= " - Package: " . $this->package->name;
        }
        
        if ($this->product_id && $this->product) {
            $desc .= " - Produit: " . $this->product->name;
        }
        
        return $desc;
    }

    public function getIsPaidAttribute()
    {
        return $this->status === 'paid';
    }

    public function getIsPendingAttribute()
    {
        return $this->status === 'pending';
    }

    public function getIsCancelledAttribute()
    {
        return $this->status === 'cancelled';
    }

    public function getIsApprovedAttribute()
    {
        return $this->status === 'approved';
    }

    // ============================================================
    // MÉTHODES
    // ============================================================

    /**
     * Marquer la commission comme payée
     */
    public function markAsPaid()
    {
        $this->status = 'paid';
        $this->paid_at = now();
        $this->save();
    }

    /**
     * Marquer la commission comme approuvée
     */
    public function markAsApproved()
    {
        $this->status = 'approved';
        $this->approved_at = now();
        $this->save();
    }

    /**
     * Marquer la commission comme annulée
     */
    public function markAsCancelled($reason = null)
    {
        $this->status = 'cancelled';
        if ($reason) {
            $this->notes = $reason;
        }
        $this->save();
    }

    /**
     * Vérifier si la commission est pour un package
     */
    public function isForPackage()
    {
        return !is_null($this->package_id);
    }

    /**
     * Vérifier si la commission est pour un produit
     */
    public function isForProduct()
    {
        return !is_null($this->product_id);
    }

    /**
     * Vérifier si la commission est de type sponsor
     */
    public function isSponsor()
    {
        return $this->type === 'sponsor';
    }

    /**
     * Vérifier si la commission est de type direct
     */
    public function isDirect()
    {
        return $this->type === 'direct';
    }

    /**
     * Vérifier si la commission est de type indirect
     */
    public function isIndirect()
    {
        return $this->type === 'indirect';
    }

    /**
     * Vérifier si la commission est de type leadership
     */
    public function isLeadership()
    {
        return $this->type === 'leadership';
    }

    /**
     * Vérifier si la commission est de type retail
     */
    public function isRetail()
    {
        return $this->type === 'retail';
    }

    /**
     * Vérifier si la commission est de type global
     */
    public function isGlobal()
    {
        return $this->type === 'global';
    }

    /**
     * Obtenir le nom de l'utilisateur qui a généré la commission
     */
    public function getFromUserNameAttribute()
    {
        return $this->fromUser ? $this->fromUser->name : 'Système';
    }

    /**
     * Obtenir le nom de l'utilisateur qui reçoit la commission
     */
    public function getToUserNameAttribute()
    {
        return $this->user ? $this->user->name : 'N/A';
    }

    /**
     * Obtenir le code de la commande
     */
    public function getOrderNumberAttribute()
    {
        return $this->order ? $this->order->order_number : 'N/A';
    }

    /**
     * Obtenir le montant formaté avec devise
     */
    public function getAmountFormattedAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    /**
     * Obtenir le pourcentage formaté
     */
    public function getPercentageFormattedAttribute()
    {
        return $this->percentage ? $this->percentage . '%' : 'N/A';
    }

    /**
     * Obtenir la date de création formatée
     */
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at->format('d/m/Y H:i');
    }

    /**
     * Obtenir la date de paiement formatée
     */
    public function getPaidAtFormattedAttribute()
    {
        return $this->paid_at ? $this->paid_at->format('d/m/Y H:i') : 'N/A';
    }

    /**
     * Obtenir le badge de statut HTML
     */
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'paid' => 'success',
            'approved' => 'info',
            'cancelled' => 'danger',
        ];
        $color = $colors[$this->status] ?? 'secondary';
        
        return '<span class="badge badge-' . $color . '">' . $this->status_label . '</span>';
    }

    /**
     * Obtenir le badge de type HTML
     */
    public function getTypeBadgeAttribute()
    {
        $color = $this->type_color;
        return '<span class="badge badge-' . $color . '">' . $this->type_icon . ' ' . $this->type_label . '</span>';
    }

    /**
     * Obtenir les détails complets de la commission
     */
    public function getDetailsAttribute()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => $this->to_user_name,
            'from_user_id' => $this->from_user_id,
            'from_user_name' => $this->from_user_name,
            'type' => $this->type,
            'type_label' => $this->type_label,
            'type_color' => $this->type_color,
            'type_icon' => $this->type_icon,
            'amount' => $this->amount,
            'amount_formatted' => $this->amount_formatted,
            'percentage' => $this->percentage,
            'percentage_formatted' => $this->percentage_formatted,
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'order_id' => $this->order_id,
            'order_number' => $this->order_number,
            'package_id' => $this->package_id,
            'product_id' => $this->product_id,
            'item_name' => $this->item_name,
            'item_type' => $this->item_type,
            'item_type_label' => $this->item_type_label,
            'generation' => $this->generation,
            'period' => $this->period,
            'created_at' => $this->created_at_formatted,
            'paid_at' => $this->paid_at_formatted,
            'is_paid' => $this->is_paid,
            'is_pending' => $this->is_pending,
            'is_cancelled' => $this->is_cancelled,
            'is_approved' => $this->is_approved,
        ];
    }
}