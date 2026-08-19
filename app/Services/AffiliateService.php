<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Organization;
use App\Models\Referral;
use Illuminate\Support\Facades\Cookie;

class AffiliateService
{
    /**
     * Track a new registration and link it to an affiliate if a cookie exists.
     */
    public function registerReferral(Organization $organization)
    {
        // Try to get from cookie first, then from current request if cookie isn't set yet during the same process
        $refCode = Cookie::get('vortex_ref') ?? request()->query('ref');

        if (!$refCode) {
            return;
        }

        $affiliate = Affiliate::where('affiliate_code', $refCode)->first();

        if (!$affiliate || !$affiliate->is_active) {
            return;
        }

        return Referral::create([
            'affiliate_id'    => $affiliate->id,
            'organization_id' => $organization->id,
            'status'          => 'registered',
        ]);
    }

    /**
     * Process commissions for a specific event (e.g., new_subscription).
     */
    public function processCommissions(Organization $organization, string $event, float $amount)
    {
        $referral = Referral::where('organization_id', $organization->id)->first();

        if (!$referral) {
            return;
        }

        $rules = CommissionRule::where('trigger_event', $event)->get();

        foreach ($rules as $rule) {
            $commissionAmount = 0;

            if ($rule->type === 'percentage') {
                $commissionAmount = $amount * ($rule->amount / 100);
            } elseif ($rule->type === 'fixed') {
                $commissionAmount = $rule->amount;
            }

            if ($commissionAmount > 0) {
                Commission::create([
                    'affiliate_id' => $referral->affiliate_id,
                    'referral_id'  => $referral->id,
                    'amount'       => $commissionAmount,
                    'status'       => 'pending',
                ]);

                // Update affiliate balance (optional, could be done on payment)
                $referral->affiliate->increment('balance', $commissionAmount);
            }
        }
    }
}
