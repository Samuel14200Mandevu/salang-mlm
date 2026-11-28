<?php

namespace App\Models\Pharmacy;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashEntry extends Model
{
    protected $table = 'pharmacy_cash_entries';

    public const CATEGORIES = [
        'sales_receipt' => 'Recette ventes',
        'expense' => 'Dépense',
        'bank_deposit' => 'Versement banque',
        'other' => 'Autre',
    ];

    protected $fillable = [
        'entry_date', 'label', 'category', 'amount_in', 'amount_out', 'notes', 'user_id',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount_in' => 'decimal:2',
        'amount_out' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
