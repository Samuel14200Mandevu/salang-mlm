<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'sponsor_id' => $this->sponsor_id,
            'is_active' => (bool) $this->is_active,
            'rank' => $this->rank,
            'rank_id' => $this->rank_id,
            'rank_level' => $this->rank_level,
            'pv_balance' => (float) ($this->pv_balance ?? 0),
            'monthly_pv' => (float) ($this->monthly_pv ?? 0),
            'team_pv' => (float) ($this->team_pv ?? 0),
            'kyc_status' => $this->kyc_status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
