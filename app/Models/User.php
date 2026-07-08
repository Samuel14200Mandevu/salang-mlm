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
        'sponsor_id',    // Code de parrain UNIQUE pour inviter
        'parrain_id',    // ID de l'utilisateur qui m'a parrainé
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

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * ✅ LE PARRAIN - La personne qui m'a invité
     * Utilise parrain_id (clé étrangère vers id)
     */
    public function parrain()
    {
        return $this->belongsTo(User::class, 'parrain_id');
    }

    /**
     * ✅ LES FILLEULS - Les personnes que j'ai invitées
     * Utilise parrain_id (clé étrangère vers id)
     */
    public function filleuls()
    {
        return $this->hasMany(User::class, 'parrain_id');
    }

    /**
     * Alias pour sponsor() - gardé pour compatibilité
     */
    public function sponsor()
    {
        return $this->belongsTo(User::class, 'parrain_id');
    }

    /**
     * Alias pour downlines() - gardé pour compatibilité
     */
    public function downlines()
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
     * Récupérer le code de parrain (pour inviter)
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