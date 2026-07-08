<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\Encryptable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, Encryptable;

    /**
     * Champs à chiffrer avec AES-256
     */
    protected $encryptable = ['phone', 'address'];

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'sponsor_id',
        'rank_id',
        'package_id',
        'pv_balance',
        'bv_balance',
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
        'commission_balance' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // ============================================================
    // RELATIONS CORRIGÉES
    // ============================================================

    /**
     * ✅ Relation pour le rank
     */
    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    /**
     * ✅ Relation pour le package
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * ✅ Relation pour le sponsor (celui qui a parrainé cet utilisateur)
     * sponsor_id est un code VARCHAR unique
     */
    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id', 'sponsor_id');
    }

    /**
     * ✅ Relation pour les fillules (ceux que cet utilisateur a parrainés)
     * sponsor_id est un code VARCHAR unique
     */
    public function downlines()
    {
        return $this->hasMany(User::class, 'sponsor_id', 'sponsor_id');
    }

    /**
     * ✅ Relation pour le portefeuille
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * ✅ Relation pour les commissions
     */
    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    /**
     * ✅ Relation pour les transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * ✅ Relation pour les retraits
     */
    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * ✅ Relation pour la généalogie
     */
    public function genealogy()
    {
        return $this->hasOne(Genealogy::class);
    }

    /**
     * ✅ Relation pour l'historique des rangs
     */
    public function rankHistory()
    {
        return $this->hasMany(RankHistory::class);
    }

    /**
     * ✅ Relation pour les commandes
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * ✅ Relation pour les documents KYC
     */
    public function kycDocuments()
    {
        return $this->hasMany(KycDocument::class);
    }

    // ============================================================
    // ACCESSEURS
    // ============================================================

    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getRankNameAttribute()
    {
        if ($this->relationLoaded('rank') && $this->rank) {
            return $this->rank->name;
        }
        
        if ($this->rank_id) {
            $rank = Rank::find($this->rank_id);
            return $rank ? $rank->name : 'Distributor';
        }
        
        return 'Distributor';
    }

    public function getPackageNameAttribute()
    {
        if ($this->relationLoaded('package') && $this->package) {
            return $this->package->name;
        }
        
        if ($this->package_id) {
            $package = Package::find($this->package_id);
            return $package ? $package->name : 'Aucun package';
        }
        
        return 'Aucun package';
    }

    public function getPackageIconAttribute()
    {
        if ($this->relationLoaded('package') && $this->package) {
            return $this->package->icon;
        }
        
        if ($this->package_id) {
            $package = Package::find($this->package_id);
            return $package ? $package->icon : '📦';
        }
        
        return '📦';
    }

    public function getWalletBalanceAttribute()
    {
        if ($this->relationLoaded('wallet') && $this->wallet) {
            return $this->wallet->balance;
        }
        
        if ($this->id) {
            $wallet = Wallet::where('user_id', $this->id)->first();
            return $wallet ? $wallet->balance : 0;
        }
        
        return 0;
    }

    public function getStatusLabelAttribute()
    {
        return $this->is_active ? '✅ Actif' : '❌ Inactif';
    }

    public function getKycStatusLabelAttribute()
    {
        $labels = [
            'not_submitted' => '📤 Non soumis',
            'pending' => '⏳ En attente',
            'partial' => '⚠️ Partiel',
            'verified' => '✅ Vérifié',
            'rejected' => '❌ Rejeté',
        ];
        return $labels[$this->kyc_status] ?? ucfirst($this->kyc_status);
    }

    public function getDecryptedPhoneAttribute()
    {
        return $this->phone;
    }

    /**
     * ✅ Récupérer le nom du sponsor
     */
    public function getSponsorNameAttribute()
    {
        $sponsor = $this->sponsor;
        return $sponsor ? $sponsor->name : 'None';
    }

    /**
     * ✅ Récupérer le code de parrain
     */
    public function getReferralCodeAttribute()
    {
        return $this->sponsor_id ?? '';
    }

    /**
     * ✅ Compter les fillules
     */
    public function countDownlines()
    {
        return $this->downlines()->count();
    }

    /**
     * ✅ Compter les fillules actives
     */
    public function countActiveDownlines()
    {
        return $this->downlines()->where('is_active', true)->count();
    }

    /**
     * ✅ Compter les fillules par niveau
     */
    public function countDownlinesByLevel($level = 1)
    {
        if ($level == 1) {
            return $this->downlines()->count();
        }
        
        $sponsorCodes = $this->downlines()->pluck('sponsor_id')->filter()->toArray();
        
        for ($i = 2; $i <= $level; $i++) {
            if (empty($sponsorCodes)) {
                return 0;
            }
            $sponsorCodes = User::whereIn('sponsor_id', $sponsorCodes)
                ->pluck('sponsor_id')
                ->filter()
                ->toArray();
        }
        
        return User::whereIn('sponsor_id', $sponsorCodes)->count();
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
}