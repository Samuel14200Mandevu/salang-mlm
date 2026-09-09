<?php

// app/Models/PvAllocation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PvAllocation extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'distributor_id', 'pv_amount', 'bv_amount',
        'distribution_type', 'notes', 'status', 'allocated_at',
        'approved_at', 'approved_by'
    ];

    protected $casts = [
        'allocated_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function distributor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributor_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}