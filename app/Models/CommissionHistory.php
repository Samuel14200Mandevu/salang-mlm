<?php
// app/Models/CommissionHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommissionHistory extends Model
{
    use HasFactory;

    protected $table = 'commission_history';

    protected $fillable = [
        'pv_history_id',
        'user_id',
        'from_user_id',
        'period',
        'type',
        'amount',
        'percentage',
        'pv_used',
        'generation',
        'status',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'pv_used' => 'decimal:2',
    ];

    public function pvHistory()
    {
        return $this->belongsTo(PVHistory::class, 'pv_history_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }
}