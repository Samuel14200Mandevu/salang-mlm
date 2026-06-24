<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_user_id',
        'type',
        'amount',
        'percentage',
        'description',
        'order_id',
        'package_id',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'direct' => 'Direct Bonus',
            'indirect' => 'Indirect Bonus',
            'leadership' => 'Leadership Bonus',
            'retail' => 'Retail Profit',
        ];
        return $labels[$this->type] ?? $this->type;
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
        ];
        return $labels[$this->status] ?? $this->status;
    }
}
