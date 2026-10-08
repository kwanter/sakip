<?php

namespace App\Services;

use App\Models\PerformanceData;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
     * @param  mixed  $user  User instance
     * @param  int  $year  Year to get statistics for
     * @return array Statistics array with totals and breakdowns
     */
    public function getReportStatistics($user, int $year): array
    {
        $query = Report::whereYear('period', $year);

        // Apply role-based filtering
        if (! $user->hasRole('superadmin')) {
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
     * @param  Report  $report  Report instance with loaded indicators
     * @return array Summary statistics
     */
    /**
     * Average achievement across performance rows, computed from targets.
     *
     * performance_data has no performance_percentage column; SQL avg()
     * against it silently returned 0 on SQLite and throws "Unknown column"
     * on MySQL. Achievement = actual vs the indicator's target for the year.
     */
    private function achievementAverage($performanceRows): float
    {
        $values = $performanceRows
            ->map(fn ($row) => $row->calculateAchievement())
            ->filter(fn ($value) => $value !== null);

        return $values->isNotEmpty() ? round($values->avg(), 2) : 0.0;
    }

    public function calculateReportSummary(Report $report): array
    {
        $indicators = $report->indicators;

        $totalIndicators = $indicators->count();

        $indicatorsWithData = $indicators
            ->filter(function ($indicator) {
                return $indicator->performanceData->isNotEmpty();
            })
            ->count();

        $averagePerformance = $this->achievementAverage(
            $indicators->flatMap->performanceData,
        );

        $achievedIndicators = $indicators
            ->filter(function ($indicator) {
                return $this->achievementAverage($indicator->performanceData) >= 100;
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
     * @param  Report  $report  Report instance
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
     * @param  Report  $report  Report instance
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
     * @param  string  $instansiId  Institution UUID
     * @param  int  $year  Year to calculate for
     * @return float Average performance percentage
     */
    public function calculateInstitutionPerformance(string $instansiId, int $year): float
    {
        $rows = PerformanceData::whereHas('indicator', function ($q) use ($instansiId) {
            $q->where('instansi_id', $instansiId);
        })
            ->whereYear('period', $year)
            ->get();

        return $this->achievementAverage($rows);
    }

    /**
     * Calculate regional average performance.
     *
     * instansis has no region_id column, so no regional dimension exists in
     * the schema; return 0.0 instead of querying a non-existent column
     * (which threw "Unknown column" on MySQL).
     */
    public function calculateRegionalPerformance(string $instansiId, int $year): float
    {
        return 0.0;
    }

    /**
     * Calculate national average performance
     *
     * @param  int  $year  Year to calculate for
     * @return float National average performance percentage
     */
    public function calculateNationalPerformance(int $year): float
    {
        $rows = PerformanceData::whereYear('period', $year)->get();

        return $this->achievementAverage($rows);
    }

    /**
     * Get comprehensive report data including summary, trends, and benchmarks
     *
     * @param  Report  $report  Report instance
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
            \Log::error('Get report data error: '.$e->getMessage());
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
