<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Organization;
use App\Enums\InventoryStatus;
use Illuminate\Database\Seeder;

class AccessorySeeder extends Seeder
{
    public function run(): void
    {
        $org = Organization::first();
        if (!$org) return;

        $accessories = [
            ['brand' => 'Apple', 'model' => 'Cargador 20W USB-C', 'price' => 25],
            ['brand' => 'Apple', 'model' => 'EarPods Lightning', 'price' => 19],
            ['brand' => 'Apple', 'model' => 'MagSafe Case iPhone 15', 'price' => 49],
            ['brand' => 'Samsung', 'model' => 'Galaxy Buds 2 Pro', 'price' => 120],
            ['brand' => 'Generic', 'model' => 'Funda Silicona iPhone 13', 'price' => 10],
            ['brand' => 'Generic', 'model' => 'Protector de Pantalla Cerámico', 'price' => 5],
            ['brand' => 'Generic', 'model' => 'Cable USB-C a Lightning 2m', 'price' => 15],
        ];

        foreach ($accessories as $acc) {
            InventoryItem::create([
                'organization_id' => $org->id,
                'category' => 'accessory',
                'brand' => $acc['brand'],
                'model' => $acc['model'],
                'purchase_price' => $acc['price'] * 0.6,
                'sale_price' => $acc['price'],
                'currency' => 'USD',
                'status' => InventoryStatus::IN_STOCK,
                'stock_type' => 'available',
            ]);
        }
    }
}
