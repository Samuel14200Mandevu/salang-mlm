<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CashierReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'report_date',
        'report_number',
        'total_sales',
        'total_pos',
        'total_mlm',
        'total_orders',
        'total_pv',
        'total_bv',
        'total_expenses',
        'total_commissions',
        'net_balance',
        'cash_amount',
        'mobile_money_amount',
        'bank_amount',
        'opening_balance',
        'closing_balance',
        'theoretical_balance',
        'difference',
        'new_members',
        'new_clients',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'notes',
        'signature_name',
        'signature_at',
        'details',
        'metadata',
    ];

    protected $casts = [
        'report_date' => 'date',
        'total_sales' => 'decimal:2',
        'total_pos' => 'decimal:2',
        'total_mlm' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'total_commissions' => 'decimal:2',
        'net_balance' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'mobile_money_amount' => 'decimal:2',
        'bank_amount' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'closing_balance' => 'decimal:2',
        'theoretical_balance' => 'decimal:2',
        'difference' => 'decimal:2',
        'approved_at' => 'datetime',
        'signature_at' => 'datetime',
        'details' => 'array',
        'metadata' => 'array',
    ];

    // ============================================================
    //  RELATIONS
    // ============================================================

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // ============================================================
    //  SCOPES
    // ============================================================

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('report_date', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('report_date', now()->month)
                     ->whereYear('report_date', now()->year);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    // ============================================================
    //  ACCESSORS
    // ============================================================

    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'draft' => 'Brouillon',
            'submitted' => 'Soumis',
            'approved' => 'Approuvé',
            'rejected' => 'Rejeté',
            default => $this->status,
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match($this->status) {
            'draft' => 'badge-secondary',
            'submitted' => 'badge-warning',
            'approved' => 'badge-success',
            'rejected' => 'badge-danger',
            default => 'badge-info',
        };
    }

    public function getHasDifferenceAttribute()
    {
        return abs($this->difference) > 0.01;
    }

    public function getDifferenceColorAttribute()
    {
        if (abs($this->difference) < 0.01) return '#16a34a';
        return $this->difference > 0 ? '#f59e0b' : '#b32a2a';
    }

    // ============================================================
    //  MÉTHODES STATIQUES
    // ============================================================

    /**
     * Générer un numéro de rapport unique
     */
    public static function generateReportNumber()
    {
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->count() + 1;
        return 'RPT-' . $date . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Vérifier si un caissier a déjà soumis un rapport aujourd'hui
     */
    public static function hasReportToday($userId)
    {
        return self::where('user_id', $userId)
            ->whereDate('report_date', today())
            ->whereIn('status', ['submitted', 'approved'])
            ->exists();
    }
}