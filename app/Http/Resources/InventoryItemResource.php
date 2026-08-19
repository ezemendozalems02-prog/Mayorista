<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category' => $this->category,
            'brand' => $this->brand,
            'model' => $this->model,
            'storage' => $this->storage,
            'color' => $this->color,
            'imei' => $this->imei,
            'serial_number' => $this->serial_number,
            'battery_health' => $this->battery_health,
            'cosmetic_condition' => $this->cosmetic_condition,
            'purchase_price' => $this->purchase_price,
            'sale_price' => $this->sale_price,
            'currency' => $this->currency,
            'status' => $this->status,
            'stock_type' => $this->stock_type,
            'notes' => $this->notes,
            'client' => new ClientResource($this->whenLoaded('client')),
            'created_at' => $this->created_at,
        ];
    }
}
