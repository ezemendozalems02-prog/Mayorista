<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'internal_code' => $this->internal_code,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'description' => $this->description,
            'cost' => $this->cost,
            'retail_price' => $this->retail_price,
            'wholesale_price' => $this->wholesale_price,
            'min_stock' => $this->min_stock,
            'image_path' => $this->image_path,
            'status' => $this->status?->value,
            'category_id' => $this->category_id,
            'brand_id' => $this->brand_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'suppliers' => SupplierResource::collection($this->whenLoaded('suppliers')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
