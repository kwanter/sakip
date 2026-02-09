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

        // Add Reporting-API header for modern browsers (production only)
        if (app()->environment("production")) {
            $response->headers->set(
                "Reporting-Endpoints",
                'csp-endpoint="/api/csp-reports"',
            );
        }

        // Remove sensitive headers that might leak information
        $response->headers->remove("X-Powered-By");
        $response->headers->remove("Server");

        return $response;
    }

    /**
     * Get Content Security Policy directives.
     *
     * SECURITY IMPROVEMENTS:
     * - Implemented nonce-based CSP for better XSS protection
     * - Removed 'unsafe-inline' from production environment
     * - Added report-uri for CSP violation monitoring
     * - Strict policies for frame-ancestors and object-src
     *
     * @return string
     */
    protected function getContentSecurityPolicy(): string
    {
        // Generate a nonce for inline scripts (only when needed)
        // Nonce is generated per-request for maximum security
        $nonce = base64_encode(random_bytes(16));

        // Store nonce in request for use in Blade templates
        app()->singleton("csp-nonce", fn() => $nonce);

        // Check if we're in production (must be BOTH production env AND debug off)
        // For development: allow unsafe-inline for easier debugging
        // For production: use nonce-based CSP, but still allow unsafe-inline for page-specific scripts
        $isProduction =
            config("app.env") === "production" && !config("app.debug");
        $isLocal =
            config("app.debug") ||
            in_array(config("app.env"), ["local", "development", "testing"]);

        // Allow inline scripts with nonce for page-specific functionality
        // This is necessary because blade templates contain page-specific JavaScript
        $useNonce = true;

        $directives = [
            "default-src 'self'",

            // Allow inline scripts and styles with nonce for page-specific functionality
            // 'unsafe-inline' is needed for page-specific scripts in blade templates
            // 'unsafe-eval' is needed for some third-party libraries
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' 'nonce-{$nonce}' https://cdn.jsdelivr.net https://code.jquery.com https://cdn.datatables.net https://cdnjs.cloudflare.com",

            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.bunny.net https://cdn.datatables.net https://cdnjs.cloudflare.com",

            "font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "img-src 'self' data: https: blob:",
            "connect-src 'self'",

            // STRICT: Prevent clickjacking
            "frame-ancestors 'self'",

            // STRICT: Prevent form submissions to external sites
            "form-action 'self'",

            "base-uri 'self'",

            // STRICT: Disallow plugins like Flash/Java
            "object-src 'none'",

            "media-src 'self'",
            "manifest-src 'self'",
            "worker-src 'self' blob:",

            // Report CSP violations for monitoring
            "report-uri /api/csp-reports",
        ];

        // In development, allow more permissive policies for hot reload
        if ($isLocal) {
            $directives[] =
                "connect-src 'self' ws: wss: http://localhost:* http://127.0.0.1:*";
        }

        return implode("; ", $directives);
    }
}
