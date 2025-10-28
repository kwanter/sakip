<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInputMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Sanitizes user input to prevent XSS and injection attacks.
     * This middleware runs before validation to clean potentially malicious input.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only sanitize for non-GET requests (POST, PUT, PATCH, DELETE)
        if (!$request->isMethod('GET')) {
            $this->sanitizeInput($request);
        }

        return $next($request);
    }

    /**
     * Sanitize request input data.
     *
     * @param Request $request
     * @return void
     */
    protected function sanitizeInput(Request $request): void
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                $value = $this->sanitizeString($value);
            }
        });

        $request->merge($input);
    }

    /**
     * Sanitize a single string value.
     *
     * @param string $value
     * @return string
     */
    protected function sanitizeString(string $value): string
    {
        // Remove null bytes
        $value = str_replace("\0", '', $value);

        // Remove invisible characters except spaces, tabs, and line breaks
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);

        // Trim whitespace
        $value = trim($value);

        // Remove potential SQL injection patterns (basic level - real protection is via parameterized queries)
        $value = $this->removeSqlInjectionPatterns($value);

        // Remove potential XSS patterns
        $value = $this->removeXssPatterns($value);

        // Remove potential command injection patterns
        $value = $this->removeCommandInjectionPatterns($value);

        return $value;
    }

    /**
     * Remove common SQL injection patterns.
     *
     * Note: This is a basic filter. Primary protection should be parameterized queries.
     *
     * @param string $value
     * @return string
     */
    protected function removeSqlInjectionPatterns(string $value): string
    {
        // Remove SQL comments
        $value = preg_replace('/--.*$/m', '', $value);
        $value = preg_replace('/\/\*.*?\*\//s', '', $value);

        // Remove multiple semicolons (often used in SQL injection)
        $value = preg_replace('/;+/', ';', $value);

        // Remove dangerous SQL keywords when followed by suspicious patterns
        $patterns = [
            '/\b(UNION\s+SELECT)\b/i',
            '/\b(OR\s+1\s*=\s*1)\b/i',
            '/\b(DROP\s+TABLE)\b/i',
            '/\b(DROP\s+DATABASE)\b/i',
            '/\b(EXEC\s*\()\b/i',
            '/\b(EXECUTE\s*\()\b/i',
            '/\b(SCRIPT\s*>)/i',
        ];

        foreach ($patterns as $pattern) {
            $value = preg_replace($pattern, '', $value);
        }

        return $value;
    }

    /**
     * Remove common XSS patterns.
     *
     * @param string $value
     * @return string
     */
    protected function removeXssPatterns(string $value): string
    {
        // Remove script tags
        $value = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);

        // Remove javascript: protocol
        $value = preg_replace('/javascript:/i', '', $value);

        // Remove vbscript: protocol
        $value = preg_replace('/vbscript:/i', '', $value);

        // Remove data: protocol with base64
        $value = preg_replace('/data:text\/html/i', '', $value);

        // Remove on* event handlers
        $value = preg_replace('/\bon\w+\s*=/i', '', $value);

        // Remove iframe tags
        $value = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $value);

        // Remove object tags
        $value = preg_replace('/<object\b[^>]*>(.*?)<\/object>/is', '', $value);

        // Remove embed tags
        $value = preg_replace('/<embed\b[^>]*>/is', '', $value);

        // Remove meta refresh
        $value = preg_replace('/<meta\b[^>]*http-equiv=["\']?refresh["\']?[^>]*>/i', '', $value);

        return $value;
    }

    /**
     * Remove common command injection patterns.
     *
     * @param string $value
     * @return string
     */
    protected function removeCommandInjectionPatterns(string $value): string
    {
        // Remove shell command separators
        $dangersous = ['&&', '||', '|', ';', '`', '$', '$(', '${'];

        foreach ($dangersous as $dangerous) {
            // Only remove if they appear in suspicious contexts
            if (strpos($value, $dangerous) !== false) {
                // Check if this looks like a command (has common shell commands)
                if (preg_match('/\b(cat|ls|rm|mv|cp|chmod|chown|curl|wget|nc|bash|sh|python|perl|ruby)\b/i', $value)) {
                    $value = str_replace($dangerous, '', $value);
                }
            }
        }

        return $value;
    }

    /**
     * Check if a field should be exempt from sanitization.
     *
     * Some fields like passwords or encrypted content should not be sanitized.
     *
     * @param string $key
     * @return bool
     */
    protected function isExemptField(string $key): bool
    {
        $exemptFields = [
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            '_token',
            '_method',
        ];

        return in_array($key, $exemptFields);
    }
}
