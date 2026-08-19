<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'display_name' => $this->display_name,
            'client_type' => $this->client_type?->value,
            'business_name' => $this->business_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'document_number' => $this->document_number,
            'cuit' => $this->cuit,
            'address' => $this->address,
            'credit_limit' => $this->credit_limit,
            'discount_percentage' => $this->discount_percentage,
            'current_balance' => $this->current_balance,
            'price_list_id' => $this->price_list_id,
            'price_list' => $this->whenLoaded('priceList', fn () => $this->priceList ? [
                'id' => $this->priceList->id,
                'name' => $this->priceList->name,
            ] : null),
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
