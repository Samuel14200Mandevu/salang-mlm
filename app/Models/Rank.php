<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'min_pv',
        'min_bv',
        'min_sponsors',
        'min_team',
        'bonus_percentage',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'min_pv' => 'integer',
        'min_bv' => 'integer',
        'min_sponsors' => 'integer',
        'min_team' => 'integer',
        'bonus_percentage' => 'decimal:2',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function rankHistory()
    {
        return $this->hasMany(RankHistory::class);
    }

    public function getNextRank()
    {
        return Rank::where('min_pv', '>', $this->min_pv)
            ->orderBy('min_pv', 'asc')
            ->first();
    }

    public function getPreviousRank()
    {
        return Rank::where('min_pv', '<', $this->min_pv)
            ->orderBy('min_pv', 'desc')
            ->first();
    }
}
