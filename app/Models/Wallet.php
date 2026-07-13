<?php
// app/Models/Wallet.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'pending_balance',
        'total_withdrawn',
        'total_deposited',
        'currency',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'total_deposited' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function credit($amount, $description = null)
    {
        $this->balance += $amount;
        $this->total_deposited += $amount;
        $this->save();

        return $this->createTransaction($amount, 'deposit', $description);
    }

    public function debit($amount, $description = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Insufficient balance');
        }

        $this->balance -= $amount;
        $this->total_withdrawn += $amount;
        $this->save();

        return $this->createTransaction($amount, 'withdrawal', $description);
    }

    public function createTransaction($amount, $type, $description = null)
    {
        return Transaction::create([
            'user_id' => $this->user_id,
            'wallet_id' => $this->id,
            'type' => $type,
            'amount' => $amount,
            'fee' => 0,
            'net_amount' => $amount,
            'balance_before' => $this->balance + ($type === 'deposit' ? -$amount : $amount),
            'balance_after' => $this->balance,
            'status' => 'completed',
            'description' => $description,
            'completed_at' => now(),
        ]);
    }

    public function getFormattedBalanceAttribute()
    {
        return $this->currency . ' ' . number_format($this->balance, 2);
    }

    public function getFormattedPendingBalanceAttribute()
    {
        return $this->currency . ' ' . number_format($this->pending_balance, 2);
    }

    public function getFormattedTotalWithdrawnAttribute()
    {
        return $this->currency . ' ' . number_format($this->total_withdrawn, 2);
    }

    public function getFormattedTotalDepositedAttribute()
    {
        return $this->currency . ' ' . number_format($this->total_deposited, 2);
    }

    public function canWithdraw($amount)
    {
        return $this->balance >= $amount;
    }

    public function addPending($amount)
    {
        $this->pending_balance += $amount;
        $this->save();
    }

    public function clearPending($amount)
    {
        $this->pending_balance -= $amount;
        $this->save();
    }
}