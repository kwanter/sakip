<?php

namespace Database\Factories;

use App\Models\Target;
use App\Models\PerformanceIndicator;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Target>
 */
class TargetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Target::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currentYear = date('Y');
        $targetValue = fake()->randomFloat(2, 50, 100);
        $minimumValue = $targetValue * fake()->randomFloat(2, 0.6, 0.8); // 60-80% of target

        return [
            'performance_indicator_id' => PerformanceIndicator::inRandomOrder()->first()->id ?? PerformanceIndicator::factory(),
            'year' => fake()->randomElement([$currentYear, $currentYear + 1]),
            'target_value' => $targetValue,
            'minimum_value' => $minimumValue,
            'justification' => fake()->sentence(20),
            'status' => fake()->randomElement(['draft', 'pending', 'approved']),
            'approved_by' => fake()->optional(0.7)->randomElement(User::pluck('id')->toArray()) ?? User::factory(),
            'approved_at' => fake()->optional(0.7)->dateTimeBetween('-6 months', 'now'),
            'notes' => fake()->optional()->sentence(15),
            'metadata' => [
                'baseline' => fake()->optional()->randomFloat(2, 30, 80),
                'benchmark' => fake()->optional()->randomFloat(2, 70, 95),
                'industry_average' => fake()->optional()->randomFloat(2, 60, 90),
                'calculation_method' => fake()->optional()->randomElement(['Simple Average', 'Weighted Average', 'Cumulative']),
            ],
            'created_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'updated_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Indicate that the target is for the current year.
     */
    public function currentYear(): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => date('Y'),
        ]);
    }

    /**
     * Indicate that the target is for next year.
     */
    public function nextYear(): static
    {
        return $this->state(fn (array $attributes) => [
            'year' => date('Y') + 1,
        ]);
    }

    /**
     * Indicate that the target is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'approved_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'approved_by' => User::inRandomOrder()->first()->id ?? User::factory(),
        ]);
    }

    /**
     * Indicate that the target is pending approval.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    /**
     * Indicate that the target is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'approved_at' => null,
            'approved_by' => null,
        ]);
    }

    /**
     * Configure the model factory to create targets for a specific indicator.
     */
    public function forIndicator($indicatorId): static
    {
        return $this->state(fn (array $attributes) => [
            'performance_indicator_id' => $indicatorId,
        ]);
    }

    /**
     * Configure the model factory to create high targets (80-95).
     */
    public function highTarget(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_value' => fake()->randomFloat(2, 80, 95),
            'minimum_value' => fake()->randomFloat(2, 60, 75),
        ]);
    }

    /**
     * Configure the model factory to create moderate targets (60-80).
     */
    public function moderateTarget(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_value' => fake()->randomFloat(2, 60, 80),
            'minimum_value' => fake()->randomFloat(2, 45, 60),
        ]);
    }

    /**
     * Configure the model factory to create low targets (40-60).
     */
    public function lowTarget(): static
    {
        return $this->state(fn (array $attributes) => [
            'target_value' => fake()->randomFloat(2, 40, 60),
            'minimum_value' => fake()->randomFloat(2, 30, 45),
        ]);
    }
}