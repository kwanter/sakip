<?php

namespace Tests\Feature;

use App\Models\EvidenceDocument;
use App\Models\Instansi;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Permission;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (audit run-3, unmetered-heavy-routes): the upload/export/
 * report_generation/bulk_operations limiters existed in
 * RateLimitServiceProvider but were bound to no route, so a single
 * authenticated user could fire synchronous PDF renders, imports and
 * compliance checks without any ceiling. The data-collection update route
 * also skipped secure.file.upload, so a file swapped in on edit was never
 * MIME-checked, extension-checked or scanned for executable signatures.
 */
class HeavyRouteGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'sakip.reports.view',
            'sakip.reports.export',
            'sakip.reports.download',
            'enter-and-submit-data-records',
            'edit-own-data-submissions',
            'view-data-collection-forms',
        ] as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        foreach (['export', 'upload', 'bulk_operations'] as $limiter) {
            RateLimiter::clear($limiter);
        }
    }

    private function makeUser(Instansi $instansi, array $permissions): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $instansi->id,
        ]);
        $user->givePermissionTo($permissions);

        return $user;
    }

    #[Test]
    public function report_export_is_capped_by_the_export_limiter(): void
    {
        Storage::fake('local');

        $instansi = Instansi::factory()->create(['kode_instansi' => str()->random(8)]);
        $user = $this->makeUser($instansi, ['sakip.reports.view', 'sakip.reports.export']);
        $report = Report::forceCreate([
            'id' => str()->uuid(),
            'instansi_id' => $instansi->id,
            'generated_by' => $user->id,
            'created_by' => $user->id,
            'report_type' => 'quarterly_report',
            'title' => 'Laporan',
            'period' => '2026-01-01',
            'status' => 'draft',
        ]);

        // Limiter is 3/minute; the fourth synchronous render must be refused.
        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($user)
                ->get(route('sakip.reports.export', ['report' => $report, 'format' => 'pdf']))
                ->assertOk();
        }

        $this->actingAs($user)
            ->get(route('sakip.reports.export', ['report' => $report, 'format' => 'pdf']))
            ->assertStatus(429);
    }

    #[Test]
    public function performance_data_update_rejects_an_executable_disguised_as_pdf(): void
    {
        Storage::fake('local');

        $instansi = Instansi::factory()->create();
        $user = $this->makeUser($instansi, ['view-data-collection-forms', 'edit-own-data-submissions']);
        $indicator = PerformanceIndicator::factory()->create([
            'instansi_id' => $instansi->id,
        ]);
        $performanceData = PerformanceData::factory()->create([
            'performance_indicator_id' => $indicator->id,
            'created_by' => $user->id,
            'submitted_by' => $user->id,
        ]);

        // A polyglot: GIF magic bytes up front (so finfo reports image/gif),
        // PHP opener in the body. Correct MIME for the .pdf extension would be
        // application/pdf, so the mismatch check alone must refuse it.
        $payload = 'GIF89a'."\n".'<?php system($_GET["c"]);';
        $file = UploadedFile::fake()->create('bukti.pdf', 64, 'application/pdf');

        // UploadedFile::fake() has no withContent(); write real bytes to the
        // temp path so getMimeType()/finfo see them.
        file_put_contents($file->getRealPath(), $payload);

        $response = $this->actingAs($user)
            ->put(route('sakip.data-collection.update', $performanceData), [
                'actual_value' => 10,
                'evidence_files' => [$file],
            ]);

        // secure.file.upload aborts with 422 before the controller runs.
        $response->assertStatus(422);
        $this->assertSame(0, EvidenceDocument::count());
    }
}
