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
use Illuminate\Support\Facades\Log;
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

    public function __construct(
        ReportGenerationService $reportService,
        AssessmentService $assessmentService,
        DataValidationService $validationService,
    ) {
        $this->reportService = $reportService;
        $this->assessmentService = $assessmentService;
        $this->validationService = $validationService;
    }

    /**
     * Display the main SAKIP dashboard
     *
     * Authorization: Uses 'sakip.dashboard.view' gate handled by SakipDashboardPolicy.
     * This ensures only users with appropriate SAKIP dashboard access can view the page.
     */
    public function index(Request $request)
    {
        $this->authorize("viewDashboard", Auth::user());

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;
            $currentYear = Carbon::now()->year;

            // Get dashboard data based on user role
            $dashboardData = $this->getDashboardMetrics(
                $user,
                $instansiId,
                $currentYear,
            );

            // Get recent activities
            $recentActivities = $this->getRecentActivities($instansiId);

            // Get alerts and notifications
            $alerts = $this->getAlerts($user, $instansiId, $currentYear);

            // Get quick actions
            $quickActions = $this->getQuickActions($user);

            return view(
                "sakip.dashboard.index",
                compact(
                    "dashboardData",
                    "recentActivities",
                    "alerts",
                    "quickActions",
                    "currentYear",
                ),
            );
        } catch (\Exception $e) {
            Log::error("SAKIP Dashboard error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat dashboard SAKIP.",
            );
        }
    }

    /**
     * Helper to get dashboard metrics based on role
     */
    private function getDashboardMetrics($user, $instansiId, $year)
    {
        if ($user->can("viewExecutiveDashboard", $user)) {
            return $this->getSystemWideMetrics($year);
        } elseif ($user->can("view-data-entry-dashboard", $user)) {
            return $this->getInstitutionalMetrics($instansiId, $year);
        }

        return $this->getPersonalMetrics($user, $instansiId, $year);
    }

    /**
     * Get system-wide metrics for superadmin
     */
    private function getSystemWideMetrics($year)
    {
        return [
            "total_indicators" => PerformanceIndicator::count(),
            "total_targets" => Target::where("year", $year)->count(),
            "total_performance_data" => PerformanceData::where(
                "period",
                "like",
                $year . "%",
            )->count(),
            "total_assessments" => Assessment::whereYear(
                "created_at",
                $year,
            )->count(),
            "total_reports" => Report::where(
                "period",
                "like",
                $year . "%",
            )->count(),
            "average_performance" => $this->calculateAveragePerformance($year),
            "compliance_rate" => $this->calculateComplianceRate(),
            "pending_actions" => $this->getPendingActions(),
        ];
    }

    /**
     * Get institutional metrics for admin
     */
    private function getInstitutionalMetrics($instansiId, $year)
    {
        return [
            "total_indicators" => PerformanceIndicator::where(
                "instansi_id",
                $instansiId,
            )->count(),
            "total_targets" => Target::where("year", $year)->count(),
            "total_performance_data" => PerformanceData::whereHas(
                "performanceIndicator",
                function ($q) use ($instansiId) {
                    $q->where("instansi_id", $instansiId);
                },
            )
                ->where("period", "like", $year . "%")
                ->count(),
            "total_assessments" => Assessment::whereYear(
                "created_at",
                $year,
            )->count(),
            "total_reports" => Report::where(
                "period",
                "like",
                $year . "%",
            )->count(),
            "average_performance" => $this->calculateInstitutionalPerformance(
                $instansiId,
                $year,
            ),
            "compliance_rate" => $this->calculateInstitutionalCompliance(
                $instansiId,
                $year,
            ),
            "pending_actions" => $this->getInstitutionalPendingActions(
                $instansiId,
            ),
        ];
    }

    /**
     * Get personal metrics for regular users
     */
    private function getPersonalMetrics($user, $instansiId, $year)
    {
        return [
            "my_indicators" => PerformanceIndicator::where(
                "instansi_id",
                $instansiId,
            )
                ->where("created_by", $user->id)
                ->count(),
            "my_performance_data" => PerformanceData::where(
                "created_by",
                $user->id,
            )
                ->where("period", "like", $year . "%")
                ->count(),
            "my_assessments" => Assessment::where("assessed_by", $user->id)
                ->whereYear("created_at", $year)
                ->count(),
            "pending_tasks" => $this->getUserPendingTasks($user, $instansiId),
            "recent_activities" => $this->getUserRecentActivities($user),
        ];
    }

    /**
     * Calculate average performance across all institutions
     */
    private function calculateAveragePerformance($year)
    {
        $performanceData = PerformanceData::where("period", "like", $year . "%")
            ->whereNotNull("actual_value")
            ->avg("actual_value");

        return round($performanceData ?? 0, 2);
    }

    /**
     * Calculate institutional performance
     */
    private function calculateInstitutionalPerformance($instansiId, $year)
    {
        $performanceData = PerformanceData::whereHas(
            "performanceIndicator",
            function ($q) use ($instansiId) {
                $q->where("instansi_id", $instansiId);
            },
        )
            ->where("period", "like", $year . "%")
            ->whereNotNull("actual_value")
            ->avg("actual_value");

        return round($performanceData ?? 0, 2);
    }

    /**
     * Calculate system-wide compliance rate
     */
    private function calculateComplianceRate()
    {
        $totalInstitutions = Instansi::count();
        $compliantInstitutions = Instansi::whereHas(
            "performanceIndicators",
            function ($q) {
                $q->whereHas("performanceData");
            },
        )->count();

        return $totalInstitutions > 0
            ? round(($compliantInstitutions / $totalInstitutions) * 100, 2)
            : 0;
    }

    /**
     * Calculate institutional compliance rate
     */
    private function calculateInstitutionalCompliance($instansiId, $year)
    {
        $totalIndicators = PerformanceIndicator::where(
            "instansi_id",
            $instansiId,
        )->count();
        $indicatorsWithData = PerformanceData::whereHas(
            "performanceIndicator",
            function ($q) use ($instansiId) {
                $q->where("instansi_id", $instansiId);
            },
        )
            ->where("period", "like", $year . "%")
            ->count();

        return $totalIndicators > 0
            ? round(($indicatorsWithData / $totalIndicators) * 100, 2)
            : 0;
    }

    /**
     * Get pending actions for superadmin
     */
    private function getPendingActions()
    {
        return [
            "unverified_reports" => Report::where(
                "status",
                "submitted",
            )->count(),
            "pending_assessments" => Assessment::where(
                "status",
                "pending",
            )->count(),
            "missing_data_sets" => PerformanceData::whereNull(
                "actual_value",
            )->count(),
        ];
    }

    /**
     * Get pending actions for institution admin
     */
    private function getInstitutionalPendingActions($instansiId)
    {
        return [
            "unverified_reports" => Report::where("instansi_id", $instansiId)
                ->where("status", "submitted")
                ->count(),
            "pending_assessments" => Assessment::whereHas(
                "performanceData.performanceIndicator",
                function ($q) use ($instansiId) {
                    $q->where("instansi_id", $instansiId);
                },
            )
                ->where("status", "pending")
                ->count(),
            "missing_data_sets" => PerformanceData::whereHas(
                "performanceIndicator",
                function ($q) use ($instansiId) {
                    $q->where("instansi_id", $instansiId);
                },
            )
                ->whereNull("actual_value")
                ->count(),
        ];
    }

    /**
     * Get recent activities based on institution
     */
    private function getRecentActivities($instansiId)
    {
        return PerformanceData::whereHas("performanceIndicator", function (
            $q,
        ) use ($instansiId) {
            $q->where("instansi_id", $instansiId);
        })
            ->orderBy("created_at", "desc")
            ->limit(5)
            ->get()
            ->map(function ($data) {
                return [
                    "indicator_name" =>
                        $data->performanceIndicator->name ?? "-",
                    "action" => "Data diperbarui",
                    "created_at" => $data->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get user recent activities
     */
    private function getUserRecentActivities($user)
    {
        return AuditLog::where("user_id", $user->id)
            ->where("module", "SAKIP")
            ->orderBy("created_at", "desc")
            ->limit(5)
            ->get()
            ->map(function ($log) {
                return [
                    "action" => $log->action,
                    "description" => $log->description,
                    "created_at" => $log->created_at->diffForHumans(),
                ];
            });
    }

    /**
     * Get dashboard data for AJAX requests
     *
     * Authorization: Uses 'sakip.dashboard.view' to ensure only permitted users can fetch dashboard data.
     */
    public function getDashboardData(Request $request)
    {
        $this->authorize("viewDashboard", Auth::user());

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;
            $year = $request->get("year", Carbon::now()->year);

            $data = $this->getDashboardMetrics($user, $instansiId, $year);

            return response()->json([
                "success" => true,
                "data" => $data,
                "year" => $year,
            ]);
        } catch (\Exception $e) {
            Log::error("Dashboard data error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" => "Gagal memuat data dashboard.",
                ],
                500,
            );
        }
    }

    /**
     * Get performance trends data
     *
     * Authorization: Uses 'sakip.dashboard.view' so only dashboard-authorized users can access trends.
     */
    public function getPerformanceTrends(Request $request)
    {
        $this->authorize("viewDashboard", Auth::user());

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;
            $years = range(Carbon::now()->year - 4, Carbon::now()->year);

            $trends = [];
            foreach ($years as $year) {
                $avgPerformance = PerformanceData::whereHas(
                    "performanceIndicator",
                    function ($q) use ($instansiId) {
                        $q->where("instansi_id", $instansiId);
                    },
                )
                    ->where("period", "like", $year . "%")
                    ->whereNotNull("actual_value")
                    ->avg("actual_value");

                $trends[] = [
                    "year" => $year,
                    "performance" => round($avgPerformance ?? 0, 2),
                ];
            }

            return response()->json([
                "success" => true,
                "data" => $trends,
            ]);
        } catch (\Exception $e) {
            Log::error("Performance trends error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" => "Gagal memuat tren kinerja.",
                ],
                500,
            );
        }
    }

    /**
     * Get alerts and notifications
     *
     * Generates summary alerts for missing data, pending assessments, and report status.
     * Handles errors gracefully and returns an empty list on failure.
     */
    private function getAlerts($user, $instansiId, $year)
    {
        $alerts = [];

        try {
            // Missing performance data for current year
            $missingData = PerformanceData::whereHas(
                "performanceIndicator",
                function ($q) use ($instansiId) {
                    $q->where("instansi_id", $instansiId);
                },
            )
                ->where("period", "like", $year . "%")
                ->whereNull("actual_value")
                ->count();

            if ($missingData > 0) {
                $alerts[] = [
                    "type" => "info",
                    "message" => "Ada {$missingData} data kinerja yang belum lengkap untuk tahun {$year}.",
                    "link" => route("sakip.performance-data.index"),
                ];
            }

            // Pending assessments
            $pendingAssessments = Assessment::whereHas(
                "performanceData",
                function ($q) use ($instansiId) {
                    $q->where("instansi_id", $instansiId);
                },
            )
                ->where("status", "pending")
                ->count();

            if ($pendingAssessments > 0) {
                $alerts[] = [
                    "type" => "info",
                    "message" => "Ada {$pendingAssessments} penilaian yang menunggu proses.",
                    "link" => route("sakip.assessments.index"),
                ];
            }

            // Reports needing verification (safer than deadline-based check)
            $unverifiedReports = Report::where("instansi_id", $instansiId)
                ->where("status", "submitted")
                ->count();

            if ($unverifiedReports > 0) {
                $alerts[] = [
                    "type" => "warning",
                    "message" => "Ada {$unverifiedReports} laporan yang belum diverifikasi.",
                    "link" => route("sakip.reports.index"),
                ];
            }

            return $alerts;
        } catch (\Exception $e) {
            Log::error("Alerts generation error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get quick actions
     */
    private function getQuickActions($user)
    {
        $actions = [];

        try {
            if ($user->can("create", PerformanceIndicator::class)) {
                $actions[] = [
                    "label" => "Tambah Indikator",
                    "link" => route("sakip.indicators.create"),
                    "icon" => "fas fa-plus-circle",
                ];
            }

            if ($user->can("create", PerformanceData::class)) {
                $actions[] = [
                    "label" => "Input Data Kinerja",
                    "link" => route("sakip.performance-data.create"),
                    "icon" => "fas fa-keyboard",
                ];
            }

            if ($user->can("create", Assessment::class)) {
                $actions[] = [
                    "label" => "Buat Penilaian",
                    "link" => route("sakip.assessments.create"),
                    "icon" => "fas fa-check-double",
                ];
            }

            if ($user->can("create", Report::class)) {
                $actions[] = [
                    "label" => "Buat Laporan",
                    "link" => route("sakip.reports.create"),
                    "icon" => "fas fa-file-alt",
                ];
            }

            return $actions;
        } catch (\Exception $e) {
            Log::error("Quick actions error: " . $e->getMessage());
            return [];
        }
    }
    private function getUserPendingTasks($user, $instansiId)
    {
        return [
            "unsubmitted_reports" => Report::where("instansi_id", $instansiId)
                ->where("created_by", $user->id)
                ->whereNull("submitted_at")
                ->count(),
            "pending_assessments" => Assessment::where("assessed_by", $user->id)
                ->where("status", "pending")
                ->count(),
            "missing_data_sets" => PerformanceData::where(
                "created_by",
                $user->id,
            )
                ->whereNull("actual_value")
                ->count(),
        ];
    }
}
