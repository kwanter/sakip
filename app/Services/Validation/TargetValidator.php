<?php

namespace App\Services\Validation;

use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Target;
use Illuminate\Support\Facades\Validator;

/**
 * Target Validator
 *
 * Handles validation of performance targets including:
 * - Target value validation
 * - Historical target comparison
 * - Realism checks based on historical data
 * - Consistency validation (baseline, target, stretch)
 */
class TargetValidator
{
    /**
     * Validation rules for targets.
     */
    protected array $rules = [
        'target_value' => 'required|numeric|min:0',
        'baseline_value' => 'nullable|numeric',
        'stretch_value' => 'nullable|numeric|min:0',
        'minimum_value' => 'nullable|numeric',
        'maximum_value' => 'nullable|numeric',
        'year' => 'required|integer|min:2020|max:2035',
        'period' => 'nullable|string',
        'weight' => 'nullable|numeric|between:0,100',
    ];

    /**
     * Validate target data.
     */
    public function validate(Target $target, array $additionalRules = []): array
    {
        $result = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        // Basic validation
        $basicResult = $this->validateBasicFields($target, $additionalRules);
        $result = $this->mergeResults($result, $basicResult);

        // Consistency validation
        $consistencyResult = $this->validateConsistency($target);
        $result = $this->mergeResults($result, $consistencyResult);

        // Historical validation
        if ($target->performanceIndicator) {
            $historicalResult = $this->validateAgainstHistory($target);
            $result = $this->mergeResults($result, $historicalResult);
        }

        // Indicator-specific validation
        if ($target->performanceIndicator) {
            $indicatorResult = $this->validateAgainstIndicator($target);
            $result = $this->mergeResults($result, $indicatorResult);
        }

        $result['is_valid'] = empty($result['errors']);

        return $result;
    }

    /**
     * Validate basic fields.
     */
    protected function validateBasicFields(Target $target, array $additionalRules): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $data = [
            'target_value' => $target->target_value,
            'baseline_value' => $target->baseline_value,
            'stretch_value' => $target->stretch_value,
            'minimum_value' => $target->minimum_value,
            'maximum_value' => $target->maximum_value,
            'year' => $target->year,
            'period' => $target->period,
            'weight' => $target->weight,
        ];

        $rules = array_merge($this->rules, $additionalRules);
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $result['errors'][] = $error;
            }
        }

        // Specific checks
        if ($target->target_value <= 0) {
            $result['errors'][] = 'Target value must be positive';
        }

        return $result;
    }

    /**
     * Validate target consistency.
     */
    protected function validateConsistency(Target $target): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        // Baseline vs target
        if (
            $target->baseline_value !== null &&
            $target->baseline_value > $target->target_value
        ) {
            $result['warnings'][] = 'Target value is less than baseline';
            $result['suggestions'][] = 'Consider if this decline is intentional';
        }

        // Stretch vs target
        if (
            $target->stretch_value !== null &&
            $target->stretch_value < $target->target_value
        ) {
            $result['errors'][] = 'Stretch value must be greater than target value';
        }

        // Min/max consistency
        if (
            $target->minimum_value !== null &&
            $target->maximum_value !== null &&
            $target->minimum_value > $target->maximum_value
        ) {
            $result['errors'][] = 'Minimum value cannot be greater than maximum value';
        }

        // Target within min/max range
        if ($target->minimum_value !== null && $target->target_value < $target->minimum_value) {
            $result['warnings'][] = 'Target value is below minimum threshold';
        }

        if ($target->maximum_value !== null && $target->target_value > $target->maximum_value) {
            $result['warnings'][] = 'Target value exceeds maximum threshold';
        }

        return $result;
    }

    /**
     * Validate against historical targets.
     */
    protected function validateAgainstHistory(Target $target): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $previousTargets = Target::where('performance_indicator_id', $target->performance_indicator_id)
            ->where('id', '!=', $target->id)
            ->where('year', '<', $target->year)
            ->orderBy('year', 'desc')
            ->limit(3)
            ->get();

        if ($previousTargets->isEmpty()) {
            return $result;
        }

        $averageTarget = $previousTargets->avg('target_value');
        $change = abs($target->target_value - $averageTarget) / max($averageTarget, 1) * 100;

        // Significant change warning
        if ($change > 50) {
            $result['warnings'][] = 'Target has significant change from historical values';
            $result['suggestions'][] = 'Ensure this change is justified and achievable';
        }

        // Check for declining trend
        if ($previousTargets->first()->target_value > $target->target_value) {
            $decliningTrend = true;
            foreach ($previousTargets as $prev) {
                if ($prev->target_value <= $target->target_value) {
                    $decliningTrend = false;
                    break;
                }
            }
            if ($decliningTrend) {
                $result['warnings'][] = 'Target shows declining trend';
                $result['suggestions'][] = 'Consider if this aligns with organizational goals';
            }
        }

        return $result;
    }

    /**
     * Validate against indicator constraints.
     */
    protected function validateAgainstIndicator(Target $target): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $indicator = $target->performanceIndicator;

        // Check measurement unit compatibility
        if (
            ! empty($indicator->measurement_unit) &&
            str_contains(strtolower($indicator->measurement_unit), 'percentage') &&
            $target->target_value > 100
        ) {
            $result['errors'][] = 'Target value exceeds 100% for percentage indicator';
        }

        // Check if target is realistic based on historical performance
        $historicalPerformance = PerformanceData::where('performance_indicator_id', $indicator->id)
            ->where('period', '<', $target->year.'-01-01')
            ->orderBy('period', 'desc')
            ->limit(3)
            ->get();

        if ($historicalPerformance->isNotEmpty()) {
            $averagePerformance = $historicalPerformance->avg('actual_value');

            if ($averagePerformance > 0) {
                // Target too ambitious
                if ($target->target_value > $averagePerformance * 1.5) {
                    $result['warnings'][] = 'Target may be too ambitious';
                    $result['suggestions'][] = 'Consider more realistic target based on historical performance';
                }

                // Target too conservative
                if ($target->target_value < $averagePerformance * 0.8) {
                    $result['warnings'][] = 'Target may be too conservative';
                    $result['suggestions'][] = 'Consider more challenging target to drive improvement';
                }
            }
        }

        return $result;
    }

    /**
     * Validate array data for target creation/update.
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

        // Check indicator exists
        if (isset($data['performance_indicator_id'])) {
            $indicator = PerformanceIndicator::find($data['performance_indicator_id']);
            if (! $indicator) {
                $result['errors'][] = 'Performance indicator not found';
                $result['is_valid'] = false;
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
            'suggestions' => array_merge($result1['suggestions'] ?? [], $result2['suggestions'] ?? []),
        ];
    }

    /**
     * Batch validate multiple targets.
     */
    public function batchValidate(array $targetIds): array
    {
        $results = [
            'total' => count($targetIds),
            'valid' => 0,
            'invalid' => 0,
            'warnings' => 0,
            'details' => [],
        ];

        foreach ($targetIds as $id) {
            $target = Target::find($id);

            if (! $target) {
                $results['details'][$id] = [
                    'status' => 'not_found',
                    'validation' => null,
                ];

                continue;
            }

            $validation = $this->validate($target);

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
