<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::all();
        return view('super-admin.plans.index', compact('plans'));
    }

    public function edit(SubscriptionPlan $subscriptionPlan)
    {
        return view('super-admin.plans.edit', ['plan' => $subscriptionPlan]);
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'promo_price' => 'nullable|numeric|min:0',
            'promo_ends_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $subscriptionPlan->update($validated);

        return redirect()->route('super-admin.plans.index')->with('success', 'Plan actualizado correctamente.');
    }
}
