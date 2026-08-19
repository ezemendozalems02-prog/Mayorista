<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackReferrals
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('ref')) {
            $refCode = $request->query('ref');
            
            // Queue the cookie for the next response
            \Illuminate\Support\Facades\Cookie::queue('vortex_ref', $refCode, 60 * 24 * 30);
        }

        return $next($request);
    }
}
