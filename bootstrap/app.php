<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . "/../routes/web.php",
        api: __DIR__ . "/../routes/api.php",
        commands: __DIR__ . "/../routes/console.php",
        health: "/up",
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
            "role" => Spatie\Permission\Middleware\RoleMiddleware::class,
            "permission" =>
                Spatie\Permission\Middleware\PermissionMiddleware::class,
            "role_or_permission" =>
                Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            "sanitize.input" =>
                \App\Http\Middleware\SanitizeInputMiddleware::class,
            "secure.file.upload" =>
                \App\Http\Middleware\SecureFileUploadMiddleware::class,
            "throttle.login" =>
                \Illuminate\Routing\Middleware\ThrottleRequests::class .
                ":login",
            "throttle.api.strict" =>
                \Illuminate\Routing\Middleware\ThrottleRequests::class .
                ":api_strict",
        ]);

        // Apply input sanitization globally to web routes
        $middleware->web(
            append: [\App\Http\Middleware\SanitizeInputMiddleware::class],
        );

        // API Rate Limiting
        $middleware->api(
            prepend: [
                \Illuminate\Routing\Middleware\ThrottleRequests::class . ":api",
            ],
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
