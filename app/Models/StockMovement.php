<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    public const TYPE_INITIAL = 'initial';

    public const TYPE_ENTRY = 'entry';

    public const TYPE_EXIT = 'exit';

    public const TYPE_SALE = 'sale';

    public const TYPE_SALE_CANCEL = 'sale_cancel';

    public const TYPE_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity_delta',
        'quantity_before',
        'quantity_after',
        'reason',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'quantity_delta' => 'integer',
        'quantity_before' => 'integer',
        'quantity_after' => 'integer',
    ];

    public static function typeLabels(): array
    {
        return [
            self::TYPE_INITIAL => 'Stock initial',
            self::TYPE_ENTRY => 'Entrée',
            self::TYPE_EXIT => 'Sortie',
            self::TYPE_SALE => 'Vente',
            self::TYPE_SALE_CANCEL => 'Annulation vente',
            self::TYPE_ADJUSTMENT => 'Ajustement',
        ];
    }

    public function getTypeLabelAttribute(): string
    {
        return self::typeLabels()[$this->type] ?? ucfirst((string) $this->type);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }
}
