<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\PerformanceIndicator;
use App\Models\Permission;
use App\Models\Target;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SECURITY: a non-super-admin must not be able to approve/reject/revise a
 * Target belonging to another institution. The indicator tenant global scope
 * must block the cross-tenant route-model binding (404).
 */
class TargetTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function makeInstansi(string $name): Instansi
    {
        return Instansi::create(['kode_instansi' => str()->random(8), 'nama_instansi' => $name]);
    }

    private function makeIndicator(Instansi $i): PerformanceIndicator
    {
        return PerformanceIndicator::create([
            'instansi_id' => $i->id,
            'code' => str()->random(10),
            'name' => 'Indikator',
            'measurement_unit' => 'unit',
            'frequency' => 'tahunan',
            'category' => 'output',
            'created_by' => null,
            'updated_by' => null,
        ]);
    }

    public function test_cannot_approve_foreign_instansi_target()
    {
        $instA = $this->makeInstansi('A');
        $instB = $this->makeInstansi('B');
        $indA = $this->makeIndicator($instA);
        $indB = $this->makeIndicator($instB);
        $targetB = Target::create(['performance_indicator_id' => $indB->id, 'year' => 2026, 'target_value' => 100, 'status' => 'draft']);

        $userA = User::factory()->create(['email_verified_at' => now(), 'instansi_id' => $instA->id]);
        $approve = Permission::firstOrCreate(['name' => 'approve-targets'], ['display_name' => 'approve-targets']);
        $userA->givePermissionTo($approve);

        $this->actingAs($userA)
            ->post(route('sakip.targets.approve', ['indicator' => $indB->id, 'target' => $targetB->id]))
            ->assertStatus(404);
    }
}
