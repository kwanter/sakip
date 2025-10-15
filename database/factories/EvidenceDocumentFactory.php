<?php

namespace Database\Factories;

use App\Models\EvidenceDocument;
use App\Models\PerformanceData;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EvidenceDocument>
 */
class EvidenceDocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EvidenceDocument::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileTypes = [
            'pdf' => ['application/pdf', 'PDF Document'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'Excel Spreadsheet'],
            'xls' => ['application/vnd.ms-excel', 'Excel Spreadsheet'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'Word Document'],
            'doc' => ['application/msword', 'Word Document'],
            'jpg' => ['image/jpeg', 'JPEG Image'],
            'png' => ['image/png', 'PNG Image'],
            'csv' => ['text/csv', 'CSV File'],
        ];

        $extension = fake()->randomElement(array_keys($fileTypes));
        $fileInfo = $fileTypes[$extension];
        $fileName = 'evidence_' . fake()->bothify('####') . '_' . fake()->word() . '.' . $extension;
        $fileSize = fake()->numberBetween(10240, 5242880); // 10KB to 5MB

        return [
            'performance_data_id' => PerformanceData::inRandomOrder()->first()->id ?? PerformanceData::factory(),
            'file_name' => $fileName,
            'file_path' => 'sakip/evidence/' . date('Y/m') . '/' . $fileName,
            'file_type' => $fileInfo[0],
            'file_size' => $fileSize,
            'description' => fake()->optional(0.8)->sentence(15),
            'metadata' => [
                'original_name' => $fileName,
                'extension' => $extension,
                'mime_type' => $fileInfo[0],
                'category' => fake()->randomElement(['Supporting Document', 'Calculation', 'Report', 'Image', 'Spreadsheet']),
                'upload_date' => fake()->date(),
                'checksum' => fake()->sha256(),
                'tags' => fake()->optional()->randomElements(['financial', 'operational', 'compliance', 'quality'], 2),
            ],
            'uploaded_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'created_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'updated_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }

    /**
     * Indicate that the document is a PDF file.
     */
    public function pdf(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_name' => 'evidence_' . fake()->bothify('####') . '_' . fake()->word() . '.pdf',
            'file_type' => 'application/pdf',
            'file_path' => 'sakip/evidence/' . date('Y/m') . '/' . 'evidence_' . fake()->bothify('####') . '_' . fake()->word() . '.pdf',
        ]);
    }

    /**
     * Indicate that the document is an Excel file.
     */
    public function excel(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_name' => 'evidence_' . fake()->bothify('####') . '_' . fake()->word() . '.xlsx',
            'file_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'file_path' => 'sakip/evidence/' . date('Y/m') . '/' . 'evidence_' . fake()->bothify('####') . '_' . fake()->word() . '.xlsx',
        ]);
    }

    /**
     * Indicate that the document is an image.
     */
    public function image(): static
    {
        $extension = fake()->randomElement(['jpg', 'png']);
        return $this->state(fn (array $attributes) => [
            'file_name' => 'evidence_' . fake()->bothify('####') . '_' . fake()->word() . '.' . $extension,
            'file_type' => $extension === 'jpg' ? 'image/jpeg' : 'image/png',
            'file_path' => 'sakip/evidence/' . date('Y/m') . '/' . 'evidence_' . fake()->bothify('####') . '_' . fake()->word() . '.' . $extension,
        ]);
    }

    /**
     * Indicate that the document is a Word document.
     */
    public function word(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_name' => 'evidence_' . fake()->bothify('####') . '_' . fake()->word() . '.docx',
            'file_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'file_path' => 'sakip/evidence/' . date('Y/m') . '/' . 'evidence_' . fake()->bothify('####') . '_' . fake()->word() . '.docx',
        ]);
    }

    /**
     * Configure the model factory to create documents for specific performance data.
     */
    public function forPerformanceData($performanceDataId): static
    {
        return $this->state(fn (array $attributes) => [
            'performance_data_id' => $performanceDataId,
        ]);
    }

    /**
     * Configure the model factory to create documents uploaded by a specific user.
     */
    public function uploadedBy($userId): static
    {
        return $this->state(fn (array $attributes) => [
            'uploaded_by' => $userId,
        ]);
    }

    /**
     * Configure the model factory to create large files.
     */
    public function largeFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => fake()->numberBetween(5242880, 10485760), // 5MB to 10MB
        ]);
    }

    /**
     * Configure the model factory to create small files.
     */
    public function smallFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => fake()->numberBetween(10240, 512000), // 10KB to 500KB
        ]);
    }

    /**
     * Configure the model factory to create documents with detailed descriptions.
     */
    public function withDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => fake()->paragraph(3),
        ]);
    }

    /**
     * Configure the model factory to create documents without descriptions.
     */
    public function withoutDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'description' => null,
        ]);
    }
}