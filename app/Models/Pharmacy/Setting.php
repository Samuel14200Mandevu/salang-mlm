<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'pharmacy_settings';

    protected $fillable = ['cash_float'];

    protected $casts = [
        'cash_float' => 'decimal:2',
    ];

    public static function current(): self
    {
        $row = static::query()->first();
        if ($row) {
            return $row;
        }

        return static::query()->create(['cash_float' => 0]);
    }
}
