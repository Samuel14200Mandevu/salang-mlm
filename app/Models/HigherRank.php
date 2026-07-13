<?php
// app/Models/HigherRank.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HigherRank extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'level',
        'min_branches_rank_9',
        'min_branches_diamond',
        'global_bonus_percentage',
        'is_active',
    ];

    protected $casts = [
        'min_branches_rank_9' => 'integer',
        'min_branches_diamond' => 'integer',
        'global_bonus_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_higher_ranks')
                    ->withPivot('achieved_at', 'period')
                    ->withTimestamps();
    }

    public function isEligible(User $user, string $period): bool
    {
        if ($this->slug === 'actionnaire') {
            $diamondBranches = QualifiedBranch::where('user_id', $user->id)
                ->where('period', $period)
                ->where('branch_rank_level', '>=', 9)
                ->count();
            
            return $diamondBranches >= ($this->min_branches_diamond ?? 4);
        }

        $branches = QualifiedBranch::where('user_id', $user->id)
            ->where('period', $period)
            ->where('branch_rank_level', 9)
            ->count();
        
        return $branches >= $this->min_branches_rank_9;
    }

    public function getLevelNameAttribute()
    {
        $levels = [
            1 => 'Rubis',
            2 => 'Saphir',
            3 => 'Diamant 1',
            4 => 'Diamant 2',
            5 => 'Diamant 3',
            6 => 'Diamant 4',
            7 => 'Diamant 5',
            8 => 'Actionnaire',
        ];
        return $levels[$this->level] ?? $this->name;
    }

    public function getIconAttribute()
    {
        $icons = [
            1 => 'ruby',
            2 => 'sapphire',
            3 => 'diamond-1',
            4 => 'diamond-2',
            5 => 'diamond-3',
            6 => 'diamond-4',
            7 => 'diamond-5',
            8 => 'shareholder',
        ];
        return $icons[$this->level] ?? 'star';
    }

    public function getFormattedBonusAttribute()
    {
        return number_format($this->global_bonus_percentage, 2) . '%';
    }
}