<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status?->value,
            'supplier' => $this->whenLoaded('supplier', fn () => [
                'id' => $this->supplier->id,
                'business_name' => $this->supplier->business_name,
            ]),
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'total' => $this->total,
            'notes' => $this->notes,
            'items_count' => $this->whenCounted('items'),
            'items' => PurchaseItemResource::collection($this->whenLoaded('items')),
            'received_at' => $this->received_at,
            'created_at' => $this->created_at,
        ];
    }
}
