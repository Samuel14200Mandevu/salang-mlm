<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\MLM\AdvancedRankCalculator;
use App\Jobs\UpdateTeamPV;

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
        // ✅ AJOUT POUR L'ACTIVATION
        'activation_code',
        'activation_code_expires_at',
        'activated_at',
        'activation_method',
        'activation_package_id',
        'ip_address',
        'last_login_at',
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
        'activation_code_expires_at' => 'datetime',
        'activated_at' => 'datetime',
    ];

    // ============================================================
    // BOOTED - TRIGGERS AUTOMATIQUES (CORRIGÉ SANS BOUCLE)
    // ============================================================

    protected static function booted(): void
    {
        // Après création d'un utilisateur
        static::created(function ($user) {
            $user->calculateAndUpdateRank();
            
            if ($user->parrain_id) {
                $parrain = User::find($user->parrain_id);
                if ($parrain) {
                    $parrain->updateTeamPVWithoutEvents();
                    $parrain->updateAllAncestorsWithoutEvents();
                }
            }
        });

        static::updated(function ($user) {
            $fieldsToWatch = ['pv_balance', 'monthly_pv', 'parrain_id'];
            $hasChange = false;
            
            foreach ($fieldsToWatch as $field) {
                if ($user->wasChanged($field)) {
                    $hasChange = true;
                    break;
                }
            }
            
            if (!$hasChange) {
                return;
            }
            
            if ($user->wasChanged('pv_balance') || $user->wasChanged('monthly_pv')) {
                $user->updateTeamPVWithoutEvents();
                $user->updateAllAncestorsWithoutEvents();
                $user->calculateAndUpdateRank();
            }
            
            if ($user->wasChanged('parrain_id')) {
                if ($user->getOriginal('parrain_id')) {
                    $oldParrain = User::find($user->getOriginal('parrain_id'));
                    if ($oldParrain) {
                        $oldParrain->updateTeamPVWithoutEvents();
                        $oldParrain->updateAllAncestorsWithoutEvents();
                    }
                }
                
                if ($user->parrain_id) {
                    $newParrain = User::find($user->parrain_id);
                    if ($newParrain) {
                        $newParrain->updateTeamPVWithoutEvents();
                        $newParrain->updateAllAncestorsWithoutEvents();
                    }
                }
            }
        });

        static::saved(function ($user) {
            if (!$user->rank_id || $user->rank_id == 1) {
                $user->calculateAndUpdateRank();
            }
        });

        static::deleted(function ($user) {
            if ($user->parrain_id) {
                $parrain = User::find($user->parrain_id);
                if ($parrain) {
                    $parrain->updateTeamPVWithoutEvents();
                    $parrain->updateAllAncestorsWithoutEvents();
                }
            }
        });
    }

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

    // ✅ RELATION POUR LE PACKAGE D'ACTIVATION
    public function activationPackage()
    {
        return $this->belongsTo(Package::class, 'activation_package_id');
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
                'Distributeur' => 1, 'Distributor' => 1,
                'Qualification' => 2, 'Supervisor' => 2,
                'Cumul Directeur' => 3, 'Assistant Manager' => 3,
                'Directeur' => 4, 'Manager' => 4,
                'Manager Senior' => 5, 'Senior Manager' => 5,
                'Directeur Envolée' => 6, 'Soaring Manager' => 6,
                'Saphire Manager' => 7,
                'Blue Diamond' => 8,
                'Diamond Pearl' => 9,
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
    // MÉTHODES DE CUMUL DES PV
    // ============================================================

    public function getAllDescendants(): \Illuminate\Support\Collection
    {
        $cacheKey = "descendants_{$this->id}";
        
        return Cache::remember($cacheKey, 3600, function () {
            $descendants = collect();
            $stack = collect([$this]);
            
            while ($stack->isNotEmpty()) {
                $current = $stack->pop();
                $children = User::where('parrain_id', $current->id)
                    ->where('is_active', true)
                    ->get();
                
                foreach ($children as $child) {
                    $descendants->push($child);
                    $stack->push($child);
                }
            }
            
            return $descendants;
        });
    }

    private function getDescendantsRecursive($user, array &$descendants): void
    {
        $children = User::where('parrain_id', $user->id)
            ->where('is_active', true)
            ->get();

        foreach ($children as $child) {
            $descendants[] = $child;
            $this->getDescendantsRecursive($child, $descendants);
        }
    }

    public function updateTeamPVWithoutEvents(): void
    {
        $total = 0;
        $count = 0;
        
        $filleuls = $this->filleuls()->with(['filleuls'])->get();
        
        foreach ($filleuls as $filleul) {
            $total += $filleul->pv_balance;
            $total += $filleul->team_pv;
            $count += 1 + $filleul->total_team;
        }
        
        DB::transaction(function () use ($total, $count) {
            User::withoutEvents(function () use ($total, $count) {
                $this->team_pv = $total;
                $this->total_team = $count;
                $this->saveQuietly();
            });
        });
        
        Cache::forget("descendants_{$this->id}");
        Cache::forget("descendants_count_{$this->id}");
    }

    public function updateAllAncestorsWithoutEvents(): void
    {
        $cacheKey = "ancestor_update_{$this->id}";
        
        if (Cache::get($cacheKey, false)) {
            return;
        }
        
        Cache::put($cacheKey, true, 60);
        
        try {
            $current = $this;
            $maxDepth = 20;
            $depth = 0;
            $updatedIds = [];
            
            while ($current->parrain_id && $depth < $maxDepth) {
                $parrain = User::find($current->parrain_id);
                if (!$parrain) break;
                
                if (in_array($parrain->id, $updatedIds)) {
                    break;
                }
                
                $updatedIds[] = $parrain->id;
                $parrain->updateTeamPVWithoutEvents();
                
                Cache::forget("descendants_{$parrain->id}");
                Cache::forget("descendants_count_{$parrain->id}");
                
                $current = $parrain;
                $depth++;
            }
        } finally {
            Cache::forget($cacheKey);
        }
    }

    public function updateTeamPV(): void
    {
        $this->updateTeamPVWithoutEvents();
    }

    public function updateAllAncestors(): void
    {
        $this->updateAllAncestorsWithoutEvents();
    }

    public function getTeamMonthlyPV(): int
    {
        $total = 0;
        $descendants = $this->getAllDescendants();
        
        foreach ($descendants as $descendant) {
            $total += $descendant->monthly_pv;
        }
        
        return $total;
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

    public function countDescendants(): int
    {
        if ($this->total_team > 0) {
            return $this->total_team;
        }
        
        $cacheKey = "descendants_count_{$this->id}";
        
        return Cache::remember($cacheKey, 3600, function () {
            $count = 0;
            $filleuls = $this->filleuls()->with(['filleuls'])->get();
            
            foreach ($filleuls as $filleul) {
                $count += 1 + $filleul->total_team;
            }
            
            return $count;
        });
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
        $this->saveQuietly();
        $this->clearRankCache();
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

    public function recalculateAllAncestors(): void
    {
        $maxDepth = 20;
        $depth = 0;
        $current = $this;
        
        while ($current->parrain_id && $depth < $maxDepth) {
            $parrain = User::find($current->parrain_id);
            if (!$parrain) break;
            
            $parrain->updateTeamPVWithoutEvents();
            $current = $parrain;
            $depth++;
        }
    }

    // ============================================================
    // MÉTHODES DE GRADE
    // ============================================================

    public function calculateAndUpdateRank(): bool
    {
        try {
            $calculator = app(AdvancedRankCalculator::class);
            $newRank = $calculator->calculateAdvancedRank($this);
            
            if (!$newRank) {
                return false;
            }
            
            if ($newRank->id != $this->rank_id) {
                $oldRankId = $this->rank_id;
                $oldRankName = $this->rank ?? 'Distributor';
                
                DB::beginTransaction();
                
                $this->rank_id = $newRank->id;
                $this->rank = $newRank->name;
                $this->last_rank_update = now();
                $this->saveQuietly();
                $this->clearRankCache();
                
                RankHistory::create([
                    'user_id' => $this->id,
                    'old_rank_id' => $oldRankId,
                    'new_rank_id' => $newRank->id,
                    'old_rank_name' => $oldRankName,
                    'new_rank_name' => $newRank->name,
                    'pv_at_time' => $this->pv_balance,
                    'bv_at_time' => $this->bv_balance,
                    'notes' => 'Automatic rank update',
                ]);
                
                DB::commit();
                
                Log::info('Grade automatique mis à jour', [
                    'user_id' => $this->id,
                    'old_rank' => $oldRankName,
                    'new_rank' => $newRank->name,
                ]);
                
                return true;
            }
            return false;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la mise à jour du grade', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function forceRankUpdate(): bool
    {
        return $this->calculateAndUpdateRank();
    }

    public function getCachedRankAttribute()
    {
        return Cache::remember("user_rank_{$this->id}", 300, function () {
            return $this->rankObject;
        });
    }

    public function clearRankCache(): void
    {
        Cache::forget("user_rank_{$this->id}");
    }
}