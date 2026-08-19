<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ── Platform Mode Bypass ─────────────────────────────────────────────
        // In single_license or white_label mode there are no subscriptions,
        // so skip ALL subscription checks unconditionally.
        $platformMode = Config::get('platform.mode', 'saas');
        if ($platformMode !== 'saas') {
            return $next($request);
        }
        // ─────────────────────────────────────────────────────────────────────

        if (Auth::check()) {
            $user = Auth::user();

            // Super Admin bypass: They don't need to belong to an organization
            if ($user->role === \App\Enums\UserRole::SUPER_ADMIN) {
                return $next($request);
            }

            $organization = $user->organization;

            if (!$organization) {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Tu usuario no tiene un negocio asociado.');
            }

            // Check if organization is active
            if (!$organization->is_active) {
                Auth::logout();
                return redirect()->route('login')->with('error', 'Tu cuenta de negocio ha sido desactivada. Por favor contacta al soporte.');
            }

            // Allow access to subscription and logout routes regardless of status
            if ($request->routeIs('subscription.*') || $request->routeIs('logout')) {
                return $next($request);
            }

            // Check if trial has expired and status is still trial
            if ($organization->subscription_status === 'trial' && $organization->trial_ends_at && now()->gt($organization->trial_ends_at)) {
                return redirect()->route('subscription.index')->with('error', 'Tu prueba gratuita ha expirado. Por favor, selecciona un plan para continuar.');
            }

            // Check if subscription has expired
            if ($organization->subscription_status === 'expired') {
                return redirect()->route('subscription.index')->with('error', 'Tu suscripción ha expirado. Por favor, realiza un pago para continuar.');
            }
        }

        return $next($request);
    }
}
