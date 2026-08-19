<?php

namespace App\Services;

use App\Enums\ClientType;
use App\Models\Client;
use App\Models\PriceListItem;
use App\Models\Product;

/**
 * Punto unico para decidir "cuanto le cobro este producto a este cliente"
 * (Fase 10). Pensado para que Ventas (Fase 11) lo use directo en vez de
 * reimplementar la logica de resolucion de precio en cada lugar.
 *
 * Orden de resolucion:
 *   1. Si el cliente tiene una lista de precios asignada y el producto tiene
 *      un precio especial ahi -> ese precio.
 *   2. Si no, segun el tipo de cliente: wholesale -> wholesale_price,
 *      retail -> retail_price (si el que corresponde es null, se usa el otro).
 *   3. Sin cliente -> retail_price (o wholesale_price si el retail es null).
 */
class PriceResolverService
{
    public function priceFor(Product $product, ?Client $client = null): float
    {
        if ($client?->price_list_id) {
            $override = PriceListItem::where('price_list_id', $client->price_list_id)
                ->where('product_id', $product->id)
                ->value('price');

            if ($override !== null) {
                return (float) $override;
            }
        }

        if ($client?->client_type === ClientType::WHOLESALE) {
            return (float) ($product->wholesale_price ?? $product->retail_price ?? 0);
        }

        return (float) ($product->retail_price ?? $product->wholesale_price ?? 0);
    }
}
