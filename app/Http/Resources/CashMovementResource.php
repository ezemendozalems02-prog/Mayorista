<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'cash_session_id' => $this->cash_session_id,
            'type' => $this->type?->value,
            'amount' => $this->amount,
            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,
            'notes' => $this->notes,
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
            ] : null),
            'created_at' => $this->created_at,
        ];
    }
}
