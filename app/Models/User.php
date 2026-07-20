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
        'position',
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

    /**
     * BOOTED - TRIGGERS AUTOMATIQUES
     */
    protected static function booted(): void
    {
        static::created(function ($user) {
            $user->updateTeamPVWithoutEvents();
            $user->updateAllAncestorsWithoutEvents();
            $user->calculateAndUpdateRank();
        });

        static::updated(function ($user) {
            $fieldsToWatch = ['pv_balance', 'monthly_pv', 'parrain_id', 'is_active', 'rank_id'];
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
        return $this->hasMany(User::class, 'parrain_id')->where('is_active', true);
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
        
        return 'Distributeur';
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
                'Directeur Envolee' => 6, 'Soaring Manager' => 6,
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
        return $this->is_active ? 'Actif' : 'Inactif';
    }

    public function getKycStatusLabelAttribute()
    {
        $labels = [
            'not_submitted' => 'Non soumis',
            'pending' => 'En attente',
            'partial' => 'Partiel',
            'verified' => 'Verifie',
            'rejected' => 'Rejete',
        ];
        return $labels[$this->kyc_status] ?? ucfirst($this->kyc_status);
    }

    public function getParrainNameAttribute()
    {
        return $this->parrain ? $this->parrain->name : 'Aucun parrain';
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

    public function getCumulPVAttribute()
    {
        return $this->team_pv ?? 0;
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

    /**
     * Récupère tous les descendants d'un utilisateur
     */
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

    /**
     * Met à jour le Team PV (sans déclencher d'événements)
     * team_pv = PV Personnel + team_pv de tous les filleuls
     * SANS double comptage
     */
    public function updateTeamPVWithoutEvents(): void
    {
        // ✅ CORRECTION : team_pv = PV Personnel + team_pv de tous les filleuls
        // team_pv du filleul contient déjà son PV + ses descendants
        $totalPV = $this->pv_balance ?? 0;
        $totalBV = $this->bv_balance ?? 0;
        $count = 0;
        
        $filleuls = $this->filleuls()->get();
        
        foreach ($filleuls as $filleul) {
            // ✅ Ajouter uniquement team_pv (qui contient déjà tout)
            $totalPV += $filleul->team_pv;
            $totalBV += $filleul->team_bv;
            $count += 1 + ($filleul->total_team ?? 0);
        }
        
        DB::transaction(function () use ($totalPV, $totalBV, $count) {
            User::withoutEvents(function () use ($totalPV, $totalBV, $count) {
                $this->team_pv = $totalPV;
                $this->team_bv = $totalBV;
                $this->total_team = $count;
                $this->saveQuietly();
            });
        });
        
        Cache::forget("descendants_{$this->id}");
        Cache::forget("descendants_count_{$this->id}");
    }

    /**
     * Met à jour tous les ancêtres (sans déclencher d'événements)
     */
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

    /**
     * Met à jour le Team PV (avec événements)
     */
    public function updateTeamPV(): void
    {
        $this->updateTeamPVWithoutEvents();
        $this->updateAllAncestorsWithoutEvents();
    }

    /**
     * Met à jour tous les ancêtres (avec événements)
     */
    public function updateAllAncestors(): void
    {
        $this->updateAllAncestorsWithoutEvents();
    }

    /**
     * Récupère le Team PV mensuel
     */
    public function getTeamMonthlyPV(): int
    {
        $total = 0;
        $descendants = $this->getAllDescendants();
        
        foreach ($descendants as $descendant) {
            $total += $descendant->monthly_pv;
        }
        
        return $total;
    }

    /**
     * Récupère les descendants
     */
    public function getDescendants()
    {
        $descendants = [];
        foreach ($this->filleuls as $filleul) {
            $descendants[] = $filleul;
            $descendants = array_merge($descendants, $filleul->getDescendants());
        }
        return $descendants;
    }

    /**
     * Compte les descendants
     */
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
                $count += 1 + ($filleul->total_team ?? 0);
            }
            
            return $count;
        });
    }

    /**
     * Calculer le CUMUL (team_pv)
     */
    public function getCumulPV(): int
    {
        return $this->team_pv ?? 0;
    }

    // ============================================================
    // MÉTHODES DE GRADE
    // ============================================================

       /**
     * Calcule et met à jour le grade
     */
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
                $oldRankName = $this->rank ?? 'Distributeur';
                $oldRankLevel = $this->rank_level ?? 1;
                
                DB::beginTransaction();
                
                $this->rank_id = $newRank->id;
                $this->rank = $newRank->name;
                $this->rank_level = $newRank->level; 
                $this->last_rank_update = now();
                $this->saveQuietly();
                $this->clearRankCache();
                
                RankHistory::create([
                    'user_id' => $this->id,
                    'old_rank_id' => $oldRankId,
                    'new_rank_id' => $newRank->id,
                    'old_rank_name' => $oldRankName,
                    'new_rank_name' => $newRank->name,
                    'old_rank_level' => $oldRankLevel, 
                    'new_rank_level' => $newRank->level, 
                    'pv_at_time' => $this->pv_balance,
                    'bv_at_time' => $this->bv_balance,
                    'monthly_pv_at_time' => $this->monthly_pv,
                    'notes' => 'Automatic rank update',
                ]);
                
                DB::commit();
                
                Log::info('Grade mis a jour', [
                    'user_id' => $this->id,
                    'old_rank' => $oldRankName,
                    'new_rank' => $newRank->name,
                    'old_level' => $oldRankLevel,
                    'new_level' => $newRank->level,
                ]);
                
                return true;
            }
            return false;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur mise a jour grade', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Force la mise à jour du grade
     */
    public function forceRankUpdate(): bool
    {
        return $this->calculateAndUpdateRank();
    }

    /**
     * Récupère le grade en cache
     */
    public function getCachedRankAttribute()
    {
        return Cache::remember("user_rank_{$this->id}", 300, function () {
            return $this->rankObject;
        });
    }

    /**
     * Vide le cache du grade
     */
    public function clearRankCache(): void
    {
        Cache::forget("user_rank_{$this->id}");
    }

    /**
 * Met à jour les PV mensuels
 * - Mois d'activation → monthly_pv = pv_balance (tout le mois)
 * - Mois suivants → monthly_pv = achats du mois en cours uniquement
 */
public function updateMonthlyPV(): void
{
    $monthStart = now()->startOfMonth();
    $monthEnd = now()->endOfMonth();
    
    // ✅ 1. Vérifier si l'utilisateur a été activé ce mois-ci
    $activatedThisMonth = false;
    if ($this->activated_at) {
        $activatedThisMonth = $this->activated_at->between($monthStart, $monthEnd);
    }
    
    // ✅ 2. Si activé ce mois-ci → monthly_pv = pv_balance
    //    pv_balance contient déjà : package d'activation + tous les achats du mois
    if ($activatedThisMonth) {
        $this->monthly_pv = $this->pv_balance;
        $this->monthly_bv = $this->bv_balance;
        $this->saveQuietly();
        $this->clearRankCache();
        return;
    }
    
    // ✅ 3. Mois suivants → Calculer uniquement les achats du mois en cours
    $totalPV = DB::table('order_items')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->where('orders.user_id', $this->id)
        ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
        ->where('orders.payment_status', 'completed')
        ->sum('order_items.pv_value');
    
    $totalBV = DB::table('order_items')
        ->join('orders', 'order_items.order_id', '=', 'orders.id')
        ->where('orders.user_id', $this->id)
        ->whereBetween('orders.created_at', [$monthStart, $monthEnd])
        ->where('orders.payment_status', 'completed')
        ->sum('order_items.bv_value');
    
    $this->monthly_pv = (int) $totalPV;
    $this->monthly_bv = (int) $totalBV;
    $this->saveQuietly();
    $this->clearRankCache();
}

    /**
     * Vérifie si l'utilisateur est qualifié pour le paiement
     */
    public function isQualifiedForPayment(): bool
    {
        if (!$this->rank) {
            return false;
        }

        $monthlyPvRequired = $this->rank->monthly_pv_required ?? 0;
        
        return $this->monthly_pv >= $monthlyPvRequired;
    }

    /**
     * Vérifie si l'utilisateur a un grade supérieur
     */
    public function isHigherRank(string $slug): bool
    {
        return $this->higherRanks()->where('slug', $slug)->exists();
    }

    /**
     * Récupère le grade supérieur actuel
     */
    public function getCurrentHigherRank()
    {
        return $this->higherRanks()
            ->orderBy('level', 'desc')
            ->first();
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

    public function recalculateAllAncestors(): void
    {
        $this->updateAllAncestorsWithoutEvents();
    }
}