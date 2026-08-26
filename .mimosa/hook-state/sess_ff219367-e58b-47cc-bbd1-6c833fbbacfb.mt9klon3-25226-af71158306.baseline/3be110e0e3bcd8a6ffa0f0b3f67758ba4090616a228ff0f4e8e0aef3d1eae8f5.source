<?php

namespace App\Services\Validation;

use App\Models\PerformanceData;
use Illuminate\Support\Facades\Validator;

/**
 * Performance Data Validator
 *
 * Handles validation of performance data entries including:
 * - Basic field validation
 * - Measurement type validation
 * - Historical data comparison
 * - Temporal consistency checks
 */
class PerformanceDataValidator
{
    /**
     * Validation rules for performance data.
     */
    protected array $rules = [
        'actual_value' => 'required|numeric',
        'target_value' => 'nullable|numeric|min:0',
        'period' => 'required|date',
        'collected_at' => 'required|date',
        'data_source' => 'nullable|string|max:255',
        'collection_method' => 'nullable|string|max:255',
        'status' => 'required|in:draft,submitted,validated,rejected',
    ];

    /**
     * Measurement type specific rules.
     */
    protected array $measurementTypeRules = [
        'percentage' => ['min' => 0, 'max' => 100],
        'ratio' => ['min' => 0],
        'count' => ['min' => 0, 'integer' => true],
        'index' => ['min' => 0, 'max' => 100],
    ];

    /**
     * Validate performance data.
     */
    public function validate(
        PerformanceData $performanceData,
        array $additionalRules = []
    ): array {
        $result = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
            'quality_score' => 100,
        ];

        // Basic Laravel validation
        $basicResult = $this->validateBasicFields($performanceData, $additionalRules);
        $result = $this->mergeResults($result, $basicResult);

        // Measurement type validation
        if ($performanceData->performanceIndicator) {
            $measurementResult = $this->validateMeasurementType($performanceData);
            $result = $this->mergeResults($result, $measurementResult);
        }

        // Historical data validation
        $historicalResult = $this->validateAgainstHistoricalData($performanceData);
        if ($historicalResult) {
            $result = $this->mergeResults($result, $historicalResult);
        }

        // Temporal validation
        $temporalResult = $this->validateTemporalConsistency($performanceData);
        $result = $this->mergeResults($result, $temporalResult);

        // Calculate quality score
        $result['quality_score'] = $this->calculateQualityScore($result);
        $result['is_valid'] = $result['quality_score'] >= 70;

        return $result;
    }

    /**
     * Validate basic fields using Laravel validator.
     */
    protected function validateBasicFields(
        PerformanceData $performanceData,
        array $additionalRules
    ): array {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $data = [
            'actual_value' => $performanceData->actual_value,
            'target_value' => $performanceData->target_value,
            'period' => $performanceData->period,
            'collected_at' => $performanceData->collected_at,
            'data_source' => $performanceData->data_source,
            'collection_method' => $performanceData->collection_method,
            'status' => $performanceData->status,
        ];

        $rules = array_merge($this->rules, $additionalRules);
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $result['errors'][] = $error;
            }
        }

        // Check for recommended fields
        if (empty($performanceData->data_source)) {
            $result['warnings'][] = 'Data source is recommended for traceability';
            $result['suggestions'][] = 'Provide the source of this data';
        }

        if (empty($performanceData->collection_method)) {
            $result['warnings'][] = 'Collection method is recommended';
            $result['suggestions'][] = 'Specify how this data was collected';
        }

        return $result;
    }

    /**
     * Validate based on measurement type.
     */
    protected function validateMeasurementType(PerformanceData $performanceData): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $indicator = $performanceData->performanceIndicator;
        $measurementType = $indicator->measurement_type ?? 'count';
        $actualValue = $performanceData->actual_value;

        if ($actualValue === null) {
            return $result;
        }

        $rules = $this->measurementTypeRules[$measurementType] ?? [];

        if (isset($rules['min']) && $actualValue < $rules['min']) {
            $result['errors'][] = ucfirst($measurementType).' value cannot be negative';
        }

        if (isset($rules['max']) && $actualValue > $rules['max']) {
            $result['errors'][] = ucfirst($measurementType)." value exceeds maximum of {$rules['max']}";
        }

        if (isset($rules['integer']) && $rules['integer']) {
            if (! is_int($actualValue) && $actualValue != (int) $actualValue) {
                $result['warnings'][] = 'Count value should be an integer';
                $result['suggestions'][] = 'Consider rounding to the nearest integer';
            }
        }

        return $result;
    }

    /**
     * Validate against historical data.
     */
    protected function validateAgainstHistoricalData(PerformanceData $performanceData): ?array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $historicalData = PerformanceData::where(
            'performance_indicator_id',
            $performanceData->performance_indicator_id,
        )
            ->where('instansi_id', $performanceData->instansi_id)
            ->where('period', '<', $performanceData->period)
            ->orderBy('period', 'desc')
            ->limit(3)
            ->get();

        if ($historicalData->isEmpty()) {
            return null;
        }

        $historicalValues = $historicalData
            ->pluck('actual_value')
            ->filter()
            ->toArray();

        if (empty($historicalValues)) {
            return null;
        }

        $currentValue = $performanceData->actual_value;
        $averageHistorical = array_sum($historicalValues) / count($historicalValues);
        $deviation = abs($currentValue - $averageHistorical) / max($averageHistorical, 1);

        // Check for significant deviation
        if ($deviation > 0.5) {
            $result['warnings'][] = 'Significant deviation from historical average';
            $result['suggestions'][] = 'Verify data accuracy and provide explanation for variation';
        }

        // Check for unusual growth/decline
        $lastValue = $historicalValues[0];
        if ($lastValue > 0) {
            $change = abs($currentValue - $lastValue) / $lastValue;
            if ($change > 1.0) {
                $result['warnings'][] = 'Unusual change from previous period';
                $result['suggestions'][] = 'Provide justification for significant change';
            }
        }

        return $result;
    }

    /**
     * Validate temporal consistency.
     */
    protected function validateTemporalConsistency(PerformanceData $performanceData): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $period = $performanceData->period;
        $collectedAt = $performanceData->collected_at;
        $validatedAt = $performanceData->validated_at;

        if ($collectedAt) {
            $periodDate = \Carbon\Carbon::parse($period);
            $collectionDate = \Carbon\Carbon::parse($collectedAt);

            // Collection date too far from period
            if ($collectionDate->diffInMonths($periodDate) > 6) {
                $result['warnings'][] = 'Data collection date is far from reporting period';
                $result['suggestions'][] = 'Ensure data collection timing is appropriate';
            }

            // Collection date in future
            if ($collectionDate->isFuture()) {
                $result['errors'][] = 'Data collection date cannot be in the future';
            }
        }

        if ($validatedAt && $collectedAt) {
            $validationDate = \Carbon\Carbon::parse($validatedAt);

            if ($validationDate->lt(\Carbon\Carbon::parse($collectedAt))) {
                $result['errors'][] = 'Validation date cannot be before collection date';
            }

            if ($validationDate->diffInMonths(now()) > 12) {
                $result['warnings'][] = 'Validation date is old';
                $result['suggestions'][] = 'Consider re-validating data';
            }
        }

        return $result;
    }

    /**
     * Calculate quality score.
     */
    protected function calculateQualityScore(array $result): float
    {
        $baseScore = 100;
        $errorDeduction = count($result['errors']) * 20;
        $warningDeduction = count($result['warnings']) * 5;

        return max(0, min(100, $baseScore - $errorDeduction - $warningDeduction));
    }

    /**
     * Merge validation results.
     */
    protected function mergeResults(array $result1, array $result2): array
    {
        return [
            'errors' => array_merge($result1['errors'] ?? [], $result2['errors'] ?? []),
            'warnings' => array_merge($result1['warnings'] ?? [], $result2['warnings'] ?? []),
            'suggestions' => array_merge($result1['suggestions'] ?? [], $result2['suggestions'] ?? []),
        ];
    }

    /**
     * Batch validate multiple performance data entries.
     */
    public function batchValidate(array $performanceDataIds): array
    {
        $results = [
            'total' => count($performanceDataIds),
            'valid' => 0,
            'invalid' => 0,
            'warnings' => 0,
            'details' => [],
        ];

        foreach ($performanceDataIds as $id) {
            $performanceData = PerformanceData::find($id);

            if (! $performanceData) {
                $results['details'][$id] = [
                    'status' => 'not_found',
                    'validation' => null,
                ];

                continue;
            }

            $validation = $this->validate($performanceData);

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
