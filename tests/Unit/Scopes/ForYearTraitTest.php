<?php

namespace Tests\Unit\Scopes;

use App\Models\PerformanceData;
use App\Support\ReportingPeriod;
use FilesystemIterator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * Seam: `ForYearTrait::scopeForCurrentYear()` — one active-year source (Spec v1.3 §3.3, REQ-014).
 *
 * Cases: TC-013 (the scope uses the configured active year) and TC-069 (the dead duplicate is gone).
 */
class ForYearTraitTest extends TestCase
{
    use RefreshDatabase;

    /** TC-013 / REQ-014 — the scope filters by the configured active year, never by date('Y'). */
    public function test_scope_filters_by_the_configured_active_year(): void
    {
        config()->set('sakip.reporting.active_year', '2024');

        $insideConfiguredYear = PerformanceData::factory()->create();
        $insideConfiguredYear->created_at = '2024-03-15 10:00:00';
        $insideConfiguredYear->save();

        $outsideConfiguredYear = PerformanceData::factory()->create();
        $outsideConfiguredYear->created_at = '2025-03-15 10:00:00';
        $outsideConfiguredYear->save();

        $matched = PerformanceData::query()->forCurrentYear('created_at')->pluck('id')->all();

        $this->assertSame([$insideConfiguredYear->id], $matched);
        $this->assertSame(2024, ReportingPeriod::activeYear());
    }

    /** TC-069 / REQ-014 — the dead `ForYearScope`/`ForYear` duplicate is deleted and unreferenced. */
    public function test_dead_year_scope_file_is_removed_and_referenced_nowhere(): void
    {
        $this->assertFileDoesNotExist(
            app_path('Models/Scopes/ForYearScope.php'),
            'The dead ForYearScope class and its unused ForYear trait must be deleted (REQ-014).',
        );

        $this->assertFileExists(app_path('Models/Scopes/ForYearTrait.php'));

        $offenders = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(app_path(), FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            if (str_contains((string) file_get_contents($file->getPathname()), 'ForYearScope')) {
                $offenders[] = str_replace(app_path().'/', '', $file->getPathname());
            }
        }

        $this->assertSame([], $offenders, 'No production file may reference the removed duplicate.');
    }
}
