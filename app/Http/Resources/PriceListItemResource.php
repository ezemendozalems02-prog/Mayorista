<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceListItemResource extends JsonResource
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
                'retail_price' => $this->product->retail_price,
                'wholesale_price' => $this->product->wholesale_price,
            ]),
            'price' => $this->price,
        ];
    }
}
