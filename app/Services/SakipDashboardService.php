<?php

namespace App\Services;

use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\Assessment;
use App\Models\Target;
use App\Models\Instansi;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SakipDashboardService
{
    protected $cacheTimeout = 3600; // 1 hour
    protected $currentYear;
    protected $currentPeriod;

    public function __construct()
    {
        $this->currentYear = date('Y');
        $this->currentPeriod = $this->getCurrentPeriod();
    }

    /**
     * Get executive dashboard data for leadership
     */
    public function getExecutiveDashboard($instansiId = null, $year = null): array
    {
        $year = $year ?: $this->currentYear;
        $cacheKey = "sakip_executive_dashboard_{$instansiId}_{$year}";

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($instansiId, $year) {
            return [
                'summary' => $this->getExecutiveSummary($instansiId, $year),
                'performance_trends' => $this->getPerformanceTrends($instansiId, $year),
                'instansi_ranking' => $this->getInstansiRanking($year),
                'compliance_status' => $this->getComplianceStatus($instansiId, $year),
                'critical_indicators' => $this->getCriticalIndicators($instansiId, $year),
                'recent_activities' => $this->getRecentActivities($instansiId, 10),
                'achievement_distribution' => $this->getAchievementDistribution($instansiId, $year),
            ];
        });
    }

    /**
     * Get data collector dashboard
     */
    public function getDataCollectorDashboard($instansiId, $year = null): array
    {
        $year = $year ?: $this->currentYear;
        $cacheKey = "sakip_data_collector_dashboard_{$instansiId}_{$year}";

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($instansiId, $year) {
            return [
                'pending_data' => $this->getPendingDataCollection($instansiId, $year),
                'overdue_indicators' => $this->getOverdueIndicators($instansiId, $year),
                'recent_submissions' => $this->getRecentSubmissions($instansiId, 10),
                'validation_status' => $this->getValidationStatus($instansiId, $year),
                'data_quality_score' => $this->getDataQualityScore($instansiId, $year),
                'collection_progress' => $this->getCollectionProgress($instansiId, $year),
                'evidence_requirements' => $this->getEvidenceRequirements($instansiId, $year),
            ];
        });
    }

    /**
     * Get assessor dashboard
     */
    public function getAssessorDashboard($instansiId = null, $year = null): array
    {
        $year = $year ?: $this->currentYear;
        $cacheKey = "sakip_assessor_dashboard_{$instansiId}_{$year}";

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($instansiId, $year) {
            return [
                'pending_assessments' => $this->getPendingAssessments($instansiId, $year),
                'assessment_progress' => $this->getAssessmentProgress($instansiId, $year),
                'quality_reviews' => $this->getQualityReviews($instansiId, $year),
                'assessment_statistics' => $this->getAssessmentStatistics($instansiId, $year),
                'recent_assessments' => $this->getRecentAssessments($instansiId, 10),
                'performance_issues' => $this->getPerformanceIssues($instansiId, $year),
                'recommendation_summary' => $this->getRecommendationSummary($instansiId, $year),
            ];
        });
    }

    /**
     * Get audit dashboard
     */
    public function getAuditDashboard($instansiId = null, $year = null): array
    {
        $year = $year ?: $this->currentYear;
        $cacheKey = "sakip_audit_dashboard_{$instansiId}_{$year}";

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($instansiId, $year) {
            return [
                'audit_findings' => $this->getAuditFindings($instansiId, $year),
                'compliance_violations' => $this->getComplianceViolations($instansiId, $year),
                'risk_indicators' => $this->getRiskIndicators($instansiId, $year),
                'audit_trail' => $this->getAuditTrail($instansiId, 20),
                'system_integrity' => $this->getSystemIntegrityStatus($instansiId, $year),
                'anomaly_detection' => $this->getAnomalyDetection($instansiId, $year),
                'audit_recommendations' => $this->getAuditRecommendations($instansiId, $year),
            ];
        });
    }

    /**
     * Get executive summary data
     */
    protected function getExecutiveSummary($instansiId = null, $year): array
    {
        $query = PerformanceIndicator::with(['targets', 'performanceData'])
            ->whereYear('created_at', '<=', $year);

        if ($instansiId) {
            $query->where('instansi_id', $instansiId);
        }

        $indicators = $query->get();

        $totalIndicators = $indicators->count();
        $indicatorsWithData = $indicators->filter(function ($indicator) use ($year) {
            return $indicator->performanceData->where('period_year', $year)->isNotEmpty();
        })->count();

        $avgAchievement = $this->calculateAverageAchievement($indicators, $year);

        return [
            'total_indicators' => $totalIndicators,
            'indicators_with_data' => $indicatorsWithData,
            'data_completeness' => $totalIndicators > 0 ? round(($indicatorsWithData / $totalIndicators) * 100, 2) : 0,
            'average_achievement' => $avgAchievement,
            'assessment_completion' => $this->getAssessmentCompletionRate($instansiId, $year),
            'report_submission_rate' => $this->getReportSubmissionRate($instansiId, $year),
        ];
    }

    /**
     * Get performance trends over time
     */
    protected function getPerformanceTrends($instansiId = null, $year): array
    {
        $trends = [];
        $startYear = $year - 4; // 5 years trend

        for ($y = $startYear; $y <= $year; $y++) {
            $achievement = $this->calculateYearlyAchievement($instansiId, $y);
            $trends[] = [
                'year' => $y,
                'achievement' => $achievement,
                'target' => $this->getYearlyTarget($instansiId, $y),
            ];
        }

        return $trends;
    }

    /**
     * Get institution ranking
     */
    protected function getInstansiRanking($year): array
    {
        $instansiRankings = Instansi::with(['performanceIndicators.targets', 'performanceIndicators.performanceData'])
            ->get()
            ->map(function ($instansi) use ($year) {
                return [
                    'instansi' => $instansi->name,
                    'instansi_id' => $instansi->id,
                    'achievement_score' => $this->calculateInstansiAchievement($instansi, $year),
                    'completion_rate' => $this->calculateInstansiCompletion($instansi, $year),
                ];
            })
            ->sortByDesc('achievement_score')
            ->values()
            ->take(10);

        return $instansiRankings->toArray();
    }

    /**
     * Get compliance status
     */
    protected function getComplianceStatus($instansiId = null, $year): array
    {
        $totalIndicators = PerformanceIndicator::when($instansiId, function ($query) use ($instansiId) {
            return $query->where('instansi_id', $instansiId);
        })->count();

        $compliantIndicators = PerformanceIndicator::when($instansiId, function ($query) use ($instansiId) {
            return $query->where('instansi_id', $instansiId);
        })
            ->whereHas('performanceData', function ($query) use ($year) {
                $query->where('period_year', $year)
                    ->where('validation_status', 'validated');
            })
            ->count();

        $nonCompliantIndicators = $totalIndicators - $compliantIndicators;

        return [
            'compliant_indicators' => $compliantIndicators,
            'non_compliant_indicators' => $nonCompliantIndicators,
            'compliance_rate' => $totalIndicators > 0 ? round(($compliantIndicators / $totalIndicators) * 100, 2) : 0,
            'critical_violations' => $this->getCriticalViolations($instansiId, $year),
        ];
    }

    /**
     * Get critical indicators
     */
    protected function getCriticalIndicators($instansiId = null, $year): array
    {
        return PerformanceIndicator::with(['targets', 'performanceData'])
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->get()
            ->filter(function ($indicator) use ($year) {
                $achievement = $this->calculateIndicatorAchievement($indicator, $year);
                return $achievement < 50; // Less than 50% achievement
            })
            ->map(function ($indicator) use ($year) {
                return [
                    'id' => $indicator->id,
                    'name' => $indicator->name,
                    'code' => $indicator->code,
                    'achievement' => $this->calculateIndicatorAchievement($indicator, $year),
                    'target' => $indicator->targets->where('year', $year)->first()?->target_value ?? 0,
                    'actual' => $indicator->performanceData->where('period_year', $year)->sum('actual_value'),
                ];
            })
            ->take(10)
            ->toArray();
    }

    /**
     * Get recent activities
     */
    protected function getRecentActivities($instansiId = null, $limit = 10): array
    {
        $query = AuditLog::with(['user', 'instansi'])
            ->where('module', 'sakip')
            ->orderBy('created_at', 'desc');

        if ($instansiId) {
            $query->where('instansi_id', $instansiId);
        }

        return $query->take($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'activity' => $log->activity,
                    'user' => $log->user?->name ?? 'System',
                    'instansi' => $log->instansi?->name,
                    'timestamp' => $log->created_at->format('Y-m-d H:i:s'),
                    'details' => $log->details,
                ];
            })
            ->toArray();
    }

    /**
     * Get achievement distribution
     */
    protected function getAchievementDistribution($instansiId = null, $year): array
    {
        $indicators = PerformanceIndicator::with(['performanceData'])
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->get();

        $distributions = [
            'excellent' => 0, // 90-100%
            'good' => 0,      // 70-89%
            'fair' => 0,      // 50-69%
            'poor' => 0,      // < 50%
        ];

        foreach ($indicators as $indicator) {
            $achievement = $this->calculateIndicatorAchievement($indicator, $year);
            
            if ($achievement >= 90) {
                $distributions['excellent']++;
            } elseif ($achievement >= 70) {
                $distributions['good']++;
            } elseif ($achievement >= 50) {
                $distributions['fair']++;
            } else {
                $distributions['poor']++;
            }
        }

        return $distributions;
    }

    /**
     * Get pending data collection
     */
    protected function getPendingDataCollection($instansiId, $year): array
    {
        $indicators = PerformanceIndicator::where('instansi_id', $instansiId)
            ->whereDoesntHave('performanceData', function ($query) use ($year) {
                $query->where('period_year', $year);
            })
            ->get()
            ->map(function ($indicator) {
                return [
                    'id' => $indicator->id,
                    'name' => $indicator->name,
                    'code' => $indicator->code,
                    'deadline' => $this->getDataCollectionDeadline($indicator),
                    'category' => $indicator->category,
                ];
            });

        return $indicators->toArray();
    }

    /**
     * Get overdue indicators
     */
    protected function getOverdueIndicators($instansiId, $year): array
    {
        $deadline = $this->getDataCollectionDeadline();
        
        return PerformanceIndicator::where('instansi_id', $instansiId)
            ->whereHas('performanceData', function ($query) use ($year, $deadline) {
                $query->where('period_year', $year)
                    ->where('created_at', '>', $deadline);
            })
            ->get()
            ->map(function ($indicator) {
                return [
                    'id' => $indicator->id,
                    'name' => $indicator->name,
                    'code' => $indicator->code,
                    'days_overdue' => Carbon::now()->diffInDays($this->getDataCollectionDeadline($indicator)),
                    'category' => $indicator->category,
                ];
            })
            ->toArray();
    }

    /**
     * Calculate average achievement
     */
    protected function calculateAverageAchievement($indicators, $year): float
    {
        if ($indicators->isEmpty()) {
            return 0;
        }

        $totalAchievement = 0;
        $count = 0;

        foreach ($indicators as $indicator) {
            $achievement = $this->calculateIndicatorAchievement($indicator, $year);
            if ($achievement !== null) {
                $totalAchievement += $achievement;
                $count++;
            }
        }

        return $count > 0 ? round($totalAchievement / $count, 2) : 0;
    }

    /**
     * Calculate indicator achievement
     */
    protected function calculateIndicatorAchievement($indicator, $year): ?float
    {
        $target = $indicator->targets->where('year', $year)->first()?->target_value;
        $actual = $indicator->performanceData->where('period_year', $year)->sum('actual_value');

        if (!$target || $target == 0) {
            return null;
        }

        return round(($actual / $target) * 100, 2);
    }

    /**
     * Calculate institution achievement
     */
    protected function calculateInstansiAchievement($instansi, $year): float
    {
        $indicators = $instansi->performanceIndicators;
        return $this->calculateAverageAchievement($indicators, $year);
    }

    /**
     * Calculate institution completion rate
     */
    protected function calculateInstansiCompletion($instansi, $year): float
    {
        $totalIndicators = $instansi->performanceIndicators->count();
        $indicatorsWithData = $instansi->performanceIndicators->filter(function ($indicator) use ($year) {
            return $indicator->performanceData->where('period_year', $year)->isNotEmpty();
        })->count();

        return $totalIndicators > 0 ? round(($indicatorsWithData / $totalIndicators) * 100, 2) : 0;
    }

    /**
     * Get current period
     */
    protected function getCurrentPeriod(): string
    {
        $month = date('n');
        return $month <= 6 ? 'first_semester' : 'second_semester';
    }

    /**
     * Get data collection deadline
     */
    protected function getDataCollectionDeadline($indicator = null): Carbon
    {
        // Default deadline: end of current semester
        $year = date('Y');
        $period = $this->currentPeriod;
        
        if ($period === 'first_semester') {
            return Carbon::create($year, 7, 31); // July 31
        } else {
            return Carbon::create($year, 12, 31); // December 31
        }
    }

    /**
     * Clear dashboard cache
     */
    public function clearCache($instansiId = null, $year = null): void
    {
        $year = $year ?: $this->currentYear;
        
        $cacheKeys = [
            "sakip_executive_dashboard_{$instansiId}_{$year}",
            "sakip_data_collector_dashboard_{$instansiId}_{$year}",
            "sakip_assessor_dashboard_{$instansiId}_{$year}",
            "sakip_audit_dashboard_{$instansiId}_{$year}",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }
}