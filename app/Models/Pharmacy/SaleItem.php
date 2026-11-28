<?php

namespace App\Models\Pharmacy;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $table = 'pharmacy_sale_items';

    protected $fillable = [
        'sale_id', 'product_id', 'quantity', 'unit_price', 'unit_cost',
        'line_total', 'insurance_share', 'patient_share', 'cost_total', 'profit',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'line_total' => 'decimal:2',
        'insurance_share' => 'decimal:2',
        'patient_share' => 'decimal:2',
        'cost_total' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
