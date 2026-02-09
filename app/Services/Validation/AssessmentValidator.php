<?php

namespace App\Services\Validation;

use App\Models\Assessment;
use App\Models\PerformanceData;
use Illuminate\Support\Facades\Validator;

/**
 * Assessment Validator
 *
 * Handles validation of assessments including:
 * - Score validation
 * - Performance data prerequisites
 * - Evidence requirements
 * - Grading consistency
 */
class AssessmentValidator
{
    /**
     * Validation rules for assessments.
     */
    protected array $rules = [
        'score' => 'required|numeric|between:0,100',
        'grade' => 'nullable|string|in:A,B,C,D,E',
        'notes' => 'nullable|string|max:5000',
        'recommendations' => 'nullable|string|max:5000',
        'assessment_period' => 'required|string|max:50',
        'assessor_id' => 'nullable|exists:users,id',
        'performance_data_id' => 'nullable|exists:performance_data,id',
    ];

    /**
     * Grade thresholds.
     */
    protected array $gradeThresholds = [
        'A' => ['min' => 90, 'max' => 100],
        'B' => ['min' => 80, 'max' => 89],
        'C' => ['min' => 70, 'max' => 79],
        'D' => ['min' => 60, 'max' => 69],
        'E' => ['min' => 0, 'max' => 59],
    ];

    /**
     * Validate assessment.
     */
    public function validate(Assessment $assessment, array $additionalRules = []): array
    {
        $result = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        // Basic validation
        $basicResult = $this->validateBasicFields($assessment, $additionalRules);
        $result = $this->mergeResults($result, $basicResult);

        // Score validation
        $scoreResult = $this->validateScore($assessment);
        $result = $this->mergeResults($result, $scoreResult);

        // Grade validation
        if ($assessment->grade) {
            $gradeResult = $this->validateGrade($assessment);
            $result = $this->mergeResults($result, $gradeResult);
        }

        // Performance data validation
        if ($assessment->performanceData) {
            $performanceDataResult = $this->validatePerformanceDataPrerequisite(
                $assessment,
            );
            $result = $this->mergeResults($result, $performanceDataResult);
        }

        // Evidence validation
        $evidenceResult = $this->validateEvidence($assessment);
        $result = $this->mergeResults($result, $evidenceResult);

        $result['is_valid'] = empty($result['errors']);

        return $result;
    }

    /**
     * Validate basic fields.
     */
    protected function validateBasicFields(Assessment $assessment, array $additionalRules): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $data = [
            'score' => $assessment->score,
            'grade' => $assessment->grade,
            'notes' => $assessment->notes,
            'recommendations' => $assessment->recommendations,
            'assessment_period' => $assessment->assessment_period,
            'assessor_id' => $assessment->assessor_id,
            'performance_data_id' => $assessment->performance_data_id,
        ];

        $rules = array_merge($this->rules, $additionalRules);
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $result['errors'][] = $error;
            }
        }

        return $result;
    }

    /**
     * Validate score.
     */
    protected function validateScore(Assessment $assessment): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        if ($assessment->score < 0 || $assessment->score > 100) {
            $result['errors'][] = 'Score must be between 0 and 100';

            return $result;
        }

        // Check score against performance data if available
        if ($assessment->performanceData) {
            $performanceData = $assessment->performanceData;

            if ($performanceData->actual_value !== null && $performanceData->target_value !== null) {
                $achievement = ($performanceData->actual_value / $performanceData->target_value) * 100;
                $expectedScore = $this->calculateExpectedScore($achievement);
                $difference = abs($assessment->score - $expectedScore);

                if ($difference > 20) {
                    $result['warnings'][] = 'Assessment score significantly differs from expected score based on performance data';
                    $result['suggestions'][] = "Expected score around {$expectedScore} based on ".round($achievement, 1).'% achievement';
                }
            }
        }

        return $result;
    }

    /**
     * Validate grade matches score.
     */
    protected function validateGrade(Assessment $assessment): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $grade = $assessment->grade;
        $score = $assessment->score;

        if (! isset($this->gradeThresholds[$grade])) {
            $result['errors'][] = "Invalid grade: {$grade}";

            return $result;
        }

        $threshold = $this->gradeThresholds[$grade];

        if ($score < $threshold['min'] || $score > $threshold['max']) {
            $result['warnings'][] = "Grade {$grade} does not match score of {$score}";
            $expectedGrade = $this->getGradeForScore($score);
            $result['suggestions'][] = "Expected grade: {$expectedGrade}";
        }

        return $result;
    }

    /**
     * Validate performance data prerequisite.
     */
    protected function validatePerformanceDataPrerequisite(Assessment $assessment): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $performanceData = $assessment->performanceData;

        // Performance data should be validated before assessment
        if ($performanceData->status !== 'validated') {
            $result['errors'][] = 'Performance data must be validated before assessment';
            $result['suggestions'][] = 'Validate the associated performance data first';
        }

        // Check if assessment period matches data period
        $assessmentPeriod = $assessment->assessment_period;
        $dataPeriod = substr($performanceData->period, 0, 7); // YYYY-MM

        if (! str_contains($assessmentPeriod, $dataPeriod)) {
            $result['warnings'][] = 'Assessment period may not match performance data period';
            $result['suggestions'][] = 'Verify the periods are correctly aligned';
        }

        return $result;
    }

    /**
     * Validate evidence documents.
     */
    protected function validateEvidence(Assessment $assessment): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $evidenceCount = $assessment->evidenceDocuments()->count();

        if ($evidenceCount === 0) {
            $result['warnings'][] = 'No evidence documents provided for assessment';
            $result['suggestions'][] = 'Upload evidence to support assessment findings';
        }

        // Completed assessments should have evidence
        if ($assessment->status === 'completed' && $evidenceCount < 2) {
            $result['warnings'][] = 'Completed assessments should have supporting evidence';
            $result['suggestions'][] = 'Upload at least 2 evidence documents';
        }

        return $result;
    }

    /**
     * Calculate expected score based on achievement.
     */
    protected function calculateExpectedScore(float $achievement): float
    {
        // Simple linear mapping: achievement % = score
        // This can be customized based on organization's scoring criteria
        return min(100, max(0, round($achievement, 2)));
    }

    /**
     * Get grade for a given score.
     */
    protected function getGradeForScore(float $score): string
    {
        foreach ($this->gradeThresholds as $grade => $threshold) {
            if ($score >= $threshold['min'] && $score <= $threshold['max']) {
                return $grade;
            }
        }

        return 'E';
    }

    /**
     * Validate array data for assessment creation/update.
     */
    public function validateFromArray(array $data): array
    {
        $result = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $validator = Validator::make($data, $this->rules);

        if ($validator->fails()) {
            $result['is_valid'] = false;
            foreach ($validator->errors()->all() as $error) {
                $result['errors'][] = $error;
            }
        }

        // Check performance data exists and is validated
        if (isset($data['performance_data_id'])) {
            $performanceData = PerformanceData::find($data['performance_data_id']);
            if (! $performanceData) {
                $result['errors'][] = 'Performance data not found';
                $result['is_valid'] = false;
            } elseif ($performanceData->status !== 'validated') {
                $result['warnings'][] = 'Associated performance data is not validated';
            }
        }

        return $result;
    }

    /**
     * Merge validation results.
     */
    protected function mergeResults(array $result1, array $result2): array
    {
        return [
            'errors' => array_merge($result1['errors'] ?? [], $result2['errors'] ?? []),
            'warnings' => array_merge($result1['warnings'] ?? [], $result2['warnings'] ?? []),
            'suggestions' => array_merge(
                $result1['suggestions'] ?? [],
                $result2['suggestions'] ?? [],
            ),
        ];
    }

    /**
     * Batch validate multiple assessments.
     */
    public function batchValidate(array $assessmentIds): array
    {
        $results = [
            'total' => count($assessmentIds),
            'valid' => 0,
            'invalid' => 0,
            'warnings' => 0,
            'details' => [],
        ];

        foreach ($assessmentIds as $id) {
            $assessment = Assessment::find($id);

            if (! $assessment) {
                $results['details'][$id] = [
                    'status' => 'not_found',
                    'validation' => null,
                ];

                continue;
            }

            $validation = $this->validate($assessment);

            if ($validation['is_valid']) {
                $results['valid']++;
                $status = 'valid';
            } else {
                $results['invalid']++;
                $status = 'invalid';
            }

            if (! empty($validation['warnings'])) {
                $results['warnings']++;
            }

            $results['details'][$id] = [
                'status' => $status,
                'validation' => $validation,
            ];
        }

        return $results;
    }
}
