<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\Assessment;
use App\Models\Report;
use App\Services\ComplianceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * SAKIP Audit Controller
 * 
 * Handles audit trails, activity logs, and compliance monitoring
 * for the SAKIP module with comprehensive audit and compliance tracking.
 */
class SakipAuditController extends Controller
{
    protected ComplianceService $complianceService;

    /**
     * Constructor with dependency injection
     */
    public function __construct(ComplianceService $complianceService)
    {
        $this->complianceService = $complianceService;
    }

    /**
     * Display audit dashboard
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', AuditLog::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;

            // Get audit logs with filtering
            $query = AuditLog::with(['user', 'instansi'])
                ->where('module', 'SAKIP')
                ->orderBy('created_at', 'desc');

            // Role-based filtering
            if (!$user->hasRole('superadmin')) {
                $query->where(function($q) use ($user, $instansiId) {
                    $q->where('user_id', $user->id)
                      ->orWhere('instansi_id', $instansiId);
                });
            }

            // Apply filters
            if ($request->filled('action')) {
                $query->where('action', $request->get('action'));
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->get('user_id'));
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->get('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->get('date_to'));
            }

            if ($request->filled('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('description', 'like', "%{$search}%")
                      ->orWhere('old_values', 'like', "%{$search}%")
                      ->orWhere('new_values', 'like', "%{$search}%");
                });
            }

            $auditLogs = $query->paginate(20);

            // Get audit statistics
            $statistics = $this->getAuditStatistics($user, $instansiId);

            // Get recent activities
            $recentActivities = $this->getRecentActivities($user, $instansiId);

            // Get compliance status
            $complianceStatus = $this->complianceService->runComplianceCheck($instansiId, date('Y'));

            // Get available actions for filtering
            $availableActions = $this->getAvailableActions();

            return view('sakip.audit.index', compact(
                'auditLogs',
                'statistics',
                'recentActivities',
                'complianceStatus',
                'availableActions'
            ));

        } catch (\Exception $e) {
            \Log::error('Audit index error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat halaman audit.');
        }
    }

    /**
     * Show audit log details
     */
    public function show(AuditLog $auditLog)
    {
        $this->authorize('view', $auditLog);

        try {
            $auditLog->load(['user', 'instansi']);

            // Parse old and new values
            $oldValues = is_string($auditLog->old_values) ? json_decode($auditLog->old_values, true) : $auditLog->old_values;
            $newValues = is_string($auditLog->new_values) ? json_decode($auditLog->new_values, true) : $auditLog->new_values;

            // Calculate changes
            $changes = $this->calculateChanges($oldValues, $newValues);

            // Get related audit logs
            $relatedLogs = AuditLog::where('user_id', $auditLog->user_id)
                ->where('module', 'SAKIP')
                ->where('id', '!=', $auditLog->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return view('sakip.audit.show', compact(
                'auditLog',
                'oldValues',
                'newValues',
                'changes',
                'relatedLogs'
            ));

        } catch (\Exception $e) {
            \Log::error('Show audit log error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat detail log audit.');
        }
    }

    /**
     * Display compliance dashboard
     */
    public function compliance(Request $request)
    {
        $this->authorize('viewCompliance', AuditLog::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;
            $currentYear = Carbon::now()->year;

            // Get compliance status
            $complianceStatus = $this->complianceService->getComplianceStatus($instansiId);

            // Get compliance metrics
            $complianceMetrics = $this->complianceService->getComplianceMetrics($instansiId, $currentYear);

            // Get compliance history
            $complianceHistory = $this->complianceService->getComplianceHistory($instansiId, $currentYear - 2, $currentYear);

            // Get pending compliance issues
            $pendingIssues = $this->complianceService->getPendingComplianceIssues($instansiId);

            // Get overdue indicators
            $overdueIndicators = $this->getOverdueIndicators($instansiId);

            // Get missing data indicators
            $missingDataIndicators = $this->getMissingDataIndicators($instansiId, $currentYear);

            // Get incomplete assessments
            $incompleteAssessments = $this->getIncompleteAssessments($instansiId, $currentYear);

            return view('sakip.audit.compliance', compact(
                'complianceStatus',
                'complianceMetrics',
                'complianceHistory',
                'pendingIssues',
                'overdueIndicators',
                'missingDataIndicators',
                'incompleteAssessments',
                'currentYear'
            ));

        } catch (\Exception $e) {
            \Log::error('Compliance dashboard error: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat memuat dashboard kepatuhan.');
        }
    }

    /**
     * Generate compliance report
     */
    public function generateComplianceReport(Request $request)
    {
        $this->authorize('generateComplianceReport', AuditLog::class);

        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:summary,detailed,comparative',
            'period' => 'required|in:monthly,quarterly,semester,annual',
            'year' => 'required|integer|min:2020|max:' . Carbon::now()->year,
            'include_recommendations' => 'nullable|boolean',
            'include_benchmarks' => 'nullable|boolean',
            'format' => 'required|in:pdf,excel',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;

            // Generate compliance report
            $report = $this->complianceService->generateComplianceReport(
                $instansiId,
                $request->get('report_type'),
                $request->get('period'),
                $request->get('year'),
                $request->only(['include_recommendations', 'include_benchmarks', 'format'])
            );

            // Log the activity
            AuditLog::create([
                'user_id' => $user->id,
                'instansi_id' => $instansiId,
                'action' => 'GENERATE_COMPLIANCE_REPORT',
                'module' => 'SAKIP',
                'description' => "Menghasilkan laporan kepatuhan ({$request->get('report_type')} - {$request->get('period')} {$request->get('year')})",
                'old_values' => null,
                'new_values' => ['report_type' => $request->get('report_type'), 'period' => $request->get('period'), 'year' => $request->get('year')],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan kepatuhan berhasil dibuat.',
                'data' => [
                    'file_path' => $report['file_path'],
                    'download_url' => route('sakip.audit.download-compliance-report', ['file' => $report['file_name']]),
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Generate compliance report error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat laporan kepatuhan.',
            ], 500);
        }
    }

    /**
     * Export audit logs
     */
    public function export(Request $request)
    {
        $this->authorize('export', AuditLog::class);

        $validator = Validator::make($request->all(), [
            'format' => 'required|in:csv,excel,pdf',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'action' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;

            // Get audit logs for export
            $query = AuditLog::with(['user', 'instansi'])
                ->where('module', 'SAKIP')
                ->orderBy('created_at', 'desc');

            // Apply filters
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->get('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->get('date_to'));
            }

            if ($request->filled('action')) {
                $query->where('action', $request->get('action'));
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->get('user_id'));
            }

            // Role-based filtering
            if (!$user->hasRole('superadmin')) {
                $query->where(function($q) use ($user, $instansiId) {
                    $q->where('user_id', $user->id)
                      ->orWhere('instansi_id', $instansiId);
                });
            }

            $auditLogs = $query->get();

            // Export based on format
            $format = $request->get('format');
            $exportResult = $this->exportAuditLogs($auditLogs, $format);

            // Log the activity
            AuditLog::create([
                'user_id' => $user->id,
                'instansi_id' => $instansiId,
                'action' => 'EXPORT_AUDIT_LOGS',
                'module' => 'SAKIP',
                'description' => "Mengekspor log audit (Format: {$format})",
                'old_values' => null,
                'new_values' => ['format' => $format, 'count' => $auditLogs->count()],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Log audit berhasil diekspor.',
                'data' => [
                    'file_path' => $exportResult['file_path'],
                    'download_url' => route('sakip.audit.download-export', ['file' => $exportResult['file_name']]),
                ],
            ]);

        } catch (\Exception $e) {
            \Log::error('Export audit logs error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengekspor log audit.',
            ], 500);
        }
    }

    /**
     * Get audit statistics
     */
    public function getStatistics(Request $request)
    {
        $this->authorize('viewStatistics', AuditLog::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;
            $period = $request->get('period', '30'); // Default 30 days

            $statistics = $this->getAuditStatistics($user, $instansiId, $period);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);

        } catch (\Exception $e) {
            \Log::error('Get audit statistics error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik audit.',
            ], 500);
        }
    }

    /**
     * Get compliance status
     */
    public function getComplianceStatus(Request $request)
    {
        $this->authorize('viewCompliance', AuditLog::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;

            $complianceStatus = $this->complianceService->getComplianceStatus($instansiId);

            return response()->json([
                'success' => true,
                'data' => $complianceStatus,
            ]);

        } catch (\Exception $e) {
            \Log::error('Get compliance status error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil status kepatuhan.',
            ], 500);
        }
    }

    /**
     * Get audit statistics
     */
    private function getAuditStatistics($user, $instansiId, $period = 30)
    {
        $startDate = Carbon::now()->subDays($period);
        $query = AuditLog::where('module', 'SAKIP')
            ->where('created_at', '>=', $startDate);

        if (!$user->hasRole('superadmin')) {
            $query->where(function($q) use ($user, $instansiId) {
                $q->where('user_id', $user->id)
                  ->orWhere('instansi_id', $instansiId);
            });
        }

        $totalLogs = $query->count();
        $byAction = $query->select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->pluck('count', 'action')
            ->toArray();

        $byUser = $query->select('user_id', DB::raw('count(*) as count'))
            ->groupBy('user_id')
            ->pluck('count', 'user_id')
            ->toArray();

        $byDate = $query->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        return [
            'total_logs' => $totalLogs,
            'by_action' => $byAction,
            'by_user' => $byUser,
            'by_date' => $byDate,
            'period' => $period,
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($user, $instansiId)
    {
        $query = AuditLog::with(['user'])
            ->where('module', 'SAKIP')
            ->orderBy('created_at', 'desc')
            ->limit(20);

        if (!$user->hasRole('superadmin')) {
            $query->where(function($q) use ($user, $instansiId) {
                $q->where('user_id', $user->id)
                  ->orWhere('instansi_id', $instansiId);
            });
        }

        return $query->get();
    }

    /**
     * Calculate changes between old and new values
     */
    private function calculateChanges($oldValues, $newValues)
    {
        $changes = [];

        if (is_array($oldValues) && is_array($newValues)) {
            foreach ($newValues as $key => $newValue) {
                if (!array_key_exists($key, $oldValues) || $oldValues[$key] !== $newValue) {
                    $changes[$key] = [
                        'old' => $oldValues[$key] ?? null,
                        'new' => $newValue,
                    ];
                }
            }
        }

        return $changes;
    }

    /**
     * Get available actions for filtering
     */
    private function getAvailableActions()
    {
        return [
            'CREATE' => 'Create',
            'UPDATE' => 'Update',
            'DELETE' => 'Delete',
            'APPROVE' => 'Approve',
            'REJECT' => 'Reject',
            'SUBMIT_FOR_APPROVAL' => 'Submit for Approval',
            'GENERATE_FILE' => 'Generate File',
            'DOWNLOAD' => 'Download',
            'IMPORT' => 'Import',
            'EXPORT' => 'Export',
            'CALCULATE' => 'Calculate',
            'ASSESS' => 'Assess',
            'GENERATE_COMPLIANCE_REPORT' => 'Generate Compliance Report',
            'EXPORT_AUDIT_LOGS' => 'Export Audit Logs',
        ];
    }

    /**
     * Get overdue indicators
     */
    private function getOverdueIndicators($instansiId)
    {
        $currentDate = Carbon::now();
        
        return PerformanceIndicator::where('instansi_id', $instansiId)
            ->whereHas('targets', function($q) use ($currentDate) {
                $q->where('target_date', '<', $currentDate)
                  ->where('status', '!=', 'completed');
            })
            ->with(['targets' => function($q) use ($currentDate) {
                $q->where('target_date', '<', $currentDate)
                  ->where('status', '!=', 'completed');
            }])
            ->get();
    }

    /**
     * Get missing data indicators
     */
    private function getMissingDataIndicators($instansiId, $year)
    {
        return PerformanceIndicator::where('instansi_id', $instansiId)
            ->whereDoesntHave('performanceData', function($q) use ($year) {
                $q->whereYear('period', $year);
            })
            ->where('is_mandatory', true)
            ->get();
    }

    /**
     * Get incomplete assessments
     */
    private function getIncompleteAssessments($instansiId, $year)
    {
        return Assessment::whereHas('performanceData.performanceIndicator', function($q) use ($instansiId) {
            $q->where('instansi_id', $instansiId);
        })->whereYear('created_at', $year)
          ->where('status', 'pending')
          ->with(['performanceData.performanceIndicator'])
          ->get();
    }

    /**
     * Export audit logs
     */
    private function exportAuditLogs($auditLogs, $format)
    {
        $fileName = 'audit_logs_' . Carbon::now()->format('Y_m_d_H_i_s');
        $filePath = 'exports/audit/' . $fileName;

        switch ($format) {
            case 'csv':
                return $this->exportToCSV($auditLogs, $filePath . '.csv');
            case 'excel':
                return $this->exportToExcel($auditLogs, $filePath . '.xlsx');
            case 'pdf':
                return $this->exportToPDF($auditLogs, $filePath . '.pdf');
            default:
                throw new \Exception('Format ekspor tidak didukung.');
        }
    }

    /**
     * Export to CSV
     */
    private function exportToCSV($auditLogs, $filePath)
    {
        // Implementation for CSV export
        // This would typically use a CSV library or Laravel's CSV export functionality
        return ['file_path' => $filePath, 'file_name' => basename($filePath)];
    }

    /**
     * Export to Excel
     */
    private function exportToExcel($auditLogs, $filePath)
    {
        // Implementation for Excel export
        // This would typically use PhpSpreadsheet or Laravel Excel package
        return ['file_path' => $filePath, 'file_name' => basename($filePath)];
    }

    /**
     * Export to PDF
     */
    private function exportToPDF($auditLogs, $filePath)
    {
        // Implementation for PDF export
        // This would typically use DomPDF or similar PDF library
        return ['file_path' => $filePath, 'file_name' => basename($filePath)];
    }
}