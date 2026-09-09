<?php

// app/Models/UserPvBalance.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPvBalance extends Model
{
    protected $fillable = [
        'user_id', 'total_pv', 'allocated_pv', 'pending_pv', 'available_pv',
        'total_bv', 'allocated_bv', 'pending_bv', 'available_bv', 'last_updated_at'
    ];

    protected $casts = [
        'last_updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Méthodes utilitaires
    public function addPv(int $amount, int $bv = 0): void
    {
        $this->total_pv += $amount;
        $this->pending_pv += $amount;
        $this->available_pv += $amount;
        
        if ($bv > 0) {
            $this->total_bv += $bv;
            $this->pending_bv += $bv;
            $this->available_bv += $bv;
        }
        
        $this->last_updated_at = now();
        $this->save();
    }

    public function allocatePv(int $amount, int $bv = 0): bool
    {
        if ($this->available_pv < $amount) {
            return false;
        }

        $this->available_pv -= $amount;
        $this->allocated_pv += $amount;
        
        if ($bv > 0) {
            $this->available_bv -= $bv;
            $this->allocated_bv += $bv;
        }
        
        $this->last_updated_at = now();
        $this->save();
        
        return true;
    }
}