<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'business_name' => $this->business_name,
            'trade_name' => $this->trade_name,
            'cuit' => $this->cuit,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'address' => $this->address,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'products_count' => $this->whenCounted('products'),
            'pivot' => $this->when($this->pivot, function () {
                return [
                    'supplier_sku' => $this->pivot->supplier_sku,
                    'cost' => $this->pivot->cost,
                    'is_primary' => $this->pivot->is_primary,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
