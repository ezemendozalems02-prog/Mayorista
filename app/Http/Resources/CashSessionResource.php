<?php

namespace App\Http\Resources;

use App\Services\CashService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashSessionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status?->value,
            'opening_amount' => $this->opening_amount,
            'closing_amount' => $this->closing_amount,
            'expected_amount' => $this->expected_amount,
            'difference' => $this->difference,
            'current_balance' => $this->isOpen() ? app(CashService::class)->balanceFor($this->resource) : null,
            'opened_by' => $this->whenLoaded('openedBy', fn () => $this->openedBy ? [
                'id' => $this->openedBy->id,
                'name' => $this->openedBy->name,
            ] : null),
            'closed_by' => $this->whenLoaded('closedBy', fn () => $this->closedBy ? [
                'id' => $this->closedBy->id,
                'name' => $this->closedBy->name,
            ] : null),
            'notes' => $this->notes,
            'opened_at' => $this->opened_at,
            'closed_at' => $this->closed_at,
            'movements' => CashMovementResource::collection($this->whenLoaded('movements')),
        ];
    }
}
