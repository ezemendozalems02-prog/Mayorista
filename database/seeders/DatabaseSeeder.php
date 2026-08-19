<?php

namespace Database\Seeders;

use App\Enums\InventoryStatus;
use App\Enums\RepairPriority;
use App\Enums\RepairStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\InventoryItem;
use App\Models\Organization;
use App\Models\Repair;
use App\Models\Sale;
use App\Models\Technician;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Seed Subscription Plans
        $this->call(SubscriptionPlanSeeder::class);
        $proPlan = \App\Models\SubscriptionPlan::where('slug', 'pro')->first();

        // 1. Create Demo Organization
        $org = Organization::create([
            'name' => 'Apple Demo Store',
            'slug' => 'apple-demo-store',
            'phone' => '+541122334455',
            'email' => 'demo@applestore.com',
            'country' => 'Argentina',
            'currency' => 'USD',
            'subscription_plan_id' => $proPlan->id,
            'subscription_status' => 'active',
            'plan' => 'pro', // Mantener por compatibilidad legacy
        ]);

        // 2. Create Users
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password'),
            'role' => UserRole::SUPER_ADMIN,
        ]);

        User::create([
            'organization_id' => $org->id,
            'name' => 'John Owner',
            'email' => 'owner@demo.com',
            'password' => Hash::make('password'),
            'role' => UserRole::OWNER,
        ]);

        User::create([
            'organization_id' => $org->id,
            'name' => 'Jane Seller',
            'email' => 'seller@demo.com',
            'password' => Hash::make('password'),
            'role' => UserRole::SELLER,
        ]);

        User::create([
            'organization_id' => $org->id,
            'name' => 'Jack Tech',
            'email' => 'tech@demo.com',
            'password' => Hash::make('password'),
            'role' => UserRole::TECHNICIAN,
        ]);

        // 3. Create Clients
        $client1 = Client::create([
            'organization_id' => $org->id,
            'full_name' => 'Enzo Amarilla',
            'phone' => '123456789',
            'email' => 'enzo@example.com',
            'document_number' => '12345678',
        ]);

        $client2 = Client::create([
            'organization_id' => $org->id,
            'full_name' => 'Maria Perez',
            'phone' => '987654321',
            'email' => 'maria@example.com',
        ]);

        // 4. Create Inventory (Available)
        foreach (['iPhone 15 Pro Max', 'iPhone 15', 'iPhone 14 Pro', 'iPhone 13', 'Apple Watch Ultra 2'] as $model) {
            InventoryItem::create([
                'organization_id' => $org->id,
                'category' => 'iphone',
                'brand' => 'Apple',
                'model' => $model,
                'storage' => '256GB',
                'color' => 'Varios',
                'imei' => mt_rand(111111111111111, 999999999999999),
                'battery_health' => mt_rand(88, 100),
                'purchase_price' => mt_rand(500, 1000),
                'sale_price' => mt_rand(1100, 1600),
                'currency' => 'USD',
                'status' => InventoryStatus::IN_STOCK,
                'stock_type' => 'available',
            ]);
        }

        // 5. Create Sold Items & Sales (for Dashboard stats)
        for ($i = 1; $i <= 10; $i++) {
            $cost = mt_rand(400, 800);
            $price = $cost + mt_rand(150, 400);

            $sale = Sale::create([
                'organization_id' => $org->id,
                'client_id' => $client1->id,
                'seller_id' => 3, // John Owner
                'sale_number' => 'S-' . strtoupper(Str::random(8)),
                'status' => 'completed',
                'currency' => 'USD',
                'subtotal' => $price,
                'total' => $price,
                'cost_total' => $cost,
                'profit_total' => $price - $cost,
                'sold_at' => now()->subDays(mt_rand(0, 30)),
            ]);

            $invItem = InventoryItem::create([
                'organization_id' => $org->id,
                'category' => 'iphone',
                'brand' => 'Apple',
                'model' => 'iPhone Usado ' . $i,
                'imei' => 'SOLD' . mt_rand(1000, 9999),
                'status' => InventoryStatus::SOLD,
                'purchase_price' => $cost,
                'sale_price' => $price,
                'currency' => 'USD',
                'stock_type' => 'available',
            ]);

            \App\Models\SaleItem::create([
                'sale_id' => $sale->id,
                'inventory_item_id' => $invItem->id,
                'item_name' => $invItem->model,
                'unit_cost' => $cost,
                'unit_price' => $price,
                'quantity' => 1,
                'line_total' => $price,
            ]);
        }

        // 6. Technicians
        $tech1 = Technician::create([
            'organization_id' => $org->id,
            'full_name' => 'Marcos Servicio',
            'phone' => '11334455',
            'specialties' => 'Microelectrónica, Cambios de Pantalla',
            'is_active' => true,
        ]);

        $tech2 = Technician::create([
            'organization_id' => $org->id,
            'full_name' => 'Elena Apple',
            'phone' => '11998877',
            'specialties' => 'Software, iCloud, Desbloqueos',
            'is_active' => true,
        ]);

        // 7. Repairs
        foreach (RepairStatus::cases() as $status) {
            Repair::create([
                'organization_id' => $org->id,
                'client_id' => mt_rand(1, 2) == 1 ? $client1->id : $client2->id,
                'technician_id' => mt_rand(1, 2) == 1 ? $tech1->id : $tech2->id,
                'repair_number' => 'REP-' . strtoupper(Str::random(5)),
                'device_brand' => 'Apple',
                'device_model' => 'iPhone 12',
                'status' => $status,
                'priority' => RepairPriority::MEDIUM,
                'reported_issue' => 'Diagnóstico general',
                'estimated_cost' => 150,
                'received_at' => now()->subDays(mt_rand(1, 10)),
            ]);
        }
    }
}
