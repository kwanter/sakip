<?php

namespace Tests\Unit\Services;

use App\Constants\SystemRoles;
use App\Models\Assessment;
use App\Models\Instansi;
use App\Models\PerformanceData;
use App\Models\Report;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminTriageService;
use App\Support\ReportingPeriod;
use App\Support\TriageScope;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Seam S2 — the instansi-scoped triage read model (Spec v1.3 §4.2, §5.0, §6.2).
 *
 * No `actingAs()`: the service receives the viewer explicitly and applies coverage explicitly
 * (SEC-002), so the ambient `InstansiScope` stays a no-op at this seam.
 *
 * Cases: TC-014 … TC-027 of docs/checklist/checklist-admin-triage-landing.md.
 *
 * Documented fixture deviation: §5.0 row F-6 attaches three assessments to one PerformanceData row,
 * but `assessments.performance_data_id` is UNIQUE (`2025_10_14_080004_create_assessments_table.php:43`).
 * The three assessments are therefore spread over three distinct A rows — the two in-period rows plus
 * the out-of-period row — which preserves every count the matrix documents.
 */
class AdminTriageServiceTest extends TestCase
{
    use RefreshDatabase;

    private AdminTriageService $service;

    private CarbonImmutable $now;

    private ReportingPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AdminTriageService;
        $this->now = CarbonImmutable::parse('2026-09-24 10:15:00');
        CarbonImmutable::setTestNow($this->now);
        $this->period = ReportingPeriod::fromKey('current_year', $this->now);
    }

    protected function tearDown(): void
    {
        config()->set('sakip.reporting.active_year', null);
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    /** TC-014 — the canonical matrix really builds the population the documented counts rely on. */
    public function test_canonical_fixture_matrix_builds_the_expected_population(): void
    {
        $this->buildCanonicalFixture();

        $this->assertSame(6, PerformanceData::query()->submitted()->count(), 'alive submitted data, one row outside the period');
        $this->assertSame(4, Assessment::query()->pending()->whereHas('performanceData')->count(), 'live parent');
        $this->assertSame(3, Report::query()->submitted()->count(), 'submitted reports');
    }

    /** TC-015 / AC-009 — an agency-bound viewer counts its own agency over the selected period only. */
    public function test_agency_bound_viewer_counts_only_own_agency_and_selected_period(): void
    {
        [$a] = $this->buildCanonicalFixture();

        $summary = $this->service->summaryFor($this->agencyViewer($a), $this->period);

        $this->assertSame(2, $summary->verificationCount, 'F-5 (out of period) must be excluded');
        $this->assertSame(3, $summary->assessmentCount, 'F-7 and F-8 must be excluded');
        $this->assertSame(1, $summary->reportCount, 'F-10 and F-11 must be excluded');
        $this->assertSame('Dinas A', $summary->scopeLabel);
        $this->assertSame(TriageScope::Agency, $summary->scope);
    }

    /** TC-016 / AC-010 — an unassigned viewer sees zeros and the canonical label. */
    public function test_unassigned_viewer_gets_zero_counts_and_the_canonical_label(): void
    {
        $this->buildCanonicalFixture();

        $summary = $this->service->summaryFor($this->unassignedViewer(), $this->period);

        $this->assertSame(TriageScope::Unassigned, $summary->scope);
        $this->assertSame('Instansi Belum Ditetapkan', $summary->scopeLabel);
        $this->assertSame(0, $summary->verificationCount);
        $this->assertSame(0, $summary->assessmentCount);
        $this->assertSame(0, $summary->reportCount);
    }

    /** TC-017 / AC-011 — the three viewer states match the matrix and the sum invariant holds. */
    public function test_three_viewer_states_match_the_canonical_matrix(): void
    {
        [$a, $b] = $this->buildCanonicalFixture();

        $cross = $this->service->summaryFor($this->superAdmin(), $this->period);
        $agencyA = $this->service->summaryFor($this->agencyViewer($a), $this->period);
        $agencyB = $this->service->summaryFor($this->agencyViewer($b), $this->period);
        $unassigned = $this->service->summaryFor($this->unassignedViewer(), $this->period);

        $this->assertSame([5, 4, 3], [$cross->verificationCount, $cross->assessmentCount, $cross->reportCount]);
        $this->assertSame([2, 3, 1], [$agencyA->verificationCount, $agencyA->assessmentCount, $agencyA->reportCount]);
        $this->assertSame([3, 1, 2], [$agencyB->verificationCount, $agencyB->assessmentCount, $agencyB->reportCount]);
        $this->assertSame([0, 0, 0], [$unassigned->verificationCount, $unassigned->assessmentCount, $unassigned->reportCount]);

        $this->assertSame('Semua Instansi', $cross->scopeLabel);
        $this->assertSame('Dinas B', $agencyB->scopeLabel);

        foreach (['verificationCount', 'assessmentCount', 'reportCount'] as $field) {
            $this->assertSame(
                $cross->{$field},
                $agencyA->{$field} + $agencyB->{$field},
                "cross-agency {$field} must equal the sum of both agencies",
            );
        }
    }

    /** TC-018 / AC-012 — the label `Semua Instansi` is reserved for the cross-agency scope (F-03). */
    public function test_scope_label_is_semua_instansi_iff_cross_agency(): void
    {
        [$a] = $this->buildCanonicalFixture();

        $summaries = [
            $this->service->summaryFor($this->superAdmin(), $this->period),
            $this->service->summaryFor($this->agencyViewer($a), $this->period),
            $this->service->summaryFor($this->unassignedViewer(), $this->period),
        ];

        foreach ($summaries as $summary) {
            $this->assertSame(
                $summary->scope === TriageScope::CrossAgency,
                $summary->scopeLabel === 'Semua Instansi',
                'the canonical label is reserved for the cross-agency scope',
            );
            $this->assertNotSame('', $summary->scopeLabel, 'a scope label is never empty');

            if ($summary->scope === TriageScope::Unassigned) {
                $this->assertSame([0, 0, 0], [
                    $summary->verificationCount,
                    $summary->assessmentCount,
                    $summary->reportCount,
                ]);
            }
        }
    }

    /** TC-019 / AC-025 — one count implementation feeds the figure and the Phase-2 badge. */
    public function test_verification_count_for_equals_the_summary_verification_count(): void
    {
        [$a] = $this->buildCanonicalFixture();

        foreach ([$this->superAdmin(), $this->agencyViewer($a), $this->unassignedViewer()] as $viewer) {
            $this->assertSame(
                $this->service->summaryFor($viewer, $this->period)->verificationCount,
                $this->service->verificationCountFor($viewer, $this->period),
            );
        }

        $this->assertSame(
            $this->service->summaryFor($this->agencyViewer($a), $this->period)->verificationCount,
            $this->service->verificationCountFor($this->agencyViewer($a)),
            'a null period argument must mean the default period',
        );
    }

    /** TC-020 / AC-036 — a trashed parent leaves the assessment queue in BOTH viewer states. */
    public function test_assessment_population_excludes_assessments_with_a_soft_deleted_parent(): void
    {
        [$a] = $this->buildCanonicalFixture();

        $this->assertSame(5, Assessment::query()->pending()->count(), 'F-8 is pending but its parent is trashed');
        $this->assertSame(4, $this->service->summaryFor($this->superAdmin(), $this->period)->assessmentCount);
        $this->assertSame(3, $this->service->summaryFor($this->agencyViewer($a), $this->period)->assessmentCount);
    }

    /** TC-021 — a soft-deleted PerformanceData row leaves all three queues. */
    public function test_trashed_parent_data_leaves_all_three_queues(): void
    {
        [$a] = $this->buildCanonicalFixture();

        $this->assertNull(
            PerformanceData::query()->where('instansi_id', $a->id)->where('period', '2026-04')->first(),
        );

        $summary = $this->service->summaryFor($this->agencyViewer($a), $this->period);

        $this->assertSame(2, $summary->verificationCount, 'the trashed row must not be counted');
        $this->assertSame(3, $summary->assessmentCount, 'its child assessment must not be counted either');
        $this->assertSame(1, $summary->reportCount, 'it owns no report');
    }

    /** TC-022 / A1 + D9 — the assessment and report figures never move with the period. */
    public function test_assessment_and_report_counts_are_period_invariant(): void
    {
        [$a] = $this->buildCanonicalFixture();
        $viewer = $this->agencyViewer($a);

        $counts = [];

        foreach (['current_year', 'last_quarter', 'current_month'] as $key) {
            $summary = $this->service->summaryFor($viewer, ReportingPeriod::fromKey($key, $this->now));
            $counts[$key] = [$summary->assessmentCount, $summary->reportCount];
        }

        $this->assertSame([3, 1], $counts['current_year']);
        $this->assertSame($counts['current_year'], $counts['last_quarter']);
        $this->assertSame($counts['current_year'], $counts['current_month']);
    }

    /** TC-023 / A7 — a soft-deleted agency still resolves its name and keeps the counts bound. */
    public function test_agency_label_resolves_for_a_soft_deleted_instansi_and_counts_stay_bound(): void
    {
        [$a] = $this->buildCanonicalFixture();
        $viewer = $this->agencyViewer($a);

        $a->delete();

        $summary = $this->service->summaryFor($viewer, $this->period);

        $this->assertSame(TriageScope::Agency, $summary->scope);
        $this->assertSame('Dinas A', $summary->scopeLabel);
        $this->assertSame(2, $summary->verificationCount);
    }

    /** TC-024 / A7 — when the agency name cannot resolve at all, the viewer becomes Unassigned. */
    public function test_label_falls_back_to_unassigned_when_the_agency_name_cannot_be_resolved(): void
    {
        [$a] = $this->buildCanonicalFixture();
        $viewer = $this->agencyViewer($a);

        $a->forceDelete();
        $viewer->refresh();

        $summary = $this->service->summaryFor($viewer, $this->period);

        $this->assertSame(TriageScope::Unassigned, $summary->scope);
        $this->assertSame('Instansi Belum Ditetapkan', $summary->scopeLabel);
        $this->assertSame(0, $summary->verificationCount);
    }

    /** TC-025 / §4.1 + §4.3 — every figure URL is an absolute URL of a registered named route. */
    public function test_summary_urls_are_absolute_urls_of_registered_named_routes(): void
    {
        [$a] = $this->buildCanonicalFixture();
        $viewer = $this->agencyViewer($a);

        $yearly = $this->service->summaryFor($viewer, $this->period);

        $this->assertSame(
            route('sakip.data-collection.index', ['validation_status' => 'submitted']),
            $yearly->verificationUrl,
        );
        $this->assertSame(route('sakip.assessments.index', ['status' => 'pending']), $yearly->assessmentUrl);
        $this->assertSame(route('sakip.reports.index', ['status' => 'submitted']), $yearly->reportUrl);

        $monthly = $this->service->summaryFor($viewer, ReportingPeriod::fromKey('current_month', $this->now));

        $this->assertSame(
            route('sakip.data-collection.index', ['validation_status' => 'submitted', 'period' => '2026-09']),
            $monthly->verificationUrl,
            'a single-month selection is the only case that can address the period exactly',
        );
        $this->assertStringNotContainsString('period=', $monthly->assessmentUrl);
        $this->assertStringNotContainsString('period=', $monthly->reportUrl);

        foreach ([$yearly->verificationUrl, $yearly->assessmentUrl, $yearly->reportUrl] as $url) {
            $this->assertStringStartsWith('http', $url);
        }
    }

    /** TC-026 / §4.1 — the period label mirrors the period object and no label is ever empty. */
    public function test_period_label_matches_the_period_object_and_scope_label_is_never_empty(): void
    {
        [$a] = $this->buildCanonicalFixture();
        $viewer = $this->agencyViewer($a);

        foreach (ReportingPeriod::KEYS as $key) {
            $period = ReportingPeriod::fromKey($key, $this->now);
            $summary = $this->service->summaryFor($viewer, $period);

            $this->assertSame($period->key, $summary->period->key, "period key of {$key}");
            $this->assertSame($period->label(), $summary->periodLabel, "period label of {$key}");
            $this->assertNotSame('', $summary->scopeLabel, "scope label of {$key}");
        }
    }

    /** TC-027 / CONSTRAINTS §3 rule 7 — the read model issues SELECTs only, so it writes nothing. */
    public function test_summary_performs_read_only_queries(): void
    {
        [$a] = $this->buildCanonicalFixture();
        $viewer = $this->agencyViewer($a);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->service->summaryFor($viewer, $this->period);

        $statements = collect(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertGreaterThan(0, $statements->count(), 'the service must have queried something');

        foreach ($statements as $statement) {
            $this->assertStringStartsWith('select', strtolower(ltrim($statement['query'])));
        }
    }

    /** Builds the §5.0 matrix. @return array{0: Instansi, 1: Instansi} */
    private function buildCanonicalFixture(): array
    {
        $a = Instansi::factory()->create(['nama_instansi' => 'Dinas A']);
        $b = Instansi::factory()->create(['nama_instansi' => 'Dinas B']);

        // performance_data carries a unique (indicator, instansi, period) index, so rows sharing a
        // period must still differ by indicator — the §5.0 matrix never constrains the indicator.
        for ($row = 0; $row < 2; $row++) {
            PerformanceData::factory()->submitted()->forInstansi($a->id)->forPeriod('2026-03')->create([
                'performance_indicator_id' => $this->newIndicatorFor($a),
            ]);
        }

        for ($row = 0; $row < 3; $row++) {
            PerformanceData::factory()->submitted()->forInstansi($b->id)->forPeriod('2026-05')->create([
                'performance_indicator_id' => $this->newIndicatorFor($b),
            ]);
        }

        PerformanceData::factory()->submitted()->forInstansi($a->id)->forPeriod('2025-03')->create([
            'performance_indicator_id' => $this->newIndicatorFor($a),
        ]);

        $trashedParent = PerformanceData::factory()->submitted()->forInstansi($a->id)->forPeriod('2026-04')->create([
            'performance_indicator_id' => $this->newIndicatorFor($a),
        ]);
        Assessment::factory()->pending()->forPerformanceData($trashedParent->id)->create();
        $trashedParent->delete();

        foreach ($this->livePerformanceDataOf($a) as $row) {
            Assessment::factory()->pending()->forPerformanceData($row->id)->create();
        }

        Assessment::factory()->pending()
            ->forPerformanceData($this->livePerformanceDataOf($b)->first()->id)
            ->create();

        // ReportFactory writes a `metadata` attribute the reports table does not have, so this fixture
        // follows the working house idiom of tests/Feature/ReportIndexRendersTest.php instead.
        $author = User::factory()->create(['instansi_id' => $a->id]);

        $this->newReportFor($a, 'submitted', $author->id);
        $this->newReportFor($a, 'pending', $author->id);
        $this->newReportFor($b, 'submitted', $author->id);
        $this->newReportFor($b, 'submitted', $author->id);

        return [$a, $b];
    }

    private function newIndicatorFor(Instansi $agency): string
    {
        return \App\Models\PerformanceIndicator::factory()->create(['instansi_id' => $agency->id])->id;
    }

    private function newReportFor(Instansi $agency, string $status, string $authorId): Report
    {
        return Report::forceCreate([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'instansi_id' => $agency->id,
            'generated_by' => $authorId,
            'report_type' => 'quarterly_report',
            'period' => '2026-Q3',
            'status' => $status,
            'generated_at' => now(),
        ]);
    }

    /** @return \Illuminate\Support\Collection<int, PerformanceData> */
    private function livePerformanceDataOf(Instansi $agency): \Illuminate\Support\Collection
    {
        return PerformanceData::query()->where('instansi_id', $agency->id)->get();
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::firstOrCreate(
            ['name' => SystemRoles::SUPER_ADMIN],
            ['display_name' => SystemRoles::SUPER_ADMIN],
        ));

        return $user;
    }

    private function agencyViewer(Instansi $agency): User
    {
        return User::factory()->create(['instansi_id' => $agency->id]);
    }

    private function unassignedViewer(): User
    {
        return User::factory()->create(['instansi_id' => null]);
    }
}
