<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (audit run-3, dangling-routes): six routes that Blade links to
 * pointed at controller methods that did not exist, so every click was a
 * 500: the data-collection reject button, the whole import flow
 * (import / import.confirm / template / sample) and the audit report
 * export. These pin the contracts the views already render.
 */
class DanglingRouteWiringTest extends TestCase
{
    use RefreshDatabase;

    private Instansi $instansi;

    private User $collector;

    private User $assessor;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['enter-and-submit-data-records', 'edit-own-data-submissions', 'view-performance-data', 'submit-evaluation-findings'] as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $this->instansi = Instansi::factory()->create(['kode_instansi' => str()->random(8)]);

        $this->collector = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansi->id,
        ]);
        $this->collector->givePermissionTo(['enter-and-submit-data-records', 'edit-own-data-submissions', 'view-performance-data']);

        $this->assessor = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansi->id,
        ]);
        $this->assessor->givePermissionTo(['submit-evaluation-findings', 'view-performance-data']);
    }

    private function makeData(array $attrs = []): PerformanceData
    {
        $indicator = PerformanceIndicator::factory()->create([
            'instansi_id' => $this->instansi->id,
        ]);

        return PerformanceData::factory()->create(array_merge([
            'performance_indicator_id' => $indicator->id,
            'created_by' => $this->collector->id,
            'submitted_by' => $this->collector->id,
            'status' => 'submitted',
        ], $attrs));
    }

    #[Test]
    public function assessor_can_reject_a_submitted_record(): void
    {
        $data = $this->makeData();

        $this->actingAs($this->assessor)
            ->post(route('sakip.data-collection.reject', $data), ['notes' => 'Bukti tidak lengkap'])
            ->assertRedirect();

        $data->refresh();
        $this->assertSame('rejected', $data->status);
        $this->assertStringContainsString('Bukti tidak lengkap', (string) $data->validation_notes);
        $this->assertSame($this->assessor->id, $data->validated_by);
    }

    #[Test]
    public function collector_cannot_reject_their_own_record(): void
    {
        $data = $this->makeData();

        $this->actingAs($this->collector)
            ->post(route('sakip.data-collection.reject', $data), ['notes' => 'x'])
            ->assertForbidden();

        $this->assertSame('submitted', $data->fresh()->status);
    }

    #[Test]
    public function template_and_sample_downloads_are_reachable(): void
    {
        $this->actingAs($this->collector)
            ->get(route('sakip.data-collection.template'))
            ->assertOk()
            ->assertDownload();

        $this->actingAs($this->collector)
            ->get(route('sakip.data-collection.sample'))
            ->assertOk()
            ->assertDownload();
    }

    #[Test]
    public function import_previews_then_confirm_persists(): void
    {
        Storage::fake('local');

        $indicator = PerformanceIndicator::factory()->create([
            'instansi_id' => $this->instansi->id,
            'code' => 'IK-001',
        ]);

        $csv = "indicator_code,period,actual_value,notes\nIK-001,".date('Y')."-01-01,88,Jan\n";
        $file = UploadedFile::fake()->create('import.csv', strlen($csv), 'text/csv');
        file_put_contents($file->getRealPath(), $csv);

        $preview = $this->actingAs($this->collector)
            ->post(route('sakip.data-collection.import'), [
                'file' => $file,
                'year' => (int) date('Y'),
            ]);

        $preview->assertOk();
        $preview->assertViewHas('previewData');
        $this->assertSame(0, PerformanceData::count(), 'preview must not persist');

        $importId = $preview->original['previewData']['import_id'];

        $this->actingAs($this->collector)
            ->post(route('sakip.data-collection.import.confirm'), ['import_id' => $importId])
            ->assertRedirect();

        $row = PerformanceData::firstOrFail();
        $this->assertSame($indicator->id, $row->performance_indicator_id);
        $this->assertSame($this->instansi->id, $row->instansi_id);
        $this->assertSame(88.0, (float) $row->actual_value);
    }

    #[Test]
    public function confirm_with_a_foreign_or_unknown_import_id_is_rejected(): void
    {
        // A form post answers with a redirect plus an error bag; nothing is
        // committed and the uploader's own preview id is the only one that
        // resolves.
        $this->actingAs($this->collector)
            ->post(route('sakip.data-collection.import.confirm'), ['import_id' => '00000000-0000-4000-8000-000000000000'])
            ->assertRedirect()
            ->assertSessionHasErrors('import_id');

        $this->assertSame(0, PerformanceData::count());
    }
}
