<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\PerformanceData;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assessment>
 */
class AssessmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Assessment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $overallScore = fake()->randomFloat(2, 40, 100);
        $status = fake()->randomElement(['pending', 'in_review', 'completed', 'approved']);
        $assessedAt = fake()->optional(0.8)->dateTimeBetween('-2 months', 'now');
        $approvedAt = $status === 'approved' ? fake()->dateTimeBetween($assessedAt ?? '-1 month', 'now') : null;

        return [
            'performance_data_id' => PerformanceData::inRandomOrder()->first()->id ?? PerformanceData::factory(),
            'assessed_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'overall_score' => $overallScore,
            'comments' => fake()->optional(0.9)->paragraph(3),
            'recommendations' => fake()->optional(0.7)->paragraph(2),
            'status' => $status,
            'assessed_at' => $assessedAt,
            'approved_at' => $approvedAt,
            'metadata' => [
                'assessment_method' => fake()->randomElement(['Quantitative', 'Qualitative', 'Mixed Method']),
                'assessment_tools' => fake()->optional()->randomElements(['Survey', 'Interview', 'Observation', 'Document Review'], 2),
                'validation_score' => fake()->optional()->randomFloat(2, 70, 100),
                'reliability_score' => fake()->optional()->randomFloat(2, 80, 100),
                'assessor_qualification' => fake()->optional()->randomElement(['Expert', 'Senior', 'Intermediate', 'Junior']),
                'review_count' => fake()->optional()->numberBetween(1, 3),
            ],
            'created_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'updated_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the assessment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'assessed_at' => null,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the assessment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'assessed_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the assessment is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'assessed_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'approved_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the assessment is in review.
     */
    public function inReview(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_review',
            'assessed_at' => fake()->dateTimeBetween('-2 months', 'now'),
            'approved_at' => null,
        ]);
    }

    /**
     * Configure the model factory to create assessments with high scores.
     */
    public function highScore(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_score' => fake()->randomFloat(2, 85, 100),
            'comments' => fake()->paragraph(3),
            'recommendations' => fake()->paragraph(2),
        ]);
    }

    /**
     * Configure the model factory to create assessments with moderate scores.
     */
    public function moderateScore(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_score' => fake()->randomFloat(2, 70, 85),
            'comments' => fake()->paragraph(3),
            'recommendations' => fake()->paragraph(2),
        ]);
    }

    /**
     * Configure the model factory to create assessments with low scores.
     */
    public function lowScore(): static
    {
        return $this->state(fn (array $attributes) => [
            'overall_score' => fake()->randomFloat(2, 40, 70),
            'comments' => fake()->paragraph(3),
            'recommendations' => fake()->paragraph(2),
        ]);
    }

    /**
     * Configure the model factory to create assessments for specific performance data.
     */
    public function forPerformanceData($performanceDataId): static
    {
        return $this->state(fn (array $attributes) => [
            'performance_data_id' => $performanceDataId,
        ]);
    }

    /**
     * Configure the model factory to create assessments by specific assessor.
     */
    public function byAssessor($assessorId): static
    {
        return $this->state(fn (array $attributes) => [
            'assessed_by' => $assessorId,
        ]);
    }

    /**
     * Configure the model factory to create assessments with detailed comments.
     */
    public function withDetailedComments(): static
    {
        return $this->state(fn (array $attributes) => [
            'comments' => fake()->paragraph(5),
            'recommendations' => fake()->paragraph(3),
        ]);
    }

    /**
     * Configure the model factory to create assessments without comments.
     */
    public function withoutComments(): static
    {
        return $this->state(fn (array $attributes) => [
            'comments' => null,
            'recommendations' => null,
        ]);
    }
}