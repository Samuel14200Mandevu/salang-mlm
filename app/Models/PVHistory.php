<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PVHistory extends Model
{
    use HasFactory;

    protected $table = 'pv_history';

    protected $fillable = [
        'user_id', 'amount', 'date', 'period',
        'type', 'notes', 'created_by',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:1',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function getTypes()
    {
        return [
            'personal' => 'PV Personnel',
            'team'     => 'PV Équipe',
            'monthly'  => 'PV Mensuel',
        ];
    }

    public function getTypeLabelAttribute()
    {
        return self::getTypes()[$this->type] ?? ucfirst($this->type);
    }
}