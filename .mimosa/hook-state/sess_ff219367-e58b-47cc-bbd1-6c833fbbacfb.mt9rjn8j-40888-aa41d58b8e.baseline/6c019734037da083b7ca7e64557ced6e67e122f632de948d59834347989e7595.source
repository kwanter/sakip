<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\User;
use App\Services\PerformanceCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression: PerformanceCalculationService must not reference phantom
 * Benchmark / PerformanceMeasurement models. calculatePerformanceTrends
 * should read real PerformanceData and return a trend per period.
 */
class PerformanceCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_trends_are_calculated_from_real_performance_data()
    {
        $instansi = Instansi::create(['kode_instansi' => str()->random(8), 'nama_instansi' => 'A']);
        $indicator = PerformanceIndicator::create([
            'instansi_id' => $instansi->id,
            'code' => str()->random(10),
            'name' => 'Ind',
            'measurement_unit' => 'unit',
            'frequency' => 'tahunan',
            'category' => 'output',
        ]);
        $user = User::factory()->create(['instansi_id' => $instansi->id]);
        PerformanceData::forceCreate([
            'performance_indicator_id' => $indicator->id,
            'instansi_id' => $instansi->id,
            'period' => '2026-01',
            'actual_value' => 100,
            'submitted_by' => $user->id,
        ]);

        $svc = new PerformanceCalculationService;
        $trends = $svc->calculatePerformanceTrends($indicator->id, [
            ['period' => '2026-01'],
            ['period' => '2026-02'],
        ]);

        $this->assertCount(1, $trends);
        $this->assertArrayHasKey('achievement', $trends[0]);
    }
}
