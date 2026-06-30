<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RankHistory extends Model
{
    use HasFactory;

    /**
     * ⚠️ IMPORTANT : Spécifier le nom correct de la table
     */
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
}