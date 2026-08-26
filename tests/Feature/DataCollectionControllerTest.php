<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Instansi;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Characterization tests for DataCollectionController.
 *
 * These capture current behavior before refactoring the controller.
 * They are NOT ideal tests — they document what exists so extraction
 * preserves it.
 */
class DataCollectionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Instansi $instansi;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instansi = Instansi::factory()->create([
            'kode_instansi' => str()->random(8),
            'nama_instansi' => 'Test Instansi',
        ]);

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansi->id,
        ]);

        Permission::firstOrCreate(['name' => 'view-performance-data']);
        Permission::firstOrCreate(['name' => 'enter-and-submit-data-records']);
        $this->user->givePermissionTo('view-performance-data');
        $this->user->givePermissionTo('enter-and-submit-data-records');
    }

    /** @test */
    public function index_returns_200()
    {
        $this->actingAs($this->user);
        $response = $this->get(route('sakip.data-collection.index'));

        $response->assertOk();
    }

    /** @test */
    public function create_returns_200()
    {
        $this->actingAs($this->user);
        $response = $this->get(route('sakip.data-collection.create'));

        $response->assertOk();
    }
}