<?php
// app/Models/CommissionPeriod.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionPeriod extends Model
{
    protected $fillable = [
        'period',
        'start_date',
        'end_date',
        'calculation_date',
        'payment_date',
        'status',
        'total_commissions',
        'total_paid',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'calculation_date' => 'date',
        'payment_date' => 'date',
        'total_commissions' => 'decimal:2',
        'total_paid' => 'decimal:2',
    ];

    public function payments()
    {
        return $this->hasMany(CommissionPayment::class);
    }

    public function commissions()
    {
        return $this->hasMany(Commission::class);
    }

    public function monthlyRanks()
    {
        return $this->hasMany(UserMonthlyRank::class, 'period', 'period');
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'calculating' => 'Calculating',
            'calculated' => 'Calculated',
            'paying' => 'Paying',
            'paid' => 'Paid',
            'closed' => 'Closed',
        ];
        return $labels[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'yellow',
            'calculating' => 'blue',
            'calculated' => 'green',
            'paying' => 'purple',
            'paid' => 'green',
            'closed' => 'gray',
        ];
        return $colors[$this->status] ?? 'gray';
    }

    public function getProgressAttribute()
    {
        if ($this->total_commissions > 0) {
            return ($this->total_paid / $this->total_commissions) * 100;
        }
        return 0;
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function isPaid()
    {
        return $this->status === 'paid' || $this->status === 'closed';
    }

    public function isCalculated()
    {
        return $this->status === 'calculated' || $this->status === 'paying' || $this->status === 'paid';
    }
}