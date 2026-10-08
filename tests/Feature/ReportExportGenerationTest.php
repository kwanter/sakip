<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\PerformanceIndicator;
use App\Models\Permission;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The sakip.reports.export route pointed at a controller method that did
 * not exist, and ReportGenerationService lacked the generate{Pdf,Excel,
 * Word}File methods the controller called. These tests pin the intended
 * behaviour: an authorized user can export any supported format, the file
 * lands on storage, and the report records its generated file.
 */
class ReportExportGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            'sakip.reports.view',
            'sakip.reports.export',
            'sakip.reports.download',
            'sakip.admin',
        ] as $name) {
            Permission::firstOrCreate(['name' => $name]);
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

    private function makeReport(Instansi $instansi, User $user): Report
    {
        $report = Report::forceCreate([
            'id' => str()->uuid(),
            'instansi_id' => $instansi->id,
            'generated_by' => $user->id,
            'created_by' => $user->id,
            'report_type' => 'quarterly_report',
            'title' => 'Laporan Triwulan I',
            'period' => '2026-01-01',
            'status' => 'draft',
            'generated_at' => now(),
        ]);

        $indicator = PerformanceIndicator::factory()->create([
            'instansi_id' => $instansi->id,
        ]);
        $report->indicators()->attach($indicator->id);

        return $report;
    }

    private function exportUserAndReport(): array
    {
        Storage::fake('local');

        $instansi = Instansi::factory()->create(['kode_instansi' => str()->random(8)]);
        $user = $this->makeUser($instansi, [
            'sakip.reports.view',
            'sakip.reports.export',
            'sakip.reports.download',
        ]);
        $report = $this->makeReport($instansi, $user);

        return [$user, $report];
    }

    #[Test]
    public function export_pdf_generates_file_and_downloads_it()
    {
        [$user, $report] = $this->exportUserAndReport();

        $response = $this->actingAs($user)
            ->get(route('sakip.reports.export', ['report' => $report, 'format' => 'pdf']));

        $response->assertOk();

        $report->refresh();
        $this->assertNotNull($report->file_path);
        $this->assertSame('pdf', $report->file_format);
        $this->assertSame('completed', $report->status);
        Storage::disk('local')->assertExists($report->file_path);
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($report->file_path));
    }

    #[Test]
    public function export_excel_generates_file_and_downloads_it()
    {
        [$user, $report] = $this->exportUserAndReport();

        $response = $this->actingAs($user)
            ->get(route('sakip.reports.export', ['report' => $report, 'format' => 'excel']));

        $response->assertOk();

        $report->refresh();
        $this->assertSame('excel', $report->file_format);
        Storage::disk('local')->assertExists($report->file_path);
        // XLSX files are ZIP archives (PK header)
        $this->assertStringStartsWith('PK', Storage::disk('local')->get($report->file_path));
    }

    #[Test]
    public function export_word_generates_file_and_downloads_it()
    {
        [$user, $report] = $this->exportUserAndReport();

        $response = $this->actingAs($user)
            ->get(route('sakip.reports.export', ['report' => $report, 'format' => 'word']));

        $response->assertOk();

        $report->refresh();
        $this->assertSame('word', $report->file_format);
        Storage::disk('local')->assertExists($report->file_path);
        $this->assertStringContainsString('<html', Storage::disk('local')->get($report->file_path));
    }

    #[Test]
    public function unsupported_format_is_not_found()
    {
        [$user, $report] = $this->exportUserAndReport();

        $response = $this->actingAs($user)
            ->get(route('sakip.reports.export', ['report' => $report, 'format' => 'csv']));

        $response->assertNotFound();
    }

    #[Test]
    public function user_without_export_permission_is_forbidden()
    {
        Storage::fake('local');

        $instansi = Instansi::factory()->create(['kode_instansi' => str()->random(8)]);
        $user = $this->makeUser($instansi, ['sakip.reports.view']);
        $report = $this->makeReport($instansi, $user);

        $response = $this->actingAs($user)
            ->get(route('sakip.reports.export', ['report' => $report, 'format' => 'pdf']));

        $response->assertForbidden();
    }
}
