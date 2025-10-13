<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Instansi;
use App\Models\Program;
use App\Models\Kegiatan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AppFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_routes_redirect_guest_to_login(): void
    {
        $this->get('/program')->assertRedirect('/login');
        $this->get('/instansi')->assertRedirect('/login');
        $this->get('/kegiatan')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_program_routes(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/program')->assertStatus(200);
    }

    public function test_policy_allows_crud_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $instansi = Instansi::factory()->create();
        $payload = [
            'instansi_id' => $instansi->id,
            'kode_program' => 'PROGTEST',
            'nama_program' => 'Program Test',
            'deskripsi' => 'Desc',
            'anggaran' => '1.000', // formatted
            'tahun_mulai' => 2024,
            'tahun_selesai' => 2025,
            'penanggung_jawab' => 'PJ',
            'status' => 'aktif',
        ];

        $this->post(route('program.store'), $payload)->assertRedirect(route('program.index'));

        $program = Program::first();
        $this->assertNotNull($program);
        $this->assertEquals(1000, (int)$program->anggaran);

        $update = $payload;
        $update['nama_program'] = 'Program Updated';
        $update['anggaran'] = '2.000';

        $this->put(route('program.update', $program), $update)->assertRedirect(route('program.index'));
        $program->refresh();
        $this->assertEquals('Program Updated', $program->nama_program);
        $this->assertEquals(2000, (int)$program->anggaran);

        $this->delete(route('program.destroy', $program))->assertRedirect(route('program.index'));
        $this->assertDatabaseMissing('programs', ['id' => $program->id]);
    }

    public function test_instansi_show_uses_eager_counts_without_n_plus_one(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $instansi = Instansi::factory()->create();
        // Create programs with varying kegiatan counts
        $programA = Program::factory()->create(['instansi_id' => $instansi->id, 'tahun' => 2024]);
        $programB = Program::factory()->create(['instansi_id' => $instansi->id, 'tahun' => 2024]);
        Kegiatan::factory()->count(3)->create(['program_id' => $programA->id]);
        Kegiatan::factory()->count(1)->create(['program_id' => $programB->id]);

        $response = $this->get(route('instansi.show', $instansi));
        $response->assertStatus(200);
        $response->assertSee((string)$programA->kegiatans()->count());
        $response->assertSee((string)$programB->kegiatans()->count());
    }
}