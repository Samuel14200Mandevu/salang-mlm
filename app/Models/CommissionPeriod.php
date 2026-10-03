<?php
// app/Models/CommissionPeriod.php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;

class CommissionPeriod extends Model
{
    use HasFactory;

    protected $table = 'commission_periods';

    protected $fillable = [
        'period',
        'start_date',
        'end_date',
        'calculation_date',
        'payment_date',
        'status',
        'is_historical',
        'paid_offline_at',
        'is_hidden',
        'total_commissions',
        'total_paid',
        'notes',
    ];

    protected $casts = [
        'start_date'        => 'date',
        'end_date'          => 'date',
        'calculation_date'  => 'datetime',
        'payment_date'      => 'datetime',
        'is_historical'     => 'boolean',
        'is_hidden'         => 'boolean',
        'paid_offline_at'   => 'datetime',
        'total_commissions' => 'decimal:2',
        'total_paid'        => 'decimal:2',
    ];

    // ═══════════════════════════════════════════════════════════════
    // CONSTANTES DU CALENDRIER MLM
    // ═══════════════════════════════════════════════════════════════

    /** Jour de début du mois MLM (le 8 à 00h00) */
    const MLM_START_DAY = 8;

    /** Jour de fin du mois MLM (le 7 du mois suivant à 23h59) */
    const MLM_END_DAY = 7;

    /** Jour de paiement (le 15 du mois suivant) */
    const MLM_PAYMENT_DAY = 15;

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
        return $query->whereIn('status', ['active', 'pending']);
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

    public function scopeVisibleToMember($query)
    {
        return $query->where('is_hidden', false);
    }

    public function scopePayableViaSystem($query)
    {
        return $query
            ->where('is_historical', false)
            ->where('is_hidden', false)
            ->where('status', 'calculated');
    }

    // ============================================================
    // ACCESSEURS
    // ============================================================

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending'     => 'En attente',
            'active'      => 'Active',
            'calculating' => 'En calcul',
            'calculated'  => 'Calculée',
            'paying'      => 'En paiement',
            'paid'        => 'Payée',
            'closed'      => 'Clôturée',
        ];
        return $labels[$this->status] ?? ucfirst($this->status ?? 'inconnu');
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending'     => 'yellow',
            'active'      => 'green',
            'calculating' => 'blue',
            'calculated'  => 'green',
            'paying'      => 'purple',
            'paid'        => 'green',
            'closed'      => 'gray',
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
            return $this->start_date->format('d/m/Y') . ' → ' . $this->end_date->format('d/m/Y');
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

    /**
     * Libellé humain du mois MLM (ex: "8 oct. 2026 → 7 nov. 2026")
     */
    public function getHumanPeriodAttribute(): string
    {
        if ($this->start_date && $this->end_date) {
            return $this->start_date->translatedFormat('d M Y')
                . ' → '
                . $this->end_date->translatedFormat('d M Y');
        }
        return $this->period;
    }

    // ============================================================
    // ✅ CALENDRIER MLM (8 → 7) — CORE
    // ============================================================

    /**
     * Détermine la période MLM à laquelle appartient une date donnée.
     *
     * RÈGLE :
     *   - Du 8 M à la fin de M         → période "M"
     *   - Du 1er au 7 de M+1           → période "M" (encore en cours)
     *   - À partir du 8 de M+1         → nouvelle période "M+1"
     *
     * @param  Carbon|string|null  $date
     * @return string  Format "YYYY-MM"
     */
    public static function getPeriodValueForDate($date = null): string
    {
        $date = $date ? Carbon::parse($date) : now();

        // Avant le 8 → on est encore dans le mois MLM précédent
        if ((int) $date->day < self::MLM_START_DAY) {
            return $date->copy()->subMonth()->format('Y-m');
        }

        return $date->format('Y-m');
    }

    /**
     * Période MLM qui vient de se clôturer (ex. le 8 à 00h05 → veille = encore l’ancienne période).
     */
    public static function getClosedPeriodValue($date = null): string
    {
        $date = $date ? Carbon::parse($date) : now();

        return self::getPeriodValueForDate($date->copy()->subDay());
    }

    /**
     * Période à payer le 15 (M+1) : alignée sur la fin de période 7 du mois courant.
     */
    public static function getPaymentPeriodValue($date = null): string
    {
        $date = $date ? Carbon::parse($date) : now();

        return self::getPeriodValueForDate($date->copy()->subDays(8));
    }

    /**
     * Retourne les dates exactes d'une période MLM ("YYYY-MM").
     *
     * @return array{start: Carbon, end: Carbon, calculation: Carbon, payment: Carbon}
     */
    public static function getPeriodDates(string $periodValue): array
    {
        [$year, $month] = explode('-', $periodValue);
        $year  = (int) $year;
        $month = (int) $month;

        // Début : 8 du mois M à 00h00
        $start = Carbon::create($year, $month, self::MLM_START_DAY)->startOfDay();

        // Fin : 7 du mois M+1 à 23h59:59
        $end = $start->copy()->addMonth()->subDay()->endOfDay();

        // Date de calcul : 8 du mois M+1 à 00h05
        $calculation = $start->copy()->addMonth()->startOfDay()->addMinutes(5);

        // Date de paiement : 15 du mois M+1 à 00h00
        $payment = $start->copy()->addMonth()->day(self::MLM_PAYMENT_DAY)->startOfDay();

        return [
            'start'       => $start,
            'end'         => $end,
            'calculation' => $calculation,
            'payment'     => $payment,
        ];
    }

    /**
     * Crée ou met à jour une période pour un "YYYY-MM" donné.
     */
    public static function findOrCreateForValue(string $periodValue): self
    {
        $dates = self::getPeriodDates($periodValue);

        return self::updateOrCreate(
            ['period' => $periodValue],
            [
                'start_date'        => $dates['start'],
                'end_date'          => $dates['end'],
                'calculation_date'  => $dates['calculation'],
                'payment_date'      => $dates['payment'],
                'total_commissions' => 0,
                'total_paid'        => 0,
            ]
        );
    }

    /**
     * ✅ Retourne la période MLM en cours (gère automatiquement la création).
     *
     * Ex :
     *   - 10 oct → "2026-10"
     *   - 05 nov → "2026-10" (encore en cours)
     *   - 08 nov → "2026-11"
     */
    public static function getCurrentPeriod(): ?self
    {
        $periodValue = self::getPeriodValueForDate(now());

        $period = self::where('period', $periodValue)->first();

        if (!$period) {
            Log::info('Création automatique de la période MLM', ['period' => $periodValue]);

            try {
                $dates = self::getPeriodDates($periodValue);

                $period = self::create([
                    'period'            => $periodValue,
                    'start_date'        => $dates['start'],
                    'end_date'          => $dates['end'],
                    'calculation_date'  => $dates['calculation'],
                    'payment_date'      => $dates['payment'],
                    'status'            => 'active',
                    'total_commissions' => 0,
                    'total_paid'        => 0,
                ]);

                Log::info('Période MLM créée', [
                    'period' => $periodValue,
                    'id'     => $period->id,
                ]);
            } catch (\Exception $e) {
                Log::error('Erreur création période MLM', [
                    'period' => $periodValue,
                    'error'  => $e->getMessage(),
                ]);
                return null;
            }
        }

        return $period;
    }

    /**
     * Retourne la période MLM précédente.
     */
    public static function getPreviousPeriod(): ?self
    {
        $previousValue = self::getClosedPeriodValue();

        return self::where('period', $previousValue)->first();
    }

    /**
     * ✅ Crée la période suivante (utile le 8 du mois).
     */
    public static function createNextPeriod(): ?self
    {
        $currentValue = self::getPeriodValueForDate(now());
        $nextValue = Carbon::createFromFormat('Y-m', $currentValue)->addMonth()->format('Y-m');

        if (self::where('period', $nextValue)->exists()) {
            return self::where('period', $nextValue)->first();
        }

        return self::findOrCreateForValue($nextValue);
    }

    /**
     * Récupère une période par date (gère le calendrier MLM).
     */
    public static function getPeriodByDate(string $date): ?self
    {
        $periodValue = self::getPeriodValueForDate($date);
        return self::where('period', $periodValue)->first();
    }

    // ============================================================
    // ÉTAT
    // ============================================================

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'pending']);
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'closed']);
    }

    public function isCalculated(): bool
    {
        return in_array($this->status, ['calculated', 'paying', 'paid']);
    }

    /**
     * ✅ La période peut-elle être payée maintenant ?
     * Vérifie qu'on est bien le 15 (ou après) DU MOIS SUIVANT la fin de période.
     */
    public function isPayable(): bool
    {
        if ($this->status !== 'calculated') {
            return false;
        }

        // On peut payer à partir de la payment_date de la période
        return now()->greaterThanOrEqualTo($this->payment_date);
    }

    /**
     * Paiement wallet via generatePayments (hors calendrier MLM).
     */
    public function isPayableViaSystem(): bool
    {
        return $this->paymentBlockReason() === null;
    }

    public function paymentBlockReason(): ?string
    {
        if ($this->is_historical) {
            return 'historical_offline';
        }

        if ($this->is_hidden) {
            return 'hidden_period';
        }

        if ($this->status !== 'calculated') {
            return 'invalid_status';
        }

        return null;
    }

    // ============================================================
    // TRANSITIONS D'ÉTAT
    // ============================================================

    public function close(): bool
    {
        if ($this->status === 'closed') {
            return false;
        }

        $this->status = 'closed';
        return $this->save();
    }

    /**
     * ✅ Marque la période comme calculée.
     */
    public function markAsCalculated(): bool
    {
        if (in_array($this->status, ['calculated', 'paid', 'closed'])) {
            return false;
        }

        $this->status = 'calculated';
        $this->calculation_date = now();
        return $this->save();
    }

    public function markAsPaid(): bool
    {
        if (in_array($this->status, ['paid', 'closed'])) {
            return false;
        }

        $this->status = 'paid';
        $this->payment_date = now();
        return $this->save();
    }

    // ============================================================
    // CALCULS
    // ============================================================

    public function calculateTotalCommissions(): float
    {
        $total = Commission::where('commission_period_id', $this->id)
            ->where('status', 'pending')
            ->sum('amount');

        $this->total_commissions = $total;
        $this->save();

        return $total;
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
}