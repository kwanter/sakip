<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Seam S4 — the sidebar queue badge on `layouts.modern` (Spec v1.3 §4.5, §5.5).
 *
 * The viewer is the `Super Admin` pre-agreed in §6.1: it satisfies both `admin.dashboard` and
 * `sakip.dashboard.view` through `Gate::before`, so one viewer can render both pages.
 *
 * Cases: TC-057, TC-058, TC-059.
 */
class SidebarQueueBadgeTest extends TestCase
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

    /** TC-057 / AC-023 — two controllers, two pages, one badge value equal to the landing figure. */
    public function test_badge_renders_on_two_layouts_modern_pages_with_the_landing_figure_value(): void
    {
        $this->agencyWithSubmittedData(['2026-03', '2026-05']);

        $viewer = $this->superAdminViewer();

        $landing = $this->actingAs($viewer)->get(route('admin.dashboard'))->getContent();
        $sakipDashboard = $this->actingAs($viewer)->get(route('sakip.dashboard'))->getContent();

        $figure = $this->verificationFigure($landing);
        $this->assertSame('2', $figure, 'the landing figure counts the two in-period rows');

        $this->assertStringContainsString('sidebar-link-badge', $landing);
        $this->assertSame($figure, $this->badgeValue($landing));

        $this->assertStringContainsString('sidebar-link-badge', $sakipDashboard, 'the other page renders the badge too');
        $this->assertSame($figure, $this->badgeValue($sakipDashboard), 'both pages agree on the value');
    }

    /** TC-058 / AC-024 — an empty queue renders no badge element at all (never a zero badge). */
    public function test_no_badge_is_rendered_when_the_queue_is_empty(): void
    {
        $viewer = $this->superAdminViewer();

        foreach ([route('admin.dashboard'), route('sakip.dashboard')] as $url) {
            $content = $this->actingAs($viewer)->get($url)->getContent();

            $this->assertStringNotContainsString('sidebar-link-badge', $content, "no badge on {$url}");
        }
    }

    /** TC-059 / AC-026 — the badge follows the requested period and defaults without one. */
    public function test_badge_follows_the_request_period_and_defaults_without_one(): void
    {
        $this->agencyWithSubmittedData(['2026-03', '2026-09']);

        $viewer = $this->superAdminViewer();

        $default = $this->actingAs($viewer)->get(route('sakip.dashboard'))->getContent();
        $monthly = $this->actingAs($viewer)->get(route('sakip.dashboard', ['period' => 'current_month']))->getContent();

        $this->assertSame('2', $this->badgeValue($default), 'no parameter means the default period (whole year)');
        $this->assertSame('1', $this->badgeValue($monthly), 'the requested month scopes the badge');
    }

    /** @param list<string> $periods */
    private function agencyWithSubmittedData(array $periods): void
    {
        $agency = \App\Models\Instansi::factory()->create(['nama_instansi' => 'Dinas A']);

        foreach ($periods as $period) {
            \App\Models\PerformanceData::factory()->submitted()->forInstansi($agency->id)->forPeriod($period)->create([
                'performance_indicator_id' => \App\Models\PerformanceIndicator::factory()->create(['instansi_id' => $agency->id])->id,
            ]);
        }
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

    private function badgeValue(string $content): string
    {
        preg_match('/sidebar-link-badge">\s*([^<]*?)\s*</s', $content, $matches);

        return trim($matches[1] ?? '');
    }

    private function verificationFigure(string $content): string
    {
        preg_match('/<a[^>]*data-triage-figure="verification"[^>]*>(.*?)<\/a>/s', $content, $anchor);
        preg_match('/stat-value">([^<]*)</', $anchor[1] ?? '', $value);

        return trim($value[1] ?? '');
    }
}
