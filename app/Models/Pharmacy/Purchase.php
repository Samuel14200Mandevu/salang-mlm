<?php

namespace App\Models\Pharmacy;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $table = 'pharmacy_purchases';

    protected $fillable = [
        'purchased_at', 'invoice_number', 'supplier_id', 'total_amount',
        'paid_amount', 'status', 'notes', 'user_id',
    ];

    protected $casts = [
        'purchased_at' => 'date',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function remainingAmount(): float
    {
        return max(0, round((float) $this->total_amount - (float) $this->paid_amount, 2));
    }

    public static function resolveStatus(float $total, float $paid): string
    {
        if ($paid <= 0) {
            return 'unpaid';
        }
        if ($paid + 0.009 >= $total) {
            return 'paid';
        }

        return 'partial';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'paid' => 'Payé',
            'partial' => 'Partiel',
            default => 'Impayé',
        };
    }
}
