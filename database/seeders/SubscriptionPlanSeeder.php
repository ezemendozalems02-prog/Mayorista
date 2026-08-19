<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basic = \App\Models\SubscriptionPlan::updateOrCreate(['slug' => 'basic'], [
            'name' => 'Plan Básico',
            'description' => 'Ideal para negocios individuales o equipos pequeños que se están iniciando.',
            'price' => 97,
            'promo_price' => 77,
            'promo_ends_at' => '2026-05-01 00:00:00',
            'currency' => 'USD',
            'billing_interval' => 30,
            'is_active' => true,
            'features_json' => [
                'Hasta 500 equipos en stock',
                'Módulo de ventas',
                'Módulo de clientes',
                'Inventario básico',
                'Reportes básicos',
                'Soporte por email'
            ]
        ]);

        $basic->features()->delete();
        $basic->features()->createMany([
            ['feature_name' => 'inventory_basic', 'feature_value' => 'true'],
            ['feature_name' => 'sales', 'feature_value' => 'true'],
            ['feature_name' => 'clients', 'feature_value' => 'true'],
            ['feature_name' => 'reports_basic', 'feature_value' => 'true'],
        ]);

        $pro = \App\Models\SubscriptionPlan::updateOrCreate(['slug' => 'pro'], [
            'name' => 'Plan Pro',
            'description' => 'Nuestra solución completa para maximizar la productividad y eficiencia.',
            'price' => 147,
            'promo_price' => 117,
            'promo_ends_at' => '2026-05-01 00:00:00',
            'currency' => 'USD',
            'billing_interval' => 30,
            'is_active' => true,
            'features_json' => [
                'Stock ilimitado',
                'Servicio técnico completo',
                'Gestión de técnicos',
                'Canjes de equipos',
                'Reportes avanzados',
                'Facturación ARCA (AFIP)',
                'Soporte prioritario'
            ]
        ]);

        $pro->features()->delete();
        $pro->features()->createMany([
            ['feature_name' => 'inventory_unlimited', 'feature_value' => 'true'],
            ['feature_name' => 'sales', 'feature_value' => 'true'],
            ['feature_name' => 'clients', 'feature_value' => 'true'],
            ['feature_name' => 'reports_basic', 'feature_value' => 'true'],
            ['feature_name' => 'reports_advanced', 'feature_value' => 'true'],
            ['feature_name' => 'repairs', 'feature_value' => 'true'],
            ['feature_name' => 'technicians', 'feature_value' => 'true'],
            ['feature_name' => 'trade_ins', 'feature_value' => 'true'],
            ['feature_name' => 'multi_branch', 'feature_value' => 'true'],
        ]);
    }
}
