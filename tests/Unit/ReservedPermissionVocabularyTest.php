<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\PermissionManagementController;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (plan-refactor-security-remediation-v1.1 SEC-201, review
 * R2-STD-01): the reserved permission vocabulary must mechanically cover
 * EVERY authority-bearing permission literal in the codebase — Spatie
 * resolves gate abilities by permission NAME, so any unreserved literal can
 * be minted by a Super Admin (or historically by a delegated manager) and
 * exchanged for unsupervised authority.
 *
 * This is the ALT-101 drift guard: it scans app/ and routes/ for ability
 * literals (hasPermissionTo / hasPermission / hasAnyPermission / Gate::define
 * / can: middleware / ->can()) and asserts the reserved union covers them.
 * When this test fails, extend
 * PermissionManagementController::RESERVED_POLICY_PERMISSION_NAMES with the
 * reported names and cite the consuming file in the constant docblock.
 */
class ReservedPermissionVocabularyTest extends TestCase
{
    private const SCAN_DIRECTORIES = ['app/Policies', 'app/Providers', 'app/Http/Controllers', 'app/Http/Requests'];

    private const SCAN_FILES = ['routes/web.php', 'routes/web_sakip.php', 'routes/api.php', 'routes/console.php'];

    #[Test]
    public function every_authority_bearing_permission_literal_is_reserved(): void
    {
        $reserved = $this->reservedPermissionNames();
        $literals = $this->scanAuthorityLiterals();

        $this->assertNotSame([], $literals, 'scanner found no literals — the scan regex is broken');

        $missing = array_values(array_diff($literals, $reserved));

        $this->assertSame(
            [],
            $missing,
            "Authority-bearing permission literals are not in the reserved set;\n".
            "extend RESERVED_POLICY_PERMISSION_NAMES (cite the consuming file):\n- ".
            implode("\n- ", $missing),
        );
    }

    /**
     * Invoke the controller's private reservedPermissionNames() so the guard
     * and this guard-of-the-guard can never drift apart.
     */
    private function reservedPermissionNames(): array
    {
        $method = new \ReflectionMethod(PermissionManagementController::class, 'reservedPermissionNames');

        return $method->invoke(app(PermissionManagementController::class));
    }

    /**
     * @return list<string> sorted unique authority-bearing literals
     */
    private function scanAuthorityLiterals(): array
    {
        $pattern =
            '/hasAnyPermission\\(\\s*\\[([^\\]]*)\\]'.       // hasAnyPermission(['a', 'b'])
            "|hasPermissionTo\\('([^']+)'\\)".               // hasPermissionTo('x')
            "|hasPermission\\('([^']+)'\\)".                 // hasPermission('x')
            "|Gate::define\\('([^']+)'".                     // Gate::define('x', ...)
            "|->can\\('([^']+)'".                            // $user->can('x')
            "|['\"]can:([a-zA-Z0-9\\-\\.]+)['\"]/";          // middleware('can:x')

        $literals = [];

        $files = [];
        foreach (self::SCAN_DIRECTORIES as $directory) {
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(base_path($directory)));
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $file->getPathname();
                }
            }
        }
        foreach (self::SCAN_FILES as $file) {
            if (is_file(base_path($file))) {
                $files[] = base_path($file);
            }
        }

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER);

            foreach ($matches as $match) {
                // Every capture group may hold one literal or a comma list of
                // quoted literals (hasAnyPermission arrays).
                foreach (array_slice($match, 1) as $segment) {
                    if ($segment === '' || $segment === null) {
                        continue;
                    }
                    foreach (str_getcsv($segment) as $candidate) {
                        $name = trim($candidate, " \t'\"");

                        if ($name === '' || str_contains($name, '$') || ! preg_match('/^[a-zA-Z0-9 .\-]+$/', $name)) {
                            continue; // dynamic or non-name segment
                        }

                        $literals[$name] = true;
                    }
                }
            }
        }

        ksort($literals);

        return array_keys($literals);
    }
}
