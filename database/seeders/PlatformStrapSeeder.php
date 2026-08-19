<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatformStrapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Subscription Plans
        $basic = \App\Models\SubscriptionPlan::updateOrCreate(['slug' => 'basic'], [
            'name' => 'Plan Básico',
            'price' => 97.00,
            'description' => 'Ideal para negocios pequeños',
        ]);

        $pro = \App\Models\SubscriptionPlan::updateOrCreate(['slug' => 'pro'], [
            'name' => 'Plan Pro',
            'price' => 147.00,
            'description' => 'Control total e ilimitado',
        ]);

        // 2. Default Commission Rules
        \App\Models\CommissionRule::updateOrCreate(['name' => 'Referido Directo (20%)'], [
            'type' => 'percentage',
            'amount' => 20.00,
            'is_recurring' => true,
            'trigger_event' => 'new_subscription',
        ]);

        \App\Models\CommissionRule::updateOrCreate(['name' => 'Bono de Registro'], [
            'type' => 'fixed',
            'amount' => 10.00,
            'is_recurring' => false,
            'trigger_event' => 'registration',
        ]);
    }
}
