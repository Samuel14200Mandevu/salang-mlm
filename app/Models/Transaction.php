<?php
// app/Models/Transaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'type',
        'amount',
        'fee',
        'net_amount',
        'balance_before',
        'balance_after',
        'status',
        'reference',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
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

    public function getTypeLabelAttribute()
    {
        $labels = [
            'commission' => 'Commission',
            'withdrawal' => 'Withdrawal',
            'deposit' => 'Deposit',
            'purchase' => 'Purchase',
            'refund' => 'Refund',
            'adjustment' => 'Adjustment',
        ];
        return $labels[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'completed' => 'green',
            'failed' => 'red',
            'cancelled' => 'gray',
        ];
        return $colors[$this->status] ?? 'gray';
    }

    public function getFormattedAmountAttribute()
    {
        $sign = $this->type === 'withdrawal' ? '-' : '+';
        return $sign . '$' . number_format($this->amount, 2);
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

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isCommission()
    {
        return $this->type === 'commission';
    }

    public function isWithdrawal()
    {
        return $this->type === 'withdrawal';
    }
}