<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Affiliate;
use App\Models\Commission;

class AffiliateManagementController extends Controller
{
    public function index()
    {
        $affiliates = Affiliate::withCount(['referrals', 'commissions'])
            ->latest()
            ->paginate(20);

        return view('super-admin.affiliates.index', compact('affiliates'));
    }

    public function commissions()
    {
        $commissions = Commission::with(['affiliate', 'referral.organization'])
            ->latest()
            ->paginate(20);

        return view('super-admin.commissions.index', compact('commissions'));
    }

    public function approveCommission(Commission $commission)
    {
        if ($commission->status !== 'pending') {
            return back()->with('error', 'Esta comisión ya ha sido procesada.');
        }

        $commission->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return back()->with('success', 'Comisión aprobada y marcada como pagada.');
    }
}
