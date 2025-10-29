<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // X-Content-Type-Options: Prevents MIME type sniffing
        $response->headers->set("X-Content-Type-Options", "nosniff");

        // X-Frame-Options: Prevents clickjacking attacks
        $response->headers->set("X-Frame-Options", "SAMEORIGIN");

        // X-XSS-Protection: Enables XSS filter in older browsers
        $response->headers->set("X-XSS-Protection", "1; mode=block");

        // Referrer-Policy: Controls referrer information
        $response->headers->set(
            "Referrer-Policy",
            "strict-origin-when-cross-origin",
        );

        // Permissions-Policy: Controls browser features
        $response->headers->set(
            "Permissions-Policy",
            "geolocation=(), microphone=(), camera=(), payment=()",
        );

        // Strict-Transport-Security: Forces HTTPS (only in production)
        if (app()->environment("production")) {
            $response->headers->set(
                "Strict-Transport-Security",
                "max-age=31536000; includeSubDomains; preload",
            );
        }

        // Content-Security-Policy: Prevents XSS and data injection attacks
        $csp = $this->getContentSecurityPolicy();
        $response->headers->set("Content-Security-Policy", $csp);

        // Remove sensitive headers that might leak information
        $response->headers->remove("X-Powered-By");
        $response->headers->remove("Server");

        return $response;
    }

    /**
     * Get Content Security Policy directives.
     *
     * @return string
     */
    protected function getContentSecurityPolicy(): string
    {
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net code.jquery.com cdn.datatables.net cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' cdn.jsdelivr.net fonts.googleapis.com fonts.bunny.net cdn.datatables.net cdnjs.cloudflare.com",
            "font-src 'self' data: fonts.gstatic.com fonts.bunny.net cdn.jsdelivr.net cdnjs.cloudflare.com",
            "img-src 'self' data: https: blob:",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "media-src 'self'",
            "manifest-src 'self'",
            "worker-src 'self' blob:",
        ];

        // In development, allow more permissive policies for hot reload
        if (app()->environment("local", "development")) {
            $directives[] =
                "connect-src 'self' ws: wss: http://localhost:* http://127.0.0.1:*";
        }

        return implode("; ", $directives);
    }
}
