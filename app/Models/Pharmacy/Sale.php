<?php

namespace App\Models\Pharmacy;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $table = 'pharmacy_sales';

    public const PAYMENT_METHODS = [
        'cash' => 'Espèces',
        'mobile_money' => 'Mobile Money',
        'card' => 'Carte',
        'insurance' => 'Assurance',
    ];

    protected $fillable = [
        'sold_at', 'ticket_number', 'patient_id', 'customer_name', 'payment_method',
        'insurer_id', 'coverage_rate', 'total_amount', 'insurance_amount',
        'patient_amount', 'cost_amount', 'profit_amount', 'notes', 'user_id',
    ];

    protected $casts = [
        'sold_at' => 'date',
        'coverage_rate' => 'decimal:4',
        'total_amount' => 'decimal:2',
        'insurance_amount' => 'decimal:2',
        'patient_amount' => 'decimal:2',
        'cost_amount' => 'decimal:2',
        'profit_amount' => 'decimal:2',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function insurer(): BelongsTo
    {
        return $this->belongsTo(Insurer::class, 'insurer_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethodLabel(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method;
    }

    public static function nextTicketNumber(): string
    {
        $last = static::query()->orderByDesc('id')->value('ticket_number');
        $n = 1;
        if (is_string($last) && preg_match('/(\d+)$/', $last, $m)) {
            $n = ((int) $m[1]) + 1;
        }

        return 'T-'.str_pad((string) $n, 4, '0', STR_PAD_LEFT);
    }
}
