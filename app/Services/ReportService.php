<?php

namespace App\Services;

use App\Models\Report;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\Assessment;
use App\Models\Instansi;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Carbon\Carbon;
use Exception;

class ReportService
{
    protected $cacheTimeout = 3600; // 1 hour
    protected $reportTemplates = [
        'executive_summary' => 'Executive Summary Report',
        'performance_analysis' => 'Performance Analysis Report',
        'assessment_results' => 'Assessment Results Report',
        'compliance_status' => 'Compliance Status Report',
        'trend_analysis' => 'Trend Analysis Report',
        'instansi_comparison' => 'Institution Comparison Report',
        'detailed_indicator' => 'Detailed Indicator Report',
        'audit_findings' => 'Audit Findings Report',
    ];

    /**
     * Generate report
     */
    public function generateReport(array $data): Report
    {
        return DB::transaction(function () use ($data) {
            // Validate data
            $validator = $this->validateReportData($data);
            if ($validator->fails()) {
                throw new Exception('Validation failed: ' . $validator->errors()->first());
            }

            // Check if report template exists
            if (!$this->reportTemplateExists($data['report_type'])) {
                throw new Exception('Report template not found');
            }

            // Generate report data
            $reportData = $this->generateReportData($data);

            // Create report record
            $report = Report::create([
                'instansi_id' => $data['instansi_id'] ?? null,
                'report_type' => $data['report_type'],
                'report_title' => $data['report_title'] ?? $this->generateReportTitle($data),
                'report_period' => $data['report_period'] ?? $this->getCurrentPeriod(),
                'report_year' => $data['report_year'] ?? date('Y'),
                'data_parameters' => json_encode($data['parameters'] ?? []),
                'generated_by' => auth()->id(),
                'generated_at' => now(),
                'file_path' => null,
                'file_size' => 0,
                'status' => 'generating',
            ]);

            // Generate report file
            $filePath = $this->generateReportFile($report, $reportData, $data['format'] ?? 'pdf');

            // Update report with file information
            $report->update([
                'file_path' => $filePath,
                'file_size' => Storage::size($filePath),
                'status' => 'completed',
            ]);

            // Log activity
            $this->logActivity('generate', $report, 'Report generated successfully');

            // Clear cache
            $this->clearReportCache($data['instansi_id'] ?? null);

            return $report->fresh(['generatedBy', 'instansi']);
        });
    }

    /**
     * Get report by ID
     */
    public function getReport($id): ?Report
    {
        return Cache::remember("report_{$id}", $this->cacheTimeout, function () use ($id) {
            return Report::with(['generatedBy', 'instansi'])->find($id);
        });
    }

    /**
     * Get reports with filters
     */
    public function getReports(array $filters = [], $perPage = 15)
    {
        $query = Report::with(['generatedBy', 'instansi']);

        // Apply filters
        if (isset($filters['instansi_id'])) {
            $query->where('instansi_id', $filters['instansi_id']);
        }

        if (isset($filters['report_type'])) {
            $query->where('report_type', $filters['report_type']);
        }

        if (isset($filters['report_period'])) {
            $query->where('report_period', $filters['report_period']);
        }

        if (isset($filters['report_year'])) {
            $query->where('report_year', $filters['report_year']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['generated_by'])) {
            $query->where('generated_by', $filters['generated_by']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('report_title', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('instansi', function ($q2) use ($filters) {
                        $q2->where('name', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }

        // Date range filters
        if (isset($filters['date_from'])) {
            $query->whereDate('generated_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('generated_at', '<=', $filters['date_to']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'generated_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Delete report
     */
    public function deleteReport(Report $report): bool
    {
        return DB::transaction(function () use ($report) {
            // Delete file if exists
            if ($report->file_path && Storage::exists($report->file_path)) {
                Storage::delete($report->file_path);
            }

            // Log activity
            $this->logActivity('delete', $report, 'Report deleted');

            // Delete report
            $result = $report->delete();

            // Clear cache
            $this->clearReportCache($report->instansi_id);

            return $result;
        });
    }

    /**
     * Download report file
     */
    public function downloadReport(Report $report): array
    {
        if (!$report->file_path || !Storage::exists($report->file_path)) {
            throw new Exception('Report file not found');
        }

        // Update download count
        $report->increment('download_count');

        // Log activity
        $this->logActivity('download', $report, 'Report downloaded');

        return [
            'file_path' => $report->file_path,
            'file_name' => $this->generateFileName($report),
            'mime_type' => Storage::mimeType($report->file_path),
            'file_size' => $report->file_size,
        ];
    }

    /**
     * Generate executive summary report data
     */
    protected function generateExecutiveSummaryData($instansiId = null, $year = null): array
    {
        $year = $year ?? date('Y');
        
        return [
            'summary' => $this->getExecutiveSummary($instansiId, $year),
            'instansi_performance' => $this->getInstansiPerformanceSummary($instansiId, $year),
            'top_performers' => $this->getTopPerformers($year),
            'critical_areas' => $this->getCriticalAreas($instansiId, $year),
            'trend_analysis' => $this->getTrendAnalysis($instansiId, $year),
            'recommendations' => $this->generateRecommendations($instansiId, $year),
        ];
    }

    /**
     * Generate performance analysis report data
     */
    protected function generatePerformanceAnalysisData($instansiId = null, $year = null): array
    {
        $year = $year ?? date('Y');
        
        return [
            'indicator_analysis' => $this->getIndicatorAnalysis($instansiId, $year),
            'achievement_distribution' => $this->getAchievementDistribution($instansiId, $year),
            'performance_trends' => $this->getPerformanceTrends($instansiId, $year),
            'category_breakdown' => $this->getCategoryBreakdown($instansiId, $year),
            'data_quality_analysis' => $this->getDataQualityAnalysis($instansiId, $year),
            'benchmark_comparison' => $this->getBenchmarkComparison($instansiId, $year),
        ];
    }

    /**
     * Generate assessment results report data
     */
    protected function generateAssessmentResultsData($instansiId = null, $year = null): array
    {
        $year = $year ?? date('Y');
        
        return [
            'assessment_summary' => $this->getAssessmentSummary($instansiId, $year),
            'rating_distribution' => $this->getRatingDistribution($instansiId, $year),
            'criteria_performance' => $this->getCriteriaPerformance($instansiId, $year),
            'assessment_trends' => $this->getAssessmentTrends($instansiId, $year),
            'recommendations' => $this->getAssessmentRecommendations($instansiId, $year),
        ];
    }

    /**
     * Generate report data based on type
     */
    protected function generateReportData(array $data): array
    {
        $instansiId = $data['instansi_id'] ?? null;
        $year = $data['report_year'] ?? date('Y');
        $reportType = $data['report_type'];

        switch ($reportType) {
            case 'executive_summary':
                return $this->generateExecutiveSummaryData($instansiId, $year);
            case 'performance_analysis':
                return $this->generatePerformanceAnalysisData($instansiId, $year);
            case 'assessment_results':
                return $this->generateAssessmentResultsData($instansiId, $year);
            case 'compliance_status':
                return $this->generateComplianceStatusData($instansiId, $year);
            case 'trend_analysis':
                return $this->generateTrendAnalysisData($instansiId, $year);
            case 'instansi_comparison':
                return $this->generateInstansiComparisonData($year);
            case 'detailed_indicator':
                return $this->generateDetailedIndicatorData($instansiId, $year);
            case 'audit_findings':
                return $this->generateAuditFindingsData($instansiId, $year);
            default:
                throw new Exception('Unknown report type');
        }
    }

    /**
     * Generate report file
     */
    protected function generateReportFile(Report $report, array $reportData, string $format): string
    {
        $fileName = $this->generateFileName($report);
        $directory = 'reports/' . date('Y/m');
        $filePath = $directory . '/' . $fileName;

        // Ensure directory exists
        Storage::makeDirectory($directory);

        switch ($format) {
            case 'pdf':
                return $this->generatePdfReport($filePath, $report, $reportData);
            case 'excel':
                return $this->generateExcelReport($filePath, $report, $reportData);
            case 'csv':
                return $this->generateCsvReport($filePath, $report, $reportData);
            default:
                throw new Exception('Unsupported report format');
        }
    }

    /**
     * Generate PDF report
     */
    protected function generatePdfReport(string $filePath, Report $report, array $reportData): string
    {
        $html = view('sakip.reports.templates.' . $report->report_type, [
            'report' => $report,
            'data' => $reportData,
            'generated_at' => now(),
            'generated_by' => auth()->user(),
        ])->render();

        $pdf = SnappyPdf::loadHTML($html)
            ->setPaper('a4')
            ->setOrientation('portrait')
            ->setOption('footer-right', 'Page [page] of [toPage]')
            ->setOption('footer-font-size', 8);

        Storage::put($filePath, $pdf->output());

        return $filePath;
    }

    /**
     * Generate Excel report
     */
    protected function generateExcelReport(string $filePath, Report $report, array $reportData): string
    {
        // This would typically use PhpSpreadsheet or similar library
        // For now, we'll create a simple CSV that Excel can open
        return $this->generateCsvReport($filePath, $report, $reportData);
    }

    /**
     * Generate CSV report
     */
    protected function generateCsvReport(string $filePath, Report $report, array $reportData): string
    {
        $csvContent = $this->convertDataToCsv($reportData);
        Storage::put($filePath, $csvContent);
        return $filePath;
    }

    /**
     * Convert data to CSV format
     */
    protected function convertDataToCsv(array $data): string
    {
        $output = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($output, ['Field', 'Value']);
        
        // Add data rows
        $this->flattenArrayToCsv($data, $output);
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }

    /**
     * Flatten array to CSV rows
     */
    protected function flattenArrayToCsv(array $data, $output, string $prefix = ''): void
    {
        foreach ($data as $key => $value) {
            $fieldName = $prefix ? $prefix . '.' . $key : $key;
            
            if (is_array($value)) {
                $this->flattenArrayToCsv($value, $output, $fieldName);
            } else {
                fputcsv($output, [$fieldName, $value]);
            }
        }
    }

    /**
     * Get executive summary
     */
    protected function getExecutiveSummary($instansiId = null, $year = null): array
    {
        // This would be implemented based on your specific requirements
        return [
            'total_instansi' => Instansi::count(),
            'total_indicators' => PerformanceIndicator::when($instansiId, fn($q) => $q->where('instansi_id', $instansiId))->count(),
            'average_achievement' => $this->calculateAverageAchievement($instansiId, $year),
        ];
    }

    /**
     * Calculate average achievement
     */
    protected function calculateAverageAchievement($instansiId = null, $year = null): float
    {
        $query = PerformanceData::where('validation_status', 'validated')
            ->when($year, fn($q) => $q->where('period_year', $year))
            ->when($instansiId, fn($q) => $q->whereHas('performanceIndicator', fn($q2) => $q2->where('instansi_id', $instansiId)));

        return round($query->avg('achievement_percentage') ?? 0, 2);
    }

    /**
     * Validate report data
     */
    protected function validateReportData(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'report_type' => 'required|string|in:' . implode(',', array_keys($this->reportTemplates)),
            'instansi_id' => 'nullable|exists:instansi,id',
            'report_period' => 'nullable|in:first_semester,second_semester,quarterly',
            'report_year' => 'required|integer|min:2020|max:2030',
            'report_title' => 'nullable|string|max:255',
            'parameters' => 'nullable|array',
            'format' => 'nullable|in:pdf,excel,csv',
        ]);
    }

    /**
     * Check if report template exists
     */
    protected function reportTemplateExists(string $template): bool
    {
        return isset($this->reportTemplates[$template]);
    }

    /**
     * Generate report title
     */
    protected function generateReportTitle(array $data): string
    {
        $templateName = $this->reportTemplates[$data['report_type']] ?? 'Report';
        $year = $data['report_year'];
        $period = $data['report_period'] ?? '';
        
        return sprintf('%s - %s %s', $templateName, $year, ucfirst($period));
    }

    /**
     * Generate file name
     */
    protected function generateFileName(Report $report): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $type = str_replace('_', '-', $report->report_type);
        $instansi = $report->instansi ? str_replace(' ', '-', $report->instansi->name) : 'all';
        
        return sprintf('sakip-%s-%s-%s.pdf', $type, $instansi, $timestamp);
    }

    /**
     * Get current period
     */
    protected function getCurrentPeriod(): string
    {
        $month = date('n');
        return $month <= 6 ? 'first_semester' : 'second_semester';
    }

    /**
     * Log activity
     */
    protected function logActivity(string $action, Report $report, string $description): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'instansi_id' => $report->instansi_id,
            'module' => 'sakip',
            'activity' => $action . '_report',
            'description' => $description,
            'old_values' => $action === 'update' ? $report->getOriginal() : null,
            'new_values' => $action !== 'delete' ? $report->toArray() : null,
        ]);
    }

    /**
     * Clear report cache
     */
    protected function clearReportCache($instansiId): void
    {
        Cache::forget("report_statistics_{$instansiId}_" . date('Y'));
        
        // Clear all report caches for this instansi
        $keys = Cache::getRedis()->keys("report_*");
        foreach ($keys as $key) {
            if (strpos($key, "_{$instansiId}_") !== false) {
                Cache::forget($key);
            }
        }
    }
}