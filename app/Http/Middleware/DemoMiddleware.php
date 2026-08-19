<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class DemoMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // First check if user is authenticated and is a demo user
        if (Auth::check() && Auth::user()->is_demo) {
            
            // Restrict sensitive actions
            $restrictedRoutes = [
                'organization.update',
                'super-admin.*',
                'subscription.process',
                'payment.index'
            ];

            foreach ($restrictedRoutes as $route) {
                if ($request->routeIs($route)) {
                    // For AJAX requests
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['message' => 'Acción no permitida en Modo Demostración.'], 403);
                    }
                    // For regular requests
                    return back()->with('error', 'Acción no permitida en Modo Demostración.');
                }
            }
        }

        return $next($request);
    }
}
