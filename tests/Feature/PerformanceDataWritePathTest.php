<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Permission;
use App\Models\Target;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (audit run-3, perfdata-write-path): the data-collection write
 * path was dead. store() referenced undefined variables ($targetValue,
 * $performancePercentage), wrote to columns that do not exist
 * (indicator_id, target_value, performance_percentage), never stamped
 * instansi_id (NOT NULL), and authorized against a 'createData' ability
 * that no policy defined. getTargetForPeriod() filtered targets on a
 * 'period' column the targets table never had. Every submission was a 500,
 * so no tenant stamp was ever exercised.
 */
class PerformanceDataWritePathTest extends TestCase
{
    use RefreshDatabase;

    private Instansi $instansi;

    private User $user;

    private PerformanceIndicator $indicator;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['enter-and-submit-data-records', 'edit-own-data-submissions', 'view-performance-data'] as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $this->instansi = Instansi::factory()->create(['kode_instansi' => str()->random(8)]);
        $this->user = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansi->id,
        ]);
        $this->user->givePermissionTo(['enter-and-submit-data-records', 'edit-own-data-submissions', 'view-performance-data']);

        $this->indicator = PerformanceIndicator::factory()->create([
            'instansi_id' => $this->instansi->id,
            'frequency' => 'monthly',
        ]);
    }

    #[Test]
    public function store_persists_a_tenant_stamped_draft(): void
    {
        Target::factory()->create([
            'performance_indicator_id' => $this->indicator->id,
            'year' => (int) date('Y'),
            'target_value' => 100,
        ]);

        $this->actingAs($this->user)
            ->post(route('sakip.data-collection.store'), [
                'indicator_id' => $this->indicator->id,
                'period' => date('Y').'-01-01',
                'actual_value' => 75,
                'notes' => 'Januari',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $row = PerformanceData::firstOrFail();
        $this->assertSame($this->indicator->id, $row->performance_indicator_id);
        $this->assertSame($this->instansi->id, $row->instansi_id);
        $this->assertSame($this->user->id, $row->submitted_by);
        $this->assertSame('draft', $row->status);
        $this->assertSame(date('Y').'-01-01', $row->period);
        $this->assertSame(75.0, (float) $row->actual_value);
        $this->assertSame(1, Target::count());
    }

    #[Test]
    public function store_refuses_an_indicator_from_another_instansi(): void
    {
        $foreign = PerformanceIndicator::factory()->create([
            'instansi_id' => Instansi::factory()->create(['kode_instansi' => str()->random(8)])->id,
            'frequency' => 'monthly',
        ]);

        // InstansiScope hides the foreign indicator entirely, so the bind
        // never resolves: a 404, not a 403, and no existence leak. That is
        // the tighter contract; the policy check is the belt to its braces.
        $this->actingAs($this->user)
            ->post(route('sakip.data-collection.store'), [
                'indicator_id' => $foreign->id,
                'period' => date('Y').'-01-01',
                'actual_value' => 75,
            ])
            ->assertNotFound();

        $this->assertSame(0, PerformanceData::withoutGlobalScopes()->count());
    }

    #[Test]
    public function store_rejects_a_second_submission_for_the_same_period(): void
    {
        PerformanceData::factory()->create([
            'performance_indicator_id' => $this->indicator->id,
            'instansi_id' => $this->instansi->id,
            'period' => date('Y').'-01-01',
        ]);

        $this->actingAs($this->user)
            ->post(route('sakip.data-collection.store'), [
                'indicator_id' => $this->indicator->id,
                'period' => date('Y').'-01-01',
                'actual_value' => 75,
            ])
            ->assertStatus(409);

        $this->assertSame(1, PerformanceData::count());
    }

    #[Test]
    public function update_writes_back_without_a_500(): void
    {
        $row = PerformanceData::factory()->create([
            'performance_indicator_id' => $this->indicator->id,
            'instansi_id' => $this->instansi->id,
            'created_by' => $this->user->id,
            'submitted_by' => $this->user->id,
            'period' => date('Y').'-01-01',
            'actual_value' => 10,
        ]);

        $this->actingAs($this->user)
            ->put(route('sakip.data-collection.update', $row), [
                'actual_value' => 42,
                'notes' => 'koreksi',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $row->refresh();
        $this->assertSame(42.0, (float) $row->actual_value);
        $this->assertSame('koreksi', $row->notes);
        $this->assertSame($this->user->id, $row->updated_by);
    }
}
