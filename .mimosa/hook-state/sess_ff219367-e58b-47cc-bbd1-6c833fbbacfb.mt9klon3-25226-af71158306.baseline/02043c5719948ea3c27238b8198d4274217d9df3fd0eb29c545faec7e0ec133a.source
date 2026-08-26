<?php

namespace Database\Factories;

use App\Models\Instansi;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Report::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reportTypes = [
            'performance_summary',
            'target_achievement',
            'indicator_analysis',
            'instansi_comparison',
            'quarterly_report',
            'annual_report',
            'compliance_report',
            'trend_analysis',
            'gap_analysis',
            'recommendation_report',
        ];

        $statuses = ['pending', 'processing', 'completed', 'failed', 'submitted'];
        $status = fake()->randomElement($statuses);
        
        $generatedAt = null;
        $submittedAt = null;
        
        if (in_array($status, ['completed', 'submitted'])) {
            $generatedAt = fake()->dateTimeBetween('-2 months', 'now');
        }
        
        if ($status === 'submitted') {
            $submittedAt = fake()->dateTimeBetween($generatedAt ?? '-1 month', 'now');
        }

        $year = fake()->year();
        $quarter = fake()->optional()->randomElement(['Q1', 'Q2', 'Q3', 'Q4']);
        $month = fake()->optional()->randomElement(['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12']);

        return [
            'instansi_id' => Instansi::inRandomOrder()->first()->id ?? Instansi::factory(),
            'generated_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'report_type' => fake()->randomElement($reportTypes),
            'period' => fake()->randomElement([
                $year,
                $year . '-' . $quarter,
                $year . '-' . $month,
                '2024-Q1',
                '2024-Q2',
                '2024-Q3',
                '2024-Q4',
            ]),
            'file_path' => $status === 'completed' || $status === 'submitted' 
                ? 'reports/' . fake()->uuid() . '.pdf' 
                : null,
            'parameters' => [
                'include_charts' => fake()->boolean(80),
                'include_trends' => fake()->boolean(70),
                'include_recommendations' => fake()->boolean(60),
                'format' => fake()->randomElement(['pdf', 'excel', 'csv']),
                'date_range' => [
                    'start' => fake()->optional()->date(),
                    'end' => fake()->optional()->date(),
                ],
                'indicators' => fake()->optional()->randomElements(['indicator1', 'indicator2', 'indicator3'], 2),
                'categories' => fake()->optional()->randomElements(['financial', 'service', 'quality'], 2),
            ],
            'status' => $status,
            'generated_at' => $generatedAt,
            'submitted_at' => $submittedAt,
            'metadata' => [
                'file_size' => $status === 'completed' || $status === 'submitted' ? fake()->numberBetween(100000, 5000000) : null,
                'page_count' => $status === 'completed' || $status === 'submitted' ? fake()->numberBetween(10, 100) : null,
                'generation_time' => $status === 'completed' || $status === 'submitted' ? fake()->numberBetween(1000, 30000) : null,
                'template_used' => fake()->optional()->randomElement(['standard', 'executive', 'detailed']),
                'data_source' => fake()->optional()->randomElement(['database', 'api', 'manual']),
                'error_message' => $status === 'failed' ? fake()->sentence() : null,
                'retry_count' => $status === 'failed' ? fake()->numberBetween(1, 3) : null,
            ],
            'created_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'updated_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the report is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'file_path' => null,
            'generated_at' => null,
            'submitted_at' => null,
        ]);
    }

    /**
     * Indicate that the report is processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'file_path' => null,
            'generated_at' => null,
            'submitted_at' => null,
        ]);
    }

    /**
     * Indicate that the report is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'file_path' => 'reports/' . fake()->uuid() . '.pdf',
            'generated_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'submitted_at' => null,
        ]);
    }

    /**
     * Indicate that the report is failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'file_path' => null,
            'generated_at' => null,
            'submitted_at' => null,
        ]);
    }

    /**
     * Indicate that the report is submitted.
     */
    public function submitted(): static
    {
        $generatedAt = fake()->dateTimeBetween('-2 months', '-1 month');
        return $this->state(fn (array $attributes) => [
            'status' => 'submitted',
            'file_path' => 'reports/' . fake()->uuid() . '.pdf',
            'generated_at' => $generatedAt,
            'submitted_at' => fake()->dateTimeBetween($generatedAt, 'now'),
        ]);
    }

    /**
     * Configure the model factory to create reports of specific type.
     */
    public function ofType($type): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => $type,
        ]);
    }

    /**
     * Configure the model factory to create performance summary reports.
     */
    public function performanceSummary(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'performance_summary',
            'parameters' => array_merge($attributes['parameters'] ?? [], [
                'include_charts' => true,
                'include_trends' => true,
                'include_recommendations' => true,
                'format' => 'pdf',
            ]),
        ]);
    }

    /**
     * Configure the model factory to create target achievement reports.
     */
    public function targetAchievement(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'target_achievement',
            'parameters' => array_merge($attributes['parameters'] ?? [], [
                'include_charts' => true,
                'include_trends' => false,
                'include_recommendations' => true,
                'format' => 'excel',
            ]),
        ]);
    }

    /**
     * Configure the model factory to create quarterly reports.
     */
    public function quarterly(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'quarterly_report',
            'period' => '2024-Q' . fake()->numberBetween(1, 4),
        ]);
    }

    /**
     * Configure the model factory to create annual reports.
     */
    public function annual(): static
    {
        return $this->state(fn (array $attributes) => [
            'report_type' => 'annual_report',
            'period' => fake()->year(),
        ]);
    }

    /**
     * Configure the model factory to create reports for specific instansi.
     */
    public function forInstansi($instansiId): static
    {
        return $this->state(fn (array $attributes) => [
            'instansi_id' => $instansiId,
        ]);
    }

    /**
     * Configure the model factory to create reports for specific period.
     */
    public function forPeriod($period): static
    {
        return $this->state(fn (array $attributes) => [
            'period' => $period,
        ]);
    }

    /**
     * Configure the model factory to create reports generated by specific user.
     */
    public function generatedBy($userId): static
    {
        return $this->state(fn (array $attributes) => [
            'generated_by' => $userId,
        ]);
    }

    /**
     * Configure the model factory to create reports with charts.
     */
    public function withCharts(): static
    {
        return $this->state(fn (array $attributes) => [
            'parameters' => array_merge($attributes['parameters'] ?? [], [
                'include_charts' => true,
                'include_trends' => true,
            ]),
        ]);
    }

    /**
     * Configure the model factory to create reports without charts.
     */
    public function withoutCharts(): static
    {
        return $this->state(fn (array $attributes) => [
            'parameters' => array_merge($attributes['parameters'] ?? [], [
                'include_charts' => false,
                'include_trends' => false,
            ]),
        ]);
    }

    /**
     * Configure the model factory to create large reports.
     */
    public function largeReport(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'file_size' => fake()->numberBetween(2000000, 10000000),
                'page_count' => fake()->numberBetween(50, 200),
                'generation_time' => fake()->numberBetween(30000, 120000),
            ]),
        ]);
    }

    /**
     * Configure the model factory to create small reports.
     */
    public function smallReport(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => array_merge($attributes['metadata'] ?? [], [
                'file_size' => fake()->numberBetween(50000, 200000),
                'page_count' => fake()->numberBetween(5, 20),
                'generation_time' => fake()->numberBetween(1000, 5000),
            ]),
        ]);
    }
}