<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'balance' => (float) ($this->balance ?? 0),
            'pending_balance' => (float) ($this->pending_balance ?? 0),
            'total_deposited' => (float) ($this->total_deposited ?? 0),
            'total_withdrawn' => (float) ($this->total_withdrawn ?? 0),
            'currency' => $this->currency ?? 'USD',
            'is_active' => (bool) ($this->is_active ?? true),
        ];
    }
}
