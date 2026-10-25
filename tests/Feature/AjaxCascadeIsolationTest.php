<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\Role;
use App\Models\SasaranStrategis;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (review SEC-103/STD-05): the AJAX cascade endpoints must enforce
 * the same instansi predicates as the fixed read paths. A viewer of instansi
 * A must not enumerate foreign programs' kegiatan, foreign instansis'
 * objectives, or foreign sasarans' programs; tenantless viewers are
 * default-denied; Super Admin keeps cross-instansi authority.
 */
class AjaxCascadeIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = false;

    private Instansi $instansiA;

    private Instansi $instansiB;

    private User $viewerA;

    private User $superAdmin;

    private Program $programA;

    private Program $programB;

    private Kegiatan $kegiatanA;

    private Kegiatan $kegiatanB;

    private SasaranStrategis $sasaranA;

    private SasaranStrategis $sasaranB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->instansiA = Instansi::factory()->create(['nama_instansi' => 'Instansi A']);
        $this->instansiB = Instansi::factory()->create(['nama_instansi' => 'Instansi B']);

        $this->viewerA = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansiA->id,
        ]);

        $role = Role::firstOrCreate(['name' => 'Super Admin'], ['guard_name' => 'web']);
        $this->superAdmin = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansiA->id,
        ]);
        $this->superAdmin->assignRole($role);

        $this->sasaranA = SasaranStrategis::create([
            'instansi_id' => $this->instansiA->id,
            'kode_sasaran_strategis' => 'SS-'.str()->random(10),
            'nama_strategis' => 'Sasaran A',
            'status' => 'aktif',
        ]);
        $this->sasaranB = SasaranStrategis::create([
            'instansi_id' => $this->instansiB->id,
            'kode_sasaran_strategis' => 'SS-'.str()->random(10),
            'nama_strategis' => 'Sasaran B',
            'status' => 'aktif',
        ]);

        $this->programA = Program::factory()->create([
            'instansi_id' => $this->instansiA->id,
            'sasaran_strategis_id' => $this->sasaranA->id,
            'nama_program' => 'PROGRAM-OWN-MARKER',
        ]);
        $this->programB = Program::factory()->create([
            'instansi_id' => $this->instansiB->id,
            'sasaran_strategis_id' => $this->sasaranB->id,
            'nama_program' => 'PROGRAM-FOREIGN-MARKER',
        ]);

        $this->kegiatanA = Kegiatan::factory()->create([
            'program_id' => $this->programA->id,
            'nama_kegiatan' => 'KEGIATAN-OWN-MARKER',
        ]);
        $this->kegiatanB = Kegiatan::factory()->create([
            'program_id' => $this->programB->id,
            'nama_kegiatan' => 'KEGIATAN-FOREIGN-MARKER',
        ]);
    }

    /**
     * Foreign selectors are denied: explicit guards answer 403; where the
     * route-model binding resolves first, InstansiScope answers 404. Both
     * are denials per the project contract (CON-002).
     */
    private function assertDenyStatus(TestResponse $response): void
    {
        $this->assertContains(
            $response->status(),
            [403, 404],
            'Foreign selector must be denied, got '.$response->status(),
        );
    }

    #[Test]
    public function by_program_denies_a_foreign_program(): void
    {
        $response = $this->actingAs($this->viewerA)
            ->getJson(route('sakip.api.kegiatan.by-program', ['program' => $this->programB->id]));

        $this->assertDenyStatus($response);
    }

    #[Test]
    public function by_program_serves_the_own_program(): void
    {
        $this->actingAs($this->viewerA)
            ->getJson(route('sakip.api.kegiatan.by-program', ['program' => $this->programA->id]))
            ->assertOk()
            ->assertJsonFragment(['nama_kegiatan' => 'KEGIATAN-OWN-MARKER']);
    }

    #[Test]
    public function by_instansi_denies_a_foreign_instansi(): void
    {
        $response = $this->actingAs($this->viewerA)
            ->getJson(route('sakip.api.sasaran-strategis.by-instansi', ['instansi' => $this->instansiB->id]));

        $this->assertDenyStatus($response);
    }

    #[Test]
    public function by_instansi_foreign_is_rejected_by_the_explicit_guard(): void
    {
        // Instansi is NOT InstansiScope-filtered, so the route-model binding
        // resolves and this 403 comes from the controller guard itself —
        // deleting the guard flips this assertion to 200 (plan v1.1
        // TASK-405 pins the guard so it cannot be removed silently).
        $this->actingAs($this->viewerA)
            ->getJson(route('sakip.api.sasaran-strategis.by-instansi', ['instansi' => $this->instansiB->id]))
            ->assertForbidden();
    }

    #[Test]
    public function by_instansi_serves_the_own_instansi(): void
    {
        $this->actingAs($this->viewerA)
            ->getJson(route('sakip.api.sasaran-strategis.by-instansi', ['instansi' => $this->instansiA->id]))
            ->assertOk()
            ->assertJsonFragment(['nama_strategis' => 'Sasaran A']);
    }

    #[Test]
    public function by_sasaran_strategis_denies_a_foreign_parent(): void
    {
        $response = $this->actingAs($this->viewerA)
            ->getJson(route('sakip.api.program.by-sasaran-strategis', ['sasaranStrategis' => $this->sasaranB->id]));

        $this->assertDenyStatus($response);
    }

    #[Test]
    public function by_sasaran_strategis_serves_the_own_parent(): void
    {
        $this->actingAs($this->viewerA)
            ->getJson(route('sakip.api.program.by-sasaran-strategis', ['sasaranStrategis' => $this->sasaranA->id]))
            ->assertOk()
            ->assertJsonFragment(['nama_program' => 'PROGRAM-OWN-MARKER']);
    }

    #[Test]
    public function a_tenantless_viewer_is_default_denied(): void
    {
        $tenantless = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => null,
        ]);

        $this->assertDenyStatus(
            $this->actingAs($tenantless)
                ->getJson(route('sakip.api.kegiatan.by-program', ['program' => $this->programB->id])),
        );

        $this->assertDenyStatus(
            $this->actingAs($tenantless)
                ->getJson(route('sakip.api.sasaran-strategis.by-instansi', ['instansi' => $this->instansiB->id])),
        );

        $this->assertDenyStatus(
            $this->actingAs($tenantless)
                ->getJson(route('sakip.api.program.by-sasaran-strategis', ['sasaranStrategis' => $this->sasaranB->id])),
        );
    }

    #[Test]
    public function super_admin_keeps_cross_instansi_authority(): void
    {
        $this->actingAs($this->superAdmin)
            ->getJson(route('sakip.api.kegiatan.by-program', ['program' => $this->programB->id]))
            ->assertOk()
            ->assertJsonFragment(['nama_kegiatan' => 'KEGIATAN-FOREIGN-MARKER']);

        $this->actingAs($this->superAdmin)
            ->getJson(route('sakip.api.sasaran-strategis.by-instansi', ['instansi' => $this->instansiB->id]))
            ->assertOk()
            ->assertJsonFragment(['nama_strategis' => 'Sasaran B']);
    }
}
