<?php
// app/Models/Commission.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'from_user_id',
        'commission_period_id',
        'period',
        'type',
        'amount',
        'percentage',
        'description',
        'order_id',
        'package_id',
        'generation',
        'calculation_type',
        'status',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'paid_at' => 'datetime',
        'generation' => 'integer',
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

    public function period()
    {
        return $this->belongsTo(CommissionPeriod::class, 'commission_period_id');
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

    public function scopePeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeDirect($query)
    {
        return $query->where('type', 'direct');
    }

    public function scopeIndirect($query)
    {
        return $query->where('type', 'indirect');
    }

    public function scopeLeadership($query)
    {
        return $query->where('type', 'leadership');
    }

    public function scopeRetail($query)
    {
        return $query->where('type', 'retail');
    }

    public function scopeGlobal($query)
    {
        return $query->where('type', 'global');
    }

    public function getTypeLabelAttribute()
    {
        $labels = [
            'direct' => 'Direct Bonus',
            'indirect' => 'Indirect Bonus',
            'leadership' => 'Leadership Bonus',
            'retail' => 'Retail Profit',
            'global' => 'Global Bonus',
            'consumer' => 'Consumer Bonus',
        ];
        return $labels[$this->type] ?? ucfirst($this->type);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getTypeColorAttribute()
    {
        $colors = [
            'direct' => 'blue',
            'indirect' => 'green',
            'leadership' => 'purple',
            'retail' => 'orange',
            'global' => 'gold',
            'consumer' => 'teal',
        ];
        return $colors[$this->type] ?? 'gray';
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getDescriptionWithDetailsAttribute()
    {
        $desc = $this->description;
        
        if ($this->fromUser) {
            $desc .= " (from " . $this->fromUser->name . ")";
        }
        
        if ($this->generation) {
            $desc .= " - Generation " . $this->generation;
        }
        
        return $desc;
    }

    public function markAsPaid()
    {
        $this->status = 'paid';
        $this->paid_at = now();
        $this->save();
    }

    public function isDirect()
    {
        return $this->type === 'direct';
    }

    public function isIndirect()
    {
        return $this->type === 'indirect';
    }

    public function isLeadership()
    {
        return $this->type === 'leadership';
    }

    public function isRetail()
    {
        return $this->type === 'retail';
    }

    public function isGlobal()
    {
        return $this->type === 'global';
    }
}