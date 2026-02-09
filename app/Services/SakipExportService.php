<?php

namespace App\Services;

use App\Services\Export\CsvExportService;
use App\Services\Export\ExcelExportService;
use App\Services\Export\JsonExportService;
use App\Services\Export\PdfExportService;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\Assessment;
use App\Models\Target;
use App\Models\Report;
use App\Models\EvidenceDocument;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

/**
 * SAKIP Export Service
 *
 * Main orchestration service for exporting SAKIP data in various formats.
 * Delegates format-specific operations to dedicated export services.
 */
class SakipExportService
{
    protected int $cacheTimeout = 3600; // 1 hour
    protected string $exportPath = "exports/sakip/";
    protected string $tempPath = "temp/";

    protected array $exportFormats = [
        "excel" => "xlsx",
        "csv" => "csv",
        "pdf" => "pdf",
        "json" => "json",
    ];

    protected array $reportTypes = [
        "performance_summary" => "Performance Summary",
        "indicator_analysis" => "Indicator Analysis",
        "assessment_results" => "Assessment Results",
        "target_achievement" => "Target Achievement",
        "data_collection" => "Data Collection Report",
        "audit_trail" => "Audit Trail",
        "evidence_summary" => "Evidence Summary",
        "instansi_comparison" => "Instansi Comparison",
        "trend_analysis" => "Trend Analysis",
        "compliance_report" => "Compliance Report",
    ];

    protected ExcelExportService $excelExport;
    protected CsvExportService $csvExport;
    protected PdfExportService $pdfExport;
    protected JsonExportService $jsonExport;

    public function __construct(
        ExcelExportService $excelExport,
        CsvExportService $csvExport,
        PdfExportService $pdfExport,
        JsonExportService $jsonExport
    ) {
        $this->excelExport = $excelExport;
        $this->csvExport = $csvExport;
        $this->pdfExport = $pdfExport;
        $this->jsonExport = $jsonExport;

        // Synchronize export paths
        $this->syncExportPaths();
    }

    /**
     * Synchronize export paths across all services.
     */
    protected function syncExportPaths(): void
    {
        $this->excelExport->setExportPath($this->exportPath);
        $this->csvExport->setExportPath($this->exportPath);
        $this->pdfExport->setExportPath($this->exportPath);
        $this->jsonExport->setExportPath($this->exportPath);
    }

    /**
     * Export performance indicators.
     */
    public function exportPerformanceIndicators(
        array $filters = [],
        string $format = "excel",
        array $options = [],
    ): array {
        try {
            $data = $this->getPerformanceIndicatorsData($filters);
            $filename = $this->generateFilename("performance_indicators", $format);
            $filePath = $this->exportPath . $filename;

            $this->performExport($data, $filename, $format, $options);

            $this->logActivity(
                "export_performance_indicators",
                $filename,
                "Performance indicators exported"
            );

            return $this->buildExportResult($filename, $filePath, count($data));
        } catch (Exception $e) {
            $this->logError("Export performance indicators failed", $e, [
                "filters" => $filters,
                "format" => $format,
            ]);
            throw $e;
        }
    }

    /**
     * Export performance data.
     */
    public function exportPerformanceData(
        array $filters = [],
        string $format = "excel",
        array $options = [],
    ): array {
        try {
            $data = $this->getPerformanceData($filters);
            $filename = $this->generateFilename("performance_data", $format);
            $filePath = $this->exportPath . $filename;

            $this->performExport($data, $filename, $format, $options);

            $this->logActivity(
                "export_performance_data",
                $filename,
                "Performance data exported"
            );

            return $this->buildExportResult($filename, $filePath, count($data));
        } catch (Exception $e) {
            $this->logError("Export performance data failed", $e, [
                "filters" => $filters,
                "format" => $format,
            ]);
            throw $e;
        }
    }

    /**
     * Export assessments.
     */
    public function exportAssessments(
        array $filters = [],
        string $format = "excel",
        array $options = [],
    ): array {
        try {
            $data = $this->getAssessmentsData($filters);
            $filename = $this->generateFilename("assessments", $format);
            $filePath = $this->exportPath . $filename;

            $this->performExport($data, $filename, $format, $options);

            $this->logActivity("export_assessments", $filename, "Assessments exported");

            return $this->buildExportResult($filename, $filePath, count($data));
        } catch (Exception $e) {
            $this->logError("Export assessments failed", $e, [
                "filters" => $filters,
                "format" => $format,
            ]);
            throw $e;
        }
    }

    /**
     * Export targets.
     */
    public function exportTargets(
        array $filters = [],
        string $format = "excel",
        array $options = [],
    ): array {
        try {
            $data = $this->getTargetsData($filters);
            $filename = $this->generateFilename("targets", $format);
            $filePath = $this->exportPath . $filename;

            $this->performExport($data, $filename, $format, $options);

            $this->logActivity("export_targets", $filename, "Targets exported");

            return $this->buildExportResult($filename, $filePath, count($data));
        } catch (Exception $e) {
            $this->logError("Export targets failed", $e, [
                "filters" => $filters,
                "format" => $format,
            ]);
            throw $e;
        }
    }

    /**
     * Generate comprehensive report.
     */
    public function generateReport(
        string $reportType,
        array $filters = [],
        string $format = "pdf",
        array $options = [],
    ): array {
        try {
            $data = $this->getReportData($reportType, $filters);
            $filename = $this->generateFilename($reportType, $format);
            $filePath = $this->exportPath . $filename;

            $this->performReportExport($data, $reportType, $filename, $format, $options);

            $this->logActivity(
                "generate_report",
                $filename,
                "Report {$reportType} generated"
            );

            return [
                "success" => true,
                "filename" => $filename,
                "file_path" => $filePath,
                "file_size" => Storage::size($filePath),
                "download_url" => Storage::url($filePath),
                "generated_at" => now()->toDateTimeString(),
                "report_type" => $reportType,
                "data_summary" => $this->getReportSummary($data),
            ];
        } catch (Exception $e) {
            $this->logError("Generate report failed", $e, [
                "report_type" => $reportType,
                "filters" => $filters,
                "format" => $format,
            ]);
            throw $e;
        }
    }

    /**
     * Export audit trail.
     */
    public function exportAuditTrail(
        array $filters = [],
        string $format = "excel",
        array $options = [],
    ): array {
        try {
            $data = $this->getAuditTrailData($filters);
            $filename = $this->generateFilename("audit_trail", $format);
            $filePath = $this->exportPath . $filename;

            $this->performExport($data, $filename, $format, $options);

            $this->logActivity("export_audit_trail", $filename, "Audit trail exported");

            return $this->buildExportResult($filename, $filePath, count($data));
        } catch (Exception $e) {
            $this->logError("Export audit trail failed", $e, [
                "filters" => $filters,
                "format" => $format,
            ]);
            throw $e;
        }
    }

    /**
     * Export evidence documents.
     */
    public function exportEvidenceDocuments(
        array $filters = [],
        string $format = "excel",
        array $options = [],
    ): array {
        try {
            $data = $this->getEvidenceDocumentsData($filters);
            $filename = $this->generateFilename("evidence_documents", $format);
            $filePath = $this->exportPath . $filename;

            $this->performExport($data, $filename, $format, $options);

            $this->logActivity(
                "export_evidence_documents",
                $filename,
                "Evidence documents exported"
            );

            return $this->buildExportResult($filename, $filePath, count($data));
        } catch (Exception $e) {
            $this->logError("Export evidence documents failed", $e, [
                "filters" => $filters,
                "format" => $format,
            ]);
            throw $e;
        }
    }

    /**
     * Bulk export multiple items.
     */
    public function bulkExport(
        array $exportRequests,
        string $format = "zip",
    ): array {
        try {
            $exportResults = [];
            $tempFiles = [];

            foreach ($exportRequests as $request) {
                $result = $this->processExportRequest($request);
                if ($result["success"]) {
                    $exportResults[] = $result;
                    $tempFiles[] = $result["file_path"];
                }
            }

            if (empty($tempFiles)) {
                throw new Exception("No files to export");
            }

            if ($format === "zip") {
                $zipFilename = $this->generateFilename("bulk_export", "zip");
                $zipPath = $this->exportPath . $zipFilename;

                $this->createZipArchive($tempFiles, $zipPath);

                // Clean up temp files
                foreach ($tempFiles as $file) {
                    if (Storage::exists($file)) {
                        Storage::delete($file);
                    }
                }

                $this->logActivity("bulk_export", $zipFilename, "Bulk export completed");

                return [
                    "success" => true,
                    "filename" => $zipFilename,
                    "file_path" => $zipPath,
                    "file_size" => Storage::size($zipPath),
                    "download_url" => Storage::url($zipPath),
                    "exported_at" => now()->toDateTimeString(),
                    "export_count" => count($exportResults),
                ];
            }

            return $exportResults[0] ?? [
                "success" => false,
                "error" => "No exports processed",
            ];
        } catch (Exception $e) {
            $this->logError("Bulk export failed", $e, [
                "export_requests" => $exportRequests,
                "format" => $format,
            ]);
            throw $e;
        }
    }

    /**
     * Get export statistics.
     */
    public function getExportStatistics(): array
    {
        $cacheKey = "export_statistics";

        return Cache::remember($cacheKey, $this->cacheTimeout, function () {
            $exports = AuditLog::where("module", "exports")
                ->whereDate("created_at", ">=", Carbon::now()->subDays(30))
                ->get();

            return [
                "total_exports" => $exports->count(),
                "by_type" => $exports
                    ->groupBy("activity")
                    ->map->count()
                    ->toArray(),
                "by_format" => $exports
                    ->map(function ($export) {
                        return pathinfo(
                            $export->description,
                            PATHINFO_EXTENSION,
                        );
                    })
                    ->groupBy(fn($ext) => $ext)
                    ->map->count()
                    ->toArray(),
                "recent_exports" => $exports
                    ->take(10)
                    ->map(function ($export) {
                        return [
                            "filename" => $export->description,
                            "type" => $export->activity,
                            "exported_at" => $export->created_at->toDateTimeString(),
                        ];
                    })
                    ->toArray(),
            ];
        });
    }

    // =====================================================
    // DATA RETRIEVAL METHODS
    // =====================================================

    /**
     * Get performance indicators data.
     */
    protected function getPerformanceIndicatorsData(array $filters = []): array
    {
        $query = PerformanceIndicator::with(["instansi", "category", "unit"]);

        if (isset($filters["instansi_id"])) {
            $query->where("instansi_id", $filters["instansi_id"]);
        }

        if (isset($filters["category_id"])) {
            $query->where("category_id", $filters["category_id"]);
        }

        if (isset($filters["status"])) {
            $query->where("status", $filters["status"]);
        }

        if (isset($filters["year"])) {
            $query->whereYear("created_at", $filters["year"]);
        }

        $indicators = $query->get();

        return $indicators
            ->map(function ($indicator) {
                return [
                    "ID" => $indicator->id,
                    "Kode Indikator" => $indicator->indicator_code,
                    "Nama Indikator" => $indicator->name,
                    "Deskripsi" => $indicator->description,
                    "Kategori" => $indicator->category?->name ?? "-",
                    "Instansi" => $indicator->instansi?->name ?? "-",
                    "Satuan" => $indicator->unit?->name ?? "-",
                    "Frekuensi" => $indicator->collection_frequency,
                    "Metode Koleksi" => $indicator->collection_method,
                    "Status" => $indicator->status,
                    "Target" => $indicator->targets
                        ->where("year", date("Y"))
                        ->first()?->target_value ?? "-",
                    "Tanggal Dibuat" => $indicator->created_at->format("Y-m-d H:i:s"),
                    "Tanggal Diperbarui" => $indicator->updated_at->format("Y-m-d H:i:s"),
                ];
            })
            ->toArray();
    }

    /**
     * Get performance data.
     */
    protected function getPerformanceData(array $filters = []): array
    {
        $query = PerformanceData::with([
            "performanceIndicator",
            "instansi",
            "evidenceDocuments",
        ]);

        if (isset($filters["instansi_id"])) {
            $query->where("instansi_id", $filters["instansi_id"]);
        }

        if (isset($filters["performance_indicator_id"])) {
            $query->where("performance_indicator_id", $filters["performance_indicator_id"]);
        }

        if (isset($filters["period"])) {
            $query->where("period", "like", $filters["period"] . "%");
        }

        if (isset($filters["year"])) {
            $query->whereYear("period", $filters["year"]);
        }

        if (isset($filters["status"])) {
            $query->where("status", $filters["status"]);
        }

        $performanceData = $query->get();

        return $performanceData
            ->map(function ($data) {
                $achievement = $this->calculateAchievement($data);

                return [
                    "ID" => $data->id,
                    "Indikator" => $data->performanceIndicator?->name ?? "-",
                    "Instansi" => $data->instansi?->name ?? "-",
                    "Periode" => $data->period,
                    "Nilai Aktual" => $data->actual_value,
                    "Nilai Target" => $data->target_value,
                    "Capaian (%)" => $achievement,
                    "Status" => $data->status,
                    "Sumber Data" => $data->data_source ?? "-",
                    "Metode Koleksi" => $data->collection_method ?? "-",
                    "Jumlah Bukti" => $data->evidenceDocuments->count(),
                    "Tanggal Dibuat" => $data->created_at->format("Y-m-d H:i:s"),
                ];
            })
            ->toArray();
    }

    /**
     * Get assessments data.
     */
    protected function getAssessmentsData(array $filters = []): array
    {
        $query = Assessment::with(["performanceData.performanceIndicator", "assessor"]);

        if (isset($filters["instansi_id"])) {
            $query->whereHas("performanceData", function ($q) use ($filters) {
                $q->where("instansi_id", $filters["instansi_id"]);
            });
        }

        if (isset($filters["period"])) {
            $query->where("assessment_period", "like", $filters["period"] . "%");
        }

        if (isset($filters["status"])) {
            $query->where("status", $filters["status"]);
        }

        $assessments = $query->get();

        return $assessments
            ->map(function ($assessment) {
                return [
                    "ID" => $assessment->id,
                    "Indikator" => $assessment->performanceData
                        ?->performanceIndicator?->name ?? "-",
                    "Periode Penilaian" => $assessment->assessment_period,
                    "Skor" => $assessment->score,
                    "Grade" => $assessment->grade,
                    "Penilai" => $assessment->assessor?->name ?? "-",
                    "Catatan" => $assessment->notes ?? "-",
                    "Rekomendasi" => $assessment->recommendations ?? "-",
                    "Status" => $assessment->status,
                    "Tanggal Dibuat" => $assessment->created_at->format("Y-m-d H:i:s"),
                ];
            })
            ->toArray();
    }

    /**
     * Get targets data.
     */
    protected function getTargetsData(array $filters = []): array
    {
        $query = Target::with(["performanceIndicator.instansi"]);

        if (isset($filters["instansi_id"])) {
            $query->whereHas("performanceIndicator", function ($q) use ($filters) {
                $q->where("instansi_id", $filters["instansi_id"]);
            });
        }

        if (isset($filters["year"])) {
            $query->where("year", $filters["year"]);
        }

        if (isset($filters["period"])) {
            $query->where("period", $filters["period"]);
        }

        $targets = $query->get();

        return $targets
            ->map(function ($target) {
                return [
                    "ID" => $target->id,
                    "Indikator" => $target->performanceIndicator?->name ?? "-",
                    "Instansi" => $target->performanceIndicator?->instansi?->name ?? "-",
                    "Tahun" => $target->year,
                    "Periode" => $target->period,
                    "Nilai Target" => $target->target_value,
                    "Satuan" => $target->unit ?? "-",
                    "Deskripsi" => $target->description ?? "-",
                    "Tanggal Dibuat" => $target->created_at->format("Y-m-d H:i:s"),
                ];
            })
            ->toArray();
    }

    /**
     * Get audit trail data.
     */
    protected function getAuditTrailData(array $filters = []): array
    {
        $query = AuditLog::with(["user", "instansi"]);

        if (isset($filters["user_id"])) {
            $query->where("user_id", $filters["user_id"]);
        }

        if (isset($filters["instansi_id"])) {
            $query->where("instansi_id", $filters["instansi_id"]);
        }

        if (isset($filters["module"])) {
            $query->where("module", $filters["module"]);
        }

        if (isset($filters["action"])) {
            $query->where("action", "like", "%" . $filters["action"] . "%");
        }

        if (isset($filters["date_from"])) {
            $query->where("created_at", ">=", $filters["date_from"]);
        }

        if (isset($filters["date_to"])) {
            $query->where("created_at", "<=", $filters["date_to"]);
        }

        $logs = $query->orderBy("created_at", "desc")->get();

        return $logs
            ->map(function ($log) {
                return [
                    "ID" => $log->id,
                    "User" => $log->user?->name ?? "-",
                    "Module" => $log->module,
                    "Activity" => $log->activity,
                    "Description" => $log->description,
                    "IP Address" => $log->ip_address ?? "-",
                    "Tanggal" => $log->created_at->format("Y-m-d H:i:s"),
                ];
            })
            ->toArray();
    }

    /**
     * Get evidence documents data.
     */
    protected function getEvidenceDocumentsData(array $filters = []): array
    {
        $query = EvidenceDocument::with([
            "performanceData.performanceIndicator",
            "performanceData.instansi",
        ]);

        if (isset($filters["instansi_id"])) {
            $query->whereHas("performanceData", function ($q) use ($filters) {
                $q->where("instansi_id", $filters["instansi_id"]);
            });
        }

        if (isset($filters["document_type"])) {
            $query->where("document_type", $filters["document_type"]);
        }

        $documents = $query->get();

        return $documents
            ->map(function ($doc) {
                return [
                    "ID" => $doc->id,
                    "Indikator" => $doc->performanceData
                        ?->performanceIndicator?->name ?? "-",
                    "Instansi" => $doc->performanceData?->instansi?->name ?? "-",
                    "Nama Dokumen" => $doc->file_name,
                    "Tipe Dokumen" => $doc->document_type ?? "-",
                    "Ukuran File" => $this->formatFileSize($doc->file_size),
                    "Uploader" => $doc->uploader?->name ?? "-",
                    "Tanggal Upload" => $doc->created_at->format("Y-m-d H:i:s"),
                ];
            })
            ->toArray();
    }

    /**
     * Get report data.
     */
    protected function getReportData(string $reportType, array $filters = []): array
    {
        return match ($reportType) {
            "performance_summary" => $this->getPerformanceSummaryReport($filters),
            "indicator_analysis" => $this->getIndicatorAnalysisReport($filters),
            "assessment_results" => $this->getAssessmentResultsReport($filters),
            "target_achievement" => $this->getTargetAchievementReport($filters),
            default => ["data" => [], "summary" => []],
        };
    }

    // =====================================================
    // EXPORT ORCHESTRATION METHODS
    // =====================================================

    /**
     * Perform export based on format.
     */
    protected function performExport(
        array $data,
        string $filename,
        string $format,
        array $options = []
    ): void {
        match ($format) {
            "excel" => $this->excelExport->export($data, $filename, $options),
            "csv" => $this->csvExport->export($data, $filename, $options),
            "pdf" => $this->pdfExport->export($data, $filename, $options),
            "json" => $this->jsonExport->export($data, $filename, $options),
            default => throw new Exception("Unsupported format: {$format}"),
        };
    }

    /**
     * Perform report export based on format.
     */
    protected function performReportExport(
        array $data,
        string $reportType,
        string $filename,
        string $format,
        array $options = []
    ): void {
        match ($format) {
            "excel" => $this->excelExport->exportReport($data, $reportType, $filename, $options),
            "pdf" => $this->pdfExport->exportReport($data, $reportType, $filename, $options),
            "json" => $this->jsonExport->exportReport($data, $reportType, $filename, $options),
            default => throw new Exception("Unsupported format: {$format}"),
        };
    }

    // =====================================================
    // HELPER METHODS
    // =====================================================

    /**
     * Calculate achievement percentage.
     */
    protected function calculateAchievement($data): float|string
    {
        if (!$data->target_value || $data->target_value == 0) {
            return $data->actual_value ? round($data->actual_value, 2) : "-";
        }

        $percentage = ($data->actual_value / $data->target_value) * 100;
        return round(min(max($percentage, 0), 100), 2);
    }

    /**
     * Generate filename.
     */
    protected function generateFilename(string $type, string $format): string
    {
        $timestamp = now()->format("Y-m-d_H-i-s");
        $format = $this->exportFormats[$format] ?? $format;
        return "sakip_{$type}_{$timestamp}.{$format}";
    }

    /**
     * Build export result array.
     */
    protected function buildExportResult(string $filename, string $filePath, int $count): array
    {
        return [
            "success" => true,
            "filename" => $filename,
            "file_path" => $filePath,
            "file_size" => Storage::size($filePath),
            "download_url" => Storage::url($filePath),
            "exported_at" => now()->toDateTimeString(),
            "record_count" => $count,
        ];
    }

    /**
     * Get report summary.
     */
    protected function getReportSummary(array $data): array
    {
        $summary = [];

        if (isset($data["summary"])) {
            $summary = $data["summary"];
        }

        if (isset($data["record_count"])) {
            $summary["total_records"] = $data["record_count"];
        }

        return $summary;
    }

    /**
     * Process export request.
     */
    protected function processExportRequest(array $request): array
    {
        $type = $request["type"] ?? "performance_indicators";
        $filters = $request["filters"] ?? [];
        $format = $request["format"] ?? "excel";
        $options = $request["options"] ?? [];

        $method = "export" . ucfirst(str_replace(" ", "", $type));

        if (!method_exists($this, $method)) {
            return [
                "success" => false,
                "error" => "Export type '{$type}' not supported",
            ];
        }

        return $this->$method($filters, $format, $options);
    }

    /**
     * Create ZIP archive.
     */
    protected function createZipArchive(array $files, string $zipPath): void
    {
        $zip = new \ZipArchive();

        if ($zip->open(Storage::path($zipPath), \ZipArchive::CREATE) !== true) {
            throw new Exception("Cannot create ZIP archive");
        }

        foreach ($files as $file) {
            if (Storage::exists($file)) {
                $zip->addFile(Storage::path($file), basename($file));
            }
        }

        $zip->close();
    }

    /**
     * Format file size.
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ["B", "KB", "MB", "GB"];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . " " . $units[$i];
    }

    /**
     * Log activity.
     */
    protected function logActivity(
        string $action,
        string $filename,
        string $description,
    ): void {
        AuditLog::create([
            "user_id" => auth()->id(),
            "instansi_id" => null,
            "module" => "exports",
            "activity" => $action,
            "description" => $description,
            "old_values" => null,
            "new_values" => ["filename" => $filename],
        ]);
    }

    /**
     * Log error with context.
     */
    protected function logError(string $message, Exception $e, array $context = []): void
    {
        Log::error($message, array_merge([
            "error" => $e->getMessage(),
            "trace" => $e->getTraceAsString(),
            "user_id" => auth()->id(),
        ], $context));
    }

    // =====================================================
    // REPORT GENERATION METHODS
    // =====================================================

    /**
     * Get performance summary report data.
     */
    protected function getPerformanceSummaryReport(array $filters): array
    {
        $data = $this->getPerformanceData($filters);

        return [
            "summary" => [
                "total_indicators" => count($data),
                "average_achievement" => collect($data)
                    ->pluck("Capaian (%)")
                    ->filter(fn($v) => $v !== "-")
                    ->avg() ?: 0,
                "excellents_count" => collect($data)
                    ->where("Capaian (%)", ">=", 90)
                    ->count(),
                "good_count" => collect($data)
                    ->where("Capaian (%)", ">=", 70)
                    ->where("Capaian (%)", "<", 90)
                    ->count(),
            ],
            "data" => array_slice($data, 0, 100), // Limit to 100 records for report
        ];
    }

    /**
     * Get indicator analysis report data.
     */
    protected function getIndicatorAnalysisReport(array $filters): array
    {
        $data = $this->getPerformanceIndicatorsData($filters);

        return [
            "summary" => [
                "total_indicators" => count($data),
                "by_category" => collect($data)->countBy("Kategori")->toArray(),
                "by_status" => collect($data)->countBy("Status")->toArray(),
            ],
            "data" => $data,
        ];
    }

    /**
     * Get assessment results report data.
     */
    protected function getAssessmentResultsReport(array $filters): array
    {
        $data = $this->getAssessmentsData($filters);

        return [
            "summary" => [
                "total_assessments" => count($data),
                "average_score" => collect($data)->avg("Skor") ?: 0,
                "by_grade" => collect($data)->countBy("Grade")->toArray(),
            ],
            "data" => $data,
        ];
    }

    /**
     * Get target achievement report data.
     */
    protected function getTargetAchievementReport(array $filters): array
    {
        $data = $this->getTargetsData($filters);

        return [
            "summary" => [
                "total_targets" => count($data),
                "total_target_value" => collect($data)->sum("Nilai Target"),
            ],
            "data" => $data,
        ];
    }
}
