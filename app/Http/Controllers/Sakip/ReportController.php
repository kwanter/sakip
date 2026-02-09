<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Services\ReportGenerationService;
use App\Services\TemplateService;
use App\Services\ReportCalculationService;
use App\Services\DropdownCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Report Controller
 *
 * Handles report generation, templates, export options, and submission tracking
 * for the SAKIP module with comprehensive reporting capabilities.
 */
class ReportController extends Controller
{
    protected ReportGenerationService $reportService;
    protected TemplateService $templateService;
    protected ReportCalculationService $calculationService;
    protected DropdownCacheService $dropdownCache;

    /**
     * Constructor with dependency injection
     */
    public function __construct(
        ReportGenerationService $reportService,
        TemplateService $templateService,
        ReportCalculationService $calculationService,
        DropdownCacheService $dropdownCache,
    ) {
        $this->reportService = $reportService;
        $this->templateService = $templateService;
        $this->calculationService = $calculationService;
        $this->dropdownCache = $dropdownCache;
    }

    /**
     * Display report dashboard
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny", Report::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;
            $currentYear = Carbon::now()->year;

            // Get reports based on user role
            $query = Report::with(["creator", "approver"])->whereYear(
                "period",
                $currentYear,
            );

            // Role-based filtering
            if (!$user->hasRole("superadmin")) {
                $query->where(function ($q) use ($user, $instansiId) {
                    $q->where("created_by", $user->id)->orWhere(
                        "instansi_id",
                        $instansiId,
                    );
                });
            }

            // Apply filters
            if ($request->filled("status")) {
                $query->where("status", $request->get("status"));
            }

            if ($request->filled("type")) {
                $query->where("report_type", $request->get("type"));
            }

            if ($request->filled("period")) {
                $query->where("period", $request->get("period"));
            }

            if ($request->filled("category")) {
                $query->where("category", $request->get("category"));
            }

            $reports = $query->orderBy("created_at", "desc")->paginate(15);

            // Get report statistics
            $statistics = $this->calculationService->getReportStatistics(
                $user,
                $currentYear,
            );

            // Get available templates
            $templates = $this->templateService->getAvailableTemplates("sakip");

            // Get recent reports
            $recentReports = Report::with(["creator", "approver"])
                ->whereYear("period", $currentYear)
                ->where(function ($q) use ($user, $instansiId) {
                    if (!$user->hasRole("superadmin")) {
                        $q->where("created_by", $user->id)->orWhere(
                            "instansi_id",
                            $instansiId,
                        );
                    }
                })
                ->orderBy("updated_at", "desc")
                ->limit(10)
                ->get();

            return view(
                "sakip.reports.index",
                compact(
                    "reports",
                    "statistics",
                    "templates",
                    "recentReports",
                    "currentYear",
                ),
            );
        } catch (\Exception $e) {
            Log::error("Report index error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat halaman laporan.",
            );
        }
    }

    /**
     * Show report creation form
     */
    public function create(Request $request)
    {
        $this->authorize("create", Report::class);

        try {
            $user = Auth::user();
            $currentYear = Carbon::now()->year;

            // Get available templates
            $templates = $this->templateService->getAvailableTemplates("sakip");

            // Get all institutions for dropdown (cached)
            $instansis = $this->dropdownCache->getActiveInstansi();

            // Get performance data for report generation
            $indicators = PerformanceIndicator::where(
                "instansi_id",
                $user->instansi_id,
            )
                ->with([
                    "performanceData" => function ($q) use ($currentYear) {
                        $q->whereYear("period", $currentYear)->where(
                            "status",
                            "approved",
                        );
                    },
                    "assessments" => function ($q) use ($currentYear) {
                        $q->whereYear("created_at", $currentYear)->where(
                            "status",
                            "approved",
                        );
                    },
                    "instansi",
                    "targets" => function ($q) use ($currentYear) {
                        $q->where("year", $currentYear);
                    },
                ])
                ->orderBy("name")
                ->get();

            // Get available periods
            $availablePeriods = $this->calculationService->getAvailableReportPeriods();

            return view(
                "sakip.reports.create",
                compact(
                    "templates",
                    "instansis",
                    "indicators",
                    "availablePeriods",
                    "currentYear",
                ),
            );
        } catch (\Exception $e) {
            Log::error("Report create form error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat formulir pembuatan laporan.",
            );
        }
    }

    /**
     * Store report
     */
    /**
     * Store report
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $this->authorize("create", Report::class);

        $validator = Validator::make($request->all(), [
            "report_type" =>
                "required|in:monthly,quarterly,semester,annual,custom",
            "period" => "required|string|max:20",
            "category" =>
                "required|in:performance,assessment,compliance,summary",
            "title" => "required|string|max:255",
            "description" => "nullable|string|max:1000",
            "template_id" => "nullable|exists:report_templates,id",
            "indicators" => "required|array|min:1",
            "indicators.*" => "exists:performance_indicators,id",
            "include_assessments" => "nullable|boolean",
            "include_benchmarks" => "nullable|boolean",
            "include_recommendations" => "nullable|boolean",
            "format" => "required|in:pdf,excel,word",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal.",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // Create report record
            $report = new Report([
                "report_type" => $request->get("report_type"),
                "period" => $request->get("period"),
                "category" => $request->get("category"),
                "title" => $request->get("title"),
                "description" => $request->get("description"),
                "template_id" => $request->get("template_id"),
                "instansi_id" => $user->instansi_id,
                "status" => "draft",
                "created_by" => $user->id,
                "updated_by" => $user->id,
            ]);
            $report->save();

            // Store selected indicators
            $report->indicators()->attach($request->get("indicators"));

            // Generate report content
            $reportContent = $this->generateReportContent(
                $report,
                $request->get("indicators"),
                $request->only([
                    "include_assessments",
                    "include_benchmarks",
                    "include_recommendations",
                ]),
            );

            // Update report with generated content
            $report->update([
                "content" => $reportContent,
                "file_path" => null,
            ]);

            // Log the activity
            $auditLog = new AuditLog([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "CREATE",
                "module" => "SAKIP",
                "description" => "Membuat laporan: {$report->title} (Periode: {$report->period})",
                "old_values" => null,
                "new_values" => $report->toArray(),
            ]);
            $auditLog->save();

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Laporan berhasil dibuat.",
                "data" => [
                    "id" => $report->id,
                    "status" => $report->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Store report error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" => "Terjadi kesalahan saat membuat laporan.",
                ],
                500,
            );
        }
    }

    /**
     * Show report details
     */
    public function show(Report $report)
    {
        $this->authorize("view", $report);

        try {
            $report->load(["creator", "approver", "indicators"]);

            // Get report content with data
            $reportData = $this->getReportData($report);

            // Get related reports
            $relatedReports = Report::where("id", "!=", $report->id)
                ->where("instansi_id", $report->instansi_id)
                ->where("report_type", $report->report_type)
                ->orderBy("period", "desc")
                ->limit(5)
                ->get();

            return view(
                "sakip.reports.show",
                compact("report", "reportData", "relatedReports"),
            );
        } catch (\Exception $e) {
            Log::error("Show report error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat detail laporan.",
            );
        }
    }

    /**
     * Generate report file
     */
    public function generateFile(Request $request, Report $report)
    {
        $this->authorize("generateFile", $report);

        $validator = Validator::make($request->all(), [
            "format" => "required|in:pdf,excel,word",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal.",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        try {
            $user = Auth::user();
            $format = $request->get("format");

            // Generate report file based on format
            $filePath = $this->generateReportFile($report, $format);

            // Update report with file path
            $report->update([
                "file_path" => $filePath,
                "file_format" => $format,
                "generated_at" => Carbon::now(),
                "generated_by" => $user->id,
            ]);

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "GENERATE_FILE",
                "module" => "SAKIP",
                "description" => "Menghasilkan file laporan: {$report->title} (Format: {$format})",
                "old_values" => null,
                "new_values" => ["file_path" => $filePath, "format" => $format],
            ]);

            return response()->json([
                "success" => true,
                "message" => "File laporan berhasil dibuat.",
                "data" => [
                    "file_path" => $filePath,
                    "download_url" => route("sakip.reports.download", [
                        "report" => $report->id,
                    ]),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error("Generate report file error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" => "Terjadi kesalahan saat membuat file laporan.",
                ],
                500,
            );
        }
    }

    /**
     * Submit report for approval
     */
    public function submitForApproval(Request $request, Report $report)
    {
        $this->authorize("submitForApproval", $report);

        $validator = Validator::make($request->all(), [
            "submission_notes" => "nullable|string|max:1000",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal.",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $oldStatus = $report->status;

            // Update report status
            $report->update([
                "status" => "pending_approval",
                "submission_notes" => $request->get("submission_notes"),
                "submitted_at" => Carbon::now(),
                "submitted_by" => $user->id,
                "updated_by" => $user->id,
            ]);

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "SUBMIT_FOR_APPROVAL",
                "module" => "SAKIP",
                "description" => "Mengirim laporan untuk persetujuan: {$report->title}",
                "old_values" => ["status" => $oldStatus],
                "new_values" => ["status" => "pending_approval"],
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Laporan berhasil dikirim untuk persetujuan.",
                "data" => [
                    "id" => $report->id,
                    "status" => $report->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Submit report for approval error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Terjadi kesalahan saat mengirim laporan untuk persetujuan.",
                ],
                500,
            );
        }
    }

    /**
     * Approve report
     */
    public function approve(Request $request, Report $report)
    {
        $this->authorize("approve", $report);

        $validator = Validator::make($request->all(), [
            "approval_decision" => "required|in:approved,rejected",
            "approval_notes" => "nullable|string|max:1000",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal.",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $oldStatus = $report->status;
            $approvalDecision = $request->get("approval_decision");

            // Update report based on approval decision
            $updateData = [
                "approver_id" => $user->id,
                "approval_notes" => $request->get("approval_notes"),
                "approved_at" => Carbon::now(),
                "updated_by" => $user->id,
            ];

            if ($approvalDecision === "approved") {
                $updateData["status"] = "approved";
            } else {
                $updateData["status"] = "rejected";
            }

            $report->update($updateData);

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "APPROVE",
                "module" => "SAKIP",
                "description" => "Menyetujui laporan: {$report->title} (Keputusan: {$approvalDecision})",
                "old_values" => ["status" => $oldStatus],
                "new_values" => ["status" => $updateData["status"]],
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Laporan berhasil diproses.",
                "data" => [
                    "id" => $report->id,
                    "status" => $report->status,
                    "decision" => $approvalDecision,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Approve report error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Terjadi kesalahan saat memproses persetujuan laporan.",
                ],
                500,
            );
        }
    }

    /**
     * Download report file
     */
    public function download(Report $report)
    {
        $this->authorize("download", $report);

        try {
            if (!$report->file_path || !Storage::exists($report->file_path)) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "File laporan tidak tersedia.",
                    ],
                    404,
                );
            }

            // Log download activity
            AuditLog::create([
                "user_id" => Auth::id(),
                "instansi_id" => Auth::user()->instansi_id,
                "action" => "DOWNLOAD",
                "module" => "SAKIP",
                "description" => "Mengunduh laporan: {$report->title}",
                "old_values" => null,
                "new_values" => ["file_path" => $report->file_path],
            ]);

            // Sanitize filename to prevent path traversal and injection
            $sanitizedFilename = $this->sanitizeFilename($report->title);
            $downloadFilename = $sanitizedFilename . "." . $report->file_format;

            return Storage::download(
                $report->file_path,
                $downloadFilename,
            );
        } catch (\Exception $e) {
            Log::error("Download report error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" => "Terjadi kesalahan saat mengunduh laporan.",
                ],
                500,
            );
        }
    }

    /**
     * Sanitize filename to prevent path traversal and injection attacks
     *
     * @param string $filename The filename to sanitize
     * @return string The sanitized filename
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove any directory traversal components
        $filename = basename($filename);

        // Remove any dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

        // Remove multiple consecutive underscores
        $filename = preg_replace('/_+/', '_', $filename);

        // Trim underscores from start and end
        $filename = trim($filename, '_');

        // Ensure filename is not empty
        if (empty($filename)) {
            $filename = 'laporan_' . date('Y-m-d_His');
        }

        // Limit filename length (255 bytes is typical filesystem limit)
        if (strlen($filename) > 200) {
            $filename = substr($filename, 0, 200);
        }

        return $filename;
    }

    /**
     * Delete report
     */
    public function destroy(Report $report)
    {
        $this->authorize("delete", $report);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $reportTitle = $report->title;
            $oldValues = $report->toArray();

            // Delete file if exists
            if ($report->file_path && Storage::exists($report->file_path)) {
                Storage::delete($report->file_path);
            }

            // Delete report
            $report->delete();

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "DELETE",
                "module" => "SAKIP",
                "description" => "Menghapus laporan: {$reportTitle}",
                "old_values" => $oldValues,
                "new_values" => null,
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Laporan berhasil dihapus.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Delete report error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" => "Terjadi kesalahan saat menghapus laporan.",
                ],
                500,
            );
        }
    }

    /**
     * Get report statistics
     */
    public function getStatistics(Request $request)
    {
        $this->authorize("viewAny", Report::class);

        try {
            $user = Auth::user();
            $year = $request->get("year", Carbon::now()->year);

            $statistics = $this->getReportStatistics($user, $year);

            return response()->json([
                "success" => true,
                "data" => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error("Get report statistics error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Terjadi kesalahan saat mengambil statistik laporan.",
                ],
                500,
            );
        }
    }

    /**
     * Get report statistics
     */
    private function getReportStatistics($user, $year)
    {
        // DELEGATED: Moved to ReportCalculationService
        return $this->calculationService->getReportStatistics($user, $year);
    }

    /**
     * Generate report content
     */
    private function generateReportContent(
        Report $report,
        array $indicatorIds,
        array $options,
    ) {
        try {
            $user = Auth::user();
            $year = Carbon::parse($report->period)->year;

            // Get performance data
            $indicators = PerformanceIndicator::whereIn("id", $indicatorIds)
                ->where("instansi_id", $user->instansi_id)
                ->with([
                    "performanceData" => function ($q) use ($year) {
                        $q->whereYear("period", $year)->where(
                            "status",
                            "approved",
                        );
                    },
                    "targets" => function ($q) use ($year) {
                        $q->where("year", $year);
                    },
                ])
                ->get();

            // Get assessments if requested
            $assessments = null;
            if ($options["include_assessments"] ?? false) {
                $assessments = Assessment::whereHas(
                    "performanceData",
                    function ($q) use ($indicatorIds) {
                        $q->whereIn("performance_indicator_id", $indicatorIds);
                    },
                )
                    ->whereYear("created_at", $year)
                    ->where("status", "approved")
                    ->with(["criteriaScores.criterion"])
                    ->get();
            }

            // Generate content based on template or default format
            $content = $this->reportService->generateReportContent(
                $report,
                $indicators,
                $assessments,
                $options,
            );

            return $content;
        } catch (\Exception $e) {
            Log::error("Generate report content error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate report file
     */
    private function generateReportFile(Report $report, string $format)
    {
        try {
            // Get report data
            $reportData = $this->getReportData($report);

            // Generate file based on format
            switch ($format) {
                case "pdf":
                    return $this->reportService->generatePDFFile(
                        $report,
                        $reportData,
                    );
                case "excel":
                    return $this->reportService->generateExcelFile(
                        $report,
                        $reportData,
                    );
                case "word":
                    return $this->reportService->generateWordFile(
                        $report,
                        $reportData,
                    );
                default:
                    throw new \Exception("Format laporan tidak didukung.");
            }
        } catch (\Exception $e) {
            Log::error("Generate report file error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get report data
     */
    private function getReportData(Report $report)
    {
        // DELEGATED: Moved to ReportCalculationService
        return $this->calculationService->getReportData($report);
    }

    /**
     * Calculate report summary
     */
    private function calculateReportSummary(Report $report)
    {
        // DELEGATED: Moved to ReportCalculationService
        return $this->calculationService->calculateReportSummary($report);
    }

    /**
     * Get report trends
     */
    private function getReportTrends(Report $report)
    {
        // DELEGATED: Moved to ReportCalculationService
        return $this->calculationService->getReportTrends($report);
    }

    /**
     * Get report benchmarks
     */
    private function getReportBenchmarks(Report $report)
    {
        // DELEGATED: Moved to ReportCalculationService
        return $this->calculationService->getReportBenchmarks($report);
    }

    // All calculation methods have been moved to ReportCalculationService
    // These methods are now delegated to the service:
    // - calculateInstitutionPerformance()
    // - calculateRegionalPerformance()
    // - calculateNationalPerformance()
    // - getAvailableReportPeriods()
}
