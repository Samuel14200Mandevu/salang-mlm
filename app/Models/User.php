<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'sponsor_id',
        'parrain_id',
        'rank_id',
        'rank',
        'package_id',
        'pv_balance',
        'bv_balance',
        'monthly_pv',
        'monthly_bv',
        'team_pv',
        'team_bv',
        'qualified_branches',
        'direct_sponsors_count',
        'commission_balance',
        'total_earnings',
        'total_sponsors',
        'total_team',
        'is_active',
        'kyc_status',
        'kyc_verified_at',
        'package_expiry',
        'avatar',
        'provider',
        'provider_id',
        'country',
        'city',
        'address',
        'last_rank_update',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'package_expiry' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'password' => 'hashed',
        'pv_balance' => 'integer',
        'bv_balance' => 'integer',
        'monthly_pv' => 'integer',
        'monthly_bv' => 'integer',
        'team_pv' => 'integer',
        'team_bv' => 'integer',
        'qualified_branches' => 'integer',
        'direct_sponsors_count' => 'integer',
        'commission_balance' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ============================================================
    // RELATIONS
    // ============================================================

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function parrain()
    {
        return $this->belongsTo(User::class, 'parrain_id');
    }

    public function filleuls()
    {
        return $this->hasMany(User::class, 'parrain_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function genealogy()
    {
        return $this->hasOne(Genealogy::class);
    }

    public function rankHistory()
    {
        return $this->hasMany(RankHistory::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function kycDocuments()
    {
        return $this->hasMany(KycDocument::class);
    }

    public function monthlyRanks()
    {
        return $this->hasMany(UserMonthlyRank::class);
    }

    public function commissionPayments()
    {
        return $this->hasMany(CommissionPayment::class);
    }

    public function qualifiedBranches()
    {
        return $this->hasMany(QualifiedBranch::class);
    }

    public function higherRanks()
    {
        return $this->belongsToMany(HigherRank::class, 'user_higher_ranks')
                    ->withPivot('achieved_at', 'period')
                    ->withTimestamps();
    }

    // ============================================================
    // ACCESSEURS
    // ============================================================

    public function getRankNameAttribute()
    {
        if ($this->relationLoaded('rank') && $this->rank && !is_string($this->rank)) {
            return $this->rank->name;
        }
        
        if (is_string($this->rank)) {
            return $this->rank;
        }
        
        if ($this->rank_id) {
            $rank = Rank::find($this->rank_id);
            if ($rank) {
                return $rank->name;
            }
        }
        
        return 'Distributor';
    }

    public function getRankLevelAttribute()
    {
        if ($this->relationLoaded('rank') && $this->rank && !is_string($this->rank)) {
            return $this->rank->level ?? 1;
        }
        
        if (is_string($this->rank)) {
            $levels = [
                'Distributeur' => 1,
                'Qualification' => 2,
                'Cumul Directeur' => 3,
                'Directeur' => 4,
                'Manager Senior' => 5,
                'Directeur Envolée' => 6,
                'Saphire Manager' => 7,
                'Diamant Bleu' => 8,
                'Perle Diamant' => 9,
                'Pearl' => 10,
                'Distributor' => 1,
                'Supervisor' => 2,
                'Assistant Manager' => 3,
                'Manager' => 4,
                'Senior Manager' => 5,
                'Soaring Manager' => 6,
                'Sapphire Manager' => 7,
                'Blue Diamond' => 8,
                'Diamond' => 9,
            ];
            return $levels[$this->rank] ?? 1;
        }
        
        if ($this->rank_id) {
            $rank = Rank::find($this->rank_id);
            if ($rank) {
                return $rank->level ?? 1;
            }
        }
        
        return 1;
    }

    public function getRankObjectAttribute()
    {
        if ($this->relationLoaded('rank') && $this->rank && !is_string($this->rank)) {
            return $this->rank;
        }
        
        if ($this->rank_id) {
            return Rank::find($this->rank_id);
        }
        
        if (is_string($this->rank)) {
            return Rank::where('name', $this->rank)->first();
        }
        
        return null;
    }

    public function getPackageNameAttribute()
    {
        return $this->package ? $this->package->name : 'None';
    }

    public function getWalletBalanceAttribute()
    {
        return $this->wallet ? $this->wallet->balance : 0;
    }

    public function getStatusLabelAttribute()
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function getKycStatusLabelAttribute()
    {
        $labels = [
            'not_submitted' => 'Not Submitted',
            'pending' => 'Pending',
            'partial' => 'Partial',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
        ];
        return $labels[$this->kyc_status] ?? ucfirst($this->kyc_status);
    }

    public function getParrainNameAttribute()
    {
        return $this->parrain ? $this->parrain->name : 'No sponsor';
    }

    public function getReferralCodeAttribute()
    {
        return $this->sponsor_id ?? '';
    }

    public function getTotalCommissionsAttribute()
    {
        return $this->commissions()->where('status', 'paid')->sum('amount');
    }

    public function getMonthlyRankAttribute()
    {
        $period = date('Y-m');
        return $this->monthlyRanks()->where('period', $period)->first();
    }

    public function getFormattedWalletBalanceAttribute()
    {
        return '$' . number_format($this->getWalletBalanceAttribute(), 2);
    }

    public function getFormattedTotalEarningsAttribute()
    {
        return '$' . number_format($this->total_earnings, 2);
    }

    // ============================================================
    // SCOPES
    // ============================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('kyc_status', 'verified');
    }

    public function scopeWithRank($query, $rankId)
    {
        return $query->where('rank_id', $rankId);
    }

    public function scopeWithMinPV($query, $minPV)
    {
        return $query->where('pv_balance', '>=', $minPV);
    }

    public function scopeWithMinMonthlyPV($query, $minPV)
    {
        return $query->where('monthly_pv', '>=', $minPV);
    }

    public function scopeQualified($query)
    {
        return $query->where('is_active', true)
            ->where('kyc_status', 'verified');
    }

    // ============================================================
    // MÉTHODES UTILITAIRES
    // ============================================================

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isKycVerified()
    {
        return $this->kyc_status === 'verified';
    }

    public function countFilleuls()
    {
        return $this->filleuls()->count();
    }

    public function countFilleulsActifs()
    {
        return $this->filleuls()->where('is_active', true)->count();
    }

    public function countDescendants()
    {
        $count = 0;
        foreach ($this->filleuls as $filleul) {
            $count++;
            $count += $filleul->countDescendants();
        }
        return $count;
    }

    public function getDescendants()
    {
        $descendants = [];
        foreach ($this->filleuls as $filleul) {
            $descendants[] = $filleul;
            $descendants = array_merge($descendants, $filleul->getDescendants());
        }
        return $descendants;
    }

    public function getTeamPV()
    {
        $total = 0;
        foreach ($this->getDescendants() as $descendant) {
            $total += $descendant->pv_balance;
        }
        return $total;
    }

    public function getMonthlyTeamPV()
    {
        $total = 0;
        foreach ($this->getDescendants() as $descendant) {
            $total += $descendant->monthly_pv;
        }
        return $total;
    }

    public function isQualifiedForPayment(): bool
    {
        if (!$this->rank) {
            return false;
        }

        $monthlyPvRequired = $this->rank->monthly_pv_required ?? 0;
        
        return $this->monthly_pv >= $monthlyPvRequired;
    }

    public function getQualifiedBranchesForPeriod(string $period)
    {
        return QualifiedBranch::where('user_id', $this->id)
            ->where('period', $period)
            ->get();
    }

    public function countQualifiedBranchesForPeriod(string $period, int $minLevel = null): int
    {
        $query = QualifiedBranch::where('user_id', $this->id)
            ->where('period', $period);
        
        if ($minLevel) {
            $query->where('branch_rank_level', '>=', $minLevel);
        }
        
        return $query->count();
    }

    public function getMonthlyRankForPeriod(string $period)
    {
        return UserMonthlyRank::where('user_id', $this->id)
            ->where('period', $period)
            ->first();
    }

    public function updateTeamPV(): void
    {
        $total = 0;
        $this->load('filleuls');
        
        foreach ($this->filleuls as $filleul) {
            $total += $filleul->pv_balance;
            $total += $filleul->team_pv;
        }
        
        $this->team_pv = $total;
        $this->save();
    }

    public function updateMonthlyPV(): void
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        
        $totalPV = OrderItem::whereHas('order', function ($query) use ($monthStart, $monthEnd) {
            $query->where('user_id', $this->id)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->where('payment_status', 'completed');
        })->sum('pv_value');
        
        $this->monthly_pv = $totalPV;
        $this->save();
    }

    public function isHigherRank(string $slug): bool
    {
        return $this->higherRanks()->where('slug', $slug)->exists();
    }

    public function getCurrentHigherRank()
    {
        return $this->higherRanks()
            ->orderBy('level', 'desc')
            ->first();
    }
}