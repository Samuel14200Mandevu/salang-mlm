<?php

namespace App\Models;


use App\Models\UserPvBalance;
use App\Support\MlmPeriod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Services\MLM\AdvancedRankCalculator;
use App\Services\MLM\TeamPVCalculator;
use App\Jobs\RecalculateAfterPVImport;

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
        'birth_date', 'gender', 'profession', 'identity_number',
        'bank_name', 'account_number', 'account_holder', 'mobile_money',
        'signature_name', 'signature_date', 'signature_location',
        'registered_by', 'registered_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    // ══════════════════════════════════════════════════════════════
    // ✅ CORRECTION 1 : decimal:1 → float (calculs fiables)
    // ══════════════════════════════════════════════════════════════
    protected $casts = [
        'email_verified_at' => 'datetime',
        'package_expiry' => 'datetime',
        'kyc_verified_at' => 'datetime',
        'password' => 'hashed',

        // ✅ PV/BV en float (PAS decimal:1)
        'pv_balance' => 'float',
        'bv_balance' => 'float',
        'monthly_pv' => 'float',
        'monthly_bv' => 'float',
        'team_pv' => 'float',
        'team_bv' => 'float',
        'commission_balance' => 'float',
        'total_earnings' => 'float',

        // ✅ Entiers
        'qualified_branches' => 'integer',
        'direct_sponsors_count' => 'integer',
        'total_team' => 'integer',
        'total_sponsors' => 'integer',
        'rank_level' => 'integer',
        'rank_id' => 'integer',

        // ✅ Booléens / dates
        'is_active' => 'boolean',
        'rank_update_queued' => 'boolean',
        'activation_code_expires_at' => 'datetime',
        'activated_at' => 'datetime',
        'last_rank_update' => 'datetime',
        'birth_date' => 'date',
        'signature_date' => 'date',
        'registered_at' => 'datetime',
        'gender' => 'string',
    ];

    // ══════════════════════════════════════════════════════════════
    // ✅ SYSTÈME UNIFIÉ : UN SEUL dispatch vers RecalculateAfterPVImport
    // ══════════════════════════════════════════════════════════════
    protected static function booted(): void
    {
        static::created(function ($user) {
            try {
                RecalculateAfterPVImport::dispatch([$user->id], MlmPeriod::current())
                    ->onQueue('rank-recalculation');
            } catch (\Exception $e) {
                Log::error('Error in User::created', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        static::updated(function ($user) {
            try {
                $fieldsToWatch = [
                    'pv_balance', 'monthly_pv', 'parrain_id', 'is_active',
                    'rank_id', 'team_pv', 'bv_balance',
                ];

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

                $userIds = [$user->id];

                if ($user->wasChanged('parrain_id')) {
                    $oldParrainId = $user->getOriginal('parrain_id');
                    if ($oldParrainId) {
                        $userIds[] = $oldParrainId;
                    }
                }

                RecalculateAfterPVImport::dispatch($userIds, MlmPeriod::current())
                    ->onQueue('rank-recalculation');

            } catch (\Exception $e) {
                Log::error('Error in User::updated', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });

        static::deleted(function ($user) {
            try {
                if ($user->parrain_id) {
                    RecalculateAfterPVImport::dispatch([$user->parrain_id], MlmPeriod::current())
                        ->onQueue('rank-recalculation');
                }

                Cache::forget("user_rank_{$user->id}");
                Cache::forget("descendants_{$user->id}");
                Cache::forget("descendants_count_{$user->id}");
                Cache::forget("team_pv_{$user->id}");
            } catch (\Exception $e) {
                Log::error('Error in User::deleted', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }

    // ══════════════════════════════════════════════════════════════
    // RELATIONS
    // ══════════════════════════════════════════════════════════════

    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }

    public function rankObject()
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

    public function pvBalance()
    {
        return $this->hasOne(\App\Models\UserPvBalance::class);
    }

    /**
     * PV cumulés (users.pv_balance) + portefeuille distribution (user_pv_balances).
     */
    public function pvSummary(?UserPvBalance $balance = null): array
    {
        if ($balance === null) {
            $balance = $this->relationLoaded('pvBalance')
                ? $this->pvBalance
                : $this->pvBalance()->first();
        }

        $available = $balance ? (int) $balance->available_pv : 0;
        $allocated = $balance ? (int) $balance->allocated_pv : 0;
        $pending = $balance ? (int) $balance->pending_pv : 0;
        $walletTotal = $balance
            ? (int) ($balance->total_pv ?: ($available + $allocated + $pending))
            : 0;

        return [
            'cumulative_pv' => (int) round((float) ($this->pv_balance ?? 0)),
            'wallet_total_pv' => $walletTotal,
            'available_pv' => $available,
            'allocated_pv' => $allocated,
            'pending_pv' => $pending,
            'total_bv' => $balance ? (int) $balance->total_bv : 0,
            'available_bv' => $balance ? (int) $balance->available_bv : 0,
        ];
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

    public function wishlist()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistProducts()
    {
        return $this->belongsToMany(Product::class, 'wishlist')->withTimestamps();
    }

    // ══════════════════════════════════════════════════════════════
    // SCOPES
    // ══════════════════════════════════════════════════════════════

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

    // ══════════════════════════════════════════════════════════════
    // ACCESSEURS
    // ══════════════════════════════════════════════════════════════

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
            return (int) $this->attributes['rank_level'];
        }
        if ($this->relationLoaded('rank') && $this->rank) {
            return (int) ($this->rank->level ?? 1);
        }
        if ($this->rank_id) {
            $rank = Rank::find($this->rank_id);
            if ($rank) {
                return (int) ($rank->level ?? 1);
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
        // ✅ team_pv inclut déjà pv_balance
        return (float) ($this->team_pv ?? 0);
    }

    public function getCachedRankAttribute()
    {
        return Cache::remember("user_rank_{$this->id}", 300, function () {
            return $this->rankObject;
        });
    }

    /** Email généré à l’inscription caisse / import (remplaçable par Google). */
    public function hasPlaceholderEmail(): bool
    {
        $email = strtolower(trim((string) ($this->email ?? '')));

        if ($email === '') {
            return false;
        }

        return str_ends_with($email, '@salanggroup.com')
            || str_ends_with($email, '@salang.com');
    }

    public function isGoogleLinked(): bool
    {
        return $this->provider === 'google'
            && filled($this->provider_id)
            && ! $this->hasPlaceholderEmail();
    }

    // ══════════════════════════════════════════════════════════════
    // MÉTHODES DE CALCUL — TOUTES VIA LES SERVICES UNIFIÉS
    // ══════════════════════════════════════════════════════════════

    public function updateTeamPVOptimized(): void
    {
        try {
            $calculator = app(TeamPVCalculator::class);
            $calculator->updateUser($this);

            Log::debug('Team PV mis à jour via TeamPVCalculator', [
                'user_id' => $this->id,
                'team_pv' => $this->team_pv,
                'total_team' => $this->total_team,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur updateTeamPVOptimized', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

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

    public function updateAllAncestorsTeamPV(): void
    {
        try {
            $calculator = app(TeamPVCalculator::class);
            $updated = $calculator->updateAncestors($this);

            Log::debug('Team PV mis à jour pour tous les ancêtres', [
                'user_id' => $this->id,
                'ancestors_updated' => $updated,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur updateAllAncestorsTeamPV', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
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

    public function updateRankSync(): bool
    {
        try {
            $calculator = app(AdvancedRankCalculator::class);
            $oldRankId = $this->rank_id;
            $oldRankName = $this->rank ?? 'Distributeur';
            $oldRankLevel = $this->rank_level ?? 1;

            $newRank = $calculator->calculateAdvancedRank($this);

            if (!$newRank) {
                Log::warning('No rank found for user', ['user_id' => $this->id]);
                return false;
            }

            $needsUpdate = (
                $newRank->id != $this->rank_id ||
                $newRank->name != $this->rank ||
                $newRank->level != $this->rank_level
            );

            if (!$needsUpdate) {
                return false;
            }

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
                'notes' => 'Rank update sync',
            ]);

            DB::commit();

            Log::info('Rank updated sync', [
                'user_id' => $this->id,
                'old_rank' => $oldRankName,
                'new_rank' => $newRank->name,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating rank sync', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function calculateAndUpdateRank(): bool
    {
        try {
            $calculator = app(AdvancedRankCalculator::class);
            $oldRankId = $this->rank_id;

            $calculator->recalculateUserRankLight($this, 'calculateAndUpdateRank');

            return $this->rank_id != $oldRankId;
        } catch (\Exception $e) {
            Log::error('Erreur calculateAndUpdateRank', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public function updateRankAfterPVChange(string $reason = 'pv_updated'): void
    {
        try {
            $calculator = app(AdvancedRankCalculator::class);
            $calculator->recalculateUserRank($this, $reason);
        } catch (\Exception $e) {
            Log::error('Erreur updateRankAfterPVChange', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function forceRankUpdate(): bool
    {
        $this->updateRankAfterPVChange('forced');
        return true;
    }

    public function updateRankAsync(string $reason = 'bulk_import'): void
    {
        RecalculateAfterPVImport::dispatch([$this->id], MlmPeriod::current())
            ->onQueue('rank-recalculation');
    }

    public function updateMonthlyPV(): void
    {
        RecalculateAfterPVImport::dispatch([$this->id], MlmPeriod::current())
            ->onQueue('rank-recalculation');
    }

    // ══════════════════════════════════════════════════════════════
    // MÉTHODES MÉTIER
    // ══════════════════════════════════════════════════════════════

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

            Log::info('PV added', [
                'user_id' => $this->id,
                'amount' => $amount,
                'source' => $source,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding PV', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

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
                'period' => $orderData['period'] ?? MlmPeriod::current(),
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
                    'period' => $orderData['period'] ?? MlmPeriod::current(),
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

            Log::info('Order imported', [
                'user_id' => $this->id,
                'order_id' => $order->id,
                'total_pv' => $totalPV,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error importing order', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    // ══════════════════════════════════════════════════════════════
    // DESCENDANTS / CACHE
    // ══════════════════════════════════════════════════════════════

    public function getAllDescendants(): \Illuminate\Support\Collection
    {
        $cacheKey = "descendants_{$this->id}";
        return Cache::remember($cacheKey, 3600, function () {
            $descendants = collect();
            $stack = collect([$this]);
            $processed = [];

            while ($stack->isNotEmpty()) {
                $current = $stack->pop();

                if (in_array($current->id, $processed)) {
                    continue;
                }
                $processed[] = $current->id;

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

    public function countDescendants(): int
    {
        if ($this->total_team > 0) {
            return $this->total_team;
        }
        return $this->getAllDescendants()->count();
    }

    public function getTeamMonthlyPV(): float
    {
        try {
            return (float) $this->getAllDescendants()->sum('monthly_pv');
        } catch (\Exception $e) {
            Log::error('Erreur getTeamMonthlyPV', [
                'user_id' => $this->id,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    public function getDescendants()
    {
        return $this->getAllDescendants();
    }

    public function clearRankCache(): void
    {
        Cache::forget("user_rank_{$this->id}");
        Cache::forget("rank_calculation_{$this->id}");
        Cache::forget("descendants_{$this->id}");
        Cache::forget("descendants_count_{$this->id}");
        Cache::forget("team_pv_{$this->id}");
    }

    // ══════════════════════════════════════════════════════════════
    // AUTRES MÉTHODES
    // ══════════════════════════════════════════════════════════════

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
        return QualifiedBranch::where('user_id', $this->id)
            ->where('period', $period)
            ->get();
    }

    public function countQualifiedBranchesForPeriod(string $period, ?int $minLevel = null): int
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

    public function commissionsByType($type = null)
    {
        $query = $this->commissions();
        if ($type) {
            $query->where('type', $type);
        }
        return $query;
    }
}