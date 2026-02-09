<?php

namespace App\Http\Controllers\Api\Sakip;

use App\Http\Controllers\Controller;
use App\Services\SakipDashboardService;
use App\Services\SakipDataTableService;
use App\Services\SakipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * SAKIP API Controller
 *
 * Provides unified API endpoints for SAKIP module with consistent
 * error handling and response formatting.
 */
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
     * Get dashboard data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function dashboard(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->handleApiRequest(
            fn() => $this->dashboardService->getDashboardData(
                $request->input('period', 'current_year')
            ),
            'dashboard',
            'Failed to fetch dashboard data'
        );
    }

    /**
     * Get performance summary.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function performanceSummary(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->handleApiRequest(
            fn() => $this->dashboardService->getPerformanceSummary(
                $request->input('period', 'current_year')
            ),
            'performance-summary',
            'Failed to fetch performance summary'
        );
    }

    /**
     * Get achievement trends.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function achievementTrends(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->handleApiRequest(
            fn() => $this->dashboardService->getAchievementTrends(
                $request->input('period', '12_months'),
                $request->input('instansi_id')
            ),
            'achievement-trends',
            'Failed to fetch achievement trends'
        );
    }

    /**
     * Get compliance status.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function complianceStatus(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->handleApiRequest(
            fn() => $this->dashboardService->getComplianceStatus(
                $request->input('instansi_id')
            ),
            'compliance-status',
            'Failed to fetch compliance status'
        );
    }

    /**
     * Get indicator comparison.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indicatorComparison(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->handleApiRequest(
            fn() => $this->dashboardService->getIndicatorComparison(
                $request->input('instansi_id'),
                $request->input('category')
            ),
            'indicator-comparison',
            'Failed to fetch indicator comparison'
        );
    }

    /**
     * Get recent indicators.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentIndicators(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->handleApiRequest(
            fn() => $this->dashboardService->getRecentIndicators(
                $request->input('limit', 10),
                $request->input('instansi_id')
            ),
            'recent-indicators',
            'Failed to fetch recent indicators'
        );
    }

    /**
     * Get recent reports.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentReports(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->handleApiRequest(
            fn() => $this->dashboardService->getRecentReports(
                $request->input('limit', 10),
                $request->input('instansi_id')
            ),
            'recent-reports',
            'Failed to fetch recent reports'
        );
    }

    /**
     * Get notifications.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function notifications(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->handleApiRequest(
            fn() => $this->dashboardService->getNotifications(
                $request->input('limit', 10),
                $request->input('user_id', auth()->id())
            ),
            'notifications',
            'Failed to fetch notifications'
        );
    }

    /**
     * Process data table.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $type The data table type
     * @return \Illuminate\Http\JsonResponse
     */
    public function dataTable(Request $request, string $type): \Illuminate\Http\JsonResponse
    {
        return $this->handleApiRequest(
            fn() => $this->dataTableService->processRequest($request, $type),
            'data-table',
            'Failed to process data table',
            ['type' => $type]
        );
    }

    /**
     * Get SAKIP configuration.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function configuration(): \Illuminate\Http\JsonResponse
    {
        return $this->handleApiRequest(
            fn() => $this->sakipService->getConfiguration(),
            'configuration',
            'Failed to fetch configuration'
        );
    }

    /**
     * Get SAKIP metadata.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function metadata(): \Illuminate\Http\JsonResponse
    {
        return $this->handleApiRequest(
            fn() => [
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
            ],
            'metadata',
            'Failed to fetch metadata'
        );
    }

    /**
     * Handle API requests with unified error handling and logging.
     *
     * This method eliminates code duplication by providing a single
     * implementation for consistent API response formatting.
     *
     * @param callable $callback The operation to execute
     * @param string $endpoint The endpoint name for logging
     * @param string $errorMessage Error message for failures
     * @param array $additionalContext Additional context for error logging
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleApiRequest(
        callable $callback,
        string $endpoint,
        string $errorMessage,
        array $additionalContext = []
    ): \Illuminate\Http\JsonResponse {
        try {
            $result = $callback();

            return response()->json([
                'success' => true,
                'data' => $result,
                'timestamp' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            Log::error("SAKIP API {$endpoint} request failed", array_merge([
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ], $additionalContext));

            return response()->json([
                'success' => false,
                'message' => $errorMessage,
            ], 500);
        }
    }
}
