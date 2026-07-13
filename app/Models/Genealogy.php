<?php
// app/Models/Genealogy.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genealogy extends Model
{
    use HasFactory;

    protected $table = 'genealogy';

    protected $fillable = [
        'user_id',
        'sponsor_id',
        'parent_id',
        'level',
        'position',
        'left_count',
        'right_count',
        'total_children',
    ];

    protected $casts = [
        'level' => 'integer',
        'left_count' => 'integer',
        'right_count' => 'integer',
        'total_children' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sponsor()
    {
        return $this->belongsTo(User::class, 'sponsor_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function getPositionLabelAttribute()
    {
        if ($this->position === 'left') {
            return 'Left';
        } elseif ($this->position === 'right') {
            return 'Right';
        }
        return 'Root';
    }

    public function getLevelLabelAttribute()
    {
        $labels = [
            0 => 'Root',
            1 => 'Level 1',
            2 => 'Level 2',
            3 => 'Level 3',
            4 => 'Level 4',
            5 => 'Level 5',
            6 => 'Level 6',
            7 => 'Level 7',
            8 => 'Level 8',
            9 => 'Level 9',
        ];
        return $labels[$this->level] ?? 'Level ' . $this->level;
    }
}