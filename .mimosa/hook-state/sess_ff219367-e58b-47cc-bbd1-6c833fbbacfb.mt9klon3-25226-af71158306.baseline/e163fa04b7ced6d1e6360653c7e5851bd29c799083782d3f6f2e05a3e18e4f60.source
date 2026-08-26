<?php

namespace App\Services;

use App\Models\Report;
use App\Models\PerformanceData;
use App\Models\Instansi;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Report Calculation Service
 *
 * Handles all business logic for calculating report statistics,
 * trends, benchmarks, and performance metrics.
 * Separates calculation logic from the ReportController.
 */
class ReportCalculationService
{
    /**
     * Calculate report statistics for a user and year
     *
     * @param mixed $user User instance
     * @param int $year Year to get statistics for
     * @return array Statistics array with totals and breakdowns
     */
    public function getReportStatistics($user, int $year): array
    {
        $query = Report::whereYear('period', $year);

        // Apply role-based filtering
        if (!$user->hasRole('superadmin')) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhere('instansi_id', $user->instansi_id);
            });
        }

        $totalReports = $query->count();

        $byStatus = $query
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $byType = $query
            ->select('report_type', DB::raw('count(*) as count'))
            ->groupBy('report_type')
            ->pluck('count', 'report_type')
            ->toArray();

        return [
            'total_reports' => $totalReports,
            'by_status' => $byStatus,
            'by_type' => $byType,
            'pending_approval' => $byStatus['pending_approval'] ?? 0,
            'approved' => $byStatus['approved'] ?? 0,
            'rejected' => $byStatus['rejected'] ?? 0,
        ];
    }

    /**
     * Calculate comprehensive report summary statistics
     *
     * @param Report $report Report instance with loaded indicators
     * @return array Summary statistics
     */
    public function calculateReportSummary(Report $report): array
    {
        $indicators = $report->indicators;

        $totalIndicators = $indicators->count();

        $indicatorsWithData = $indicators
            ->filter(function ($indicator) {
                return $indicator->performanceData->isNotEmpty();
            })
            ->count();

        $averagePerformance = $indicators->avg(function ($indicator) {
            return $indicator->performanceData->avg('performance_percentage') ?? 0;
        });

        $achievedIndicators = $indicators
            ->filter(function ($indicator) {
                return $indicator->performanceData->avg('performance_percentage') >= 100;
            })
            ->count();

        return [
            'total_indicators' => $totalIndicators,
            'indicators_with_data' => $indicatorsWithData,
            'average_performance' => round($averagePerformance, 2),
            'achieved_indicators' => $achievedIndicators,
            'achievement_rate' => $totalIndicators > 0
                ? round(($achievedIndicators / $totalIndicators) * 100, 2)
                : 0,
        ];
    }

    /**
     * Get monthly performance trends for a report
     *
     * @param Report $report Report instance
     * @return array Monthly trend data
     */
    public function getReportTrends(Report $report): array
    {
        $year = Carbon::parse($report->period)->year;
        $trends = [];

        // Get monthly trends for the year
        for ($month = 1; $month <= 12; $month++) {
            $monthlyData = PerformanceData::whereIn(
                'indicator_id',
                $report->indicators->pluck('id')
            )
                ->whereYear('period', $year)
                ->whereMonth('period', $month)
                ->get();

            $trends[] = [
                'month' => Carbon::create($year, $month, 1)->format('M'),
                'average_performance' => $monthlyData->isNotEmpty()
                    ? round($monthlyData->avg('performance_percentage'), 2)
                    : 0,
                'data_points' => $monthlyData->count(),
            ];
        }

        return $trends;
    }

    /**
     * Get benchmark comparisons for a report
     *
     * @param Report $report Report instance
     * @return array Benchmark data (institution, regional, national)
     */
    public function getReportBenchmarks(Report $report): array
    {
        $instansiId = $report->instansi_id;
        $year = Carbon::parse($report->period)->year;

        return [
            'institution' => $this->calculateInstitutionPerformance($instansiId, $year),
            'regional' => $this->calculateRegionalPerformance($instansiId, $year),
            'national' => $this->calculateNationalPerformance($year),
        ];
    }

    /**
     * Calculate institution's average performance
     *
     * @param int $instansiId Institution ID
     * @param int $year Year to calculate for
     * @return float Average performance percentage
     */
    public function calculateInstitutionPerformance(int $instansiId, int $year): float
    {
        return (float) PerformanceData::whereHas('indicator', function ($q) use ($instansiId) {
            $q->where('instansi_id', $instansiId);
        })
            ->whereYear('period', $year)
            ->avg('performance_percentage') ?? 0;
    }

    /**
     * Calculate regional average performance
     *
     * @param int $instansiId Institution ID to determine region
     * @param int $year Year to calculate for
     * @return float Regional average performance percentage
     */
    public function calculateRegionalPerformance(int $instansiId, int $year): float
    {
        return (float) PerformanceData::whereHas('indicator.instansi', function ($q) use ($instansiId) {
            $q->where('region_id', function ($subQuery) use ($instansiId) {
                $subQuery
                    ->select('region_id')
                    ->from('instansis')
                    ->where('id', $instansiId);
            });
        })
            ->whereYear('period', $year)
            ->avg('performance_percentage') ?? 0;
    }

    /**
     * Calculate national average performance
     *
     * @param int $year Year to calculate for
     * @return float National average performance percentage
     */
    public function calculateNationalPerformance(int $year): float
    {
        return (float) PerformanceData::whereYear('period', $year)
            ->avg('performance_percentage') ?? 0;
    }

    /**
     * Get comprehensive report data including summary, trends, and benchmarks
     *
     * @param Report $report Report instance
     * @return array Complete report data
     */
    public function getReportData(Report $report): array
    {
        try {
            $report->load(['indicators.performanceData', 'indicators.targets']);

            return [
                'report' => $report,
                'summary' => $this->calculateReportSummary($report),
                'trends' => $this->getReportTrends($report),
                'benchmarks' => $this->getReportBenchmarks($report),
            ];
        } catch (\Exception $e) {
            \Log::error('Get report data error: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get available report periods for the current year
     *
     * @return array Available periods (monthly, quarterly, yearly)
     */
    public function getAvailableReportPeriods(): array
    {
        $currentYear = Carbon::now()->year;
        $periods = [];

        // Monthly periods
        for ($month = 1; $month <= 12; $month++) {
            $periods[] = [
                'value' => Carbon::create($currentYear, $month, 1)->format('Y-m-d'),
                'label' => Carbon::create($currentYear, $month, 1)->format('F Y'),
                'type' => 'monthly',
            ];
        }

        // Quarterly periods
        foreach ([1, 4, 7, 10] as $month) {
            $quarter = (int) ceil($month / 3);
            $periods[] = [
                'value' => Carbon::create($currentYear, $month, 1)->format('Y-m-d'),
                'label' => "Q{$quarter} {$currentYear}",
                'type' => 'quarterly',
            ];
        }

        // Yearly period
        $periods[] = [
            'value' => Carbon::create($currentYear, 1, 1)->format('Y-m-d'),
            'label' => "Annual {$currentYear}",
            'type' => 'yearly',
        ];

        return $periods;
    }
}
