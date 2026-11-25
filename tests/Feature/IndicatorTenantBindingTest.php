<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Kegiatan;
use App\Models\Permission;
use App\Models\Program;
use App\Models\SasaranStrategis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (audit run-3, master-data-cross-tenant): StorePerformanceIndicatorRequest
 * accepted any sasaran_strategis/program/kegiatan id, so an indicator of one
 * institution could be hung under another institution's plan. The category
 * vocabulary also diverged between the store and update requests, which made
 * every update fail validation.
 */
class IndicatorTenantBindingTest extends TestCase
{
    use RefreshDatabase;

    private Instansi $own;

    private Instansi $foreign;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['create performance indicators', 'update performance indicators', 'view performance indicators'] as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $this->own = Instansi::factory()->create();
        $this->foreign = Instansi::factory()->create();

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->own->id,
        ]);
        $this->user->givePermissionTo(['create performance indicators', 'update performance indicators', 'view performance indicators']);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'instansi_id' => $this->own->id,
            'code' => 'IK-'.str()->random(6),
            'name' => 'Indikator uji',
            'measurement_unit' => 'Persen',
            'measurement_type' => 'percentage',
            'data_source' => 'Internal',
            'collection_method' => 'manual',
            'frequency' => 'monthly',
            'category' => 'output',
            'weight' => 50,
            'targets' => [[
                'year' => (int) date('Y'),
                'target_value' => 100,
            ]],
        ], $overrides);
    }

    #[Test]
    public function a_foreign_sasaran_strategis_is_rejected(): void
    {
        // No factory exists for SasaranStrategis; build the row directly.
        $foreign = SasaranStrategis::create([
            'instansi_id' => $this->foreign->id,
            'kode_sasaran_strategis' => 'SS-'.str()->random(6),
            'nama_strategis' => 'Sasaran milik instansi lain',
            'status' => 'aktif',
        ]);

        $this->actingAs($this->user)
            ->post(route('sakip.indicators.store'), $this->payload([
                'sasaran_strategis_id' => $foreign->id,
            ]))
            ->assertSessionHasErrors('sasaran_strategis_id');
    }

    #[Test]
    public function a_foreign_program_is_rejected(): void
    {
        $foreign = Program::factory()->create(['instansi_id' => $this->foreign->id]);

        $this->actingAs($this->user)
            ->post(route('sakip.indicators.store'), $this->payload([
                'program_id' => $foreign->id,
            ]))
            ->assertSessionHasErrors('program_id');
    }

    #[Test]
    public function an_indicator_cannot_be_created_for_another_instansi(): void
    {
        $this->actingAs($this->user)
            ->post(route('sakip.indicators.store'), $this->payload([
                'instansi_id' => $this->foreign->id,
            ]))
            ->assertSessionHasErrors('instansi_id');
    }

    #[Test]
    public function the_stored_category_vocabulary_is_accepted_on_update(): void
    {
        $indicator = \App\Models\PerformanceIndicator::factory()->create([
            'instansi_id' => $this->own->id,
            'category' => 'outcome',
        ]);

        $this->actingAs($this->user)
            ->put(route('sakip.indicators.update', $indicator), [
                'instansi_id' => $this->own->id,
                'code' => $indicator->code,
                'name' => 'Indikator diperbarui',
                'measurement_unit' => 'Persen',
                'data_source' => 'Internal',
                'collection_method' => 'manual',
                'frequency' => 'monthly',
                'category' => 'outcome',
                'weight' => 60,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('outcome', $indicator->fresh()->category);
    }
}
