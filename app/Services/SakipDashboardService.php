<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use App\Services\Sakip\SakipService;

/**
 * Service to aggregate and provide dashboard data for SAKIP.
 *
 * Provides metrics, charts, recent activities, notifications,
 * compliance status, and performance summaries for a given period.
 *
 * All methods include basic error handling and return safe fallbacks
 * when data sources fail to avoid breaking the dashboard UI.
 */
class SakipDashboardService
{
    /** @var SakipService */
    protected $sakipService;

    /**
     * Construct the dashboard service.
     *
     * @param SakipService $sakipService SAKIP core service dependency.
     */
    public function __construct(SakipService $sakipService)
    {
        $this->sakipService = $sakipService;
    }

    /**
     * Get dashboard data aggregate for a period.
     *
     * @param string $period One of: current_year, current_month, last_month, current_quarter, last_quarter
     * @return array Structured dashboard payload with safe fallbacks.
     */
    public function getDashboardData(string $period = 'current_year'): array
    {
        $cacheKey = 'sakip_dashboard_' . $period . '_' . auth()->id();
        
        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($period) {
            return [
                'metrics' => $this->getMetrics($period),
                'charts' => $this->getChartData($period),
                'recent_activities' => $this->getRecentActivities($period),
                // Updated to pass limit and user id safely
                'notifications' => $this->getNotifications(5, auth()->id()),
                'compliance_status' => $this->getComplianceStatus($period),
                'performance_summary' => $this->getPerformanceSummary($period),
            ];
        });
    }

    /**
     * Get dashboard metrics summary for a period.
     *
     * @param string $period Period filter string.
     * @return array Metrics payload with counts and averages.
     */
    public function getMetrics(string $period = 'current_year'): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'total_indicators' => $this->getTotalIndicators($dateRange),
            'active_programs' => $this->getActivePrograms($dateRange),
            'total_activities' => $this->getTotalActivities($dateRange),
            'total_reports' => $this->getTotalReports($dateRange),
            'average_achievement' => $this->getAverageAchievement($dateRange),
            'compliance_rate' => $this->getComplianceRate($dateRange),
        ];
    }

    /**
     * Get chart datasets for a period.
     *
     * @param string $period Period filter string.
     * @return array Chart datasets (achievement trend, category breakdown, instansi comparison, quarterly).
     */
    public function getChartData(string $period = 'current_year'): array
    {
        $dateRange = $this->getDateRange($period);
        
        return [
            'achievement_trend' => $this->getAchievementTrend($dateRange),
            'category_breakdown' => $this->getCategoryBreakdown($dateRange),
            'instansi_comparison' => $this->getInstansiComparison($dateRange),
            'quarterly_performance' => $this->getQuarterlyPerformance($dateRange),
        ];
    }

    /**
     * Achievement trend over months within the date range.
     *
     * @param array{0: Carbon, 1: Carbon} $dateRange Start and end dates.
     * @return array{labels: array<int,string>, data: array<int,float>} Chart data.
     */
    protected function getAchievementTrend(array $dateRange): array
    {
        try {
            $data = DB::table('laporan_kinerjas')
                ->select(
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('AVG(persentase_capaian) as average_achievement')
                )
                ->whereBetween('created_at', $dateRange)
                ->groupBy(DB::raw('MONTH(created_at)'))
                ->orderBy('month')
                ->get();

            return [
                'labels' => $data->pluck('month')->map(function ($month) {
                    return date('M', mktime(0, 0, 0, $month, 1));
                })->toArray(),
                'data' => $data->pluck('average_achievement')->map(function ($value) {
                    return round($value, 2);
                })->toArray(),
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to get achievement trend', ['exception' => $e]);
            return ['labels' => [], 'data' => []];
        }
    }

    /**
     * Breakdown of indicators by category.
     *
     * @param array{0: Carbon, 1: Carbon} $dateRange Start and end dates.
     * @return array<int,array{name:string,value:int}> Category breakdown list.
     */
    protected function getCategoryBreakdown(array $dateRange): array
    {
        try {
            $categories = DB::table('indikator_kinerjas')
                ->select('jenis')
                ->distinct()
                ->pluck('jenis');

            $data = [];
            foreach ($categories as $category) {
                $count = DB::table('laporan_kinerjas')
                    ->join('indikator_kinerjas', 'laporan_kinerjas.indikator_kinerja_id', '=', 'indikator_kinerjas.id')
                    ->where('indikator_kinerjas.jenis', $category)
                    ->whereBetween('laporan_kinerjas.created_at', $dateRange)
                    ->count();
                $data[] = [
                    'name' => $category,
                    'value' => $count,
                ];
            }
            return $data;
        } catch (\Throwable $e) {
            Log::error('Failed to get category breakdown', ['exception' => $e]);
            return [];
        }
    }

    /**
     * Compare instansi average achievement.
     *
     * @param array{0: Carbon, 1: Carbon} $dateRange Start and end dates.
     * @return array{labels: array<int,string>, data: array<int,float>} Chart data.
     */
    protected function getInstansiComparison(array $dateRange): array
    {
        try {
            $data = DB::table('laporan_kinerjas')
                ->join('indikator_kinerjas', 'laporan_kinerjas.indikator_kinerja_id', '=', 'indikator_kinerjas.id')
                ->join('kegiatans', 'indikator_kinerjas.kegiatan_id', '=', 'kegiatans.id')
                ->join('programs', 'kegiatans.program_id', '=', 'programs.id')
                ->join('instansis', 'programs.instansi_id', '=', 'instansis.id')
                ->select(
                    'instansis.nama_instansi as instansi_name',
                    DB::raw('COUNT(laporan_kinerjas.id) as report_count'),
                    DB::raw('AVG(laporan_kinerjas.persentase_capaian) as average_achievement')
                )
                ->whereBetween('laporan_kinerjas.created_at', $dateRange)
                ->groupBy('instansis.id', 'instansis.nama_instansi')
                ->orderByDesc('average_achievement')
                ->limit(10)
                ->get();

            return [
                'labels' => $data->pluck('instansi_name')->toArray(),
                'data' => $data->pluck('average_achievement')->map(function ($value) {
                    return round($value, 2);
                })->toArray(),
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to get instansi comparison', ['exception' => $e]);
            return ['labels' => [], 'data' => []];
        }
    }

    /**
     * Quarterly performance summary (Q1-Q4).
     *
     * @param array{0: Carbon, 1: Carbon} $dateRange Start and end dates.
     * @return array{labels: array<int,string>, data: array<int,float>} Chart data.
     */
    protected function getQuarterlyPerformance(array $dateRange): array
    {
        $quarters = [
            'Q1' => [1, 2, 3],
            'Q2' => [4, 5, 6],
            'Q3' => [7, 8, 9],
            'Q4' => [10, 11, 12],
        ];

        try {
            $data = [];
            foreach ($quarters as $quarter => $months) {
                $achievement = DB::table('laporan_kinerjas')
                    ->whereIn(DB::raw('MONTH(created_at)'), $months)
                    ->whereBetween('created_at', $dateRange)
                    ->avg('persentase_capaian');

                $data[] = [
                    'quarter' => $quarter,
                    'achievement' => round($achievement ?? 0, 2),
                ];
            }

            return [
                'labels' => array_keys($quarters),
                'data' => array_column($data, 'achievement'),
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to get quarterly performance', ['exception' => $e]);
            return [
                'labels' => array_keys($quarters),
                'data' => [0, 0, 0, 0],
            ];
        }
    }

    /**
     * Get recent activities including reports and audit logs.
     *
     * @param string $period Period filter string.
     * @return array<int,array<string,string>> Recent activity items.
     */
    protected function getRecentActivities(string $period = 'current_year'): array
    {
        try {
            $dateRange = $this->getDateRange($period);

            $reportActivities = collect();
            if (Schema::hasTable('reports')) {
                $selects = [];
                if (Schema::hasColumn('reports', 'report_type')) {
                    $selects[] = 'reports.report_type as report_type';
                }
                if (Schema::hasColumn('reports', 'period')) {
                    $selects[] = 'reports.period as report_period';
                }
                $selects[] = 'reports.created_at as activity_date';
                $selects[] = DB::raw("'report_generated' as activity_type");

                $reportsQuery = DB::table('reports');
                if (Schema::hasTable('users')) {
                    if (Schema::hasColumn('reports', 'generated_by')) {
                        $reportsQuery = $reportsQuery->join('users', 'reports.generated_by', '=', 'users.id');
                        $selects[] = 'users.name as user_name';
                    } elseif (Schema::hasColumn('reports', 'user_id')) {
                        $reportsQuery = $reportsQuery->join('users', 'reports.user_id', '=', 'users.id');
                        $selects[] = 'users.name as user_name';
                    } else {
                        $selects[] = DB::raw("'Unknown' as user_name");
                    }
                } else {
                    $selects[] = DB::raw("'Unknown' as user_name");
                }

                $reportActivities = $reportsQuery
                    ->select($selects)
                    ->whereBetween('reports.created_at', $dateRange)
                    ->orderByDesc('reports.created_at')
                    ->limit(10)
                    ->get();
            }

            // Choose correct audit log table
            $auditTable = Schema::hasTable('sakip_audit_logs') ? 'sakip_audit_logs' : (Schema::hasTable('audit_logs') ? 'audit_logs' : null);

            $auditActivities = collect();
            if ($auditTable) {
                $auditActivities = DB::table($auditTable)
                    ->join('users', $auditTable . '.user_id', '=', 'users.id')
                    ->select(
                        ($auditTable === 'sakip_audit_logs' ? $auditTable . '.activity' : $auditTable . '.action') . ' as action',
                        'users.name as user_name',
                        $auditTable . '.created_at as activity_date',
                        DB::raw("'audit_log' as activity_type")
                    )
                    ->whereBetween($auditTable . '.created_at', $dateRange)
                    ->orderByDesc($auditTable . '.created_at')
                    ->limit(10)
                    ->get();
            }

            $activities = $reportActivities->concat($auditActivities)
                ->sortByDesc('activity_date')
                ->take(10)
                ->values();

            return $activities->map(function ($activity) {
                $activity = (array) $activity;
                if (($activity['activity_type'] ?? null) === 'report_generated') {
                    return [
                        'type' => 'report_generated',
                        'title' => "Generated report: {$activity['report_type']} ({$activity['report_period']})",
                        'user' => $activity['user_name'],
                        'date' => Carbon::parse($activity['activity_date'])->diffForHumans(),
                    ];
                }
                return [
                    'type' => 'audit_log',
                    'title' => "Action: {$activity['action']}",
                    'user' => $activity['user_name'],
                    'date' => Carbon::parse($activity['activity_date'])->diffForHumans(),
                ];
            })->toArray();
        } catch (\Throwable $e) {
            Log::error('Failed to get recent activities', ['exception' => $e]);
            return [];
        }
    }

    /**
     * Get unread SAKIP notifications for a user.
     *
     * @param int $limit Max notifications to return.
     * @param int|null $userId Target user ID (defaults to current auth user).
     * @return array<int,array<string,string>> Notification items.
     */
    public function getNotifications(int $limit = 5, ?int $userId = null): array
    {
        try {
            $userId = $userId ?? auth()->id();
            if (!$userId) {
                return [];
            }

            $user = \App\Models\User::find($userId);
            if (!$user || !method_exists($user, 'unreadNotifications')) {
                return [];
            }
            
            $notifications = $user->unreadNotifications()
                ->where('type', 'like', '%Sakip%')
                ->limit($limit)
                ->get();

            return $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => class_basename($notification->type),
                    'title' => $notification->data['title'] ?? 'Notification',
                    'message' => $notification->data['message'] ?? '',
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            })->toArray();
        } catch (\Throwable $e) {
            Log::error('Failed to get notifications', ['exception' => $e]);
            return [];
        }
    }

    /**
     * Compute compliance status across instansi over the period.
     *
     * @param string $period Period filter string.
     * @return array{total_instansi:int,compliant_instansi:int,compliance_rate:float,instansi_list:array<int,array{name:string,status:string,last_report:string}>}
     */
    protected function getComplianceStatus(string $period = 'current_year'): array
    {
        try {
            $dateRange = $this->getDateRange($period);
            $complianceData = DB::table('instansis')
                ->leftJoin('reports', function ($join) use ($dateRange) {
                    $join->on('instansis.id', '=', 'reports.instansi_id')
                         ->whereBetween('reports.created_at', $dateRange);
                })
                ->select(
                    'instansis.nama_instansi as instansi_name',
                    DB::raw('COUNT(reports.id) as report_count'),
                    DB::raw('MAX(reports.created_at) as last_report_date')
                )
                ->groupBy('instansis.id', 'instansis.nama_instansi')
                ->get();

            $totalInstansi = $complianceData->count();
            $compliantInstansi = $complianceData->filter(function ($item) {
                return $item->report_count > 0;
            })->count();

            return [
                'total_instansi' => $totalInstansi,
                'compliant_instansi' => $compliantInstansi,
                'compliance_rate' => $totalInstansi > 0 ? round(($compliantInstansi / $totalInstansi) * 100, 2) : 0,
                'instansi_list' => $complianceData->map(function ($item) {
                    return [
                        'name' => $item->instansi_name,
                        'status' => $item->report_count > 0 ? 'compliant' : 'non-compliant',
                        'last_report' => $item->last_report_date ? Carbon::parse($item->last_report_date)->format('d M Y') : 'No reports',
                    ];
                })->toArray(),
            ];
        } catch (\Throwable $e) {
            Log::error('Failed to get compliance status', ['exception' => $e]);
            return [
                'total_instansi' => 0,
                'compliant_instansi' => 0,
                'compliance_rate' => 0.0,
                'instansi_list' => [],
            ];
        }
    }

    /**
     * Aggregate performance summary grouped by indicator category.
     *
     * @param string $period Period filter string.
     * @return array<int,array<string,mixed>> Performance summary list.
     */
    protected function getPerformanceSummary(string $period = 'current_year'): array
    {
        try {
            $dateRange = $this->getDateRange($period);

            $performanceByCategory = DB::table('laporan_kinerjas')
                ->join('indikator_kinerjas', 'laporan_kinerjas.indikator_kinerja_id', '=', 'indikator_kinerjas.id')
                ->select(
                    'indikator_kinerjas.jenis as kategori',
                    DB::raw('COUNT(*) as indicator_count'),
                    DB::raw('AVG(persentase_capaian) as average_achievement'),
                    DB::raw('COUNT(CASE WHEN persentase_capaian >= 100 THEN 1 END) as excellent_count'),
                    DB::raw('COUNT(CASE WHEN persentase_capaian >= 80 AND persentase_capaian < 100 THEN 1 END) as good_count'),
                    DB::raw('COUNT(CASE WHEN persentase_capaian >= 60 AND persentase_capaian < 80 THEN 1 END) as fair_count'),
                    DB::raw('COUNT(CASE WHEN persentase_capaian < 60 THEN 1 END) as poor_count')
                )
                ->whereBetween('laporan_kinerjas.created_at', $dateRange)
                ->groupBy('indikator_kinerjas.jenis')
                ->get();

            return $performanceByCategory->map(function ($item) {
                return [
                    'category' => $item->kategori,
                    'total_indicators' => $item->indicator_count,
                    'average_achievement' => round($item->average_achievement, 2),
                    'performance_distribution' => [
                        'excellent' => $item->excellent_count,
                        'good' => $item->good_count,
                        'fair' => $item->fair_count,
                        'poor' => $item->poor_count,
                    ],
                ];
            })->toArray();
        } catch (\Throwable $e) {
            Log::error('Failed to get performance summary', ['exception' => $e]);
            return [];
        }
    }

    /**
     * Count total indicators created in the date range.
     *
     * @param array{0: Carbon, 1: Carbon} $dateRange Start and end dates.
     * @return int Total count or 0 on error.
     */
    protected function getTotalIndicators(array $dateRange): int
    {
        try {
            return DB::table('indikator_kinerjas')
                ->whereBetween('created_at', $dateRange)
                ->count();
        } catch (\Throwable $e) {
            Log::error('Failed to count total indicators', ['exception' => $e]);
            return 0;
        }
    }

    /**
     * Count active programs in the date range.
     *
     * @param array{0: Carbon, 1: Carbon} $dateRange Start and end dates.
     * @return int Active programs count or 0 on error.
     */
    protected function getActivePrograms(array $dateRange): int
    {
        try {
            return DB::table('programs')
                ->where('status', 'aktif')
                ->whereBetween('created_at', $dateRange)
                ->count();
        } catch (\Throwable $e) {
            Log::error('Failed to count active programs', ['exception' => $e]);
            return 0;
        }
    }

    /**
     * Count total activities in the date range.
     *
     * @param array{0: Carbon, 1: Carbon} $dateRange Start and end dates.
     * @return int Activities count or 0 on error.
     */
    protected function getTotalActivities(array $dateRange): int
    {
        try {
            return DB::table('kegiatans')
                ->whereBetween('created_at', $dateRange)
                ->count();
        } catch (\Throwable $e) {
            Log::error('Failed to count total activities', ['exception' => $e]);
            return 0;
        }
    }

    /**
     * Count total reports in the date range.
     *
     * @param array{0: Carbon, 1: Carbon} $dateRange Start and end dates.
     * @return int Reports count or 0 on error.
     */
    protected function getTotalReports(array $dateRange): int
    {
        try {
            return DB::table('laporan_kinerjas')
                ->whereBetween('created_at', $dateRange)
                ->count();
        } catch (\Throwable $e) {
            Log::error('Failed to count total reports', ['exception' => $e]);
            return 0;
        }
    }

    /**
     * Average achievement percentage in the date range.
     *
     * @param array{0: Carbon, 1: Carbon} $dateRange Start and end dates.
     * @return float Average or 0.0 on error.
     */
    protected function getAverageAchievement(array $dateRange): float
    {
        try {
            $average = DB::table('laporan_kinerjas')
                ->whereBetween('created_at', $dateRange)
                ->avg('persentase_capaian');
            return round($average ?? 0, 2);
        } catch (\Throwable $e) {
            Log::error('Failed to compute average achievement', ['exception' => $e]);
            return 0.0;
        }
    }

    /**
     * Compliance rate based on reporting instansi.
     *
     * @param array{0: Carbon, 1: Carbon} $dateRange Start and end dates.
     * @return float Rate percentage or 0.0 on error.
     */
    protected function getComplianceRate(array $dateRange): float
    {
        try {
            $totalInstansi = DB::table('instansis')->count();
            $reportingInstansi = DB::table('reports')
                ->whereBetween('created_at', $dateRange)
                ->distinct()
                ->count('instansi_id');
            return $totalInstansi > 0 ? round(($reportingInstansi / $totalInstansi) * 100, 2) : 0.0;
        } catch (\Throwable $e) {
            Log::error('Failed to compute compliance rate', ['exception' => $e]);
            return 0.0;
        }
    }

    /**
     * Resolve date range based on period.
     *
     * @param string $period One of allowed period keys; defaults to current_year.
     * @return array{0: Carbon, 1: Carbon} Start and end Carbon instances.
     */
    protected function getDateRange(string $period): array
    {
        $allowed = ['current_month', 'last_month', 'current_quarter', 'last_quarter', 'current_year'];
        $period = in_array($period, $allowed, true) ? $period : 'current_year';

        $now = Carbon::now();
        switch ($period) {
            case 'current_month':
                return [
                    $now->copy()->startOfMonth(),
                    $now->copy()->endOfMonth()
                ];
            case 'last_month':
                $lastMonth = $now->copy()->subMonth();
                return [
                    $lastMonth->copy()->startOfMonth(),
                    $lastMonth->copy()->endOfMonth()
                ];
            case 'current_quarter':
                return [
                    $now->copy()->startOfQuarter(),
                    $now->copy()->endOfQuarter()
                ];
            case 'last_quarter':
                $lastQuarter = $now->copy()->subQuarter();
                return [
                    $lastQuarter->copy()->startOfQuarter(),
                    $lastQuarter->copy()->endOfQuarter()
                ];
            case 'current_year':
            default:
                return [
                    $now->copy()->startOfYear(),
                    $now->copy()->endOfYear()
                ];
        }
    }

    /**
     * Clear cached dashboard aggregates for common periods.
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget('sakip_dashboard_current_year_' . auth()->id());
        Cache::forget('sakip_dashboard_current_month_' . auth()->id());
        Cache::forget('sakip_dashboard_current_quarter_' . auth()->id());
    }
}