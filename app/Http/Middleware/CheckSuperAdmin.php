<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->role === UserRole::SUPER_ADMIN) {
            return $next($request);
        }

        abort(403, 'Acceso denegado. Se requiere privilegios de Super Administrador.');
    }
}
