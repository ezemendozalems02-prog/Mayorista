<?php

use App\Models\SubscriptionPlan;
use App\Models\PlanFeature;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $pro = SubscriptionPlan::where('slug', 'pro')->first();

        if ($pro) {
            PlanFeature::firstOrCreate(
                ['subscription_plan_id' => $pro->id, 'feature_name' => 'arca_billing'],
                ['feature_value' => 'true']
            );
        }
    }

    public function down(): void
    {
        $pro = SubscriptionPlan::where('slug', 'pro')->first();

        if ($pro) {
            PlanFeature::where('subscription_plan_id', $pro->id)
                ->where('feature_name', 'arca_billing')
                ->delete();
        }
    }
};
