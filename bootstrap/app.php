<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\CheckSubscription::class,
        ]);
        $middleware->alias([
            'feature' => \App\Http\Middleware\CheckFeature::class,
            'super_admin' => \App\Http\Middleware\CheckSuperAdmin::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'suscripcion/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->report(function (\Throwable $e) {
            \App\Services\MonitoringService::log(
                'errors',
                "Exception: " . $e->getMessage(),
                'error',
                [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url'  => !app()->runningInConsole() ? request()->fullUrl() : 'console',
                    'input' => !app()->runningInConsole() ? request()->except(['password', 'password_confirmation']) : []
                ]
            );
        });
    })->create();
