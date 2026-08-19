<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SubscriptionController extends Controller
{
    public function index()
    {
        if (config('platform.mode', 'saas') !== 'saas') {
            return redirect()->route('dashboard');
        }

        $organization = Auth::user()->organization;
        $trialDaysLeft = $organization->trial_ends_at ? now()->diffInDays($organization->trial_ends_at, false) : 0;

        // Fetch Blue Dollar rate for conversion
        $blueRate = 1200; // Fallback
        try {
            $response = \Illuminate\Support\Facades\Http::get('https://dolarapi.com/v1/dolares/blue');
            if ($response->successful()) {
                $blueRate = $response->json()['venta'];
            }
        } catch (\Exception $e) {
            // Use fallback
        }

        $plans = SubscriptionPlan::where('is_active', true)->get()->map(function($plan) use ($blueRate) {
            return [
                'id' => $plan->slug, // Usamos slug como ID para el frontend
                'name' => $plan->name,
                'price_usd' => (float) $plan->effective_price,
                'original_price_usd' => (float) $plan->price,
                'price_ars' => (float) $plan->effective_price * $blueRate,
                'on_sale' => $plan->isOnSale(),
                'features' => $plan->features_json,
                'color' => $plan->slug === 'pro' ? 'indigo' : 'blue',
                'popular' => $plan->slug === 'pro'
            ];
        });

        return view('subscription.index', [
            'organization' => $organization,
            'currentPlanSlug' => $organization->plan,
            'trialDaysLeft' => max(0, (int) $trialDaysLeft),
            'blueRate' => $blueRate,
            'plans' => $plans
        ]);
    }

    public function checkout(Request $request)
    {
        $planId = $request->input('plan_id'); // Es el slug
        $method = $request->input('method');

        $plan = SubscriptionPlan::where('slug', $planId)->firstOrFail();

        // Fetch Blue Dollar rate
        $blueRate = 1200;
        try {
            $response = \Illuminate\Support\Facades\Http::get('https://dolarapi.com/v1/dolares/blue');
            if ($response->successful()) {
                $blueRate = $response->json()['venta'];
            }
        } catch (\Exception $e) {
        }

        return view('subscription.checkout', [
            'planId' => $planId,
            'planName' => $plan->name,
            'priceUsd' => (float) $plan->effective_price,
            'priceArs' => (float) $plan->effective_price * $blueRate,
            'isOnSale' => $plan->isOnSale(),
            'method' => $method
        ]);
    }
    public function process(Request $request)
    {
        $planId = $request->input('plan_id');
        $method = $request->input('method');

        if ($method === 'mercadopago') {
            return $this->processMercadoPago($request, $planId);
        }

        return back()->with('error', 'Método de pago no soportado actualmente.');
    }

    private function processMercadoPago(Request $request, $planId)
    {
        $user = Auth::user();
        $organization = $user->organization;

        $plan = SubscriptionPlan::where('slug', $planId)->firstOrFail();

        // Get Blue Rate
        $blueRate = 1200;
        try {
            $response = Http::get('https://dolarapi.com/v1/dolares/blue');
            if ($response->successful()) {
                $blueRate = $response->json()['venta'];
            }
        } catch (\Exception $e) {
        }

        $priceUsd = (float) $plan->effective_price;
        $priceArs = $priceUsd * $blueRate;

        // Mercado Pago API call to create a Preapproval (Subscription)
        $accessToken = config('services.mercadopago.access_token');
        
        // External reference: orgId|planSlug|timestamp
        $externalReference = "ORG_{$organization->id}_{$plan->slug}_" . time();

        $successUrl = route('subscription.success');
        $failureUrl = route('subscription.index');

        $payload = [
            'back_url' => $successUrl,
            'external_reference' => $externalReference,
            'reason' => $plan->name . ' - Vortex Control Phone',
            'auto_recurring' => [
                'frequency' => 1,
                'frequency_type' => 'months',
                'transaction_amount' => $priceArs,
                'currency_id' => 'ARS',
            ],
            'payer_email' => $user->email,
            'status' => 'pending',
            'card_token_id' => $request->input('token'), // If using card token, otherwise init_point
        ];

        try {
            // Log attempt
            \App\Services\MonitoringService::log('payments', "Initiating MP payment for Org: {$organization->id}, Plan: {$plan->slug}");

            $response = Http::withToken($accessToken)
                ->post('https://api.mercadopago.com/preapproval', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                // Save plan in session for instant success page feedback if webhook is slow
                session(['pending_plan_id' => $plan->id, 'external_reference' => $externalReference]);

                $initPoint = $data['sandbox_init_point'] ?? $data['init_point'];
                return redirect()->away($initPoint);
            }

            \Illuminate\Support\Facades\Log::error('MP Error: ' . $response->body());
            return back()->with('error', 'Error al conectar con Mercado Pago. Por favor intente nuevamente.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('MP Exception: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error inesperado al procesar el pago.');
        }
    }

    public function success(Request $request)
    {
        $user = Auth::user();
        $organization = $user->organization;

        // If we have a pending plan in session, let's activate it (provisionally)
        $pendingPlanId = session('pending_plan_id');
        
        if ($pendingPlanId) {
            $plan = SubscriptionPlan::find($pendingPlanId);
            if ($plan) {
                $organization->update([
                    'subscription_plan_id' => $plan->id,
                    'plan' => $plan->slug,
                    'subscription_status' => 'active',
                    'subscription_ends_at' => now()->addMonth(),
                    'trial_ends_at' => null // End trial if any
                ]);

                \App\Services\MonitoringService::log('payments', "Plan activated via success redirect for Org: {$organization->id}", 'info');
                
                // Notify Enzo
                try {
                    \Illuminate\Support\Facades\Mail::to('enzo100amarilla@gmail.com')->send(new \App\Mail\SubscriptionPaidNotification($organization, $plan, $plan->effective_price));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to notify Enzo about subscription: " . $e->getMessage());
                }

                session()->forget('pending_plan_id');
            }
        }

        return view('subscription.success');
    }

    public function webhook(Request $request)
    {
        $data = $request->all();
        \Log::info('Mercado Pago Webhook Received:', $data);

        // Standard MP Webhook handling
        $action = $data['action'] ?? null;
        $type = $data['type'] ?? $data['topic'] ?? null;

        // If it's a preapproval (subscription created/authorized)
        if ($type === 'preapproval' || $type === 'subscription') {
            $id = $data['id'] ?? $data['data']['id'] ?? null;
            if ($id) {
                // Fetch full preapproval data from MP
                $response = Http::withToken(config('services.mercadopago.access_token'))
                    ->get("https://api.mercadopago.com/preapproval/{$id}");

                if ($response->successful()) {
                    $mpData = $response->json();
                    $externalReference = $mpData['external_reference'] ?? null;
                    $status = $mpData['status'] ?? null;
                    
                    if ($externalReference && str_starts_with($externalReference, 'ORG_')) {
                        // Parse: ORG_{id}_{slug}_{timestamp}
                        $parts = explode('_', $externalReference);
                        $orgId = $parts[1] ?? null;
                        $planSlug = $parts[2] ?? null;

                        $org = \App\Models\Organization::find($orgId);
                        $plan = SubscriptionPlan::where('slug', $planSlug)->first();

                        if ($org && $plan) {
                            if ($status === 'authorized' || $status === 'active') {
                                $org->update([
                                    'subscription_plan_id' => $plan->id,
                                    'plan' => $plan->slug,
                                    'subscription_status' => 'active',
                                    'subscription_ends_at' => now()->addMonth(),
                                    'trial_ends_at' => null
                                ]);
                                
                                \App\Services\MonitoringService::log('payments', "Plan activated/renewed via Webhook for Org: {$orgId}", 'info', ['mp_id' => $id]);

                                // Notify Enzo
                                try {
                                    \Illuminate\Support\Facades\Mail::to('enzo100amarilla@gmail.com')->send(new \App\Mail\SubscriptionPaidNotification($org, $plan, $plan->effective_price));
                                } catch (\Exception $e) {
                                    \Illuminate\Support\Facades\Log::error("Failed to notify Enzo about subscription: " . $e->getMessage());
                                }
                            } elseif ($status === 'cancelled' || $status === 'stopped' || $status === 'paused') {
                                $org->update([
                                    'subscription_status' => 'cancelled',
                                ]);
                                \App\Services\MonitoringService::log('payments', "Plan cancelled via Webhook for Org: {$orgId}", 'warning', ['mp_id' => $id, 'status' => $status]);
                            }
                        }
                    }
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}
