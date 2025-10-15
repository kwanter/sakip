<?php

namespace App\Services;

use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\PerformanceMeasurement;
use App\Models\Benchmark;
use Illuminate\Support\Facades\Log;

/**
 * Performance Calculation Service
 * 
 * Handles complex performance calculations, scoring, and benchmarking
 * for SAKIP performance indicators.
 */
class PerformanceCalculationService
{
    /**
     * Calculate performance measurement for an indicator
     */
    public function calculatePerformance($indicatorId, $period, $year)
    {
        try {
            $indicator = PerformanceIndicator::find($indicatorId);
            
            if (!$indicator) {
                throw new \Exception('Performance indicator not found');
            }

            // Get performance data for the period
            $performanceData = PerformanceData::where('indicator_id', $indicatorId)
                ->where('period', $period)
                ->where('year', $year)
                ->first();

            if (!$performanceData) {
                return null;
            }

            // Calculate achievement based on indicator type
            $achievement = $this->calculateAchievement($indicator, $performanceData);

            // Calculate score based on achievement
            $score = $this->calculateScore($indicator, $achievement);

            // Get benchmark comparison
            $benchmarkComparison = $this->getBenchmarkComparison($indicator, $achievement);

            return [
                'indicator_id' => $indicatorId,
                'period' => $period,
                'year' => $year,
                'target' => $indicator->target,
                'actual' => $performanceData->actual_value,
                'achievement' => $achievement,
                'score' => $score,
                'benchmark_comparison' => $benchmarkComparison,
                'calculation_method' => $indicator->calculation_method,
                'evidence_document' => $performanceData->evidence_document,
                'notes' => $performanceData->notes,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to calculate performance: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate achievement based on indicator type and calculation method
     */
    private function calculateAchievement($indicator, $performanceData)
    {
        $target = $indicator->target;
        $actual = $performanceData->actual_value;

        switch ($indicator->calculation_method) {
            case 'percentage':
                return $this->calculatePercentageAchievement($actual, $target);
            
            case 'ratio':
                return $this->calculateRatioAchievement($actual, $target);
            
            case 'absolute':
                return $this->calculateAbsoluteAchievement($actual, $target);
            
            case 'index':
                return $this->calculateIndexAchievement($actual, $target);
            
            default:
                return $this->calculatePercentageAchievement($actual, $target);
        }
    }

    /**
     * Calculate percentage achievement
     */
    private function calculatePercentageAchievement($actual, $target)
    {
        if ($target == 0) {
            return $actual > 0 ? 100 : 0;
        }

        return round(($actual / $target) * 100, 2);
    }

    /**
     * Calculate ratio achievement
     */
    private function calculateRatioAchievement($actual, $target)
    {
        if ($target == 0) {
            return $actual > 0 ? 100 : 0;
        }

        $ratio = $actual / $target;
        
        // For ratio indicators, achievement is based on how close the ratio is to 1
        return round((1 - abs(1 - $ratio)) * 100, 2);
    }

    /**
     * Calculate absolute achievement
     */
    private function calculateAbsoluteAchievement($actual, $target)
    {
        // For absolute indicators, achievement is 100% if actual >= target
        return $actual >= $target ? 100 : round(($actual / $target) * 100, 2);
    }

    /**
     * Calculate index achievement
     */
    private function calculateIndexAchievement($actual, $target)
    {
        // Index calculation based on predefined scales
        $indexRanges = [
            ['min' => 0, 'max' => 0.5, 'score' => 25],
            ['min' => 0.5, 'max' => 0.75, 'score' => 50],
            ['min' => 0.75, 'max' => 0.9, 'score' => 75],
            ['min' => 0.9, 'max' => 1.0, 'score' => 90],
            ['min' => 1.0, 'max' => 999, 'score' => 100],
        ];

        $ratio = $actual / $target;
        
        foreach ($indexRanges as $range) {
            if ($ratio >= $range['min'] && $ratio < $range['max']) {
                return $range['score'];
            }
        }

        return 0;
    }

    /**
     * Calculate score based on achievement
     */
    private function calculateScore($indicator, $achievement)
    {
        // Get scoring criteria for the indicator
        $scoringCriteria = $this->getScoringCriteria($indicator);

        foreach ($scoringCriteria as $criteria) {
            if ($achievement >= $criteria['min_achievement'] && $achievement <= $criteria['max_achievement']) {
                return $criteria['score'];
            }
        }

        return 0;
    }

    /**
     * Get scoring criteria
     */
    private function getScoringCriteria($indicator)
    {
        // Default scoring criteria (can be customized per indicator)
        return [
            ['min_achievement' => 0, 'max_achievement' => 25, 'score' => 1, 'grade' => 'E'],
            ['min_achievement' => 25, 'max_achievement' => 50, 'score' => 2, 'grade' => 'D'],
            ['min_achievement' => 50, 'max_achievement' => 75, 'score' => 3, 'grade' => 'C'],
            ['min_achievement' => 75, 'max_achievement' => 90, 'score' => 4, 'grade' => 'B'],
            ['min_achievement' => 90, 'max_achievement' => 100, 'score' => 5, 'grade' => 'A'],
        ];
    }

    /**
     * Get benchmark comparison
     */
    private function getBenchmarkComparison($indicator, $achievement)
    {
        $benchmarks = Benchmark::where('indicator_id', $indicator->id)
            ->where('is_active', true)
            ->get();

        if ($benchmarks->isEmpty()) {
            return null;
        }

        $comparisons = [];

        foreach ($benchmarks as $benchmark) {
            $comparison = $this->calculateBenchmarkComparison($achievement, $benchmark);
            $comparisons[] = $comparison;
        }

        return $comparisons;
    }

    /**
     * Calculate benchmark comparison
     */
    private function calculateBenchmarkComparison($achievement, $benchmark)
    {
        $benchmarkValue = $benchmark->benchmark_value;
        $difference = $achievement - $benchmarkValue;
        $percentageDifference = $benchmarkValue > 0 ? ($difference / $benchmarkValue) * 100 : 0;

        return [
            'benchmark_name' => $benchmark->name,
            'benchmark_value' => $benchmarkValue,
            'actual_value' => $achievement,
            'difference' => $difference,
            'percentage_difference' => round($percentageDifference, 2),
            'performance_level' => $this->getPerformanceLevel($percentageDifference),
        ];
    }

    /**
     * Get performance level based on benchmark difference
     */
    private function getPerformanceLevel($percentageDifference)
    {
        if ($percentageDifference >= 10) {
            return 'excellent';
        } elseif ($percentageDifference >= 0) {
            return 'good';
        } elseif ($percentageDifference >= -10) {
            return 'average';
        } else {
            return 'poor';
        }
    }

    /**
     * Calculate overall performance for multiple indicators
     */
    public function calculateOverallPerformance($indicatorIds, $period, $year)
    {
        try {
            $performances = [];
            $totalScore = 0;
            $validIndicators = 0;

            foreach ($indicatorIds as $indicatorId) {
                $performance = $this->calculatePerformance($indicatorId, $period, $year);
                
                if ($performance) {
                    $performances[] = $performance;
                    $totalScore += $performance['score'];
                    $validIndicators++;
                }
            }

            if ($validIndicators === 0) {
                return null;
            }

            $averageScore = round($totalScore / $validIndicators, 2);
            $overallGrade = $this->getGradeFromScore($averageScore);

            return [
                'performances' => $performances,
                'average_score' => $averageScore,
                'overall_grade' => $overallGrade,
                'total_indicators' => count($indicatorIds),
                'valid_indicators' => $validIndicators,
                'period' => $period,
                'year' => $year,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to calculate overall performance: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get grade from score
     */
    private function getGradeFromScore($score)
    {
        if ($score >= 4.5) return 'A';
        if ($score >= 3.5) return 'B';
        if ($score >= 2.5) return 'C';
        if ($score >= 1.5) return 'D';
        return 'E';
    }

    /**
     * Calculate performance trends
     */
    public function calculatePerformanceTrends($indicatorId, $periods)
    {
        try {
            $trends = [];

            foreach ($periods as $period) {
                $performance = PerformanceMeasurement::where('indicator_id', $indicatorId)
                    ->where('period', $period['period'])
                    ->where('year', $period['year'])
                    ->first();

                if ($performance) {
                    $trends[] = [
                        'period' => $period['period'],
                        'year' => $period['year'],
                        'achievement' => $performance->achievement,
                        'score' => $performance->score,
                        'grade' => $performance->grade,
                    ];
                }
            }

            return $trends;

        } catch (\Exception $e) {
            Log::error('Failed to calculate performance trends: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Calculate compliance rate
     */
    public function calculateComplianceRate($indicatorIds, $period, $year)
    {
        try {
            $totalIndicators = count($indicatorIds);
            $compliantIndicators = 0;

            foreach ($indicatorIds as $indicatorId) {
                $performance = $this->calculatePerformance($indicatorId, $period, $year);
                
                if ($performance && $performance['achievement'] >= 80) { // 80% threshold for compliance
                    $compliantIndicators++;
                }
            }

            if ($totalIndicators === 0) {
                return 0;
            }

            return round(($compliantIndicators / $totalIndicators) * 100, 2);

        } catch (\Exception $e) {
            Log::error('Failed to calculate compliance rate: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Validate performance data
     */
    public function validatePerformanceData($indicatorId, $actualValue, $period, $year)
    {
        try {
            $indicator = PerformanceIndicator::find($indicatorId);
            
            if (!$indicator) {
                return ['valid' => false, 'message' => 'Indicator not found'];
            }

            $validation = [
                'valid' => true,
                'messages' => [],
                'warnings' => [],
            ];

            // Check if value is within expected range
            if ($indicator->min_value !== null && $actualValue < $indicator->min_value) {
                $validation['warnings'][] = 'Value is below minimum expected range';
            }

            if ($indicator->max_value !== null && $actualValue > $indicator->max_value) {
                $validation['warnings'][] = 'Value is above maximum expected range';
            }

            // Check for unusual patterns compared to historical data
            $historicalValidation = $this->validateAgainstHistoricalData($indicatorId, $actualValue, $period, $year);
            if (!$historicalValidation['valid']) {
                $validation['warnings'][] = $historicalValidation['message'];
            }

            return $validation;

        } catch (\Exception $e) {
            Log::error('Failed to validate performance data: ' . $e->getMessage());
            return ['valid' => false, 'message' => 'Validation failed'];
        }
    }

    /**
     * Validate against historical data
     */
    private function validateAgainstHistoricalData($indicatorId, $actualValue, $period, $year)
    {
        // Get historical data for the same period in previous years
        $historicalData = PerformanceData::where('indicator_id', $indicatorId)
            ->where('period', $period)
            ->where('year', '<', $year)
            ->orderBy('year', 'desc')
            ->limit(3)
            ->get();

        if ($historicalData->isEmpty()) {
            return ['valid' => true];
        }

        $averageHistoricalValue = $historicalData->avg('actual_value');
        $percentageChange = $averageHistoricalValue > 0 ? abs(($actualValue - $averageHistoricalValue) / $averageHistoricalValue) * 100 : 0;

        // Flag if change is more than 50% from historical average
        if ($percentageChange > 50) {
            return [
                'valid' => false,
                'message' => 'Value shows significant deviation (' . round($percentageChange, 2) . '%) from historical average'
            ];
        }

        return ['valid' => true];
    }
}