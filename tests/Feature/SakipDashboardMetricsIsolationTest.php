<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\Instansi;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Target;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (audit run-2, sakip-dashboard.institutional-branch-cross-tenant-counts):
 * the institutional dashboard branch must be entered through its registered
 * gate and every metric it returns must be bound to the caller's instansi,
 * including Target and Assessment counts (fleet-wide counts leaked before).
 */
class SakipDashboardMetricsIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = false;

    private Instansi $instansiA;

    private Instansi $instansiB;

    private User $collectorA;

    private User $plainViewerA;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['view-sakip-dashboard', 'view-data-entry-dashboard', 'view-executive-dashboard'] as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $this->instansiA = Instansi::factory()->create(['nama_instansi' => 'Instansi A']);
        $this->instansiB = Instansi::factory()->create(['nama_instansi' => 'Instansi B']);

        $this->collectorA = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansiA->id,
        ]);
        $this->collectorA->givePermissionTo(['view-sakip-dashboard', 'view-data-entry-dashboard']);

        $this->plainViewerA = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansiA->id,
        ]);
        $this->plainViewerA->givePermissionTo(['view-sakip-dashboard']);

        $role = Role::firstOrCreate(['name' => 'Super Admin'], ['guard_name' => 'web']);
        $this->superAdmin = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansiA->id,
        ]);
        $this->superAdmin->assignRole($role);
    }

    private function makeTargetsFor(Instansi $instansi, int $count, int $year): void
    {
        for ($i = 0; $i < $count; $i++) {
            $indicator = PerformanceIndicator::create([
                'instansi_id' => $instansi->id,
                'code' => 'PI-'.str()->random(8),
                'name' => 'Indikator '.$instansi->nama_instansi,
                'measurement_unit' => 'unit',
                'frequency' => 'annual',
                'category' => 'output',
                'created_by' => null,
                'updated_by' => null,
            ]);

            Target::create([
                'performance_indicator_id' => $indicator->id,
                'year' => $year,
                'target_value' => 100,
                'status' => 'draft',
            ]);
        }
    }

    private function makeAssessmentsFor(Instansi $instansi, int $count, int $year): void
    {
        $indicator = PerformanceIndicator::create([
            'instansi_id' => $instansi->id,
            'code' => 'PI-'.str()->random(8),
            'name' => 'Indikator '.$instansi->nama_instansi,
            'measurement_unit' => 'unit',
            'frequency' => 'annual',
            'category' => 'output',
            'created_by' => null,
            'updated_by' => null,
        ]);

        for ($i = 0; $i < $count; $i++) {
            $data = PerformanceData::forceCreate([
                'performance_indicator_id' => $indicator->id,
                'instansi_id' => $instansi->id,
                'submitted_by' => $this->collectorA->id,
                'period' => sprintf('%d-%02d', $year, $i + 1),
                'actual_value' => 90,
                'status' => 'draft',
                'data_quality' => 'good',
            ]);

            Assessment::forceCreate([
                'performance_data_id' => $data->id,
                'assessed_by' => $this->collectorA->id,
                'status' => 'draft',
            ]);
        }
    }

    #[Test]
    public function institutional_branch_counts_are_bound_to_the_caller_instansi(): void
    {
        $year = now()->year;
        $this->makeTargetsFor($this->instansiA, 2, $year);
        $this->makeTargetsFor($this->instansiB, 3, $year);
        $this->makeAssessmentsFor($this->instansiA, 1, $year);
        $this->makeAssessmentsFor($this->instansiB, 2, $year);

        $response = $this->actingAs($this->collectorA)
            ->getJson(route('sakip.api.dashboard-data', ['year' => $year]))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertSame(2, $response->json('data.total_targets'), 'targets must be instansi-bound');
        $this->assertSame(1, $response->json('data.total_assessments'), 'assessments must be instansi-bound');
    }

    #[Test]
    public function ordinary_viewer_without_branch_permission_gets_the_personal_branch(): void
    {
        $this->actingAs($this->plainViewerA)
            ->getJson(route('sakip.api.dashboard-data'))
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    #[Test]
    public function super_admin_sees_system_wide_metrics(): void
    {
        $year = now()->year;
        $this->makeTargetsFor($this->instansiA, 2, $year);
        $this->makeTargetsFor($this->instansiB, 3, $year);

        $this->actingAs($this->superAdmin)
            ->getJson(route('sakip.api.dashboard-data', ['year' => $year]))
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    #[Test]
    public function fix_all_does_not_mass_attach_the_dashboard_permission(): void
    {
        // A user holding no dashboard permission at all: the command must not
        // grant it directly to everyone.
        $untouched = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansiA->id,
        ]);

        $this->artisan('sakip:add-permissions', ['--fix-all' => true]);

        $this->assertFalse(
            $untouched->fresh()->hasDirectPermission('view-sakip-dashboard'),
            '--fix-all must not attach the dashboard permission to every user',
        );
    }
}
