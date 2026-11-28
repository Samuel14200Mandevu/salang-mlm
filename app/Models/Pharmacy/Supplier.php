<?php

namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    protected $table = 'pharmacy_suppliers';

    protected $fillable = [
        'name', 'contact', 'phone', 'address', 'notes', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class, 'supplier_id');
    }

    public function debtAmount(): float
    {
        return round((float) $this->purchases()
            ->selectRaw('COALESCE(SUM(total_amount - paid_amount), 0) as debt')
            ->value('debt'), 2);
    }
}
