<?php
// app/Models/UserMonthlyRank.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMonthlyRank extends Model
{
    protected $table = 'user_monthly_ranks';

    protected $fillable = [
        'user_id',
        'rank_id',
        'period',
        'pv_monthly',
        'bv_monthly',
        'team_pv',
        'team_bv',
        'direct_sponsors',
        'qualified_branches',
    ];

    protected $casts = [
        'pv_monthly' => 'integer',
        'bv_monthly' => 'integer',
        'team_pv' => 'integer',
        'team_bv' => 'integer',
        'direct_sponsors' => 'integer',
        'qualified_branches' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    public function getRankNameAttribute()
    {
        return $this->rank ? $this->rank->name : 'Distributor';
    }

    public function getRankLevelAttribute()
    {
        return $this->rank ? $this->rank->level : 1;
    }

    public function getFormattedPVAttribute()
    {
        return number_format($this->pv_monthly) . ' PV';
    }

    public function getFormattedTeamPVAttribute()
    {
        return number_format($this->team_pv) . ' PV';
    }

    public function getPeriodLabelAttribute()
    {
        return date('F Y', strtotime($this->period . '-01'));
    }
}