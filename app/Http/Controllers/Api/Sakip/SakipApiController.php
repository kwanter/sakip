<?php

namespace App\Http\Controllers\Api\Sakip;

use App\Http\Controllers\Controller;
use App\Services\SakipDashboardService;
use App\Services\SakipDataTableService;
use App\Services\SakipService;
use Illuminate\Http\Request;

class SakipApiController extends Controller
{
    protected $sakipService;
    protected $dashboardService;
    protected $dataTableService;

    public function __construct(
        SakipService $sakipService,
        SakipDashboardService $dashboardService,
        SakipDataTableService $dataTableService
    ) {
        $this->sakipService = $sakipService;
        $this->dashboardService = $dashboardService;
        $this->dataTableService = $dataTableService;
    }

    /**
     * Get dashboard data
     */
    public function dashboard(Request $request)
    {
        try {
            $period = $request->input('period', 'current_year');
            $instansiId = $request->input('instansi_id');

            $data = $this->dashboardService->getDashboardData($period);

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get performance summary
     */
    public function performanceSummary(Request $request)
    {
        try {
            $period = $request->input('period', 'current_year');
            $instansiId = $request->input('instansi_id');

            $data = $this->dashboardService->getPerformanceSummary($period);

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch performance summary',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get achievement trends
     */
    public function achievementTrends(Request $request)
    {
        try {
            $period = $request->input('period', '12_months');
            $instansiId = $request->input('instansi_id');

            $data = $this->dashboardService->getAchievementTrends($period, $instansiId);

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch achievement trends',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get compliance status
     */
    public function complianceStatus(Request $request)
    {
        try {
            $instansiId = $request->input('instansi_id');

            $data = $this->dashboardService->getComplianceStatus($instansiId);


            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch compliance status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get indicator comparison
     */
    public function indicatorComparison(Request $request)
    {
        try {
            $instansiId = $request->input('instansi_id');
            $category = $request->input('category');

            $data = $this->dashboardService->getIndicatorComparison($instansiId, $category);

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch indicator comparison',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent indicators
     */
    public function recentIndicators(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $instansiId = $request->input('instansi_id');

            $data = $this->dashboardService->getRecentIndicators($limit, $instansiId);

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent indicators',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get recent reports
     */
    public function recentReports(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $instansiId = $request->input('instansi_id');

            $data = $this->dashboardService->getRecentReports($limit, $instansiId);

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent reports',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get notifications
     */
    public function notifications(Request $request)
    {
        try {
            $limit = $request->input('limit', 10);
            $userId = $request->input('user_id', auth()->id());

            $data = $this->dashboardService->getNotifications($limit, $userId);

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process data table
     */
    public function dataTable(Request $request, string $type)
    {
        try {
            $data = $this->dataTableService->processRequest($request, $type);

            return response()->json([
                'success' => true,
                'data' => $data,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process data table',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get SAKIP configuration
     */
    public function configuration()
    {
        try {
            $config = $this->sakipService->getConfiguration();

            return response()->json([
                'success' => true,
                'data' => $config,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch configuration',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get SAKIP metadata
     */
    public function metadata()
    {
        try {
            $metadata = [
                'indicator_categories' => $this->sakipService->getIndicatorCategories(),
                'indicator_units' => $this->sakipService->getIndicatorUnits(),
                'report_types' => $this->sakipService->getReportTypes(),
                'report_formats' => $this->sakipService->getReportFormats(),
                'assessment_scoring' => $this->sakipService->getAssessmentScoring(),
                'assessment_grading' => $this->sakipService->getAssessmentGrading(),
                'notification_channels' => $this->sakipService->getNotificationChannels(),
                'notification_types' => $this->sakipService->getNotificationTypes(),
                'file_upload_settings' => $this->sakipService->getFileUploadSettings(),
                'audit_log_types' => $this->sakipService->getAuditLogTypes(),
            ];

            return response()->json([
                'success' => true,
                'data' => $metadata,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch metadata',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
