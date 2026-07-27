<?php
// app/Models/CommissionPeriod.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class CommissionPeriod extends Model
{
    protected $fillable = [
        'period',
        'start_date',
        'end_date',
        'calculation_date',
        'payment_date',
        'status',
        'total_commissions',
        'total_paid',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'calculation_date' => 'date',
        'payment_date' => 'date',
        'total_commissions' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    // ============================================================
    // RELATIONS
    // ============================================================

    public function payments()
    {
        return $this->hasMany(CommissionPayment::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function monthlyRanks()
    {
        return $this->hasMany(UserMonthlyRank::class, 'period', 'period');
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCalculated($query)
    {
        return $query->where('status', 'calculated');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    // ============================================================
    // ACCESSEURS
    // ============================================================

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'En attente',
            'active' => 'Active',
            'calculating' => 'En calcul',
            'calculated' => 'Calculée',
            'paying' => 'En paiement',
            'paid' => 'Payée',
            'closed' => 'Clôturée',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'active' => 'green',
            'calculating' => 'blue',
            'calculated' => 'green',
            'paying' => 'purple',
            'paid' => 'green',
            'closed' => 'gray',
        ];
        return $colors[$this->status] ?? 'gray';
    }

    public function getProgressAttribute()
    {
        if ($this->total_commissions > 0) {
            return ($this->total_paid / $this->total_commissions) * 100;
        }
        return 0;
    }

    public function getFormattedTotalCommissionsAttribute()
    {
        return '$' . number_format($this->total_commissions, 2);
    }

    public function getFormattedTotalPaidAttribute()
    {
        return '$' . number_format($this->total_paid, 2);
    }

    public function getPeriodLabelAttribute()
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->format('d/m/Y') . ' - ' . $this->end_date->format('d/m/Y');
        }
        return $this->period;
    }

    public function getMonthNameAttribute()
    {
        if ($this->start_date) {
            return $this->start_date->format('F Y');
        }
        return $this->period;
    }

    // ============================================================
    // MÉTHODES EXISTANTES
    // ============================================================

    public static function getCurrentPeriod(): ?self
    {
        $currentPeriod = date('Y-m');
        
        $period = self::where('period', $currentPeriod)
            ->whereIn('status', ['active', 'pending'])
            ->first();
        
        if (!$period) {
            Log::info('Aucune période de commission trouvée, création automatique', [
                'period' => $currentPeriod,
            ]);
            
            try {
                $period = self::create([
                    'period' => $currentPeriod,
                    'start_date' => now()->startOfMonth(),
                    'end_date' => now()->endOfMonth(),
                    'calculation_date' => now()->endOfMonth(),
                    'payment_date' => now()->startOfMonth()->addMonth()->day(15), // ✅ 15 du mois suivant
                    'status' => 'active',
                    'total_commissions' => 0,
                    'total_paid' => 0,
                ]);
                
                Log::info('Période de commission créée automatiquement', [
                    'period' => $currentPeriod,
                    'id' => $period->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur lors de la création automatique de la période', [
                    'error' => $e->getMessage(),
                ]);
                return null;
            }
        }
        
        return $period;
    }

    public static function getPreviousPeriod(): ?self
    {
        $previousPeriod = date('Y-m', strtotime('-1 month'));
        return self::where('period', $previousPeriod)->first();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === 'pending';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid' || $this->status === 'closed';
    }

    public function isCalculated(): bool
    {
        return $this->status === 'calculated' || $this->status === 'paying' || $this->status === 'paid';
    }

    public function close(): bool
    {
        if ($this->status === 'closed') {
            return false;
        }

        $this->status = 'closed';
        return $this->save();
    }

    public function calculateTotalCommissions(): float
    {
        $total = Commission::where('commission_period_id', $this->id)
            ->where('status', 'pending')
            ->sum('amount');
        
        $this->total_commissions = $total;
        $this->save();
        
        return $total;
    }

    public function markAsPaid(): bool
    {
        if ($this->status === 'paid' || $this->status === 'closed') {
            return false;
        }

        $this->status = 'paid';
        $this->payment_date = now();
        return $this->save();
    }

    public function getUnpaidCommissions()
    {
        return $this->commissions()->where('status', 'pending')->get();
    }

    public function getPaidCommissions()
    {
        return $this->commissions()->where('status', 'paid')->get();
    }

    public function getUsersWithCommissions()
    {
        return User::whereHas('commissions', function ($query) {
            $query->where('commission_period_id', $this->id);
        })->get();
    }

    // ============================================================
    // ✅ NOUVELLES MÉTHODES POUR LE CALENDRIER
    // ============================================================

    /**
     * ✅ VÉRIFIER SI LA PÉRIODE EST PAYABLE (LE 15 DU MOIS)
     */
    public function isPayable(): bool
    {
        $today = now();
        $paymentDay = 15;
        
        // On peut payer à partir du 15 du mois
        return $today->day >= $paymentDay && $this->status === 'calculated';
    }

    /**
     * ✅ MARQUER LA PÉRIODE COMME CALCULÉE
     */
    public function markAsCalculated(): bool
    {
        if ($this->status === 'calculated' || $this->status === 'paid') {
            return false;
        }

        $this->status = 'calculated';
        $this->calculation_date = now();
        return $this->save();
    }

    /**
     * ✅ OBTENIR LA PÉRIODE PAR DATE
     */
    public static function getPeriodByDate(string $date): ?self
    {
        $period = date('Y-m', strtotime($date));
        return self::where('period', $period)->first();
    }

    /**
     * ✅ CRÉER LA PÉRIODE SUIVANTE
     */
    public static function createNextPeriod(): ?self
    {
        $nextPeriod = date('Y-m', strtotime('+1 month', strtotime($this->period)));
        
        return self::create([
            'period' => $nextPeriod,
            'start_date' => now()->startOfMonth()->addMonth(),
            'end_date' => now()->endOfMonth()->addMonth(),
            'calculation_date' => now()->endOfMonth()->addMonth(),
            'payment_date' => now()->startOfMonth()->addMonth(2)->day(15),
            'status' => 'active',
            'total_commissions' => 0,
            'total_paid' => 0,
        ]);
    }
}