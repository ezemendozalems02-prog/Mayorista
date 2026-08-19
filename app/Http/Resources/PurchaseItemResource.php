<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', fn () => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'internal_code' => $this->product->internal_code,
            ]),
            'quantity' => $this->quantity,
            'unit_cost' => $this->unit_cost,
            'line_total' => $this->line_total,
        ];
    }
}
