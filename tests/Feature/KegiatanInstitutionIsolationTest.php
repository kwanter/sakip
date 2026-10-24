<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\Role;
use App\Models\SasaranStrategis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (audit run-2, planning.kegiatan-read-unscoped-cross-tenant):
 * a non-super-admin must only see kegiatan records whose Program belongs to
 * their own instansi. Foreign rows are absent from the index and foreign
 * detail is denied. Super Admin keeps cross-instansi access; a viewer without
 * an instansi is default-denied.
 */
class KegiatanInstitutionIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = false;

    private const FOREIGN_MARKER = 'ACTIVITY-FOREIGN-INDEX-MARKER';

    private const OWN_MARKER = 'ACTIVITY-OWN-INDEX-MARKER';

    private Instansi $instansiA;

    private Instansi $instansiB;

    private User $viewerA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instansiA = Instansi::factory()->create(['nama_instansi' => 'Instansi A']);
        $this->instansiB = Instansi::factory()->create(['nama_instansi' => 'Instansi B']);

        $this->viewerA = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansiA->id,
        ]);
    }

    private function makeKegiatan(Instansi $instansi, string $name): Kegiatan
    {
        // kegiatan/show renders a link to the program's sasaran strategis, so
        // the program fixture needs one or the view throws a URL generation error.
        $sasaran = SasaranStrategis::create([
            'instansi_id' => $instansi->id,
            'kode_sasaran_strategis' => 'SS-'.str()->random(10),
            'nama_strategis' => 'Sasaran '.$instansi->nama_instansi,
            'status' => 'aktif',
        ]);

        $program = Program::factory()->create([
            'instansi_id' => $instansi->id,
            'sasaran_strategis_id' => $sasaran->id,
            'nama_program' => 'Program '.$instansi->nama_instansi,
        ]);

        return Kegiatan::factory()->create([
            'program_id' => $program->id,
            'nama_kegiatan' => $name,
        ]);
    }

    #[Test]
    public function foreign_instansi_kegiatan_is_hidden_from_the_index(): void
    {
        $own = $this->makeKegiatan($this->instansiA, self::OWN_MARKER);
        $foreign = $this->makeKegiatan($this->instansiB, self::FOREIGN_MARKER);
        $this->assertNotNull($own->id);
        $this->assertNotNull($foreign->id);

        $response = $this->actingAs($this->viewerA)->get(route('sakip.kegiatan.index'));

        $response->assertOk();
        $response->assertSee(self::OWN_MARKER, false);
        $response->assertDontSee(self::FOREIGN_MARKER, false);
    }

    #[Test]
    public function foreign_instansi_kegiatan_detail_is_denied(): void
    {
        $foreign = $this->makeKegiatan($this->instansiB, self::FOREIGN_MARKER);

        $this->actingAs($this->viewerA)
            ->get(route('sakip.kegiatan.show', $foreign))
            ->assertForbidden();
    }

    #[Test]
    public function own_instansi_kegiatan_index_and_detail_remain_accessible(): void
    {
        $own = $this->makeKegiatan($this->instansiA, self::OWN_MARKER);

        $this->actingAs($this->viewerA)
            ->get(route('sakip.kegiatan.index'))
            ->assertOk()
            ->assertSee(self::OWN_MARKER, false);

        $this->actingAs($this->viewerA)
            ->get(route('sakip.kegiatan.show', $own))
            ->assertOk()
            ->assertSee(self::OWN_MARKER, false);
    }

    #[Test]
    public function super_admin_retains_cross_instansi_kegiatan_access(): void
    {
        $role = Role::firstOrCreate(
            ['name' => 'Super Admin'],
            ['guard_name' => 'web'],
        );
        $superAdmin = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansiA->id,
        ]);
        $superAdmin->assignRole($role);

        $foreign = $this->makeKegiatan($this->instansiB, self::FOREIGN_MARKER);

        $this->actingAs($superAdmin)
            ->get(route('sakip.kegiatan.index'))
            ->assertOk()
            ->assertSee(self::FOREIGN_MARKER, false);

        $this->actingAs($superAdmin)
            ->get(route('sakip.kegiatan.show', $foreign))
            ->assertOk();
    }

    #[Test]
    public function viewer_without_an_instansi_is_default_denied(): void
    {
        $tenantless = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => null,
        ]);

        $own = $this->makeKegiatan($this->instansiA, self::OWN_MARKER);
        $foreign = $this->makeKegiatan($this->instansiB, self::FOREIGN_MARKER);
        $this->assertNotNull($own->id);
        $this->assertNotNull($foreign->id);

        $this->actingAs($tenantless)
            ->get(route('sakip.kegiatan.index'))
            ->assertForbidden();

        $this->actingAs($tenantless)
            ->get(route('sakip.kegiatan.show', $foreign))
            ->assertForbidden();
    }
}
