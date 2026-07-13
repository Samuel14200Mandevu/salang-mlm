<?php
// app/Models/Rank.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'level',
        'min_pv',
        'min_bv',
        'monthly_pv_required',
        'team_pv_required',
        'min_sponsors',
        'min_team',
        'bonus_percentage',
        'pv_payment_required',
        'description',
        'conditions',
        'commission_types',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_pv' => 'integer',
        'min_bv' => 'integer',
        'monthly_pv_required' => 'integer',
        'team_pv_required' => 'integer',
        'min_sponsors' => 'integer',
        'min_team' => 'integer',
        'bonus_percentage' => 'decimal:2',
        'pv_payment_required' => 'integer',
        'level' => 'integer',
        'conditions' => 'array',
        'commission_types' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function rankHistory()
    {
        return $this->hasMany(RankHistory::class);
    }

    public function monthlyRanks()
    {
        return $this->hasMany(UserMonthlyRank::class);
    }

    public function getNextRank()
    {
        return Rank::where('level', '>', $this->level)
            ->where('is_active', true)
            ->orderBy('level', 'asc')
            ->first();
    }

    public function getPreviousRank()
    {
        return Rank::where('level', '<', $this->level)
            ->where('is_active', true)
            ->orderBy('level', 'desc')
            ->first();
    }

    public function getLevelNameAttribute()
    {
        $levels = [
            1 => 'Level 1',
            2 => 'Level 2',
            3 => 'Level 3',
            4 => 'Level 4',
            5 => 'Level 5',
            6 => 'Level 6',
            7 => 'Level 7',
            8 => 'Level 8',
            9 => 'Level 9',
            10 => 'Level 10',
        ];
        return $levels[$this->level] ?? $this->name;
    }

    public function getColorAttribute()
    {
        $colors = [
            1 => 'gray',
            2 => 'gray',
            3 => 'blue',
            4 => 'blue',
            5 => 'green',
            6 => 'green',
            7 => 'purple',
            8 => 'purple',
            9 => 'gold',
            10 => 'gold',
        ];
        return $colors[$this->level] ?? 'gray';
    }

    public function getIconAttribute()
    {
        $icons = [
            1 => 'level-1',
            2 => 'level-2',
            3 => 'level-3',
            4 => 'level-4',
            5 => 'level-5',
            6 => 'level-6',
            7 => 'level-7',
            8 => 'level-8',
            9 => 'level-9',
            10 => 'level-10',
        ];
        return $icons[$this->level] ?? 'level';
    }

    public function getFormattedBonusAttribute()
    {
        return number_format($this->bonus_percentage, 2) . '%';
    }

    public function isEligible(User $user): bool
    {
        if ($user->pv_balance < $this->min_pv) {
            return false;
        }

        if ($this->level >= 4 && $this->conditions) {
            return $this->checkConditions($user);
        }

        return true;
    }

    public function checkConditions(User $user): bool
    {
        $conditions = $this->conditions;
        
        if (empty($conditions) || !is_array($conditions)) {
            return true;
        }

        $directChildren = $user->filleuls()->with('rank')->get();
        
        foreach ($conditions as $condition) {
            if ($this->checkSingleCondition($condition, $directChildren)) {
                return true;
            }
        }

        return false;
    }

    private function checkSingleCondition(array $condition, $directChildren): bool
    {
        if (isset($condition['branch_level'])) {
            $count = $directChildren->filter(function ($child) use ($condition) {
                $rankLevel = $child->rank->level ?? 0;
                return $rankLevel >= $condition['branch_level'] 
                    && $child->pv_balance >= ($condition['min_branch_pv'] ?? 0);
            })->count();

            return $count >= ($condition['min_branches'] ?? 1);
        }

        if (isset($condition['branches']) && is_array($condition['branches'])) {
            foreach ($condition['branches'] as $branchCondition) {
                $count = $directChildren->filter(function ($child) use ($branchCondition) {
                    $rankLevel = $child->rank->level ?? 0;
                    return $rankLevel >= $branchCondition['branch_level'] 
                        && $child->pv_balance >= ($branchCondition['min_branch_pv'] ?? 0);
                })->count();

                if ($count < ($branchCondition['min_branches'] ?? 1)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }

    public function getCommissionTypesListAttribute()
    {
        if ($this->commission_types) {
            return implode(', ', $this->commission_types);
        }
        return 'Standard';
    }

    public function getConditionsListAttribute()
    {
        if ($this->conditions) {
            $list = [];
            foreach ($this->conditions as $condition) {
                $list[] = $condition['label'] ?? 'Condition';
            }
            return implode(' | ', $list);
        }
        return 'No specific conditions';
    }
}