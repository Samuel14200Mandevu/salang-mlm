<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Insurer extends Model
{
    protected $table = 'pharmacy_insurers';

    protected $fillable = [
        'name', 'coverage_rate', 'contact', 'notes', 'is_active',
    ];

    protected $casts = [
        'coverage_rate' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'insurer_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'insurer_id');
    }

    public function billedAmount(): float
    {
        return round((float) $this->sales()->sum('insurance_amount'), 2);
    }
}
