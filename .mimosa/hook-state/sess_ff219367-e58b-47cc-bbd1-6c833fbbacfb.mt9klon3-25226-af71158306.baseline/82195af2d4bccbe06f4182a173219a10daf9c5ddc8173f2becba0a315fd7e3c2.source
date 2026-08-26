<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\SakipService;
use App\Services\SakipDashboardService;
use App\Services\SakipDataTableService;

class SakipTestController extends Controller
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
     * Test the SAKIP dashboard integration
     */
    public function testDashboard()
    {
        // Test both view and API response
        if (request()->wantsJson()) {
            try {
                // Test dashboard data retrieval
                $dashboardData = $this->dashboardService->getDashboardData('current_year');
                
                // Test notification system
                $notifications = $this->sakipService->getNotificationChannels();
                
                // Test configuration
                $config = $this->sakipService->getConfig();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'dashboard' => $dashboardData,
                        'notifications' => $notifications,
                        'config' => $config,
                        'message' => 'SAKIP Dashboard integration test successful'
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        }

        return view('sakip.test');
    }

    /**
     * Test the SAKIP data table integration
     */
    public function testDataTable(Request $request)
    {
        // Test both view and API response
        if (request()->wantsJson()) {
            try {
                // Test data table initialization
                $dataTableConfig = $this->sakipService->getDataTableConfig('indicator');
                
                // Test data retrieval
                $data = $this->sakipService->getDataTableData('indicator', request()->all());
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'config' => $dataTableConfig,
                        'data' => $data,
                        'message' => 'SAKIP DataTable integration test successful'
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        }

        return view('sakip.test');
    }

    /**
     * Test the SAKIP notification system
     */
    public function testNotification()
    {
        // Test both view and API response
        if (request()->wantsJson()) {
            try {
                // Test notification channels
                $channels = $this->sakipService->getNotificationChannels();
                
                // Test notification types
                $types = $this->sakipService->getNotificationTypes();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'channels' => $channels,
                        'types' => $types,
                        'message' => 'SAKIP Notification system test successful'
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        }

        return view('sakip.test');
    }

    /**
     * Test the SAKIP service configuration
     */
    public function testConfiguration()
    {
        // Test both view and API response
        if (request()->wantsJson()) {
            try {
                // Test configuration loading
                $config = $this->sakipService->getConfig();
                
                // Test configuration validation
                $validation = $this->sakipService->validateConfiguration();
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'config' => $config,
                        'validation' => $validation,
                        'message' => 'SAKIP Configuration test successful'
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        }

        return view('sakip.test');
    }

    /**
     * Test the SAKIP helpers
     */
    public function testHelpers()
    {
        // Test both view and API response
        if (request()->wantsJson()) {
            try {
                // Test helper functions
                $helpers = [
                    'formatCurrency' => $this->sakipService->formatCurrency(1000000),
                    'formatPercentage' => $this->sakipService->formatPercentage(85.75),
                    'formatDate' => $this->sakipService->formatDate(now()),
                    'formatIndicatorValue' => $this->sakipService->formatIndicatorValue(75.5, 'percentage'),
                    'calculateAchievement' => $this->sakipService->calculateAchievement(80, 100),
                    'getStatusBadge' => $this->sakipService->getStatusBadge('active'),
                    'getAssessmentColor' => $this->sakipService->getAssessmentColor(85),
                    'getIndicatorIcon' => $this->sakipService->getIndicatorIcon('quantitative')
                ];
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'helpers' => $helpers,
                        'message' => 'SAKIP Helpers test successful'
                    ]
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ], 500);
            }
        }

        return view('sakip.test');
    }
}