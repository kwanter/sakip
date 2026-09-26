<?php

namespace Tests\Unit;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Tests\TestCase;

/**
 * Static Floor-Guard scan — Checklist TC-070, `CONSTRAINTS.md` §3 rules 1 and 3.
 *
 * The admin triage landing review verified the floor-guard rules by hand: no suppression token and no
 * skipped test anywhere in the changed files. This case makes that verification repeatable rather than
 * manual, so a future change cannot quietly reintroduce either.
 *
 * Documented limitation: this file excludes itself, because the tokens it forbids are necessarily present
 * in it as string literals. A suppression added to *this* file therefore escapes the scan — an acceptable
 * blind spot for a guard that ships no production behaviour.
 */
class FloorGuardTest extends TestCase
{
    /** Suppression tokens that may appear in no scanned file (`CONSTRAINTS.md` §3 rule 1). */
    private const FORBIDDEN_SUPPRESSIONS = [
        '@phpstan-ignore',
        '@noinspection',
        'phpcs:ignore',
        'eslint-disable',
        '@ts-ignore',
        'noqa',
    ];

    /** @var array<string, string>|null */
    private ?array $scanned = null;

    /** TC-070 — no suppression token exists in any scanned root. */
    public function test_no_suppression_annotation_exists_in_the_scanned_roots(): void
    {
        $files = $this->scannedFiles();

        $this->assertGreaterThan(100, count($files), 'the scanner must actually walk the repository');
        $this->assertArrayHasKey('app/Services/AdminTriageService.php', $files, 'the app root must be scanned');
        $this->assertArrayHasKey('config/sakip.php', $files, 'the config root must be scanned');
        $this->assertArrayHasKey('resources/views/admin/dashboard.blade.php', $files, 'the views root must be scanned');
        $this->assertArrayHasKey('tests/Feature/AdminTriageLandingTest.php', $files, 'the tests root must be scanned');
        $this->assertArrayNotHasKey('tests/Unit/FloorGuardTest.php', $files, 'this guard excludes itself by design');

        $offenders = [];

        foreach ($files as $relative => $contents) {
            foreach (self::FORBIDDEN_SUPPRESSIONS as $token) {
                if (str_contains($contents, $token)) {
                    $offenders[] = $relative.' -> '.$token;
                }
            }
        }

        $this->assertSame(
            [],
            $offenders,
            'A suppression token was found. Fix the diagnostic instead of silencing the tool (CONSTRAINTS.md §3 rule 1).',
        );
    }

    /**
     * TC-070 — no test is skipped or marked incomplete anywhere.
     *
     * The skip allow-list is empty as of 2026-09-26: backlog F1 (the two skipped rate-limit cases)
     * is closed, so `markTestSkipped` is forbidden in every scanned file, not only in the seam files.
     */
    public function test_no_test_is_skipped_outside_the_documented_backlog(): void
    {
        $skips = [];
        $incompletes = [];

        foreach ($this->scannedFiles() as $relative => $contents) {
            if (($occurrences = substr_count($contents, 'markTestSkipped')) > 0) {
                $skips[$relative] = $occurrences;
            }

            if (str_contains($contents, 'markTestIncomplete')) {
                $incompletes[] = $relative;
            }
        }

        $this->assertSame([], $incompletes, 'markTestIncomplete is never acceptable (CONSTRAINTS.md §3 rule 3).');
        $this->assertSame(
            [],
            $skips,
            'A skipped test appeared. Fix the test instead of skipping it (CONSTRAINTS.md §3 rule 3): backlog F1 is closed, so no allow-list remains.',
        );
    }

    /** @return array<string, string> */
    private function scannedFiles(): array
    {
        return $this->scanned ??= $this->scan();
    }

    /**
     * Every scannable PHP file under the four roots, keyed by repository-relative path.
     *
     * @return array<string, string>
     */
    private function scan(): array
    {
        $files = [];

        foreach ([app_path(), config_path(), resource_path('views'), base_path('tests')] as $root) {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if (! $file->isFile() || $file->getExtension() !== 'php') {
                    continue;
                }

                $path = $file->getPathname();

                if ($path === __FILE__) {
                    continue;
                }

                $files[str_replace(base_path().'/', '', $path)] = (string) file_get_contents($path);
            }
        }

        return $files;
    }
}
