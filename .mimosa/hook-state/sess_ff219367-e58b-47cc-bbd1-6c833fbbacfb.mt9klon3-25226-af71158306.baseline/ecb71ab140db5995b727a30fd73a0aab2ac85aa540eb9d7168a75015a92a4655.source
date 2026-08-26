<?php

namespace App\Services\Calculation;

use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Target;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Performance Calculation Service
 *
 * Centralizes all performance percentage calculations with comprehensive
 * edge case handling. Extracted from controllers to ensure consistency
 * across the application.
 *
 * Handles:
 * - Standard percentage calculations
 * - Zero and null target values
 * - Negative targets (cost reduction goals)
 * - Reduction vs achievement goals
 * - Rounding and capping logic
 *
 * @package App\Services\Calculation
 */
class PerformanceCalculationService
{
    /**
     * Calculation formula types
     */
    public const FORMULA_STANDARD = 'standard';           // (actual / target) * 100
    public const FORMULA_REDUCTION = 'reduction';         // Reduction goal (cost, time, etc.)
    public const FORMULA_ACHIEVEMENT = 'achievement';     // Achievement goal (quality, satisfaction)
    public const FORMULA_CUSTOM = 'custom';               // Custom formula defined in indicator

    /**
     * Default performance percentage when target is null/zero but actual exists
     */
    public const DEFAULT_PERCENTAGE = 100;

    /**
     * Maximum performance percentage for standard calculations
     */
    public const MAX_PERCENTAGE = 200;

    /**
     * Calculate performance percentage with comprehensive edge case handling
     *
     * This method centralizes the calculation logic that was previously duplicated
     * across DataCollectionController and PerformanceIndicator model.
     *
     * @param float $actualValue The actual achieved value
     * @param float|null $targetValue The target/goal value
     * @param string|null $formulaType Calculation formula type (standard, reduction, achievement, custom)
     * @return float Performance percentage (0-200 depending on formula)
     */
    public function calculatePercentage(
        float $actualValue,
        ?float $targetValue,
        ?string $formulaType = self::FORMULA_STANDARD
    ): float {
        // Handle null/empty/zero targets
        if ($this->isNullOrEmpty($targetValue)) {
            return $this->handleNullOrEmptyTarget($actualValue);
        }

        // Handle negative target values (e.g., cost reduction goals)
        if ($targetValue < 0) {
            return $this->handleNegativeTarget($actualValue, $targetValue);
        }

        // Handle negative actual values with positive targets
        if ($actualValue < 0) {
            return 0.0;
        }

        // Calculate based on formula type
        return $this->applyFormula($actualValue, $targetValue, $formulaType);
    }

    /**
     * Calculate performance percentage for a PerformanceData record
     *
     * Convenience method that fetches target and formula from the indicator
     *
     * @param PerformanceData $performanceData The performance data record
     * @return float Performance percentage
     */
    public function calculateForPerformanceData(PerformanceData $performanceData): float
    {
        $indicator = $performanceData->indicator;
        $formulaType = $indicator->calculation_formula ?? self::FORMULA_STANDARD;

        return $this->calculatePercentage(
            $performanceData->actual_value,
            $performanceData->target_value,
            $formulaType
        );
    }

    /**
     * Calculate performance percentage with manual target lookup
     *
     * Used when target value needs to be fetched from Target model
     *
     * @param PerformanceIndicator $indicator The indicator
     * @param float $actualValue The actual achieved value
     * @param Carbon $period The period for the data
     * @return float Performance percentage
     */
    public function calculateWithTargetLookup(
        PerformanceIndicator $indicator,
        float $actualValue,
        Carbon $period
    ): float {
        // Fetch target for the period
        $target = $indicator->targets()
            ->where('year', $period->year)
            ->where('period', '<=', $period->format('Y-m-d'))
            ->orderBy('period', 'desc')
            ->first();

        $targetValue = $target ? $target->target_value : null;
        $formulaType = $indicator->calculation_formula ?? self::FORMULA_STANDARD;

        return $this->calculatePercentage($actualValue, $targetValue, $formulaType);
    }

    /**
     * Recalculate performance percentage for all data in a period
     *
     * Useful when target values or calculation formulas change
     *
     * @param string $indicatorId The indicator ID
     * @param int $year The year to recalculate
     * @return array Recalculation results
     */
    public function recalculatePeriod(string $indicatorId, int $year): array
    {
        $performanceData = PerformanceData::where('indicator_id', $indicatorId)
            ->whereYear('period', $year)
            ->get();

        $results = [
            'recalculated' => 0,
            'unchanged' => 0,
            'errors' => [],
        ];

        foreach ($performanceData as $data) {
            try {
                $oldPercentage = $data->performance_percentage;
                $newPercentage = $this->calculateForPerformanceData($data);

                if ($oldPercentage != $newPercentage) {
                    $data->update([
                        'performance_percentage' => $newPercentage,
                        'updated_by' => auth()->id(),
                    ]);
                    $results['recalculated']++;
                } else {
                    $results['unchanged']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'period' => $data->period,
                    'error' => $e->getMessage(),
                ];
                Log::error("Recalculation error for data {$data->id}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Handle null or empty target values
     *
     * @param float $actualValue
     * @return float Performance percentage
     */
    protected function handleNullOrEmptyTarget(float $actualValue): float
    {
        // If target is 0 or null, we cannot calculate percentage
        // Return 100 if actual exists (achievement by default), else 0
        return !empty($actualValue) && $actualValue != 0
            ? self::DEFAULT_PERCENTAGE
            : 0.0;
    }

    /**
     * Handle negative target values (reduction goals)
     *
     * @param float $actualValue
     * @param float $targetValue
     * @return float Performance percentage
     */
    protected function handleNegativeTarget(float $actualValue, float $targetValue): float
    {
        if ($actualValue < 0) {
            // Both negative: calculate ratio of reduction achieved
            $performance = abs($actualValue / $targetValue) * 100;
            return min($performance, self::MAX_PERCENTAGE); // Cap at MAX_PERCENTAGE
        } else {
            // Target negative, actual positive: goal not met
            return 0.0;
        }
    }

    /**
     * Apply calculation formula based on type
     *
     * @param float $actualValue
     * @param float $targetValue
     * @param string $formulaType
     * @return float Performance percentage
     */
    protected function applyFormula(float $actualValue, float $targetValue, string $formulaType): float
    {
        switch ($formulaType) {
            case self::FORMULA_REDUCTION:
                return $this->calculateReduction($actualValue, $targetValue);

            case self::FORMULA_ACHIEVEMENT:
                return $this->calculateAchievement($actualValue, $targetValue);

            case self::FORMULA_CUSTOM:
                // Custom formulas would be handled here
                // For now, fall back to standard
                return $this->calculateStandard($actualValue, $targetValue);

            case self::FORMULA_STANDARD:
            default:
                return $this->calculateStandard($actualValue, $targetValue);
        }
    }

    /**
     * Standard calculation: (actual / target) * 100
     *
     * @param float $actualValue
     * @param float $targetValue
     * @return float Performance percentage
     */
    protected function calculateStandard(float $actualValue, float $targetValue): float
    {
        $performance = ($actualValue / $targetValue) * 100;
        return round(max(0, $performance), 2);
    }

    /**
     * Reduction formula: for cost/time reduction goals
     *
     * @param float $actualValue
     * @param float $targetValue
     * @return float Performance percentage
     */
    protected function calculateReduction(float $actualValue, float $targetValue): float
    {
        // Reduction is the inverse of standard
        // If we aimed to reduce by 100 and reduced by 120, that's 120%
        $performance = ($targetValue / $actualValue) * 100;
        return round(min(max(0, $performance), self::MAX_PERCENTAGE), 2);
    }

    /**
     * Achievement formula: for quality/satisfaction goals
     *
     * @param float $actualValue
     * @param float $targetValue
     * @return float Performance percentage
     */
    protected function calculateAchievement(float $actualValue, float $targetValue): float
    {
        // Achievement is similar to standard but may have different capping
        $performance = ($actualValue / $targetValue) * 100;

        // For achievement, we typically cap at 100% (no bonus for exceeding)
        return round(min(max(0, $performance), 100), 2);
    }

    /**
     * Check if value is null or empty
     *
     * @param mixed $value
     * @return bool
     */
    protected function isNullOrEmpty($value): bool
    {
        return $value === null || $value === '';
    }

    /**
     * Calculate average performance for a collection of performance data
     *
     * @param \Illuminate\Support\Collection $performanceData
     * @return float Average performance percentage
     */
    public function calculateAverage($performanceData): float
    {
        if ($performanceData->isEmpty()) {
            return 0.0;
        }

        $total = $performanceData->sum('performance_percentage');
        $count = $performanceData->count();

        return round($total / $count, 2);
    }

    /**
     * Calculate weighted average performance
     *
     * Useful when different indicators have different weights
     *
     * @param array $performanceData Array of ['value' => float, 'weight' => float]
     * @return float Weighted average
     */
    public function calculateWeightedAverage(array $performanceData): float
    {
        if (empty($performanceData)) {
            return 0.0;
        }

        $totalWeight = 0;
        $weightedSum = 0;

        foreach ($performanceData as $item) {
            $weightedSum += $item['value'] * $item['weight'];
            $totalWeight += $item['weight'];
        }

        if ($totalWeight == 0) {
            return 0.0;
        }

        return round($weightedSum / $totalWeight, 2);
    }

    /**
     * Validate if performance percentage is within acceptable range
     *
     * @param float $percentage
     * @param float $min Minimum acceptable (default 0)
     * @param float $max Maximum acceptable (default 200)
     * @return bool
     */
    public function isValidPercentage(float $percentage, float $min = 0, float $max = self::MAX_PERCENTAGE): bool
    {
        return $percentage >= $min && $percentage <= $max;
    }

    /**
     * Get performance rating based on percentage
     *
     * Returns a human-readable rating like "Excellent", "Good", etc.
     *
     * @param float $percentage
     * @return string Performance rating
     */
    public function getPerformanceRating(float $percentage): string
    {
        $thresholds = config('sakip.performance.thresholds', [
            'excellent' => 100,
            'good' => 80,
            'satisfactory' => 60,
        ]);

        if ($percentage >= $thresholds['excellent']) {
            return 'Excellent';
        } elseif ($percentage >= $thresholds['good']) {
            return 'Good';
        } elseif ($percentage >= $thresholds['satisfactory']) {
            return 'Satisfactory';
        } else {
            return 'Poor';
        }
    }

    /**
     * Calculate achievement rate (count of achieved vs total)
     *
     * @param \Illuminate\Support\Collection $performanceData
     * @param float $threshold Threshold percentage to consider "achieved" (default 100)
     * @return float Achievement rate as percentage
     */
    public function calculateAchievementRate($performanceData, float $threshold = 100): float
    {
        if ($performanceData->isEmpty()) {
            return 0.0;
        }

        $achieved = $performanceData->filter(function ($data) use ($threshold) {
            return $data->performance_percentage >= $threshold;
        })->count();

        return round(($achieved / $performanceData->count()) * 100, 2);
    }

    /**
     * Calculate performance trend (improving, stable, declining)
     *
     * Compares current period with previous period
     *
     * @param float $currentPercentage
     * @param float $previousPercentage
     * @param float $threshold Threshold for significant change (default 5%)
     * @return string 'improving', 'stable', or 'declining'
     */
    public function calculateTrend(float $currentPercentage, float $previousPercentage, float $threshold = 5.0): string
    {
        $difference = $currentPercentage - $previousPercentage;

        if (abs($difference) < $threshold) {
            return 'stable';
        } elseif ($difference > 0) {
            return 'improving';
        } else {
            return 'declining';
        }
    }

    /**
     * Calculate year-to-date performance
     *
     * Aggregates performance data from start of year to given period
     *
     * @param string $indicatorId
     * @param Carbon $endDate
     * @return array YTD statistics
     */
    public function calculateYearToDate(string $indicatorId, Carbon $endDate): array
    {
        $data = PerformanceData::where('indicator_id', $indicatorId)
            ->whereYear('period', $endDate->year)
            ->where('period', '<=', $endDate->format('Y-m-d'))
            ->get();

        return [
            'average' => $this->calculateAverage($data),
            'achievement_rate' => $this->calculateAchievementRate($data),
            'data_points' => $data->count(),
            'trend' => $this->calculateTrend(
                $data->last()->performance_percentage ?? 0,
                $data->first()->performance_percentage ?? 0
            ),
        ];
    }
}
