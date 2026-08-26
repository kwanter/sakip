<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Services\SakipValidationService;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Target;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

class SakipValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SakipValidationService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SakipValidationService();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_validates_performance_data_successfully()
    {
        $indicator = PerformanceIndicator::factory()->create();
        $target = Target::factory()->create([
            'performance_indicator_id' => $indicator->id,
            'target_value' => 100,
        ]);

        $data = [
            'performance_indicator_id' => $indicator->id,
            'period_year' => now()->year,
            'period_month' => now()->month,
            'value' => 80,
            'data_source' => 'Manual Entry',
            'collection_method' => 'Automated',
            'notes' => 'Test performance data',
        ];

        $result = $this->service->validatePerformanceData($data);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function it_rejects_performance_data_without_target()
    {
        $indicator = PerformanceIndicator::factory()->create();

        $data = [
            'performance_indicator_id' => $indicator->id,
            'period_year' => now()->year,
            'period_month' => now()->month,
            'actual_value' => 80,
        ];

        $result = $this->service->validatePerformanceData($data);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    /** @test */
    public function it_validates_target_data_successfully()
    {
        $indicator = PerformanceIndicator::factory()->create();

        $data = [
            'performance_indicator_id' => $indicator->id,
            'target_year' => now()->year,
            'target_period' => 'Q1',
            'target_value' => 100,
            'weight' => 10,
        ];

        $result = $this->service->validateTarget($data);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function it_detects_data_integrity_issues()
    {
        $indicator = PerformanceIndicator::factory()->create();

        // Create orphaned performance data (no target)
        PerformanceData::factory()->create([
            'performance_indicator_id' => $indicator->id,
        ]);

        $result = $this->service->validateDataIntegrity();

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['issues']);
    }

    /** @test */
    public function it_validates_quarterly_target_consistency()
    {
        $indicator = PerformanceIndicator::factory()->create();

        $data = [
            'performance_indicator_id' => $indicator->id,
            'target_year' => now()->year,
            'target_value' => 100,
            'q1_target' => 25,
            'q2_target' => 25,
            'q3_target' => 25,
            'q4_target' => 20, // Doesn't sum to 100
        ];

        $result = $this->service->validateTarget($data);

        $this->assertFalse($result['valid']);
        $this->assertArrayHasKey('warnings', $result);
    }
}