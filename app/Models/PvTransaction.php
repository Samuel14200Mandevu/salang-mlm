<?php

// app/Models/PvTransaction.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PvTransaction extends Model
{
    protected $fillable = [
        'user_id', 'order_id', 'pv_allocation_id', 'type',
        'source', 'pv_amount', 'bv_amount', 'cash_amount',
        'description', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function allocation(): BelongsTo
    {
        return $this->belongsTo(PvAllocation::class, 'pv_allocation_id');
    }
}