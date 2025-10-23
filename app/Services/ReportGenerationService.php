<?php

namespace App\Services;

use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\Assessment;
use App\Models\Instansi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportGenerationService
{
    public function generateReportData($institutionId, $reportType, $period, $dataSources, $options = [])
    {
        $reportData = [
            'metadata' => [
                'instansi_id' => $institutionId,
                'report_type' => $reportType,
                'period' => $period,
                'generated_at' => Carbon::now()->toDateTimeString(),
                'data_sources' => $dataSources,
                'options' => $options,
            ],
            'summary' => [],
            'indicators' => [],
            'performance_data' => [],
            'assessments' => [],
            'trends' => [],
            'benchmarks' => [],
            'recommendations' => [],
        ];

        if (in_array('indicators', $dataSources)) {
            $reportData['indicators'] = $this->generateIndicatorsData($institutionId, $period);
        }

        if (in_array('performance_data', $dataSources)) {
            $reportData['performance_data'] = $this->generatePerformanceData($institutionId, $period);
        }

        if (in_array('assessments', $dataSources)) {
            $reportData['assessments'] = $this->generateAssessmentsData($institutionId, $period);
        }

        if ($options['include_trends'] ?? false) {
            $reportData['trends'] = $this->generateTrendAnalysis($institutionId, $period);
        }

        if ($options['include_benchmarks'] ?? false) {
            $reportData['benchmarks'] = $this->generateBenchmarkAnalysis($institutionId, $period);
        }

        $reportData['summary'] = $this->generateSummary($reportData);

        return $reportData;
    }

    private function generateIndicatorsData($institutionId, $period)
    {
        return PerformanceIndicator::where('instansi_id', $institutionId)
            ->where('is_active', true)
            ->with(['category', 'measurementType'])
            ->orderBy('code')
            ->get()
            ->map(function ($indicator) use ($period) {
                return [
                    'id' => $indicator->id,
                    'code' => $indicator->code,
                    'name' => $indicator->name,
                    'description' => $indicator->description,
                    'category' => $indicator->category->name ?? null,
                    'measurement_type' => $indicator->measurementType->name ?? null,
                    'unit' => $indicator->unit,
                    'polarity' => $indicator->polarity,
                    'baseline_value' => $indicator->baseline_value,
                    'baseline_year' => $indicator->baseline_year,
                    'data_source' => $indicator->data_source,
                    'frequency' => $indicator->frequency,
                    'is_mandatory' => $indicator->is_mandatory,
                    'is_active' => $indicator->is_active,
                ];
            })
            ->toArray();
    }

    private function generatePerformanceData($institutionId, $period)
    {
        return PerformanceData::whereHas('indicator', function ($query) use ($institutionId) {
                $query->where('instansi_id', $institutionId);
            })
            ->where('period', $period)
            ->with(['indicator', 'evidence'])
            ->orderBy('indicator_id')
            ->get()
            ->map(function ($data) {
                return [
                    'id' => $data->id,
                    'indicator_id' => $data->indicator_id,
                    'indicator_code' => $data->indicator->code,
                    'indicator_name' => $data->indicator->name,
                    'actual_value' => $data->actual_value,
                    'target_value' => $data->target_value,
                    'achievement_percentage' => $data->achievement_percentage,
                    'status' => $data->status,
                    'data_quality_score' => $data->data_quality_score,
                    'evidence_count' => $data->evidence->count(),
                    'data_source' => $data->data_source,
                    'collection_date' => $data->collection_date,
                    'notes' => $data->notes,
                ];
            })
            ->toArray();
    }

    private function generateAssessmentsData($institutionId, $period)
    {
        return Assessment::whereHas('performanceData.indicator', function ($query) use ($institutionId) {
                $query->where('instansi_id', $institutionId);
            })
            ->where('period', $period)
            ->with(['performanceData.indicator', 'assessor'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($assessment) {
                return [
                    'id' => $assessment->id,
                    'indicator_id' => $assessment->performanceData->indicator->id,
                    'indicator_code' => $assessment->performanceData->indicator->code,
                    'indicator_name' => $assessment->performanceData->indicator->name,
                    'assessment_score' => $assessment->assessment_score,
                    'achievement_level' => $assessment->achievement_level,
                    'assessor_name' => $assessment->assessor->name ?? null,
                    'assessed_at' => $assessment->assessed_at,
                    'status' => $assessment->status,
                ];
            })
            ->toArray();
    }

    private function generateTrendAnalysis($institutionId, $currentPeriod)
    {
        $currentDate = Carbon::createFromFormat('Y-m', $currentPeriod);
        $previousPeriods = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $previousPeriods[] = $currentDate->copy()->subMonths($i)->format('Y-m');
        }

        $allPeriods = array_merge(array_reverse($previousPeriods), [$currentPeriod]);

        return PerformanceIndicator::where('instansi_id', $institutionId)
            ->where('is_active', true)
            ->get()
            ->map(function ($indicator) use ($allPeriods) {
                $indicatorTrends = [];

                foreach ($allPeriods as $period) {
                    $performanceData = PerformanceData::where('indicator_id', $indicator->id)
                        ->where('period', $period)
                        ->first();

                    $indicatorTrends[] = [
                        'period' => $period,
                        'actual_value' => $performanceData ? $performanceData->actual_value : null,
                        'achievement_percentage' => $performanceData ? $performanceData->achievement_percentage : null,
                    ];
                }

                $achievementValues = array_filter(array_column($indicatorTrends, 'achievement_percentage'));
                
                $trendDirection = 'stable';
                if (count($achievementValues) >= 2) {
                    $firstValue = reset($achievementValues);
                    $lastValue = end($achievementValues);
                    $change = $lastValue - $firstValue;
                    
                    if ($change > 5) {
                        $trendDirection = 'improving';
                    } elseif ($change < -5) {
                        $trendDirection = 'declining';
                    }
                }

                return [
                    'indicator_id' => $indicator->id,
                    'indicator_code' => $indicator->code,
                    'indicator_name' => $indicator->name,
                    'trend_data' => $indicatorTrends,
                    'trend_direction' => $trendDirection,
                    'average_achievement' => count($achievementValues) > 0 ? array_sum($achievementValues) / count($achievementValues) : 0,
                ];
            })
            ->toArray();
    }

    private function generateBenchmarkAnalysis($institutionId, $period)
    {
        $currentInstitution = Instansi::find($institutionId);
        $peerInstitutions = Instansi::where('id', '!=', $institutionId)
            ->where('type', $currentInstitution->type)
            ->pluck('id');

        return PerformanceIndicator::where('instansi_id', $institutionId)
            ->where('is_active', true)
            ->get()
            ->map(function ($indicator) use ($period, $peerInstitutions) {
                $currentPerformance = PerformanceData::where('indicator_id', $indicator->id)
                    ->where('period', $period)
                    ->first();

                if (!$currentPerformance) {
                    return null;
                }

                $peerPerformances = PerformanceData::whereHas('indicator', function ($query) use ($peerInstitutions) {
                        $query->whereIn('instansi_id', $peerInstitutions);
                    })
                    ->where('indicator_id', $indicator->id)
                    ->where('period', $period)
                    ->get();

                if ($peerPerformances->isEmpty()) {
                    return null;
                }

                $peerAchievements = $peerPerformances->pluck('achievement_percentage')->filter()->toArray();
                
                if (empty($peerAchievements)) {
                    return null;
                }

                $averageAchievement = array_sum($peerAchievements) / count($peerAchievements);
                $maxAchievement = max($peerAchievements);
                $minAchievement = min($peerAchievements);

                return [
                    'indicator_id' => $indicator->id,
                    'indicator_code' => $indicator->code,
                    'indicator_name' => $indicator->name,
                    'current_achievement' => $currentPerformance->achievement_percentage,
                    'peer_average' => $averageAchievement,
                    'peer_max' => $maxAchievement,
                    'peer_min' => $minAchievement,
                    'benchmark_status' => $this->getBenchmarkStatus($currentPerformance->achievement_percentage, $averageAchievement),
                ];
            })
            ->filter()
            ->toArray();
    }

    private function generateSummary($reportData)
    {
        $summary = [
            'total_indicators' => count($reportData['indicators'] ?? []),
            'total_performance_data' => count($reportData['performance_data'] ?? []),
            'total_assessments' => count($reportData['assessments'] ?? []),
            'average_achievement' => 0,
            'achievement_distribution' => [
                'excellent' => 0,
                'good' => 0,
                'fair' => 0,
                'poor' => 0,
            ],
        ];

        if (!empty($reportData['performance_data'])) {
            $achievements = array_filter(array_column($reportData['performance_data'], 'achievement_percentage'));

            if (count($achievements) > 0) {
                $summary['average_achievement'] = array_sum($achievements) / count($achievements);
            }

            foreach ($achievements as $achievement) {
                if ($achievement >= 100) {
                    $summary['achievement_distribution']['excellent']++;
                } elseif ($achievement >= 80) {
                    $summary['achievement_distribution']['good']++;
                } elseif ($achievement >= 60) {
                    $summary['achievement_distribution']['fair']++;
                } else {
                    $summary['achievement_distribution']['poor']++;
                }
            }
        }

        return $summary;
    }

    private function getBenchmarkStatus($current, $average)
    {
        $difference = $current - $average;
        
        if ($difference >= 10) {
            return 'above_average';
        } elseif ($difference <= -10) {
            return 'below_average';
        } else {
            return 'average';
        }
    }
}