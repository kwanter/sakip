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
        // Generate CSP nonce BEFORE rendering so Blade templates can attach it
        // to inline scripts. Nonce is per-request for maximum security;
        // deterministic under tests so page renders stay reproducible.
        $nonce = app()->runningUnitTests()
            ? 'test-nonce'
            : base64_encode(random_bytes(16));
        app()->singleton('csp-nonce', fn () => $nonce);

        $response = $next($request);

        // X-Content-Type-Options: Prevents MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // X-Frame-Options: Prevents clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // X-XSS-Protection: Enables XSS filter in older browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer-Policy: Controls referrer information
        $response->headers->set(
            'Referrer-Policy',
            'strict-origin-when-cross-origin',
        );

        // Permissions-Policy: Controls browser features
        $response->headers->set(
            'Permissions-Policy',
            'geolocation=(), microphone=(), camera=(), payment=()',
        );

        // Strict-Transport-Security: Forces HTTPS (only in production)
        if (app()->environment('production')) {
            $response->headers->set(
                'Strict-Transport-Security',
                'max-age=31536000; includeSubDomains; preload',
            );
        }

        // Content-Security-Policy: Prevents XSS and data injection attacks
        $csp = $this->getContentSecurityPolicy();
        $response->headers->set('Content-Security-Policy', $csp);

        // Add Reporting-API header for modern browsers (production only)
        if (app()->environment('production')) {
            $response->headers->set(
                'Reporting-Endpoints',
                'csp-endpoint="/api/csp-reports"',
            );
        }

        // Remove sensitive headers that might leak information
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

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
     */
    protected function getContentSecurityPolicy(): string
    {
        // Nonce is generated in handle() before rendering; reuse it here.
        $nonce = app()->bound('csp-nonce') ? app('csp-nonce') : base64_encode(random_bytes(16));

        // Check if we're in production (must be BOTH production env AND debug off)
        // For development: allow unsafe-inline for easier debugging
        // For production: use nonce-based CSP, but still allow unsafe-inline for page-specific scripts
        $isProduction =
            app()->environment('production') && ! config('app.debug');
        $isLocal =
            app()->environment(['local', 'development']) ||
            config('app.debug');

        // Allow inline scripts with nonce for page-specific functionality
        // This is necessary because blade templates contain page-specific JavaScript
        $useNonce = true;

        // Production: nonce-based scripts, no unsafe-eval.
        // Local/dev: still allow unsafe-inline for Vite HMR convenience.
        $scriptSrc = $isLocal
            ? "script-src 'self' 'unsafe-inline' 'nonce-{$nonce}' https://cdn.jsdelivr.net https://code.jquery.com https://cdn.datatables.net https://cdnjs.cloudflare.com"
            : "script-src 'self' 'nonce-{$nonce}' https://cdn.jsdelivr.net https://code.jquery.com https://cdn.datatables.net https://cdnjs.cloudflare.com";

        // Single connect-src directive (duplicates are ignored by browsers).
        $connectSrc = $isLocal
            ? "connect-src 'self' ws: wss: http://localhost:* http://127.0.0.1:*"
            : "connect-src 'self'";

        $directives = [
            "default-src 'self'",
            $scriptSrc,
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://fonts.bunny.net https://api.fontshare.com https://cdn.datatables.net https://cdnjs.cloudflare.com",
            "font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net https://cdn.fontshare.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "img-src 'self' data: https: blob:",
            $connectSrc,
            "frame-ancestors 'self'",
            "form-action 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "media-src 'self'",
            "manifest-src 'self'",
            "worker-src 'self' blob:",
            'report-uri /api/csp-reports',
        ];

        return implode('; ', $directives);
    }
}
