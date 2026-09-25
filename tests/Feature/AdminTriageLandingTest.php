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
}
