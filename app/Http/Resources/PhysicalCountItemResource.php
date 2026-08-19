<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PhysicalCountItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'internal_code' => $this->product->internal_code,
                'barcode' => $this->product->barcode,
            ],
            'expected_quantity' => $this->expected_quantity,
            'counted_quantity' => $this->counted_quantity,
            'difference' => $this->difference,
            'counted_at' => $this->counted_at,
        ];
    }
}
