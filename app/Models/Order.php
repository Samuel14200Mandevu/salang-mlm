<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'subtotal',
        'tax',
        'shipping',
        'discount',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'shipping_address',
        'billing_address',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ⚠️ AJOUTER CETTE RELATION ⚠️
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'En attente',
            'processing' => 'En traitement',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getPaymentStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'En attente',
            'completed' => 'Payé',
            'failed' => 'Échoué',
        ];
        return $labels[$this->payment_status] ?? ucfirst($this->payment_status);
    }
}