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

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'commission' => 'Commission',
            'withdrawal' => 'Withdrawal',
            'deposit' => 'Deposit',
            'purchase' => 'Purchase',
        ];
        return $labels[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
        ];
        return $labels[$this->status] ?? $this->status;
    }
}
