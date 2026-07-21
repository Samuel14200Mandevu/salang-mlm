<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'order_id',
        'type',
        'amount',
        'fee',
        'net_amount',
        'balance_before',
        'balance_after',
        'status',
        'reference',
        'transaction_id',
        'provider',
        'description',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
        'completed_at' => 'datetime',
    ];

    // ============================================================
    // RELATIONS
    // ============================================================
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // ============================================================
    // SCOPES
    // ============================================================
    
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCommission($query)
    {
        return $query->where('type', 'commission');
    }

    public function scopeWithdrawal($query)
    {
        return $query->where('type', 'withdrawal');
    }

    public function scopeDeposit($query)
    {
        return $query->where('type', 'deposit');
    }

    public function scopeByProvider($query, $provider)
    {
        return $query->where('provider', $provider);
    }

    public function scopeByReference($query, $reference)
    {
        return $query->where('reference', $reference);
    }

    public function scopeByTransactionId($query, $transactionId)
    {
        return $query->where('transaction_id', $transactionId);
    }

    // ============================================================
    // ATTRIBUTS ACCESSIBLES
    // ============================================================
    
    public function getTypeLabelAttribute()
    {
        $labels = [
            'commission' => 'Commission',
            'withdrawal' => 'Retrait',
            'deposit' => 'Dépôt',
            'purchase' => 'Achat',
            'refund' => 'Remboursement',
            'adjustment' => 'Ajustement',
        ];
        return $labels[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'En attente',
            'completed' => 'Complété',
            'failed' => 'Échoué',
            'cancelled' => 'Annulé',
            'processing' => 'En traitement',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'processing' => 'blue',
            'completed' => 'green',
            'failed' => 'red',
            'cancelled' => 'gray',
        ];
        return $colors[$this->status] ?? 'gray';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
        ];
        return $badges[$this->status] ?? 'secondary';
    }

    public function getFormattedAmountAttribute()
    {
        $sign = in_array($this->type, ['withdrawal', 'purchase', 'refund']) ? '-' : '+';
        return $sign . '$' . number_format($this->amount, 2);
    }

    public function getAmountWithSignAttribute()
    {
        $sign = in_array($this->type, ['withdrawal', 'purchase', 'refund']) ? '-' : '+';
        return $sign . number_format($this->amount, 2);
    }

    public function getTypeIconAttribute()
    {
        $icons = [
            'commission' => 'coins',
            'withdrawal' => 'arrow-right',
            'deposit' => 'arrow-left',
            'purchase' => 'shopping-cart',
            'refund' => 'rotate-left',
            'adjustment' => 'sliders',
        ];
        return $icons[$this->type] ?? 'circle';
    }

    public function getProviderLabelAttribute()
    {
        $labels = [
            'orange' => 'Orange Money',
            'airtel' => 'Airtel Money',
            'mpesa' => 'M-Pesa',
            'crypto' => 'Cryptomonnaie',
            'binance' => 'Binance Pay',
            'coinbase' => 'Coinbase',
        ];
        return $labels[$this->provider] ?? ucfirst($this->provider);
    }

    public function getProviderIconAttribute()
    {
        $icons = [
            'orange' => '🟠',
            'airtel' => '🔴',
            'mpesa' => '🟢',
            'crypto' => '₿',
            'binance' => '🟡',
            'coinbase' => '🔵',
        ];
        return $icons[$this->provider] ?? '💰';
    }

    // ============================================================
    // MÉTHODES UTILITAIRES
    // ============================================================
    
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isFailed()
    {
        return $this->status === 'failed';
    }

    public function isCommission()
    {
        return $this->type === 'commission';
    }

    public function isWithdrawal()
    {
        return $this->type === 'withdrawal';
    }

    public function isDeposit()
    {
        return $this->type === 'deposit';
    }

    public function isMobileMoney()
    {
        return in_array($this->provider, ['orange', 'airtel', 'mpesa']);
    }

    public function isCrypto()
    {
        return $this->provider === 'crypto';
    }

    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    public function markAsFailed()
    {
        $this->status = 'failed';
        $this->save();
    }

    public function markAsProcessing()
    {
        $this->status = 'processing';
        $this->save();
    }

    // ============================================================
    // SCOPES STATISTIQUES
    // ============================================================
    
    public static function getTotalDeposits($userId = null)
    {
        $query = self::deposit()->completed();
        if ($userId) {
            $query->where('user_id', $userId);
        }
        return $query->sum('amount');
    }

    public static function getTotalWithdrawals($userId = null)
    {
        $query = self::withdrawal()->completed();
        if ($userId) {
            $query->where('user_id', $userId);
        }
        return $query->sum('amount');
    }

    public static function getTotalCommissions($userId = null)
    {
        $query = self::commission()->completed();
        if ($userId) {
            $query->where('user_id', $userId);
        }
        return $query->sum('amount');
    }

    public static function getBalance($userId)
    {
        $deposits = self::deposit()->completed()->where('user_id', $userId)->sum('amount');
        $withdrawals = self::withdrawal()->completed()->where('user_id', $userId)->sum('amount');
        $commissions = self::commission()->completed()->where('user_id', $userId)->sum('amount');
        
        return $deposits + $commissions - $withdrawals;
    }
}