<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'expense_type',
        'related_user_id',
        'category',
        'title',
        'description',
        'amount',
        'currency',
        'expense_date',
        'payment_method',
        'reference',
        'receipt_image',
        'status',
        'approved_by',
        'approved_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'approved_at' => 'datetime',
        'metadata' => 'array',
    ];

    // ============================================================
    //  RELATIONS
    // ============================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ============================================================
    //  SCOPES
    // ============================================================

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('expense_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('expense_date', now()->month)
                     ->whereYear('expense_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('expense_date', now()->year);
    }

    public function scopeCaisse($query)
    {
        return $query->where('expense_type', 'caisse');
    }

    public function scopeMembre($query)
    {
        return $query->where('expense_type', 'membre');
    }

    // ============================================================
    //  ACCESSORS
    // ============================================================

    public function getFormattedAmountAttribute()
    {
        $symbol = $this->currency === 'CDF' ? 'FC' : '$';
        return $symbol . ' ' . number_format($this->amount, 2);
    }

    public function getCurrencySymbolAttribute()
    {
        return $this->currency === 'CDF' ? 'FC' : '$';
    }

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'pending' => 'En attente',
            'approved' => 'Approuvée',
            'rejected' => 'Rejetée',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'pending' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-info',
        };
    }

    public function getPaymentMethodLabelAttribute()
    {
        return match($this->payment_method) {
            'cash' => 'Espèces',
            'mobile_money' => 'Mobile Money',
            'bank' => 'Banque',
            'autre' => 'Autre',
            default => $this->payment_method,
        };
    }

    public function getExpenseTypeLabelAttribute()
    {
        return match($this->expense_type) {
            'caisse' => 'Caisse',
            'membre' => 'Membre',
            default => $this->expense_type,
        };
    }

    public function getReceiptUrlAttribute()
    {
        if ($this->receipt_image) {
            return asset('storage/expenses/' . $this->receipt_image);
        }
        return null;
    }

    // ============================================================
    //  STATIQUES
    // ============================================================

    public static function getCategories(): array
    {
        return [
            'fournitures' => 'Fournitures de bureau',
            'transport' => 'Transport / Carburant',
            'communication' => 'Communication (crédit, internet)',
            'entretien' => 'Entretien / Réparation',
            'frais_bancaires' => 'Frais bancaires',
            'salaire' => 'Salaire / Avance',
            'restauration' => 'Restauration',
            'publicite' => 'Publicité',
            'remboursement' => 'Remboursement membre',
            'autre' => 'Autre',
        ];
    }

    public static function getPaymentMethods(): array
    {
        return [
            'cash' => 'Espèces',
            'mobile_money' => 'Mobile Money',
            'bank' => 'Banque',
            'autre' => 'Autre',
        ];
    }

    public static function getCurrencies(): array
    {
        return [
            'USD' => 'USD ($)',
            'CDF' => 'CDF (FC)',
        ];
    }

    public function getCategoryLabelAttribute()
    {
        return self::getCategories()[$this->category] ?? $this->category;
    }
}