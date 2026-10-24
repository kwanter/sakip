<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Program;
use App\Models\Role;
use App\Models\SasaranStrategis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (audit run-2, planning.instansi-id-write-unbound-parent):
 * planning writes must not accept a foreign instansi_id. SasaranStrategis
 * create/update and Program update bind ownership server-side; the Program
 * strategic parent must belong to the destination instansi. Super Admin
 * keeps explicit cross-instansi authority.
 */
class MasterDataInstitutionIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = false;

    private const FOREIGN_CODE = 'SS-FOREIGN-WRITE-MARKER';

    private Instansi $instansiA;

    private Instansi $instansiB;

    private User $executiveA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instansiA = Instansi::factory()->create(['nama_instansi' => 'Instansi A']);
        $this->instansiB = Instansi::factory()->create(['nama_instansi' => 'Instansi B']);

        $role = Role::firstOrCreate(['name' => 'Executive'], ['guard_name' => 'web']);
        $this->executiveA = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansiA->id,
        ]);
        $this->executiveA->assignRole($role);
    }

    private function makeSasaran(Instansi $instansi, string $suffix = ''): SasaranStrategis
    {
        return SasaranStrategis::create([
            'instansi_id' => $instansi->id,
            'kode_sasaran_strategis' => 'SS-'.str()->random(10).$suffix,
            'nama_strategis' => 'Sasaran '.$instansi->nama_instansi,
            'status' => 'aktif',
        ]);
    }

    private function makeProgram(Instansi $instansi, SasaranStrategis $sasaran): Program
    {
        return Program::factory()->create([
            'instansi_id' => $instansi->id,
            'sasaran_strategis_id' => $sasaran->id,
            'nama_program' => 'Program '.$instansi->nama_instansi,
            'status' => 'aktif',
        ]);
    }

    #[Test]
    public function executive_cannot_create_a_foreign_instansi_strategic_objective(): void
    {
        $this->actingAs($this->executiveA)
            ->post(route('sakip.sasaran-strategis.store'), [
                'instansi_id' => $this->instansiB->id,
                'kode_sasaran_strategis' => self::FOREIGN_CODE,
                'nama_strategis' => 'Foreign Objective Marker',
                'status' => 'aktif',
            ])
            ->assertInvalid('instansi_id');

        $this->assertDatabaseMissing('sasaran_strategis', [
            'kode_sasaran_strategis' => self::FOREIGN_CODE,
        ]);
    }

    #[Test]
    public function executive_can_create_an_own_instansi_strategic_objective(): void
    {
        $ownCode = 'SS-OWN-WRITE-MARKER';
        $this->actingAs($this->executiveA)
            ->post(route('sakip.sasaran-strategis.store'), [
                'instansi_id' => $this->instansiA->id,
                'kode_sasaran_strategis' => $ownCode,
                'nama_strategis' => 'Own Objective Marker',
                'status' => 'aktif',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('sasaran_strategis', [
            'kode_sasaran_strategis' => $ownCode,
            'instansi_id' => $this->instansiA->id,
        ]);
    }

    #[Test]
    public function executive_cannot_reassign_own_strategic_objective_to_a_foreign_instansi(): void
    {
        $own = $this->makeSasaran($this->instansiA, '-REASSIGN');

        $this->actingAs($this->executiveA)
            ->put(route('sakip.sasaran-strategis.update', $own), [
                'instansi_id' => $this->instansiB->id,
                'kode_sasaran_strategis' => $own->kode_sasaran_strategis,
                'nama_strategis' => $own->nama_strategis,
                'status' => 'aktif',
            ])
            ->assertInvalid('instansi_id');

        $this->assertDatabaseHas('sasaran_strategis', [
            'id' => $own->id,
            'instansi_id' => $this->instansiA->id,
        ]);
    }

    #[Test]
    public function super_admin_can_create_for_an_explicit_instansi(): void
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin'], ['guard_name' => 'web']);
        $superAdmin = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansiA->id,
        ]);
        $superAdmin->assignRole($role);

        $code = 'SS-SUPERADMIN-EXPLICIT';
        $this->actingAs($superAdmin)
            ->post(route('sakip.sasaran-strategis.store'), [
                'instansi_id' => $this->instansiB->id,
                'kode_sasaran_strategis' => $code,
                'nama_strategis' => 'Explicit Super Admin Objective',
                'status' => 'aktif',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('sasaran_strategis', [
            'kode_sasaran_strategis' => $code,
            'instansi_id' => $this->instansiB->id,
        ]);
    }

    #[Test]
    public function program_update_cannot_reassign_a_foreign_instansi(): void
    {
        $program = $this->makeProgram($this->instansiA, $this->makeSasaran($this->instansiA, '-PROG'));

        $this->actingAs($this->executiveA)
            ->put(route('sakip.program.update', $program), [
                'instansi_id' => $this->instansiB->id,
                'sasaran_strategis_id' => $program->sasaran_strategis_id,
                'kode_program' => $program->kode_program,
                'nama_program' => $program->nama_program,
                'status' => 'aktif',
                'tahun_mulai' => 2026,
                'tahun_selesai' => 2027,
            ])
            ->assertInvalid('instansi_id');

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'instansi_id' => $this->instansiA->id,
        ]);
    }

    #[Test]
    public function program_update_cannot_bind_a_foreign_strategic_parent(): void
    {
        $program = $this->makeProgram($this->instansiA, $this->makeSasaran($this->instansiA, '-PARENT'));
        $foreignSasaran = $this->makeSasaran($this->instansiB, '-FOREIGN');

        $this->actingAs($this->executiveA)
            ->put(route('sakip.program.update', $program), [
                'instansi_id' => $this->instansiA->id,
                'sasaran_strategis_id' => $foreignSasaran->id,
                'kode_program' => $program->kode_program,
                'nama_program' => $program->nama_program,
                'status' => 'aktif',
                'tahun_mulai' => 2026,
                'tahun_selesai' => 2027,
            ])
            ->assertInvalid('sasaran_strategis_id');

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'sasaran_strategis_id' => $program->sasaran_strategis_id,
        ]);
    }

    #[Test]
    public function program_update_with_a_same_institution_parent_succeeds(): void
    {
        $program = $this->makeProgram($this->instansiA, $this->makeSasaran($this->instansiA, '-OLD'));
        $ownSasaran = $this->makeSasaran($this->instansiA, '-NEW');

        $this->actingAs($this->executiveA)
            ->put(route('sakip.program.update', $program), [
                'instansi_id' => $this->instansiA->id,
                'sasaran_strategis_id' => $ownSasaran->id,
                'kode_program' => $program->kode_program,
                'nama_program' => $program->nama_program,
                'status' => 'aktif',
                'tahun_mulai' => 2026,
                'tahun_selesai' => 2027,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('programs', [
            'id' => $program->id,
            'sasaran_strategis_id' => $ownSasaran->id,
        ]);
    }
}
