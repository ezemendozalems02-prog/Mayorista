<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AffiliateTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear un Afiliado de prueba
        $affiliate = \App\Models\Affiliate::updateOrCreate(['email' => 'partner@test.com'], [
            'name' => 'Enzo Master Partner',
            'affiliate_code' => 'VORTEX2026',
            'type' => 'partner',
            'balance' => 250.00,
            'is_active' => true,
        ]);

        // 2. Vincular una organización existente como referido si existe
        $org = \App\Models\Organization::first();
        if ($org) {
            $referral = \App\Models\Referral::updateOrCreate([
                'affiliate_id' => $affiliate->id,
                'organization_id' => $org->id,
            ], [
                'status' => 'subscribed',
            ]);

            // 3. Crear una comisión de prueba
            \App\Models\Commission::create([
                'affiliate_id' => $affiliate->id,
                'referral_id' => $referral->id,
                'amount' => 50.00,
                'status' => 'pending',
            ]);
        }
    }
}
