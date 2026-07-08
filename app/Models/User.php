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
    // RELATIONS
    // ============================================================

    /**
     * Relation avec le rang
     */
    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    /**
     * Relation avec le package
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Relation avec le parrain (celui qui m'a invité)
     * sponsor_id est le code de parrain unique
     */
    public function parrain()
    {
        return $this->belongsTo(User::class, 'sponsor_id', 'sponsor_id');
    }

    /**
     * Alias pour sponsor (compatibilité)
     */
    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id', 'sponsor_id');
    }

    /**
     * Relation avec les filleuls (ceux que j'ai invités)
     */
    public function filleuls()
    {
        return $this->hasMany(User::class, 'sponsor_id', 'sponsor_id');
    }

    /**
     * Alias pour downlines (compatibilité)
     */
    public function downlines()
    {
        return $this->hasMany(User::class, 'sponsor_id', 'sponsor_id');
    }

    /**
     * Relation avec le portefeuille
     */
    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    /**
     * Relation avec les commissions
     */
    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    /**
     * Relation avec les transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Relation avec les retraits
     */
    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Relation avec la généalogie
     */
    public function genealogy()
    {
        return $this->hasOne(Genealogy::class);
    }

    /**
     * Relation avec l'historique des rangs
     */
    public function rankHistory()
    {
        return $this->hasMany(RankHistory::class);
    }

    /**
     * Relation avec les commandes
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relation avec les documents KYC
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
            return $package ? $package->name : 'No package';
        }
        
        return 'No package';
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

    public function getDecryptedPhoneAttribute()
    {
        return $this->phone;
    }

    /**
     * Récupérer le nom du parrain
     */
    public function getParrainNameAttribute()
    {
        $parrain = $this->parrain;
        return $parrain ? $parrain->name : 'No sponsor';
    }

    /**
     * Récupérer le code de parrain
     */
    public function getReferralCodeAttribute()
    {
        return $this->sponsor_id ?? '';
    }

    /**
     * Compter les filleuls
     */
    public function countFilleuls()
    {
        return $this->filleuls()->count();
    }

    /**
     * Compter les filleuls actifs
     */
    public function countFilleulsActifs()
    {
        return $this->filleuls()->where('is_active', true)->count();
    }

    public function isAdmin()
    {
        return $this->hasRole('admin');
    }

    public function isKycVerified()
    {
        return $this->kyc_status === 'verified';
    }
}