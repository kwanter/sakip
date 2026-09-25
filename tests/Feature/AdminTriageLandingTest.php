<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Seam S3 — `GET /admin/dashboard` (Spec v1.3 §4.3, §4.4, §4.5).
 *
 * Cases: TC-028 … TC-032, TC-035 … TC-037, TC-042, TC-043, TC-051.
 */
class AdminTriageLandingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-09-24 10:15:00'));
        Carbon::setTestNow(Carbon::parse('2026-09-24 10:15:00'));
    }

    protected function tearDown(): void
    {
        config()->set('sakip.reporting.active_year', null);
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        parent::tearDown();
    }

    /**
     * TC-035 / AC-015 — the legitimate RED for finding C1: a verified holder of `admin.dashboard`
     * that is NOT a Super Admin must reach the landing (today the controller middleware answers 403).
     */
    public function test_permitted_non_super_admin_reaches_the_landing_with_200(): void
    {
        $response = $this->actingAs($this->permittedViewer())->get(route('admin.dashboard'));

        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('data-triage-figure="verification"', $content);
        $this->assertStringContainsString('Antrean Verifikasi', $content);
        $this->assertStringContainsString('Antrean Asesmen', $content);
        $this->assertStringContainsString('Antrean Laporan', $content);
    }

    /** TC-036 / AC-016 — a viewer without the permission is forbidden and leaks no triage copy. */
    public function test_viewer_without_the_permission_is_forbidden_and_leaks_no_triage_copy(): void
    {
        $viewer = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($viewer)->get(route('admin.dashboard'));

        $response->assertForbidden();

        $content = $response->getContent();
        $this->assertStringNotContainsString('Antrean Verifikasi', $content);
        $this->assertStringNotContainsString('Semua Instansi', $content);
        $this->assertStringNotContainsString('data-triage-region', $content);
    }

    /** TC-037 / AC-017 — the permitted viewer is served directly, never redirected. */
    public function test_permitted_viewer_is_not_redirected(): void
    {
        $response = $this->actingAs($this->permittedViewer())->get(route('admin.dashboard'));

        $this->assertFalse($response->isRedirect(), 'the landing must render, not bounce elsewhere');
        $response->assertOk();
    }

    /** TC-028 / AC-005 — the default request renders the current year and every basis label. */
    public function test_default_request_renders_current_year_and_every_basis_label(): void
    {
        $content = $this->actingAs($this->permittedViewer())
            ->get(route('admin.dashboard'))
            ->getContent();

        $this->assertStringContainsString('data-triage-period="current_year"', $content);
        $this->assertStringContainsString('data-triage-period-label', $content);
        $this->assertStringContainsString('Tahun 2026', $content);
        $this->assertStringContainsString('Tidak dibatasi periode', $content);
    }

    /** TC-029 / AC-006 — an unknown period falls back to the default without an error page. */
    public function test_invalid_period_value_falls_back_to_default_without_an_error_page(): void
    {
        $response = $this->actingAs($this->permittedViewer())
            ->get(route('admin.dashboard', ['period' => 'not-a-key']));

        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('data-triage-period="current_year"', $content);
        $this->assertStringContainsString('Tahun 2026', $content);
        $this->assertStringContainsString('Tidak dibatasi periode', $content);
    }

    /** TC-030 / AC-007 — an array-shaped `period` is rejected and the default is used (SEC-003). */
    public function test_array_period_input_is_rejected_and_falls_back_to_default(): void
    {
        $response = $this->actingAs($this->permittedViewer())
            ->get(route('admin.dashboard').'?period[]=current_month');

        $response->assertOk();
        $this->assertStringContainsString('data-triage-period="current_year"', $response->getContent());
    }

    /** TC-031 / SEC-003 — a hostile period value never reaches the page unvalidated. */
    public function test_hostile_period_value_falls_back_to_default_and_is_never_reflected(): void
    {
        $payload = 'current_year\'--"<b>boom</b>';

        $response = $this->actingAs($this->permittedViewer())
            ->get(route('admin.dashboard').'?period='.urlencode($payload));

        $response->assertOk();

        $content = $response->getContent();
        $this->assertStringContainsString('data-triage-period="current_year"', $content);
        $this->assertStringNotContainsString('boom', $content);
    }

    /** TC-032 / AC-008 — re-requesting the same URL yields the same period and the same figures. */
    public function test_repeated_request_for_the_same_period_is_reproducible(): void
    {
        $viewer = $this->permittedViewer();
        $url = route('admin.dashboard', ['period' => 'last_quarter']);

        $first = $this->actingAs($viewer)->get($url)->getContent();
        $second = $this->actingAs($viewer)->get($url)->getContent();

        $this->assertSame($first, $second, 'the same URL must render the same page');
        $this->assertStringContainsString('data-triage-period="last_quarter"', $first);
        $this->assertStringContainsString('Triwulan II 2026', $first);
    }

    /** TC-042 / §4.4 — the region carries its three attributes and each figure handle appears once. */
    public function test_triage_region_and_figure_handles_render_exactly_once_each(): void
    {
        $content = $this->actingAs($this->permittedViewer())
            ->get(route('admin.dashboard'))
            ->getContent();

        $this->assertSame(1, substr_count($content, 'data-triage-region'), 'one triage region');
        $this->assertSame(1, substr_count($content, 'data-triage-period="'), 'one period attribute');
        $this->assertSame(1, substr_count($content, 'data-triage-scope="'), 'one scope attribute');
        $this->assertSame(1, substr_count($content, 'data-triage-period-label'), 'one period label handle');

        foreach (['verification', 'assessment', 'report'] as $handle) {
            $this->assertSame(1, substr_count($content, 'data-triage-figure="'.$handle.'"'), "handle {$handle}");
        }
    }

    /** TC-043 / AC-029 — each figure name is rendered once inside its own anchor, with its basis. */
    public function test_each_figure_name_renders_once_in_its_own_anchor_with_its_basis_label(): void
    {
        $content = $this->actingAs($this->permittedViewer())
            ->get(route('admin.dashboard'))
            ->getContent();

        $expected = [
            'verification' => ['Antrean Verifikasi', 'Tahun 2026'],
            'assessment' => ['Antrean Asesmen', 'Tidak dibatasi periode'],
            'report' => ['Antrean Laporan', 'Tidak dibatasi periode'],
        ];

        foreach ($expected as $handle => [$name, $basis]) {
            $this->assertSame(1, substr_count($content, $name), "the name of {$handle} renders once");
            $this->assertSame(
                1,
                preg_match('/<a[^>]*data-triage-figure="'.$handle.'"[^>]*>(.*?)<\/a>/s', $content, $matches),
                "anchor {$handle} is extractable",
            );
            $this->assertStringContainsString($name, $matches[1], "name inside {$handle}");
            $this->assertStringContainsString($basis, $matches[1], "basis inside {$handle}");
        }
    }

    /** TC-051 / CON-004 — the selector is a GET form and carries no inline event handler. */
    public function test_selector_is_a_get_form_without_inline_event_handlers(): void
    {
        $content = $this->actingAs($this->permittedViewer())
            ->get(route('admin.dashboard'))
            ->getContent();

        $this->assertStringContainsString('Periode Pelaporan', $content);
        $this->assertStringContainsString('Terapkan', $content);
        $this->assertMatchesRegularExpression('/<form[^>]*method="GET"[^>]*>/i', $content);
        $this->assertStringNotContainsString('onchange=', $content);
        $this->assertStringNotContainsString('onsubmit=', $content);
    }

    /** A verified holder of `admin.dashboard` that is deliberately NOT a Super Admin (finding C1). */
    private function permittedViewer(): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'instansi_id' => null]);

        $permission = Permission::firstOrCreate(
            ['name' => 'admin.dashboard'],
            ['display_name' => 'admin.dashboard'],
        );

        $user->givePermissionTo($permission);

        return $user->refresh();
    }

    /** TC-033 / AC-013 — each viewer state renders its own scope handle and label. */
    public function test_scope_handle_and_label_match_the_viewer_state(): void
    {
        $agency = \App\Models\Instansi::factory()->create(['nama_instansi' => 'Dinas A']);

        $agencyContent = $this->actingAs($this->permittedViewerFor($agency))
            ->get(route('admin.dashboard'))
            ->getContent();

        $this->assertStringContainsString('data-triage-scope="agency"', $agencyContent);
        $this->assertStringContainsString('data-triage-scope-label', $agencyContent);
        $this->assertStringContainsString('Cakupan: Dinas A', $agencyContent);

        $crossContent = $this->actingAs($this->superAdminViewer())
            ->get(route('admin.dashboard'))
            ->getContent();

        $this->assertStringContainsString('data-triage-scope="cross_agency"', $crossContent);
        $this->assertStringContainsString('Cakupan: Semua Instansi', $crossContent);
    }

    /** TC-034 / AC-014 — an unassigned viewer sees its own handle, three zeros, no `Semua Instansi`. */
    public function test_unassigned_viewer_renders_its_handle_with_zero_figures(): void
    {
        $content = $this->actingAs($this->permittedViewer())
            ->get(route('admin.dashboard'))
            ->getContent();

        preg_match('/<section[^>]*data-triage-region[^>]*>(.*?)<\/section>/s', $content, $region);

        $this->assertStringContainsString('data-triage-scope="unassigned"', $content);
        $this->assertStringContainsString('Cakupan: Instansi Belum Ditetapkan', $content);
        $this->assertStringNotContainsString('Semua Instansi', $content);
        $this->assertSame(3, substr_count($region[1] ?? '', 'stat-value">0<'), 'three zero-valued figures');
    }

    /** TC-044 / AC-027 — the period-independent anchors never claim the selected period. */
    public function test_assessment_and_report_anchors_never_claim_the_selected_period(): void
    {
        $content = $this->actingAs($this->permittedViewer())
            ->get(route('admin.dashboard', ['period' => 'last_quarter']))
            ->getContent();

        foreach (['assessment', 'report'] as $handle) {
            preg_match('/<a[^>]*data-triage-figure="'.$handle.'"[^>]*>(.*?)<\/a>/s', $content, $matches);

            $this->assertNotSame('', $matches[1] ?? '', "anchor {$handle} is extractable");
            $this->assertStringContainsString('Tidak dibatasi periode', $matches[1], "basis of {$handle}");
            $this->assertStringNotContainsString('Triwulan II 2026', $matches[1], "period label leaked into {$handle}");
        }

        $this->assertStringContainsString('Triwulan II 2026', $content, 'the verification figure states its period');
    }

    /** TC-049 / AC-033 — the removed telemetry and inventory copy is gone from the region. */
    public function test_removed_telemetry_and_inventory_copy_is_absent_from_the_triage_region(): void
    {
        $content = $this->actingAs($this->permittedViewer())
            ->get(route('admin.dashboard'))
            ->getContent();

        preg_match('/<section[^>]*data-triage-region[^>]*>(.*?)<\/section>/s', $content, $region);

        $this->assertNotSame('', $region[1] ?? '', 'the triage region must be extractable');
        $this->assertStringNotContainsString('Aktivitas Login (7 Hari)', $region[1]);
        $this->assertStringNotContainsString('Tervalidasi', $region[1]);
    }

    /** TC-050 / finding C-1 — the layout shell keeps its own copy, outside the asserted region. */
    public function test_layout_shell_copy_is_untouched_and_sits_outside_the_region(): void
    {
        // A Super Admin also satisfies `@can('manage-sakip')`, so the shell's own sidebar section renders.
        $content = $this->actingAs($this->superAdminViewer())
            ->get(route('admin.dashboard'))
            ->getContent();

        preg_match('/<section[^>]*data-triage-region[^>]*>(.*?)<\/section>/s', $content, $region);

        $this->assertStringContainsString('Indikator Kinerja', $content, 'the shell still renders its own label');
        $this->assertStringNotContainsString('Indikator Kinerja', $region[1] ?? '');
        $this->assertStringContainsString('Indikator Kinerja', (string) file_get_contents(resource_path('views/layouts/modern.blade.php')));
    }

    private function permittedViewerFor(\App\Models\Instansi $agency): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'instansi_id' => $agency->id]);
        $user->givePermissionTo(Permission::firstOrCreate(
            ['name' => 'admin.dashboard'],
            ['display_name' => 'admin.dashboard'],
        ));

        return $user->refresh();
    }

    private function superAdminViewer(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::firstOrCreate(
            ['name' => 'admin.dashboard'],
            ['display_name' => 'admin.dashboard'],
        ));
        $user->assignRole(\App\Models\Role::firstOrCreate(
            ['name' => \App\Constants\SystemRoles::SUPER_ADMIN],
            ['display_name' => \App\Constants\SystemRoles::SUPER_ADMIN],
        ));

        return $user->refresh();
    }

    /** TC-038 / AC-018 — the verification anchor carries the period only for a single-month selection. */
    public function test_verification_anchor_carries_the_period_only_for_single_month_selection(): void
    {
        $viewer = $this->permittedViewer();

        $yearlyContent = $this->actingAs($viewer)->get(route('admin.dashboard', ['period' => 'current_year']))->getContent();
        $yearlyHref = $this->figureHref($yearlyContent, 'verification');

        $this->assertSame(
            route('sakip.data-collection.index', ['validation_status' => 'submitted']),
            $yearlyHref,
            'a year selection cannot be expressed by the target filter, so no period is sent',
        );
        $this->assertStringContainsString('validation_status=submitted', $yearlyHref);
        $this->assertStringNotContainsString('period=', $yearlyHref);

        $monthlyContent = $this->actingAs($viewer)->get(route('admin.dashboard', ['period' => 'current_month']))->getContent();
        $monthlyHref = $this->figureHref($monthlyContent, 'verification');

        $this->assertSame(
            route('sakip.data-collection.index', ['validation_status' => 'submitted', 'period' => '2026-09']),
            $monthlyHref,
            'a single-month selection is expressible exactly',
        );
    }

    /** TC-039 / AC-019 — the assessment anchor always carries its status and never a period. */
    public function test_assessment_anchor_always_carries_pending_status_and_never_a_period(): void
    {
        $this->assertAnchorHoldsForEveryPeriod('assessment', ['status' => 'pending']);
    }

    /** TC-040 / AC-020 — the report anchor always carries its status and never a period. */
    public function test_report_anchor_always_carries_submitted_status_and_never_a_period(): void
    {
        $this->assertAnchorHoldsForEveryPeriod('report', ['status' => 'submitted']);
    }

    /** TC-041 / AC-021 — every figure href resolves to a reachable page, never 404 or 500. */
    public function test_every_figure_href_resolves_to_a_reachable_page(): void
    {
        $viewer = $this->superAdminViewer();
        $content = $this->actingAs($viewer)->get(route('admin.dashboard'))->getContent();

        foreach (['verification', 'assessment', 'report'] as $handle) {
            $path = parse_url($this->figureHref($content, $handle), PHP_URL_PATH) ?: '/';
            $status = $this->get($path)->getStatusCode();

            $this->assertContains($status, [200, 302], "href of {$handle} resolved with {$status}");
        }
    }

    /** TC-056 / §4.3 — no figure emits a parameter its target does not honour. */
    public function test_no_forbidden_target_parameter_is_ever_emitted_by_any_figure(): void
    {
        $content = $this->actingAs($this->superAdminViewer())
            ->get(route('admin.dashboard', ['period' => 'current_month']))
            ->getContent();

        foreach (['verification', 'assessment', 'report'] as $handle) {
            $href = $this->figureHref($content, $handle);

            foreach (['instansi=', 'category=', 'type=', 'priority='] as $forbidden) {
                $this->assertStringNotContainsString($forbidden, $href, "{$forbidden} in the {$handle} anchor");
            }
        }
    }

    private function assertAnchorHoldsForEveryPeriod(string $handle, array $parameters): void
    {
        $viewer = $this->permittedViewer();
        $resource = $handle === 'assessment' ? 'assessments' : 'reports';
        $expected = route("sakip.{$resource}.index", $parameters);

        foreach (\App\Support\ReportingPeriod::KEYS as $key) {
            $content = $this->actingAs($viewer)->get(route('admin.dashboard', ['period' => $key]))->getContent();
            $href = $this->figureHref($content, $handle);

            $this->assertSame($expected, $href, "href of {$handle} for {$key}");
            $this->assertStringNotContainsString('period=', $href, "period leaked into {$handle} for {$key}");
        }
    }

    private function figureHref(string $content, string $handle): string
    {
        preg_match('/<a[^>]*data-triage-figure="'.$handle.'"[^>]*>/s', $content, $tag);
        preg_match('/href="([^"]*)"/', $tag[0] ?? '', $href);

        return html_entity_decode($href[1] ?? '');
    }

    /** TC-046 / AC-030 — an empty period is explained, and no empty strip is rendered. */
    public function test_empty_state_hides_the_attention_strip_and_explains_the_period(): void
    {
        $agency = \App\Models\Instansi::factory()->create(['nama_instansi' => 'Dinas Kosong']);

        $content = $this->actingAs($this->permittedViewerFor($agency))
            ->get(route('admin.dashboard'))
            ->getContent();

        $this->assertStringNotContainsString('data-triage-attention', $content);
        $this->assertStringNotContainsString('data-triage-signal', $content);
        $this->assertStringContainsString('data-triage-empty', $content);
        $this->assertStringContainsString('Belum ada pekerjaan tertunda pada periode ini.', $content);
    }

    /** TC-047 / AC-031 — one signal for the single non-empty queue, with exactly one action. */
    public function test_single_non_empty_queue_renders_exactly_one_signal_with_one_action(): void
    {
        $agency = $this->agencyWithSubmittedData(['2026-03']);

        $content = $this->actingAs($this->permittedViewerFor($agency))
            ->get(route('admin.dashboard'))
            ->getContent();

        $this->assertStringContainsString('data-triage-attention', $content);
        $this->assertSame(1, substr_count($content, 'data-triage-signal='), 'exactly one signal');
        $this->assertStringContainsString('data-triage-signal="verification"', $content);
        $this->assertSame(1, substr_count($content, 'Tinjau Antrean Verifikasi'), 'exactly one action');
        $this->assertStringNotContainsString('Tinjau Antrean Asesmen', $content);
        $this->assertStringNotContainsString('Tinjau Antrean Laporan', $content);
        $this->assertStringNotContainsString('data-triage-empty', $content);
    }

    /** TC-048 / AC-032 — switching periods toggles the strip and carries no value over. */
    public function test_switching_between_empty_and_non_empty_periods_carries_no_values_over(): void
    {
        $agency = $this->agencyWithSubmittedData(['2026-03', '2026-05']);
        $viewer = $this->permittedViewerFor($agency);

        $nonEmpty = $this->actingAs($viewer)->get(route('admin.dashboard', ['period' => 'current_year']))->getContent();
        $empty = $this->actingAs($viewer)->get(route('admin.dashboard', ['period' => 'current_month']))->getContent();

        $this->assertStringContainsString('data-triage-attention', $nonEmpty);
        $this->assertStringNotContainsString('data-triage-empty', $nonEmpty);
        $this->assertStringNotContainsString('data-triage-attention', $empty);
        $this->assertStringContainsString('data-triage-empty', $empty);

        $this->assertSame('2', $this->figureValue($nonEmpty, 'verification'));
        $this->assertSame('0', $this->figureValue($empty, 'verification'), 'no value carries over from the previous period');
    }

    /** @param list<string> $periods */
    private function agencyWithSubmittedData(array $periods): \App\Models\Instansi
    {
        $agency = \App\Models\Instansi::factory()->create(['nama_instansi' => 'Dinas A']);

        foreach ($periods as $period) {
            \App\Models\PerformanceData::factory()->submitted()->forInstansi($agency->id)->forPeriod($period)->create([
                'performance_indicator_id' => \App\Models\PerformanceIndicator::factory()->create(['instansi_id' => $agency->id])->id,
            ]);
        }

        return $agency;
    }

    private function figureValue(string $content, string $handle): string
    {
        preg_match('/<a[^>]*data-triage-figure="'.$handle.'"[^>]*>(.*?)<\/a>/s', $content, $anchor);
        preg_match('/stat-value">([^<]*)</', $anchor[1] ?? '', $value);

        return trim($value[1] ?? '');
    }
}
