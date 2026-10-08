<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\PerformanceIndicator;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportGenerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * generateReportData() crashed for real-world periods and for benchmark
 * reporting:
 *  - generateTrendAnalysis() parsed the period with createFromFormat('Y-m'),
 *    which throws for 'YYYY-MM-DD' and 'YYYY-Qn' values the app actually
 *    stores in reports.period.
 *  - generateBenchmarkAnalysis() queried an instansis.type column that does
 *    not exist in the schema.
 */
class ReportGenerationServicePeriodTest extends TestCase
{
    use RefreshDatabase;

    private function seedInstitution(): Instansi
    {
        $instansi = Instansi::factory()->create(['kode_instansi' => str()->random(8)]);
        PerformanceIndicator::factory()->create(['instansi_id' => $instansi->id]);

        return $instansi;
    }

    /**
     * @return array{deprecated: bool}
     */
    private function generateFor(string $period): array
    {
        $instansi = $this->seedInstitution();

        $service = app(ReportGenerationService::class);
        $data = $service->generateReportData(
            $instansi->id,
            'quarterly_report',
            $period,
            ['indicators'],
            ['include_trends' => true, 'include_benchmarks' => true],
        );

        return $data;
    }

    #[Test]
    public function full_date_periods_do_not_crash_trend_analysis()
    {
        $data = $this->generateFor('2026-01-01');

        $this->assertIsArray($data['trends']);
        $this->assertIsArray($data['benchmarks']);
    }

    #[Test]
    public function quarter_periods_do_not_crash_trend_analysis()
    {
        $data = $this->generateFor('2026-Q1');

        $this->assertIsArray($data['trends']);
    }

    #[Test]
    public function year_month_periods_do_not_crash_trend_analysis()
    {
        $data = $this->generateFor('2026-06');

        $this->assertIsArray($data['trends']);
    }

    #[Test]
    public function bare_year_periods_do_not_crash_trend_analysis()
    {
        $data = $this->generateFor('2026');

        $this->assertIsArray($data['trends']);
    }

    #[Test]
    public function average_performance_is_computed_from_targets_not_a_missing_column()
    {
        // performance_data has no performance_percentage column; on MySQL
        // avg('performance_percentage') throws "Unknown column". Achievement
        // must come from the target: actual 80 vs target 100 => 80%.
        $instansi = $this->seedInstitution();
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $instansi->id,
        ]);

        $indicator = $instansi->performanceIndicators()->first()
            ?? PerformanceIndicator::factory()->create(['instansi_id' => $instansi->id]);

        \App\Models\Target::factory()->create([
            'performance_indicator_id' => $indicator->id,
            'year' => 2026,
            'target_value' => 100,
        ]);

        \App\Models\PerformanceData::factory()->create([
            'performance_indicator_id' => $indicator->id,
            'instansi_id' => $instansi->id,
            'period' => '2026-01',
            'actual_value' => 80,
            'status' => 'approved',
        ]);

        $report = Report::forceCreate([
            'id' => str()->uuid(),
            'instansi_id' => $instansi->id,
            'generated_by' => $user->id,
            'created_by' => $user->id,
            'report_type' => 'quarterly_report',
            'title' => 'Laporan Capaian',
            'period' => '2026-01-01',
            'status' => 'draft',
        ]);
        $report->indicators()->attach($indicator->id);

        $data = app(\App\Services\ReportCalculationService::class)->getReportData($report);

        $this->assertSame(80.0, (float) $data['summary']['average_performance']);
    }

    #[Test]
    public function report_export_pipeline_survives_a_prepared_report()
    {
        // Guards the exact path the export button walks: a persisted report
        // with a date-like period through ReportCalculationService.
        $instansi = $this->seedInstitution();
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $instansi->id,
        ]);

        $report = Report::forceCreate([
            'id' => str()->uuid(),
            'instansi_id' => $instansi->id,
            'generated_by' => $user->id,
            'created_by' => $user->id,
            'report_type' => 'quarterly_report',
            'title' => 'Laporan Uji',
            'period' => '2026-01-01',
            'status' => 'draft',
        ]);

        $data = app(\App\Services\ReportCalculationService::class)->getReportData($report);

        $this->assertArrayHasKey('summary', $data);
        $this->assertArrayHasKey('trends', $data);
        $this->assertArrayHasKey('benchmarks', $data);
    }
}
