<?php

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
use App\Services\MLM\RankUpdateService;
use App\Jobs\UpdateRanks;
use App\Jobs\UpdateTeamPV;
use App\Jobs\CalculatePVBV;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $table = 'users';

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'sponsor_id', 'parrain_id',
        'position', 'rank_id', 'rank', 'rank_level', 'package_id',
        'pv_balance', 'bv_balance', 'monthly_pv', 'monthly_bv',
        'team_pv', 'team_bv', 'qualified_branches', 'direct_sponsors_count',
        'commission_balance', 'total_earnings', 'total_sponsors', 'total_team',
        'is_active', 'user_type', 'kyc_status', 'kyc_verified_at',
        'package_expiry', 'avatar', 'provider', 'provider_id',
        'country', 'city', 'address', 'ip_address', 'last_login_at',
        'last_rank_update', 'rank_update_queued', 'activation_code',
        'activation_code_expires_at', 'activated_at', 'activation_method',
        'activation_package_id', 'activation_commission_used',
        'activation_commission_balance', 'email_verified_at', 'remember_token',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'package_expiry' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'password' => 'hashed',
        'pv_balance' => 'decimal:1',
        'bv_balance' => 'decimal:1',
        'monthly_pv' => 'decimal:1',
        'monthly_bv' => 'decimal:1',
        'team_pv' => 'decimal:1',
        'team_bv' => 'decimal:1',
        'qualified_branches' => 'integer',
        'direct_sponsors_count' => 'integer',
        'commission_balance' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'is_active' => 'boolean',
        'activation_code_expires_at' => 'datetime',
        'activated_at' => 'datetime',
        'last_rank_update' => 'datetime',
        'rank_update_queued' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(function ($user) {
            try {
                dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
                dispatch(new UpdateRanks($user->id))->onQueue('high');
                Log::info('User created, jobs dispatched', ['user_id' => $user->id]);
            } catch (\Exception $e) {
                Log::error('Error in created event', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        });

        static::updated(function ($user) {
            try {
                $fieldsToWatch = ['pv_balance', 'monthly_pv', 'parrain_id', 'is_active', 'rank_id', 'team_pv', 'bv_balance'];
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
                
                if ($user->wasChanged('pv_balance') || $user->wasChanged('bv_balance') || $user->wasChanged('monthly_pv')) {
                    dispatch(new CalculatePVBV($user->id))->onQueue('high');
                }
                
                if ($user->wasChanged('team_pv') || $user->wasChanged('pv_balance') || $user->wasChanged('bv_balance')) {
                    dispatch(new UpdateTeamPV($user->id, true))->onQueue('high');
                    dispatch(new UpdateRanks($user->id))->onQueue('high');
                    
                    if ($user->parrain_id) {
                        dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                        dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
                    }
                }
                
                if ($user->wasChanged('parrain_id')) {
                    if ($user->getOriginal('parrain_id')) {
                        $oldParrain = User::find($user->getOriginal('parrain_id'));
                        if ($oldParrain) {
                            dispatch(new UpdateTeamPV($oldParrain->id, true))->onQueue('low');
                            dispatch(new UpdateRanks($oldParrain->id))->onQueue('low');
                        }
                    }
                    if ($user->parrain_id) {
                        dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                        dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
                    }
                }
                
                if ($user->wasChanged('is_active') && $user->parrain_id) {
                    dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                    dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
                }
                
            } catch (\Exception $e) {
                Log::error('Error in updated event', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        });

        static::deleted(function ($user) {
            try {
                if ($user->parrain_id) {
                    dispatch(new UpdateTeamPV($user->parrain_id, true))->onQueue('low');
                    dispatch(new UpdateRanks($user->parrain_id))->onQueue('low');
                }
                Cache::forget("user_rank_{$user->id}");
                Cache::forget("descendants_{$user->id}");
                Cache::forget("descendants_count_{$user->id}");
            } catch (\Exception $e) {
                Log::error('Error in deleted event', ['user_id' => $user->id, 'error' => $e->getMessage()]);
            }
        });
    }

    // ============================================================
    // RELATIONS
    // ============================================================

    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
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
        return $this->hasMany(User::class, 'parrain_id')
            ->where('user_type', 'member')
            ->where('is_active', true);
    }

    public function tousFilleuls()
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
    // SCOPES
    // ============================================================

    public function scopeMembers($query)
    {
        return $query->where('user_type', 'member');
    }

    public function scopeClients($query)
    {
        return $query->where('user_type', 'client');
    }

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

    public function scopeQualified($query)
    {
        return $query->where('is_active', true)->where('kyc_status', 'verified');
    }

    // ============================================================
    // ACCESSEURS
    // ============================================================

    public function getRankNameAttribute()
    {
        if (!empty($this->rank)) {
            return $this->rank;
        }
        if ($this->relationLoaded('rank') && $this->rank) {
            return $this->rank->name;
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
        if (isset($this->attributes['rank_level']) && $this->attributes['rank_level'] > 0) {
            return $this->attributes['rank_level'];
        }
        if ($this->relationLoaded('rank') && $this->rank) {
            return $this->rank->level ?? 1;
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
        if ($this->rank_id) {
            return Rank::find($this->rank_id);
        }
        if (!empty($this->rank)) {
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

    public function getCumulPVAttribute()
    {
        return ($this->pv_balance ?? 0) + ($this->team_pv ?? 0);
    }

    // ============================================================
    // METHODES PRINCIPALES
    // ============================================================

    /**
     * Met à jour le grade (synchrone)
     */
    public function updateRankSync(): bool
    {
        try {
            $calculator = app(AdvancedRankCalculator::class);
            $newRank = $calculator->calculateAdvancedRank($this);
            
            if (!$newRank) {
                Log::warning('No rank found for user', ['user_id' => $this->id]);
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
                $this->rank_update_queued = 0;
                $this->saveQuietly();
                $this->clearRankCache();
                
                RankHistory::create([
                    'user_id' => $this->id,
                    'old_rank_id' => $oldRankId,
                    'new_rank_id' => $newRank->id,
                    'old_rank_name' => $oldRankName,
                    'old_rank_level' => $oldRankLevel,
                    'new_rank_name' => $newRank->name,
                    'new_rank_level' => $newRank->level,
                    'pv_at_time' => $this->pv_balance ?? 0,
                    'bv_at_time' => $this->bv_balance ?? 0,
                    'notes' => 'Rank update from import/script',
                ]);
                
                DB::commit();
                
                Log::info('Rank updated sync', [
                    'user_id' => $this->id,
                    'old_rank' => $oldRankName,
                    'new_rank' => $newRank->name,
                    'new_level' => $newRank->level,
                ]);
                
                return true;
            }
            return false;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating rank sync', ['user_id' => $this->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

/**
 * Relation avec la wishlist
 */
public function wishlist()
{
    return $this->hasMany(Wishlist::class);
}

/**
 * Relation directe avec les produits dans la wishlist
 */
public function wishlistProducts()
{
    return $this->belongsToMany(Product::class, 'wishlist')->withTimestamps();
}

    /**
     * RECALCUL DU TEAM_PV AVEC TOUS LES DESCENDANTS (RECURSIF)
     */
    public function updateTeamPVOptimized(): void
    {
        try {
            $teamData = $this->calculateTeamPVRecursive();
            
            $this->team_pv = $teamData['pv'];
            $this->team_bv = $teamData['bv'];
            $this->total_team = $teamData['total'];
            $this->saveQuietly();
            
            Cache::forget("descendants_{$this->id}");
            Cache::forget("descendants_count_{$this->id}");
            
            Log::debug('Team PV mis a jour avec tous les descendants', [
                'user_id' => $this->id,
                'team_pv' => $teamData['pv'],
                'total_team' => $teamData['total'],
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur updateTeamPVOptimized', [
                'user_id' => $this->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * CALCUL RECURSIF DU TEAM_PV AVEC TOUS LES DESCENDANTS
     */
    private function calculateTeamPVRecursive(): array
    {
        $totalPV = $this->pv_balance ?? 0;
        $totalBV = $this->bv_balance ?? 0;
        $totalCount = 0;

        $filleuls = User::where('parrain_id', $this->id)
            ->where('is_active', true)
            ->get();

        foreach ($filleuls as $filleul) {
            $childData = $filleul->calculateTeamPVRecursive();
            $totalPV += $childData['pv'];
            $totalBV += $childData['bv'];
            $totalCount += 1 + $childData['total'];
        }

        return [
            'pv' => $totalPV,
            'bv' => $totalBV,
            'total' => $totalCount,
        ];
    }

    /**
     * MET A JOUR LE TEAM_PV DE TOUS LES ANCETRES
     */
    public function updateAllAncestorsTeamPV(): void
    {
        $ancestor = $this->parrain;
        $level = 0;
        $maxLevel = 10;
        
        while ($ancestor && $level < $maxLevel) {
            $ancestor->updateTeamPVOptimized();
            $ancestor->updateRankAfterPVChange('ancestor_update');
            $ancestor = $ancestor->parrain;
            $level++;
        }
        
        Log::debug('Team PV mis à jour pour tous les ancêtres', [
            'user_id' => $this->id,
            'depth' => $level,
        ]);
    }

    /**
     * AJOUTER DES PV AVEC RECALCUL AUTOMATIQUE DE TOUS LES ANCETRES
     */
    public function addPV(float $amount, string $source = 'pos_sale', ?int $sourceId = null): void
    {
        if ($amount <= 0 || $this->user_type === 'client') {
            return;
        }

        DB::beginTransaction();
        try {
            $this->pv_balance += $amount;
            $this->monthly_pv += $amount;
            $this->bv_balance += $amount;
            $this->monthly_bv += $amount;
            $this->saveQuietly();

            DB::commit();

            // Déclencher la mise à jour du grade de l'utilisateur
            $this->updateRankAfterPVChange('add_pv');

            // Mettre à jour TOUS les ancêtres
            $this->updateAllAncestorsTeamPV();

            Log::info('PV added with rank update', [
                'user_id' => $this->id,
                'amount' => $amount,
                'source' => $source,
                'new_rank' => $this->rank,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding PV', ['user_id' => $this->id, 'amount' => $amount, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * DECLENCHE LA MISE A JOUR DU GRADE
     */
    public function updateRankAfterPVChange(string $reason = 'pv_updated'): void
    {
        $service = app(RankUpdateService::class);
        $service->triggerRankUpdate($this, $reason);
    }

    /**
     * AJOUTER UNE COMMANDE COMPLETE AVEC RECALCUL DE TOUS LES ANCETRES
     */
    public function addOrderWithRankUpdate(array $orderData, array $products): void
    {
        DB::beginTransaction();
        try {
            $totalPV = 0;
            $totalBV = 0;

            $order = Order::create([
                'user_id' => $this->id,
                'order_number' => $orderData['order_number'] ?? 'ORD-' . time(),
                'total_pv' => 0,
                'total_bv' => 0,
                'total_amount' => $orderData['total_amount'] ?? 0,
                'period' => $orderData['period'] ?? date('Y-m'),
                'order_date' => $orderData['order_date'] ?? now(),
                'status' => 'completed',
                'created_by' => $orderData['created_by'] ?? null,
            ]);

            foreach ($products as $product) {
                $pv = (float) $product['quantity'] * (float) $product['unit_pv'];
                $bv = $pv * 0.8;
                $totalPV += $pv;
                $totalBV += $bv;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_code' => $product['code'],
                    'product_name' => $product['name'],
                    'quantity' => (int) $product['quantity'],
                    'unit_pv' => (float) $product['unit_pv'],
                    'total_pv' => $pv,
                    'unit_bv' => (float) $product['unit_pv'] * 0.8,
                    'total_bv' => $bv,
                ]);

                PVHistory::create([
                    'user_id' => $this->id,
                    'amount' => $pv,
                    'date' => $orderData['order_date'] ?? now(),
                    'period' => $orderData['period'] ?? date('Y-m'),
                    'type' => 'personal',
                    'notes' => $product['code'] . ' - ' . $product['name'] . ' x ' . $product['quantity'],
                    'created_by' => $orderData['created_by'] ?? null,
                ]);
            }

            $order->update(['total_pv' => $totalPV, 'total_bv' => $totalBV]);

            $this->pv_balance += $totalPV;
            $this->monthly_pv += $totalPV;
            $this->bv_balance += $totalBV;
            $this->monthly_bv += $totalBV;
            $this->saveQuietly();

            DB::commit();

            $this->updateRankAfterPVChange('order_import');

            // Mettre à jour TOUS les ancêtres
            $this->updateAllAncestorsTeamPV();

            Log::info('Order imported with rank update', [
                'user_id' => $this->id,
                'order_id' => $order->id,
                'total_pv' => $totalPV,
                'new_rank' => $this->rank,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing order', ['user_id' => $this->id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * AUTRES METHODES DE COMPATIBILITE
     */
    public function updateTeamPVWithoutEvents(): void
    {
        $this->updateTeamPVOptimized();
    }

    public function updateTeamPV(): void
    {
        $this->updateTeamPVOptimized();
        if ($this->parrain_id) {
            $parrain = User::find($this->parrain_id);
            if ($parrain) {
                $parrain->updateTeamPVOptimized();
            }
        }
    }

    public function updateAllAncestors(): void
    {
        $this->updateAllAncestorsTeamPV();
    }

    public function updateAllAncestorsWithoutEvents(): void
    {
        $this->updateAllAncestorsTeamPV();
    }

    public function recalculateAllAncestors(): void
    {
        $this->updateAllAncestorsTeamPV();
    }

    public function calculateAndUpdateRank(): bool
    {
        $this->updateRankAfterPVChange('manual');
        return true;
    }

    public function forceRankUpdate(): bool
    {
        $this->updateRankAfterPVChange('forced');
        return true;
    }

    public function updateMonthlyPV(): void
    {
        dispatch(new CalculatePVBV($this->id))->onQueue('high');
    }

    public function getAllDescendants(): \Illuminate\Support\Collection
    {
        $cacheKey = "descendants_{$this->id}";
        return Cache::remember($cacheKey, 3600, function () {
            $descendants = collect();
            $stack = collect([$this]);
            while ($stack->isNotEmpty()) {
                $current = $stack->pop();
                $children = User::where('parrain_id', $current->id)->where('is_active', true)->get();
                foreach ($children as $child) {
                    $descendants->push($child);
                    $stack->push($child);
                }
            }
            return $descendants;
        });
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
                $count += 1 + ($filleul->total_team ?? 0);
            }
            return $count;
        });
    }

    public function getTeamMonthlyPV(): float
    {
        try {
            $total = 0;
            $descendants = $this->getAllDescendants();
            foreach ($descendants as $descendant) {
                $total += $descendant->monthly_pv;
            }
            return $total;
        } catch (\Exception $e) {
            Log::error('Erreur getTeamMonthlyPV', ['user_id' => $this->id, 'error' => $e->getMessage()]);
            return 0;
        }
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

    public function isQualifiedForPayment(): bool
    {
        if (!$this->rank) {
            return false;
        }
        $monthlyPvRequired = $this->rank->monthly_pv_required ?? 0;
        return $this->monthly_pv >= $monthlyPvRequired;
    }

    public function isHigherRank(string $slug): bool
    {
        return $this->higherRanks()->where('slug', $slug)->exists();
    }

    public function getCurrentHigherRank()
    {
        return $this->higherRanks()->orderBy('level', 'desc')->first();
    }

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
        return QualifiedBranch::where('user_id', $this->id)->where('period', $period)->get();
    }

    public function countQualifiedBranchesForPeriod(string $period, ?int $minLevel = null): int
    {
        $query = QualifiedBranch::where('user_id', $this->id)->where('period', $period);
        if ($minLevel) {
            $query->where('branch_rank_level', '>=', $minLevel);
        }
        return $query->count();
    }

    public function getMonthlyRankForPeriod(string $period)
    {
        return UserMonthlyRank::where('user_id', $this->id)->where('period', $period)->first();
    }

    public function getDescendants()
    {
        return $this->getAllDescendants();
    }

    public function updateRankAsync(string $reason = 'bulk_import'): void
    {
        $service = app(RankUpdateService::class);
        $service->triggerRankUpdateAsync($this, $reason);
    }
}