<?php

namespace Database\Factories;

use App\Models\PerformanceIndicator;
use App\Models\Instansi;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PerformanceIndicator>
 */
class PerformanceIndicatorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PerformanceIndicator::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['Financial', 'Service Quality', 'Operational Efficiency', 'Customer Satisfaction', 'Compliance'];
        $frequencies = ['monthly', 'quarterly', 'annual'];
        $measurementUnits = ['Percentage', 'Count', 'Ratio', 'Score', 'Days', 'IDR', 'Hours'];
        $dataSources = ['Manual Entry', 'System Integration', 'Survey', 'External API', 'Database Query'];
        $collectionMethods = ['Automated', 'Manual', 'Survey', 'Calculation'];

        return [
            'instansi_id' => Instansi::inRandomOrder()->first()->id ?? Instansi::factory(),
            'code' => 'PI-' . strtoupper(fake()->bothify('??###')),
            'name' => fake()->catchPhrase(),
            'description' => fake()->sentence(15),
            'measurement_unit' => fake()->randomElement($measurementUnits),
            'data_source' => fake()->randomElement($dataSources),
            'collection_method' => fake()->randomElement($collectionMethods),
            'calculation_formula' => fake()->randomElement([
                'Actual / Target * 100',
                '(Actual - Minimum) / (Target - Minimum) * 100',
                'Actual Value',
                'Sum of all values',
                'Average of monthly values'
            ]),
            'frequency' => fake()->randomElement($frequencies),
            'category' => fake()->randomElement($categories),
            'weight' => fake()->randomFloat(2, 5, 30),
            'is_mandatory' => fake()->boolean(60),
            'metadata' => [
                'benchmark' => fake()->optional()->randomFloat(2, 70, 95),
                'industry_standard' => fake()->optional()->randomFloat(2, 60, 90),
                'calculation_notes' => fake()->optional()->sentence(10),
                'validation_rules' => fake()->optional()->randomElements(['min:0', 'max:100', 'required'], 2)
            ],
            'created_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'updated_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /**
     * Indicate that the indicator is mandatory.
     */
    public function mandatory(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_mandatory' => true,
            'weight' => fake()->randomFloat(2, 15, 30),
        ]);
    }

    /**
     * Indicate that the indicator is financial.
     */
    public function financial(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Financial',
            'measurement_unit' => fake()->randomElement(['Percentage', 'IDR', 'Ratio']),
            'calculation_formula' => 'Actual / Budget * 100',
        ]);
    }

    /**
     * Indicate that the indicator is for service quality.
     */
    public function serviceQuality(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'Service Quality',
            'measurement_unit' => fake()->randomElement(['Score', 'Percentage', 'Count']),
            'calculation_formula' => 'Satisfied Customers / Total Customers * 100',
        ]);
    }

    /**
     * Indicate that the indicator has monthly frequency.
     */
    public function monthly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'monthly',
        ]);
    }

    /**
     * Indicate that the indicator has quarterly frequency.
     */
    public function quarterly(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'quarterly',
        ]);
    }

    /**
     * Indicate that the indicator has annual frequency.
     */
    public function annual(): static
    {
        return $this->state(fn (array $attributes) => [
            'frequency' => 'annual',
        ]);
    }

    /**
     * Configure the model factory to create indicators for a specific instansi.
     */
    public function forInstansi($instansiId): static
    {
        return $this->state(fn (array $attributes) => [
            'instansi_id' => $instansiId,
        ]);
    }
}