<?php
// app/Models/QualifiedBranch.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QualifiedBranch extends Model
{
    protected $fillable = [
        'user_id',
        'branch_user_id',
        'period',
        'branch_rank_level',
        'branch_pv',
    ];

    protected $casts = [
        'branch_rank_level' => 'integer',
        'branch_pv' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function branchUser()
    {
        return $this->belongsTo(User::class, 'branch_user_id');
    }

    public function scopePeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeMinLevel($query, $level)
    {
        return $query->where('branch_rank_level', '>=', $level);
    }

    public function getBranchRankNameAttribute()
    {
        $rank = Rank::where('level', $this->branch_rank_level)->first();
        return $rank ? $rank->name : 'Level ' . $this->branch_rank_level;
    }

    public function getFormattedPVAttribute()
    {
        return number_format($this->branch_pv) . ' PV';
    }
}