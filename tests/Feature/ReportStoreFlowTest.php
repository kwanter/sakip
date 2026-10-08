<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\PerformanceIndicator;
use App\Models\Permission;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Creating a report from the form has always called
 * ReportGenerationService::generateReportContent(), which did not exist,
 * and wrote columns that were not in the schema. Pin the working flow:
 * store persists the report with content and attached indicators.
 */
class ReportStoreFlowTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function authorized_user_can_create_a_report_with_indicators()
    {
        foreach (['sakip.reports.create', 'sakip.reports.view', 'sakip.admin'] as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $instansi = Instansi::factory()->create(['kode_instansi' => str()->random(8)]);
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $instansi->id,
        ]);
        $user->givePermissionTo(['sakip.reports.create', 'sakip.reports.view']);

        $indicator = PerformanceIndicator::factory()->create([
            'instansi_id' => $instansi->id,
        ]);

        $response = $this->actingAs($user)->postJson(route('sakip.reports.store'), [
            'report_type' => 'quarterly',
            'period' => '2026-01-01',
            'category' => 'performance',
            'title' => 'Laporan Triwulan I 2026',
            'description' => 'Laporan uji',
            'indicators' => [$indicator->id],
            'include_assessments' => true,
            'include_benchmarks' => false,
            'include_recommendations' => false,
            'format' => 'pdf',
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $report = Report::where('title', 'Laporan Triwulan I 2026')->first();
        $this->assertNotNull($report, 'report row must be persisted');
        $this->assertSame('draft', $report->status);
        $this->assertSame($instansi->id, $report->instansi_id);
        $this->assertIsArray($report->content);
        $this->assertSame('Laporan Triwulan I 2026', $report->content['report']['title']);
        $this->assertCount(1, $report->indicators);
        $this->assertSame($indicator->id, $report->indicators->first()->id);
    }
}
