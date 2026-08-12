<?php
// app/Models/PVHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PVHistory extends Model
{
    use HasFactory;

    protected $table = 'pv_history';

    protected $fillable = [
        'user_id',
        'amount',
        'date',
        'period',
        'type',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:1',
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec l'admin qui a créé l'entrée
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Types de PV disponibles
     */
    public static function getTypes()
    {
        return [
            'personal' => 'PV Personnel',
            'team' => 'PV Équipe',
            'monthly' => 'PV Mensuel',
        ];
    }

    /**
     * Labels des types
     */
    public function getTypeLabelAttribute()
    {
        return self::getTypes()[$this->type] ?? ucfirst($this->type);
    }
}