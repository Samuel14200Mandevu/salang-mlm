<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Genealogy extends Model
{
    use HasFactory;

    protected $table = 'genealogy'; // Spécifier le nom de la table

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
}
