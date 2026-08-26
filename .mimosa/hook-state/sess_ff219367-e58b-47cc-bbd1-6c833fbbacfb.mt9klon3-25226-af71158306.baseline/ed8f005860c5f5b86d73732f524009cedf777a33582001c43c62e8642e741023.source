<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\PerformanceCalculationService;
use App\Models\PerformanceIndicator;
use App\Models\Target;
use App\Models\PerformanceData;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PerformanceCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PerformanceCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PerformanceCalculationService();
    }

    /** @test */
    public function it_calculates_simple_percentage_correctly()
    {
        $indicator = PerformanceIndicator::factory()->create([
            'calculation_method' => 'simple',
            'polarity' => 'maximize',
        ]);

        $target = Target::factory()->create([
            'performance_indicator_id' => $indicator->id,
            'target_value' => 100,
        ]);

        $result = $this->service->calculatePercentage(80, 100, 'maximize');

        $this->assertEquals(80.0, $result);
    }

    /** @test */
    public function it_respects_maximum_percentage_cap()
    {
        $result = $this->service->calculatePercentage(300, 100, 'maximize');

        // Should be capped at 200% as per config
        $this->assertEquals(200.0, $result);
    }

    /** @test */
    public function it_handles_minimize_polarity_correctly()
    {
        // For minimize: lower actual is better
        $result = $this->service->calculatePercentage(50, 100, 'minimize');

        // 50/100 = 0.5, inverted = 150%
        $this->assertGreaterThan(100, $result);
    }

    /** @test */
    public function it_handles_zero_target_gracefully()
    {
        $result = $this->service->calculatePercentage(50, 0, 'maximize');

        $this->assertEquals(0, $result);
    }

    /** @test */
    public function it_calculates_achievement_rating()
    {
        $excellentRating = $this->service->getAchievementRating(100);
        $this->assertEquals('excellent', $excellentRating);

        $goodRating = $this->service->getAchievementRating(85);
        $this->assertEquals('good', $goodRating);

        $satisfactoryRating = $this->service->getAchievementRating(65);
        $this->assertEquals('satisfactory', $satisfactoryRating);

        $poorRating = $this->service->getAchievementRating(45);
        $this->assertEquals('poor', $poorRating);
    }
}