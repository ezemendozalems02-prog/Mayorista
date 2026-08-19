<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'status' => $this->status?->value,
            'client' => $this->whenLoaded('client', fn () => [
                'id' => $this->client->id,
                'display_name' => $this->client->display_name,
            ]),
            'subtotal' => $this->subtotal,
            'discount' => $this->discount,
            'total' => $this->total,
            'notes' => $this->notes,
            'sale_id' => $this->sale_id,
            'items_count' => $this->whenCounted('items'),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'fulfilled_at' => $this->fulfilled_at,
            'created_at' => $this->created_at,
        ];
    }
}
