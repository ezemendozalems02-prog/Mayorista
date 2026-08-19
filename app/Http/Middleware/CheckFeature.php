<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class CheckFeature
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        // ── Platform Mode Bypass ─────────────────────────────────────────────
        // In single_license or white_label mode all features are available.
        $platformMode = Config::get('platform.mode', 'saas');
        if ($platformMode !== 'saas') {
            return $next($request);
        }
        // ─────────────────────────────────────────────────────────────────────

        if (Auth::check()) {
            $organization = Auth::user()->organization;

            if (!$organization->hasFeature($feature)) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Tu plan actual no incluye esta funcionalidad.'], 403);
                }

                return redirect()->route('subscription.index')
                    ->with('error', 'Tu plan actual no incluye acceso a: ' . ucfirst(str_replace('_', ' ', $feature)) . '. Por favor, actualiza tu plan.');
            }
        }

        return $next($request);
    }
}
