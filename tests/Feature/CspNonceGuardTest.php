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
