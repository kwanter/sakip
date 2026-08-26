<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\AssessmentCriterion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AssessmentCriterion>
 */
class AssessmentCriterionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AssessmentCriterion::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $criteriaNames = [
            'Relevance',
            'Effectiveness',
            'Efficiency',
            'Sustainability',
            'Impact',
            'Quality',
            'Timeliness',
            'Innovation',
            'Stakeholder Satisfaction',
            'Resource Utilization',
            'Process Compliance',
            'Outcome Achievement',
            'Data Quality',
            'Documentation Quality',
            'Methodology Appropriateness',
        ];

        $score = fake()->randomFloat(2, 50, 100);
        $weight = fake()->randomFloat(2, 5, 20);

        return [
            'assessment_id' => Assessment::inRandomOrder()->first()->id ?? Assessment::factory(),
            'criteria_name' => fake()->randomElement($criteriaNames),
            'score' => $score,
            'weight' => $weight,
            'justification' => fake()->optional(0.9)->paragraph(2),
            'metadata' => [
                'evaluation_method' => fake()->randomElement(['Quantitative', 'Qualitative', 'Mixed']),
                'data_source' => fake()->optional()->randomElement(['Survey', 'Interview', 'Document Review', 'Observation', 'Secondary Data']),
                'benchmark' => fake()->optional()->randomFloat(2, 60, 90),
                'threshold' => fake()->optional()->randomFloat(2, 40, 70),
                'confidence_level' => fake()->optional()->randomFloat(2, 0.7, 0.95),
                'evaluator_notes' => fake()->optional()->sentence(3),
            ],
            'created_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'updated_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'created_at' => fake()->dateTimeBetween('-3 months', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Configure the model factory to create criteria with high scores.
     */
    public function highScore(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->randomFloat(2, 85, 100),
            'justification' => fake()->paragraph(2),
        ]);
    }

    /**
     * Configure the model factory to create criteria with moderate scores.
     */
    public function moderateScore(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->randomFloat(2, 70, 85),
            'justification' => fake()->paragraph(2),
        ]);
    }

    /**
     * Configure the model factory to create criteria with low scores.
     */
    public function lowScore(): static
    {
        return $this->state(fn (array $attributes) => [
            'score' => fake()->randomFloat(2, 50, 70),
            'justification' => fake()->paragraph(2),
        ]);
    }

    /**
     * Configure the model factory to create criteria with high weight.
     */
    public function highWeight(): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => fake()->randomFloat(2, 15, 20),
        ]);
    }

    /**
     * Configure the model factory to create criteria with moderate weight.
     */
    public function moderateWeight(): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => fake()->randomFloat(2, 10, 15),
        ]);
    }

    /**
     * Configure the model factory to create criteria with low weight.
     */
    public function lowWeight(): static
    {
        return $this->state(fn (array $attributes) => [
            'weight' => fake()->randomFloat(2, 5, 10),
        ]);
    }

    /**
     * Configure the model factory to create criteria for specific assessment.
     */
    public function forAssessment($assessmentId): static
    {
        return $this->state(fn (array $attributes) => [
            'assessment_id' => $assessmentId,
        ]);
    }

    /**
     * Configure the model factory to create criteria with detailed justification.
     */
    public function withDetailedJustification(): static
    {
        return $this->state(fn (array $attributes) => [
            'justification' => fake()->paragraph(4),
        ]);
    }

    /**
     * Configure the model factory to create criteria without justification.
     */
    public function withoutJustification(): static
    {
        return $this->state(fn (array $attributes) => [
            'justification' => null,
        ]);
    }

    /**
     * Configure the model factory to create criteria with metadata.
     */
    public function withMetadata(): static
    {
        return $this->state(fn (array $attributes) => [
            'metadata' => [
                'evaluation_method' => fake()->randomElement(['Quantitative', 'Qualitative', 'Mixed']),
                'data_source' => fake()->randomElement(['Survey', 'Interview', 'Document Review', 'Observation', 'Secondary Data']),
                'benchmark' => fake()->randomFloat(2, 60, 90),
                'threshold' => fake()->randomFloat(2, 40, 70),
                'confidence_level' => fake()->randomFloat(2, 0.7, 0.95),
                'evaluator_notes' => fake()->sentence(3),
                'scoring_rubric' => fake()->optional()->randomElement(['Excellent', 'Good', 'Satisfactory', 'Needs Improvement']),
                'validation_method' => fake()->optional()->randomElement(['Peer Review', 'External Validation', 'Internal Check']),
            ],
        ]);
    }
}