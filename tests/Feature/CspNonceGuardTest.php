<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Guard rails for the CSP setup.
 *
 * Inline scripts without a nonce are blocked by the browser (the nonce in
 * script-src disables 'unsafe-inline'), and the Fontshare stylesheet is the
 * General Sans source. Both regressions already shipped once; these tests
 * keep them from shipping again.
 */
class CspNonceGuardTest extends TestCase
{
    #[Test]
    public function every_inline_script_in_views_carries_a_nonce()
    {
        $offenders = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = File::get($file->getPathname());

            preg_match_all('/<script\b[^>]*>/i', $contents, $matches);

            foreach ($matches[0] as $tag) {
                $hasSrc = preg_match('/\bsrc\s*=/i', $tag) === 1;
                $hasNonce = preg_match('/\bnonce\s*=/i', $tag) === 1;

                if (! $hasSrc && ! $hasNonce) {
                    $offenders[] = str_replace(resource_path('views').'/', '', $file->getPathname()).': '.$tag;
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Inline <script> tags must carry a nonce attribute:\n".implode("\n", $offenders)
        );
    }

    #[Test]
    public function no_inline_event_handlers_in_views()
    {
        // Inline on* handlers are blocked by the nonce-based CSP
        // ('unsafe-inline' is ignored when a nonce is present). Use the
        // data-onclick / data-onchange / data-confirm / data-onsubmit
        // delegation implemented in public/js/custom-scripts.js.
        $offenders = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = File::get($file->getPathname());

            // Whitespace before "on…=" excludes data-onclick style attributes
            // (there the preceding character is "-", not whitespace).
            if (preg_match_all('/\son(?:click|change|submit|input|keyup|keydown|load|error)\s*=\s*"/i', $contents, $matches)) {
                foreach ($matches[0] as $match) {
                    $offenders[] = str_replace(resource_path('views').'/', '', $file->getPathname()).': '.trim($match);
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Inline event handlers are blocked by the CSP; use data-* delegation:\n".implode("\n", $offenders)
        );
    }

    #[Test]
    public function every_cross_origin_script_and_stylesheet_carries_subresource_integrity()
    {
        // The production CSP still lists the CDN hosts the layouts load from.
        // SRI pins each versioned asset to the bytes that were reviewed, so a
        // compromised or coerced CDN cannot swap in its own script. Without
        // crossorigin="anonymous" the browser does not check the hash at all.
        //
        // Font CSS endpoints are exempt on purpose: Google Fonts and Fontshare
        // serve a different stylesheet per User-Agent, so no single hash holds.
        $sriExemptHosts = ['fonts.googleapis.com', 'api.fontshare.com'];

        $offenders = [];

        foreach (File::allFiles(resource_path('views')) as $file) {
            if (! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            $contents = File::get($file->getPathname());

            preg_match_all('/<script\b[^>]*\bsrc\s*=\s*"(https?:\/\/[^\"]+)"[^>]*>/i', $contents, $scripts);
            preg_match_all('/<link\b[^>]*\bhref\s*=\s*"(https?:\/\/[^\"]+)"[^>]*>/i', $contents, $styles);

            foreach ([$scripts, $styles] as $tags) {
                foreach ($tags[0] as $index => $tag) {
                    $url = $tags[1][$index];
                    $host = parse_url($url, PHP_URL_HOST);

                    $isStylesheet = preg_match('/rel\s*=\s*"stylesheet"/i', $tag) === 1;
                    $isScript = str_starts_with(strtolower($tag), '<script');

                    if (! ($isScript || $isStylesheet) || in_array($host, $sriExemptHosts, true)) {
                        continue;
                    }

                    $hasIntegrity = preg_match('/\bintegrity\s*=\s*"sha384-[A-Za-z0-9+\/]+={0,2}"/', $tag) === 1;
                    $hasCrossorigin = preg_match('/\bcrossorigin\s*=\s*"anonymous"/i', $tag) === 1;

                    if (! $hasIntegrity || ! $hasCrossorigin) {
                        $offenders[] = str_replace(resource_path('views').'/', '', $file->getPathname()).': '.$tag;
                    }
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Cross-origin scripts and stylesheets must carry integrity=\"sha384-...\" and crossorigin=\"anonymous\":\n".implode("\n", $offenders)
        );
    }

    #[Test]
    public function csp_allows_fontshare_and_is_single_valued()
    {
        $response = $this->get('/login');

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertNotNull($csp);

        $this->assertStringContainsString('https://api.fontshare.com', $csp);
        $this->assertStringContainsString('https://cdn.fontshare.com', $csp);

        // Browsers ignore a duplicated directive; keep exactly one.
        $this->assertSame(1, substr_count($csp, 'connect-src'));
    }
}
