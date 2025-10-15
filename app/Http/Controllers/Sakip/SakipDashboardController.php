<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\Assessment;
use App\Models\Report;
use App\Models\Target;
use App\Models\EvidenceDocument;
use App\Models\Instansi;
use App\Models\AuditLog;
use App\Services\ReportGenerationService;
use App\Services\AssessmentService;
use App\Services\DataValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * SAKIP Dashboard Controller
 * 
 * Handles dashboard overview, metrics, alerts, and quick actions
 * for the SAKIP (Sistem Akuntabilitas Kinerja Instansi Pemerintah) module.
 */
class SakipDashboardController extends Controller
{
    protected ReportGenerationService $reportService;
    protected AssessmentService $assessmentService;
    protected DataValidationService $validationService;

    /**
     * Constructor with dependency injection
     */
    public function __construct(
        ReportGenerationService $reportService,
        AssessmentService $assessmentService,
        DataValidationService $validationService
    ) {
        $this->reportService = $reportService;
        $this->assessmentService = $assessmentService;
        $this->validationService = $validationService;
    }

    /**
     * Display the main SAKIP dashboard
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', PerformanceIndicator::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;
            $currentYear = Carbon::now()->year;

            // Get dashboard data based on user role
            $dashboardData = $this->getDashboardMetrics($user, $instansiId, $currentYear);
            
            // Get recent activities
            $recentActivities = $this->getRecentActivities($instansiId);
            
            // Get alerts and notifications
            $alerts = $this->getAlerts($user, $instansiId, $currentYear);
            
            // Get quick actions
            $quickActions = $this->getQuickActions($user);

            return view('sakip.dashboard.index', compact(
                'dashboardData',
                'recentActivities',
                'alerts',
                'quickActions',
                'currentYear'
            ));

        } catch (\Exception $e) {
            \Log::error('SAKIP Dashboard error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat dashboard SAKIP.');
        }
    }

    /**
     * Get dashboard data based on user role
     */
    private function getDashboardMetrics($user, $instansiId, $year)
    {
        $data = [];

        if ($user->hasRole('superadmin')) {
            // Superadmin sees system-wide metrics
            $data = $this->getSystemWideMetrics($year);
        } elseif ($user->hasRole('admin')) {
            // Admin sees institutional metrics
            $data = $this->getInstitutionalMetrics($instansiId, $year);
        } else {
            // Regular user sees personal/department metrics
            $data = $this->getPersonalMetrics($user, $instansiId, $year);
        }

        return $data;
    }

    /**
     * Get system-wide metrics for superadmin
     */
    private function getSystemWideMetrics($year)
    {
        return [
            'total_institutions' => Instansi::count(),
            'total_indicators' => PerformanceIndicator::count(),
            'total_targets' => Target::where('year', $year)->count(),
            'total_performance_data' => PerformanceData::whereYear('period', $year)->count(),
            'total_assessments' => Assessment::whereYear('created_at', $year)->count(),
            'total_reports' => Report::whereYear('reporting_period', $year)->count(),
            'average_performance' => $this->calculateAveragePerformance($year),
            'compliance_rate' => $this->calculateComplianceRate(),
            'pending_actions' => $this->getPendingActions(),
        ];
    }

    /**
     * Get institutional metrics for admin
     */
    private function getInstitutionalMetrics($instansiId, $year)
    {
        return [
            'total_indicators' => PerformanceIndicator::where('instansi_id', $instansiId)->count(),
            'total_targets' => Target::where('instansi_id', $instansiId)->where('year', $year)->count(),
            'total_performance_data' => PerformanceData::whereHas('indicator', function($q) use ($instansiId) {
                $q->where('instansi_id', $instansiId);
            })->whereYear('period', $year)->count(),
            'total_assessments' => Assessment::where('instansi_id', $instansiId)->whereYear('created_at', $year)->count(),
            'total_reports' => Report::where('instansi_id', $instansiId)->whereYear('reporting_period', $year)->count(),
            'average_performance' => $this->calculateInstitutionalPerformance($instansiId, $year),
            'compliance_rate' => $this->calculateInstitutionalCompliance($instansiId, $year),
            'pending_actions' => $this->getInstitutionalPendingActions($instansiId),
        ];
    }

    /**
     * Get personal metrics for regular users
     */
    private function getPersonalMetrics($user, $instansiId, $year)
    {
        return [
            'my_indicators' => PerformanceIndicator::where('instansi_id', $instansiId)
                ->where('created_by', $user->id)->count(),
            'my_performance_data' => PerformanceData::where('created_by', $user->id)
                ->whereYear('period', $year)->count(),
            'my_assessments' => Assessment::where('assessor_id', $user->id)
                ->whereYear('created_at', $year)->count(),
            'pending_tasks' => $this->getUserPendingTasks($user, $instansiId),
            'recent_activities' => $this->getUserRecentActivities($user),
        ];
    }

    /**
     * Calculate average performance across all institutions
     */
    private function calculateAveragePerformance($year)
    {
        $performanceData = PerformanceData::whereYear('period', $year)
            ->whereNotNull('performance_percentage')
            ->avg('performance_percentage');

        return round($performanceData ?? 0, 2);
    }

    /**
     * Calculate institutional performance
     */
    private function calculateInstitutionalPerformance($instansiId, $year)
    {
        $performanceData = PerformanceData::whereHas('indicator', function($q) use ($instansiId) {
            $q->where('instansi_id', $instansiId);
        })->whereYear('period', $year)
          ->whereNotNull('performance_percentage')
          ->avg('performance_percentage');

        return round($performanceData ?? 0, 2);
    }

    /**
     * Calculate system-wide compliance rate
     */
    private function calculateComplianceRate()
    {
        $totalInstitutions = Instansi::count();
        $compliantInstitutions = Instansi::whereHas('performanceIndicators', function($q) {
            $q->whereHas('performanceData');
        })->count();

        return $totalInstitutions > 0 ? round(($compliantInstitutions / $totalInstitutions) * 100, 2) : 0;
    }

    /**
     * Calculate institutional compliance rate
     */
    private function calculateInstitutionalCompliance($instansiId, $year)
    {
        $totalIndicators = PerformanceIndicator::where('instansi_id', $instansiId)->count();
        $indicatorsWithData = PerformanceIndicator::where('instansi_id', $instansiId)
            ->whereHas('performanceData', function($q) use ($year) {
                $q->whereYear('period', $year);
            })->count();

        return $totalIndicators > 0 ? round(($indicatorsWithData / $totalIndicators) * 100, 2) : 0;
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($instansiId = null)
    {
        $query = AuditLog::with('user')
            ->where('module', 'SAKIP')
            ->orderBy('created_at', 'desc')
            ->limit(10);

        if ($instansiId) {
            $query->where('instansi_id', $instansiId);
        }

        return $query->get()->map(function ($log) {
            return [
                'id' => $log->id,
                'action' => $log->action,
                'description' => $log->description,
                'user' => $log->user ? $log->user->name : 'System',
                'created_at' => $log->created_at->diffForHumans(),
            ];
        });
    }

    /**
     * Get alerts and notifications
     */
    private function getAlerts($user, $instansiId, $year)
    {
        $alerts = [];

        try {
            // Check for overdue targets
            $overdueTargets = Target::where('instansi_id', $instansiId)
                ->where('year', $year)
                ->where('deadline', '<', Carbon::now())
                ->whereNull('achieved_value')
                ->count();

            if ($overdueTargets > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => "Ada {$overdueTargets} target yang melewati batas waktu.",
                    'link' => route('sakip.targets.index'),
                ];
            }

            // Check for missing performance data
            $missingData = PerformanceIndicator::where('instansi_id', $instansiId)
                ->whereDoesntHave('performanceData', function($q) use ($year) {
                    $q->whereYear('period', $year);
                })->count();

            if ($missingData > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'message' => "Ada {$missingData} indikator yang belum memiliki data kinerja untuk tahun {$year}.",
                    'link' => route('sakip.data-collection.index'),
                ];
            }

            // Check for pending assessments
            $pendingAssessments = Assessment::where('instansi_id', $instansiId)
                ->where('status', 'pending')
                ->count();

            if ($pendingAssessments > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'message' => "Ada {$pendingAssessments} penilaian yang menunggu proses.",
                    'link' => route('sakip.assessments.index'),
                ];
            }

            // Check for overdue reports
            $overdueReports = Report::where('instansi_id', $instansiId)
                ->where('status', 'draft')
                ->where('deadline', '<', Carbon::now())
                ->count();

            if ($overdueReports > 0) {
                $alerts[] = [
                    'type' => 'danger',
                    'message' => "Ada {$overdueReports} laporan yang melewati batas pengumpulan.",
                    'link' => route('sakip.reports.index'),
                ];
            }

        } catch (\Exception $e) {
            \Log::error('Error generating alerts: ' . $e->getMessage());
        }

        return $alerts;
    }

    /**
     * Get quick actions based on user role
     */
    private function getQuickActions($user)
    {
        $actions = [];

        if ($user->can('create', PerformanceIndicator::class)) {
            $actions[] = [
                'title' => 'Tambah Indikator',
                'description' => 'Buat indikator kinerja baru',
                'icon' => 'fas fa-plus',
                'link' => route('sakip.indicators.create'),
                'color' => 'primary',
            ];
        }

        if ($user->can('create', PerformanceData::class)) {
            $actions[] = [
                'title' => 'Input Data Kinerja',
                'description' => 'Masukkan data kinerja terbaru',
                'icon' => 'fas fa-chart-line',
                'link' => route('sakip.data-collection.create'),
                'color' => 'success',
            ];
        }

        if ($user->can('create', Assessment::class)) {
            $actions[] = [
                'title' => 'Buat Penilaian',
                'description' => 'Lakukan penilaian kinerja',
                'icon' => 'fas fa-clipboard-check',
                'link' => route('sakip.assessments.create'),
                'color' => 'warning',
            ];
        }

        if ($user->can('create', Report::class)) {
            $actions[] = [
                'title' => 'Buat Laporan',
                'description' => 'Buat laporan kinerja',
                'icon' => 'fas fa-file-alt',
                'link' => route('sakip.reports.create'),
                'color' => 'info',
            ];
        }

        return $actions;
    }

    /**
     * Get pending actions for superadmin
     */
    private function getPendingActions()
    {
        return [
            'pending_institutions' => Instansi::where('is_active', false)->count(),
            'pending_assessments' => Assessment::where('status', 'pending')->count(),
            'pending_reports' => Report::where('status', 'submitted')->count(),
        ];
    }

    /**
     * Get pending actions for institution admin
     */
    private function getInstitutionalPendingActions($instansiId)
    {
        return [
            'missing_data' => PerformanceIndicator::where('instansi_id', $instansiId)
                ->whereDoesntHave('performanceData', function($q) {
                    $q->whereYear('period', Carbon::now()->year);
                })->count(),
            'pending_assessments' => Assessment::where('instansi_id', $instansiId)
                ->where('status', 'pending')->count(),
            'overdue_reports' => Report::where('instansi_id', $instansiId)
                ->where('status', 'draft')
                ->where('deadline', '<', Carbon::now())->count(),
        ];
    }

    /**
     * Get pending tasks for regular user
     */
    private function getUserPendingTasks($user, $instansiId)
    {
        return [
            'data_entry_tasks' => PerformanceIndicator::where('instansi_id', $instansiId)
                ->where('created_by', $user->id)
                ->whereDoesntHave('performanceData', function($q) {
                    $q->whereYear('period', Carbon::now()->year);
                })->count(),
            'assessment_tasks' => Assessment::where('assessor_id', $user->id)
                ->where('status', 'pending')->count(),
        ];
    }

    /**
     * Get user recent activities
     */
    private function getUserRecentActivities($user)
    {
        return AuditLog::where('user_id', $user->id)
            ->where('module', 'SAKIP')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()->map(function ($log) {
                return [
                    'action' => $log->action,
                    'description' => $log->description,
                    'created_at' => $log->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get dashboard data for AJAX requests
     */
    public function getDashboardData(Request $request)
    {
        $this->authorize('viewAny', PerformanceIndicator::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;
            $year = $request->get('year', Carbon::now()->year);

            $data = $this->getDashboardData($user, $instansiId, $year);

            return response()->json([
                'success' => true,
                'data' => $data,
                'year' => $year,
            ]);

        } catch (\Exception $e) {
            \Log::error('Dashboard data error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data dashboard.',
            ], 500);
        }
    }

    /**
     * Get performance trends data
     */
    public function getPerformanceTrends(Request $request)
    {
        $this->authorize('viewAny', PerformanceIndicator::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;
            $years = range(Carbon::now()->year - 4, Carbon::now()->year);

            $trends = [];
            foreach ($years as $year) {
                $avgPerformance = PerformanceData::whereHas('indicator', function($q) use ($instansiId) {
                    $q->where('instansi_id', $instansiId);
                })->whereYear('period', $year)
                  ->whereNotNull('performance_percentage')
                  ->avg('performance_percentage');

                $trends[] = [
                    'year' => $year,
                    'performance' => round($avgPerformance ?? 0, 2),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $trends,
            ]);

        } catch (\Exception $e) {
            \Log::error('Performance trends error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat tren kinerja.',
            ], 500);
        }
    }
}