<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    protected $table = 'pharmacy_patients';

    protected $fillable = [
        'name', 'phone', 'insurer_id', 'member_number', 'notes', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function insurer(): BelongsTo
    {
        return $this->belongsTo(Insurer::class, 'insurer_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class, 'patient_id');
    }

    public function totalPurchases(): float
    {
        return round((float) $this->sales()->sum('total_amount'), 2);
    }
}
