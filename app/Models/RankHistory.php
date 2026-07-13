<?php
// app/Models/RankHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RankHistory extends Model
{
    use HasFactory;

    protected $table = 'rank_history';

    protected $fillable = [
        'user_id',
        'old_rank_id',
        'new_rank_id',
        'old_rank_name',
        'new_rank_name',
        'pv_at_time',
        'bv_at_time',
        'notes',
    ];

    protected $casts = [
        'pv_at_time' => 'integer',
        'bv_at_time' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function oldRank()
    {
        return $this->belongsTo(Rank::class, 'old_rank_id');
    }

    public function newRank()
    {
        return $this->belongsTo(Rank::class, 'new_rank_id');
    }

    public function getTypeAttribute()
    {
        $oldLevel = $this->oldRank ? $this->oldRank->level : 0;
        $newLevel = $this->newRank ? $this->newRank->level : 0;
        
        if ($newLevel > $oldLevel) {
            return 'Promotion';
        } elseif ($newLevel < $oldLevel) {
            return 'Demotion';
        }
        return 'Update';
    }

    public function getColorAttribute()
    {
        $oldLevel = $this->oldRank ? $this->oldRank->level : 0;
        $newLevel = $this->newRank ? $this->newRank->level : 0;
        
        if ($newLevel > $oldLevel) {
            return 'green';
        } elseif ($newLevel < $oldLevel) {
            return 'red';
        }
        return 'yellow';
    }

    public function getTypeIconAttribute()
    {
        $types = [
            'Promotion' => 'arrow-up',
            'Demotion' => 'arrow-down',
            'Update' => 'refresh',
        ];
        return $types[$this->type] ?? 'circle';
    }

    public function getFormattedDateAttribute()
    {
        return $this->created_at ? $this->created_at->format('d M Y H:i') : '-';
    }
}