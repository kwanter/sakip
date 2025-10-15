<?php

namespace Database\Factories;

use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Instansi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PerformanceData>
 */
class PerformanceDataFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PerformanceData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currentYear = date('Y');
        $periods = [
            $currentYear . '-01', // January
            $currentYear . '-02', // February
            $currentYear . '-03', // March
            $currentYear . '-04', // April
            $currentYear . '-05', // May
            $currentYear . '-06', // June
        ];

        $actualValue = fake()->randomFloat(2, 40, 120); // 40% to 120% of typical target
        $dataQuality = fake()->randomElement(['excellent', 'good', 'fair', 'poor']);
        $status = fake()->randomElement(['draft', 'submitted', 'validated', 'rejected']);

        return [
            'performance_indicator_id' => PerformanceIndicator::inRandomOrder()->first()->id ?? PerformanceIndicator::factory(),
            'instansi_id' => Instansi::inRandomOrder()->first()->id ?? Instansi::factory(),
            'submitted_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'period' => fake()->randomElement($periods),
            'actual_value' => $actualValue,
            'notes' => fake()->optional(0.7)->sentence(20),
            'status' => $status,
            'data_quality' => $dataQuality,
            'validation_notes' => fake()->optional(0.3)->sentence(15),
            'validated_by' => fake()->optional(0.6)->randomElement(User::pluck('id')->toArray()) ?? User::factory(),
            'validated_at' => fake()->optional(0.6)->dateTimeBetween('-3 months', 'now'),
            'submitted_at' => fake()->optional(0.8)->dateTimeBetween('-4 months', 'now'),
            'metadata' => [
                'data_source' => fake()->optional()->randomElement(['Manual Entry', 'System Integration', 'Survey']),
                'collection_date' => fake()->optional()->date(),
                'verification_status' => fake()->optional()->randomElement(['verified', 'pending_verification', 'needs_review']),
                'quality_score' => fake()->optional()->randomFloat(2, 70, 100),
                'confidence_level' => fake()->optional()->randomElement(['high', 'medium', 'low']),
            ],
            'created_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'updated_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'updated_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }

    /**
     * Indicate that the performance data is submitted.
     */
    public function submitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'submitted_at' => fake()->dateTimeBetween('-4 months', 'now'),
        ]);
    }

    /**
     * Indicate that the performance data is validated.
     */
    public function validated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'validated',
            'submitted_at' => fake()->dateTimeBetween('-4 months', '-2 months'),
            'validated_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'validated_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'data_quality' => fake()->randomElement(['excellent', 'good']),
        ]);
    }

    /**
     * Indicate that the performance data is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
            'submitted_at' => fake()->dateTimeBetween('-4 months', '-2 months'),
            'validated_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'validated_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'validation_notes' => fake()->sentence(15),
        ]);
    }

    /**
     * Indicate that the performance data is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'submitted_at' => null,
            'validated_at' => null,
            'validated_by' => null,
        ]);
    }

    /**
     * Configure the model factory to create performance data for a specific indicator.
     */
    public function forIndicator($indicatorId): static
    {
        return $this->state(fn (array $attributes) => [
            'performance_indicator_id' => $indicatorId,
        ]);
    }

    /**
     * Configure the model factory to create performance data for a specific instansi.
     */
    public function forInstansi($instansiId): static
    {
        return $this->state(fn (array $attributes) => [
            'instansi_id' => $instansiId,
        ]);
    }

    /**
     * Configure the model factory to create performance data for a specific period.
     */
    public function forPeriod($period): static
    {
        return $this->state(fn (array $attributes) => [
            'period' => $period,
        ]);
    }

    /**
     * Configure the model factory to create high-quality data.
     */
    public function highQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_quality' => 'excellent',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'quality_score' => fake()->randomFloat(2, 90, 100),
                'confidence_level' => 'high',
                'verification_status' => 'verified',
            ]),
        ]);
    }

    /**
     * Configure the model factory to create low-quality data.
     */
    public function lowQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'data_quality' => 'poor',
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'quality_score' => fake()->randomFloat(2, 40, 60),
                'confidence_level' => 'low',
                'verification_status' => 'needs_review',
            ]),
        ]);
    }

    /**
     * Configure the model factory to create data with high achievement.
     */
    public function highAchievement(): static
    {
        return $this->state(fn (array $attributes) => [
            'actual_value' => fake()->randomFloat(2, 90, 120),
        ]);
    }

    /**
     * Configure the model factory to create data with low achievement.
     */
    public function lowAchievement(): static
    {
        return $this->state(fn (array $attributes) => [
            'actual_value' => fake()->randomFloat(2, 30, 60),
        ]);
    }
}