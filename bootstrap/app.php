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
        // Redirect authenticated users away from guest pages to SAKIP dashboard
        \Illuminate\Auth\Middleware\RedirectIfAuthenticated::redirectUsing(
            fn () => route('sakip.dashboard'),
        );

        // Security Headers Middleware (applied globally)
        $middleware->append(
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
        );

        $middleware->alias([
            'role' => Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'secure.file.upload' => \App\Http\Middleware\SecureFileUploadMiddleware::class,
            'throttle.login' => \Illuminate\Routing\Middleware\ThrottleRequests::class.
                ':login',
            'throttle.api.strict' => \Illuminate\Routing\Middleware\ThrottleRequests::class.
                ':api_strict',
            // SECURITY: the limiters below were defined but never bound, so
            // the heavy routes they describe ran unmetered (audit run-3).
            'throttle.upload' => \Illuminate\Routing\Middleware\ThrottleRequests::class.
                ':upload',
            'throttle.report_generation' => \Illuminate\Routing\Middleware\ThrottleRequests::class.
                ':report_generation',
            'throttle.export' => \Illuminate\Routing\Middleware\ThrottleRequests::class.
                ':export',
            'throttle.bulk_operations' => \Illuminate\Routing\Middleware\ThrottleRequests::class.
                ':bulk_operations',
        ]);

        // API Rate Limiting
        $middleware->api(
            prepend: [
                \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            ],
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
