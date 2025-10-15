<?php

namespace App\Services;

use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\Assessment;
use App\Models\Target;
use App\Models\Report;
use App\Models\EvidenceDocument;
use App\Models\Instansi;
use App\Models\AuditLog;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class SakipExportService
{
    protected $cacheTimeout = 3600; // 1 hour
    protected $exportPath = 'exports/sakip/';
    protected $tempPath = 'temp/';
    
    protected $exportFormats = [
        'excel' => 'xlsx',
        'csv' => 'csv',
        'pdf' => 'pdf',
        'json' => 'json',
    ];

    protected $reportTypes = [
        'performance_summary' => 'Performance Summary',
        'indicator_analysis' => 'Indicator Analysis',
        'assessment_results' => 'Assessment Results',
        'target_achievement' => 'Target Achievement',
        'data_collection' => 'Data Collection Report',
        'audit_trail' => 'Audit Trail',
        'evidence_summary' => 'Evidence Summary',
        'instansi_comparison' => 'Instansi Comparison',
        'trend_analysis' => 'Trend Analysis',
        'compliance_report' => 'Compliance Report',
    ];

    /**
     * Export performance indicators
     */
    public function exportPerformanceIndicators(array $filters = [], string $format = 'excel', array $options = []): array
    {
        try {
            $data = $this->getPerformanceIndicatorsData($filters);
            $filename = $this->generateFilename('performance_indicators', $format);
            $filePath = $this->exportPath . $filename;

            $exportResult = match ($format) {
                'excel' => $this->exportToExcel($data, $filename, $options),
                'csv' => $this->exportToCsv($data, $filename, $options),
                'pdf' => $this->exportToPdf($data, $filename, $options),
                'json' => $this->exportToJson($data, $filename, $options),
                default => throw new Exception("Unsupported format: {$format}"),
            };

            $this->logActivity('export_performance_indicators', $filename, 'Performance indicators exported');

            return [
                'success' => true,
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => Storage::size($filePath),
                'download_url' => Storage::url($filePath),
                'exported_at' => now()->toDateTimeString(),
                'record_count' => count($data),
            ];

        } catch (Exception $e) {
            Log::error('Export performance indicators failed', [
                'error' => $e->getMessage(),
                'filters' => $filters,
                'format' => $format,
            ]);

            throw $e;
        }
    }

    /**
     * Export performance data
     */
    public function exportPerformanceData(array $filters = [], string $format = 'excel', array $options = []): array
    {
        try {
            $data = $this->getPerformanceData($filters);
            $filename = $this->generateFilename('performance_data', $format);
            $filePath = $this->exportPath . $filename;

            $exportResult = match ($format) {
                'excel' => $this->exportToExcel($data, $filename, $options),
                'csv' => $this->exportToCsv($data, $filename, $options),
                'pdf' => $this->exportToPdf($data, $filename, $options),
                'json' => $this->exportToJson($data, $filename, $options),
                default => throw new Exception("Unsupported format: {$format}"),
            };

            $this->logActivity('export_performance_data', $filename, 'Performance data exported');

            return [
                'success' => true,
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => Storage::size($filePath),
                'download_url' => Storage::url($filePath),
                'exported_at' => now()->toDateTimeString(),
                'record_count' => count($data),
            ];

        } catch (Exception $e) {
            Log::error('Export performance data failed', [
                'error' => $e->getMessage(),
                'filters' => $filters,
                'format' => $format,
            ]);

            throw $e;
        }
    }

    /**
     * Export assessments
     */
    public function exportAssessments(array $filters = [], string $format = 'excel', array $options = []): array
    {
        try {
            $data = $this->getAssessmentsData($filters);
            $filename = $this->generateFilename('assessments', $format);
            $filePath = $this->exportPath . $filename;

            $exportResult = match ($format) {
                'excel' => $this->exportToExcel($data, $filename, $options),
                'csv' => $this->exportToCsv($data, $filename, $options),
                'pdf' => $this->exportToPdf($data, $filename, $options),
                'json' => $this->exportToJson($data, $filename, $options),
                default => throw new Exception("Unsupported format: {$format}"),
            };

            $this->logActivity('export_assessments', $filename, 'Assessments exported');

            return [
                'success' => true,
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => Storage::size($filePath),
                'download_url' => Storage::url($filePath),
                'exported_at' => now()->toDateTimeString(),
                'record_count' => count($data),
            ];

        } catch (Exception $e) {
            Log::error('Export assessments failed', [
                'error' => $e->getMessage(),
                'filters' => $filters,
                'format' => $format,
            ]);

            throw $e;
        }
    }

    /**
     * Export targets
     */
    public function exportTargets(array $filters = [], string $format = 'excel', array $options = []): array
    {
        try {
            $data = $this->getTargetsData($filters);
            $filename = $this->generateFilename('targets', $format);
            $filePath = $this->exportPath . $filename;

            $exportResult = match ($format) {
                'excel' => $this->exportToExcel($data, $filename, $options),
                'csv' => $this->exportToCsv($data, $filename, $options),
                'pdf' => $this->exportToPdf($data, $filename, $options),
                'json' => $this->exportToJson($data, $filename, $options),
                default => throw new Exception("Unsupported format: {$format}"),
            };

            $this->logActivity('export_targets', $filename, 'Targets exported');

            return [
                'success' => true,
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => Storage::size($filePath),
                'download_url' => Storage::url($filePath),
                'exported_at' => now()->toDateTimeString(),
                'record_count' => count($data),
            ];

        } catch (Exception $e) {
            Log::error('Export targets failed', [
                'error' => $e->getMessage(),
                'filters' => $filters,
                'format' => $format,
            ]);

            throw $e;
        }
    }

    /**
     * Generate comprehensive report
     */
    public function generateReport(string $reportType, array $filters = [], string $format = 'pdf', array $options = []): array
    {
        try {
            $data = $this->getReportData($reportType, $filters);
            $filename = $this->generateFilename($reportType, $format);
            $filePath = $this->exportPath . $filename;

            $exportResult = match ($format) {
                'excel' => $this->exportReportToExcel($data, $reportType, $filename, $options),
                'pdf' => $this->exportReportToPdf($data, $reportType, $filename, $options),
                'json' => $this->exportReportToJson($data, $reportType, $filename, $options),
                default => throw new Exception("Unsupported format: {$format}"),
            };

            $this->logActivity('generate_report', $filename, "Report {$reportType} generated");

            return [
                'success' => true,
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => Storage::size($filePath),
                'download_url' => Storage::url($filePath),
                'generated_at' => now()->toDateTimeString(),
                'report_type' => $reportType,
                'data_summary' => $this->getReportSummary($data),
            ];

        } catch (Exception $e) {
            Log::error('Generate report failed', [
                'error' => $e->getMessage(),
                'report_type' => $reportType,
                'filters' => $filters,
                'format' => $format,
            ]);

            throw $e;
        }
    }

    /**
     * Export audit trail
     */
    public function exportAuditTrail(array $filters = [], string $format = 'excel', array $options = []): array
    {
        try {
            $data = $this->getAuditTrailData($filters);
            $filename = $this->generateFilename('audit_trail', $format);
            $filePath = $this->exportPath . $filename;

            $exportResult = match ($format) {
                'excel' => $this->exportToExcel($data, $filename, $options),
                'csv' => $this->exportToCsv($data, $filename, $options),
                'pdf' => $this->exportToPdf($data, $filename, $options),
                'json' => $this->exportToJson($data, $filename, $options),
                default => throw new Exception("Unsupported format: {$format}"),
            };

            $this->logActivity('export_audit_trail', $filename, 'Audit trail exported');

            return [
                'success' => true,
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => Storage::size($filePath),
                'download_url' => Storage::url($filePath),
                'exported_at' => now()->toDateTimeString(),
                'record_count' => count($data),
            ];

        } catch (Exception $e) {
            Log::error('Export audit trail failed', [
                'error' => $e->getMessage(),
                'filters' => $filters,
                'format' => $format,
            ]);

            throw $e;
        }
    }

    /**
     * Export evidence documents
     */
    public function exportEvidenceDocuments(array $filters = [], string $format = 'excel', array $options = []): array
    {
        try {
            $data = $this->getEvidenceDocumentsData($filters);
            $filename = $this->generateFilename('evidence_documents', $format);
            $filePath = $this->exportPath . $filename;

            $exportResult = match ($format) {
                'excel' => $this->exportToExcel($data, $filename, $options),
                'csv' => $this->exportToCsv($data, $filename, $options),
                'pdf' => $this->exportToPdf($data, $filename, $options),
                'json' => $this->exportToJson($data, $filename, $options),
                default => throw new Exception("Unsupported format: {$format}"),
            };

            $this->logActivity('export_evidence_documents', $filename, 'Evidence documents exported');

            return [
                'success' => true,
                'filename' => $filename,
                'file_path' => $filePath,
                'file_size' => Storage::size($filePath),
                'download_url' => Storage::url($filePath),
                'exported_at' => now()->toDateTimeString(),
                'record_count' => count($data),
            ];

        } catch (Exception $e) {
            Log::error('Export evidence documents failed', [
                'error' => $e->getMessage(),
                'filters' => $filters,
                'format' => $format,
            ]);

            throw $e;
        }
    }

    /**
     * Bulk export
     */
    public function bulkExport(array $exportRequests, string $format = 'zip'): array
    {
        try {
            $exportResults = [];
            $tempFiles = [];

            foreach ($exportRequests as $request) {
                $result = $this->processExportRequest($request);
                if ($result['success']) {
                    $exportResults[] = $result;
                    $tempFiles[] = $result['file_path'];
                }
            }

            if (empty($tempFiles)) {
                throw new Exception('No files to export');
            }

            if ($format === 'zip') {
                $zipFilename = $this->generateFilename('bulk_export', 'zip');
                $zipPath = $this->exportPath . $zipFilename;
                
                $this->createZipArchive($tempFiles, $zipPath);
                
                // Clean up temp files
                foreach ($tempFiles as $file) {
                    if (Storage::exists($file)) {
                        Storage::delete($file);
                    }
                }

                $this->logActivity('bulk_export', $zipFilename, 'Bulk export completed');

                return [
                    'success' => true,
                    'filename' => $zipFilename,
                    'file_path' => $zipPath,
                    'file_size' => Storage::size($zipPath),
                    'download_url' => Storage::url($zipPath),
                    'exported_at' => now()->toDateTimeString(),
                    'export_count' => count($exportResults),
                ];
            }

            return $exportResults[0] ?? ['success' => false, 'error' => 'No exports processed'];

        } catch (Exception $e) {
            Log::error('Bulk export failed', [
                'error' => $e->getMessage(),
                'export_requests' => $exportRequests,
                'format' => $format,
            ]);

            throw $e;
        }
    }

    /**
     * Get export statistics
     */
    public function getExportStatistics(): array
    {
        $cacheKey = 'export_statistics';
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () {
            $exports = AuditLog::where('module', 'exports')
                ->whereDate('created_at', '>=', Carbon::now()->subDays(30))
                ->get();

            return [
                'total_exports' => $exports->count(),
                'by_type' => $exports->groupBy('activity')
                    ->map->count()
                    ->toArray(),
                'by_format' => $exports->map(function ($export) {
                        return pathinfo($export->description, PATHINFO_EXTENSION);
                    })
                    ->groupBy(fn($ext) => $ext)
                    ->map->count()
                    ->toArray(),
                'recent_exports' => $exports->take(10)->map(function ($export) {
                    return [
                        'filename' => $export->description,
                        'type' => $export->activity,
                        'exported_at' => $export->created_at->toDateTimeString(),
                    ];
                })->toArray(),
            ];
        });
    }

    /**
     * Get performance indicators data
     */
    protected function getPerformanceIndicatorsData(array $filters = []): array
    {
        $query = PerformanceIndicator::with(['instansi', 'category', 'unit']);

        // Apply filters
        if (isset($filters['instansi_id'])) {
            $query->where('instansi_id', $filters['instansi_id']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['year'])) {
            $query->whereYear('created_at', $filters['year']);
        }

        $indicators = $query->get();

        return $indicators->map(function ($indicator) {
            return [
                'ID' => $indicator->id,
                'Kode Indikator' => $indicator->indicator_code,
                'Nama Indikator' => $indicator->name,
                'Deskripsi' => $indicator->description,
                'Kategori' => $indicator->category->name ?? '-',
                'Instansi' => $indicator->instansi->name ?? '-',
                'Satuan' => $indicator->unit->name ?? '-',
                'Frekuensi' => $indicator->collection_frequency,
                'Metode Koleksi' => $indicator->collection_method,
                'Status' => $indicator->status,
                'Target' => $indicator->targets->where('target_year', date('Y'))->first()->target_value ?? '-',
                'Tanggal Dibuat' => $indicator->created_at->format('Y-m-d H:i:s'),
                'Tanggal Diperbarui' => $indicator->updated_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Get performance data
     */
    protected function getPerformanceData(array $filters = []): array
    {
        $query = PerformanceData::with(['performanceIndicator', 'instansi', 'evidenceDocuments']);

        // Apply filters
        if (isset($filters['instansi_id'])) {
            $query->where('instansi_id', $filters['instansi_id']);
        }

        if (isset($filters['performance_indicator_id'])) {
            $query->where('performance_indicator_id', $filters['performance_indicator_id']);
        }

        if (isset($filters['period_year'])) {
            $query->where('period_year', $filters['period_year']);
        }

        if (isset($filters['validation_status'])) {
            $query->where('validation_status', $filters['validation_status']);
        }

        $performanceData = $query->get();

        return $performanceData->map(function ($data) {
            return [
                'ID' => $data->id,
                'Indikator' => $data->performanceIndicator->name ?? '-',
                'Instansi' => $data->instansi->name ?? '-',
                'Periode' => "{$data->period_year}-{$data->period_month}",
                'Nilai Aktual' => $data->actual_value,
                'Nilai Target' => $data->target_value,
                'Pencapaian (%)' => $data->achievement_percentage,
                'Status Validasi' => $data->validation_status,
                'Dokumen Bukti' => $data->evidenceDocuments->count(),
                'Tanggal Pengumpulan' => $data->submission_date->format('Y-m-d H:i:s'),
                'Tanggal Validasi' => $data->validation_date?->format('Y-m-d H:i:s') ?? '-',
            ];
        })->toArray();
    }

    /**
     * Get assessments data
     */
    protected function getAssessmentsData(array $filters = []): array
    {
        $query = Assessment::with(['performanceIndicator', 'instansi', 'assessor']);

        // Apply filters
        if (isset($filters['instansi_id'])) {
            $query->where('instansi_id', $filters['instansi_id']);
        }

        if (isset($filters['performance_indicator_id'])) {
            $query->where('performance_indicator_id', $filters['performance_indicator_id']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['assessor_id'])) {
            $query->where('assessor_id', $filters['assessor_id']);
        }

        $assessments = $query->get();

        return $assessments->map(function ($assessment) {
            return [
                'ID' => $assessment->id,
                'Indikator' => $assessment->performanceIndicator->name ?? '-',
                'Instansi' => $assessment->instansi->name ?? '-',
                'Penilai' => $assessment->assessor->name ?? '-',
                'Skor' => $assessment->score,
                'Kategori' => $assessment->rating_category,
                'Status' => $assessment->status,
                'Tanggal Penilaian' => $assessment->assessment_date->format('Y-m-d H:i:s'),
                'Tanggal Selesai' => $assessment->completion_date?->format('Y-m-d H:i:s') ?? '-',
                'Komentar' => $assessment->comments ?? '-',
            ];
        })->toArray();
    }

    /**
     * Get targets data
     */
    protected function getTargetsData(array $filters = []): array
    {
        $query = Target::with(['performanceIndicator', 'instansi', 'approvedBy']);

        // Apply filters
        if (isset($filters['instansi_id'])) {
            $query->where('instansi_id', $filters['instansi_id']);
        }

        if (isset($filters['performance_indicator_id'])) {
            $query->where('performance_indicator_id', $filters['performance_indicator_id']);
        }

        if (isset($filters['target_year'])) {
            $query->where('target_year', $filters['target_year']);
        }

        if (isset($filters['approval_status'])) {
            $query->where('approval_status', $filters['approval_status']);
        }

        $targets = $query->get();

        return $targets->map(function ($target) {
            return [
                'ID' => $target->id,
                'Indikator' => $target->performanceIndicator->name ?? '-',
                'Instansi' => $target->instansi->name ?? '-',
                'Tahun Target' => $target->target_year,
                'Nilai Target' => $target->target_value,
                'Status Persetujuan' => $target->approval_status,
                'Disetujui Oleh' => $target->approvedBy->name ?? '-',
                'Tanggal Pengajuan' => $target->submission_date->format('Y-m-d H:i:s'),
                'Tanggal Persetujuan' => $target->approval_date?->format('Y-m-d H:i:s') ?? '-',
                'Catatan' => $target->approval_notes ?? '-',
            ];
        })->toArray();
    }

    /**
     * Get audit trail data
     */
    protected function getAuditTrailData(array $filters = []): array
    {
        $query = AuditLog::with(['user', 'instansi']);

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['instansi_id'])) {
            $query->where('instansi_id', $filters['instansi_id']);
        }

        if (isset($filters['module'])) {
            $query->where('module', $filters['module']);
        }

        if (isset($filters['activity'])) {
            $query->where('activity', $filters['activity']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $auditLogs = $query->get();

        return $auditLogs->map(function ($log) {
            return [
                'ID' => $log->id,
                'Pengguna' => $log->user->name ?? '-',
                'Instansi' => $log->instansi->name ?? '-',
                'Modul' => $log->module,
                'Aktivitas' => $log->activity,
                'Deskripsi' => $log->description,
                'Nilai Lama' => $log->old_values ? json_encode($log->old_values) : '-',
                'Nilai Baru' => $log->new_values ? json_encode($log->new_values) : '-',
                'Alamat IP' => $log->ip_address ?? '-',
                'Agen Pengguna' => $log->user_agent ?? '-',
                'Tanggal' => $log->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();
    }

    /**
     * Get evidence documents data
     */
    protected function getEvidenceDocumentsData(array $filters = []): array
    {
        $query = EvidenceDocument::with(['performanceData', 'instansi', 'uploadedBy']);

        // Apply filters
        if (isset($filters['instansi_id'])) {
            $query->where('instansi_id', $filters['instansi_id']);
        }

        if (isset($filters['performance_data_id'])) {
            $query->where('performance_data_id', $filters['performance_data_id']);
        }

        if (isset($filters['validation_status'])) {
            $query->where('validation_status', $filters['validation_status']);
        }

        if (isset($filters['file_type'])) {
            $query->where('file_type', $filters['file_type']);
        }

        $documents = $query->get();

        return $documents->map(function ($document) {
            return [
                'ID' => $document->id,
                'Nama File' => $document->file_name,
                'Tipe File' => $document->file_type,
                'Ukuran' => $this->formatFileSize($document->file_size),
                'Indikator' => $document->performanceData->performanceIndicator->name ?? '-',
                'Instansi' => $document->instansi->name ?? '-',
                'Diunggah Oleh' => $document->uploadedBy->name ?? '-',
                'Status Validasi' => $document->validation_status,
                'Tanggal Unggah' => $document->upload_date->format('Y-m-d H:i:s'),
                'Tanggal Validasi' => $document->validation_date?->format('Y-m-d H:i:s') ?? '-',
                'Komentar' => $document->validation_comments ?? '-',
            ];
        })->toArray();
    }

    /**
     * Get report data
     */
    protected function getReportData(string $reportType, array $filters = []): array
    {
        $method = 'get' . ucfirst(camel_case($reportType)) . 'ReportData';
        
        if (!method_exists($this, $method)) {
            throw new Exception("Report type '{$reportType}' not supported");
        }

        return $this->$method($filters);
    }

    /**
     * Get performance summary report data
     */
    protected function getPerformanceSummaryReportData(array $filters = []): array
    {
        $instansiId = $filters['instansi_id'] ?? null;
        $year = $filters['year'] ?? date('Y');

        $indicators = PerformanceIndicator::when($instansiId, fn($q) => $q->where('instansi_id', $instansiId))
            ->with(['targets', 'performanceData'])
            ->get();

        $summary = [
            'total_indicators' => $indicators->count(),
            'active_indicators' => $indicators->where('status', 'active')->count(),
            'indicators_with_targets' => $indicators->filter(fn($i) => $i->targets->where('target_year', $year)->isNotEmpty())->count(),
            'indicators_with_data' => $indicators->filter(fn($i) => $i->performanceData->where('period_year', $year)->isNotEmpty())->count(),
            'average_achievement' => $indicators->avg('performanceData.achievement_percentage') ?? 0,
        ];

        $byCategory = $indicators->groupBy('category.name')->map(function ($group) use ($year) {
            return [
                'total' => $group->count(),
                'with_targets' => $group->filter(fn($i) => $i->targets->where('target_year', $year)->isNotEmpty())->count(),
                'with_data' => $group->filter(fn($i) => $i->performanceData->where('period_year', $year)->isNotEmpty())->count(),
                'average_achievement' => $group->avg('performanceData.achievement_percentage') ?? 0,
            ];
        });

        return [
            'summary' => $summary,
            'by_category' => $byCategory,
            'indicators' => $indicators->map(function ($indicator) use ($year) {
                return [
                    'name' => $indicator->name,
                    'category' => $indicator->category->name ?? '-',
                    'target' => $indicator->targets->where('target_year', $year)->first()->target_value ?? '-',
                    'actual' => $indicator->performanceData->where('period_year', $year)->first()->actual_value ?? '-',
                    'achievement' => $indicator->performanceData->where('period_year', $year)->first()->achievement_percentage ?? '-',
                ];
            }),
        ];
    }

    /**
     * Export to Excel
     */
    protected function exportToExcel(array $data, string $filename, array $options = []): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        if (!empty($data)) {
            $headers = array_keys(reset($data));
            $sheet->fromArray($headers, null, 'A1');
            $sheet->fromArray($data, null, 'A2');

            // Apply styling
            $this->applyExcelStyling($sheet, count($headers), count($data));
        }

        // Save file
        $writer = new Xlsx($spreadsheet);
        $filePath = Storage::path($this->exportPath . $filename);
        
        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer->save($filePath);
    }

    /**
     * Export to CSV
     */
    protected function exportToCsv(array $data, string $filename, array $options = []): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        if (!empty($data)) {
            $sheet->fromArray($data, null, 'A1');
        }

        $writer = new Csv($spreadsheet);
        $writer->setDelimiter(',');
        $writer->setEnclosure('"');
        $writer->setLineEnding("\r\n");
        $writer->setSheetIndex(0);

        $filePath = Storage::path($this->exportPath . $filename);
        
        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer->save($filePath);
    }

    /**
     * Export to PDF
     */
    protected function exportToPdf(array $data, string $filename, array $options = []): void
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $html = $this->generatePdfHtml($data, $options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filePath = Storage::path($this->exportPath . $filename);
        
        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($filePath, $dompdf->output());
    }

    /**
     * Export to JSON
     */
    protected function exportToJson(array $data, string $filename, array $options = []): void
    {
        $filePath = Storage::path($this->exportPath . $filename);
        
        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $jsonData = [
            'exported_at' => now()->toDateTimeString(),
            'record_count' => count($data),
            'data' => $data,
        ];

        file_put_contents($filePath, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Generate filename
     */
    protected function generateFilename(string $type, string $format): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $format = $this->exportFormats[$format] ?? $format;
        return "sakip_{$type}_{$timestamp}.{$format}";
    }

    /**
     * Apply Excel styling
     */
    protected function applyExcelStyling($sheet, int $columnCount, int $rowCount): void
    {
        // Header styling
        $headerRange = 'A1:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount) . '1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Data styling
        $dataRange = 'A2:' . \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount) . ($rowCount + 1);
        $sheet->getStyle($dataRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_TOP,
            ],
        ]);

        // Auto-size columns
        for ($col = 1; $col <= $columnCount; $col++) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }
    }

    /**
     * Generate PDF HTML
     */
    protected function generatePdfHtml(array $data, array $options = []): string
    {
        $title = $options['title'] ?? 'SAKIP Export';
        $subtitle = $options['subtitle'] ?? 'Performance Data Export';
        $date = now()->format('d F Y H:i:s');

        $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset=\"UTF-8\">
                <title>{$title}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .title { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
                    .subtitle { font-size: 16px; color: #666; margin-bottom: 20px; }
                    .date { font-size: 12px; color: #999; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #4472C4; color: white; font-weight: bold; }
                    tr:nth-child(even) { background-color: #f9f9f9; }
                    .footer { margin-top: 30px; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class=\"header\">
                    <div class=\"title\">{$title}</div>
                    <div class=\"subtitle\">{$subtitle}</div>
                    <div class=\"date\">Generated on: {$date}</div>
                </div>
        ";

        if (!empty($data)) {
            $html .= "<table>\n<thead>\n<tr>";
            $headers = array_keys(reset($data));
            foreach ($headers as $header) {
                $html .= "<th>{$header}</th>";
            }
            $html .= "</tr>\n</thead>\n<tbody>\n";

            foreach ($data as $row) {
                $html .= "<tr>";
                foreach ($row as $value) {
                    $html .= "<td>" . htmlspecialchars($value) . "</td>";
                }
                $html .= "</tr>\n";
            }

            $html .= "</tbody>\n</table>";
        }

        $html .= "
                <div class=\"footer\">
                    <p>Total Records: " . count($data) . "</p>
                    <p>This document was generated automatically by the SAKIP System.</p>
                </div>
            </body>
            </html>
        ";

        return $html;
    }

    /**
     * Export report to Excel
     */
    protected function exportReportToExcel(array $data, string $reportType, string $filename, array $options = []): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Report');

        // Add report header
        $sheet->setCellValue('A1', 'SAKIP Report');
        $sheet->setCellValue('A2', $this->reportTypes[$reportType] ?? $reportType);
        $sheet->setCellValue('A3', 'Generated: ' . now()->format('Y-m-d H:i:s'));

        // Add data
        if (isset($data['summary'])) {
            $row = 5;
            $sheet->setCellValue("A{$row}", 'Summary');
            $row++;
            foreach ($data['summary'] as $key => $value) {
                $sheet->setCellValue("A{$row}", ucfirst(str_replace('_', ' ', $key)));
                $sheet->setCellValue("B{$row}", $value);
                $row++;
            }
        }

        // Save file
        $writer = new Xlsx($spreadsheet);
        $filePath = Storage::path($this->exportPath . $filename);
        
        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $writer->save($filePath);
    }

    /**
     * Export report to PDF
     */
    protected function exportReportToPdf(array $data, string $reportType, string $filename, array $options = []): void
    {
        $options = new Options();
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        $html = $this->generateReportHtml($data, $reportType, $options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filePath = Storage::path($this->exportPath . $filename);
        
        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($filePath, $dompdf->output());
    }

    /**
     * Export report to JSON
     */
    protected function exportReportToJson(array $data, string $reportType, string $filename, array $options = []): void
    {
        $filePath = Storage::path($this->exportPath . $filename);
        
        // Ensure directory exists
        $directory = dirname($filePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $jsonData = [
            'report_type' => $reportType,
            'report_name' => $this->reportTypes[$reportType] ?? $reportType,
            'generated_at' => now()->toDateTimeString(),
            'filters' => $options['filters'] ?? [],
            'data' => $data,
        ];

        file_put_contents($filePath, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Generate report HTML
     */
    protected function generateReportHtml(array $data, string $reportType, array $options = []): string
    {
        $title = $this->reportTypes[$reportType] ?? $reportType;
        $date = now()->format('d F Y H:i:s');

        $html = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset=\"UTF-8\">
                <title>{$title}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 30px; }
                    .header { text-align: center; margin-bottom: 40px; border-bottom: 2px solid #4472C4; padding-bottom: 20px; }
                    .title { font-size: 28px; font-weight: bold; color: #4472C4; margin-bottom: 10px; }
                    .subtitle { font-size: 18px; color: #666; margin-bottom: 20px; }
                    .date { font-size: 14px; color: #999; }
                    .summary { margin: 30px 0; padding: 20px; background-color: #f5f5f5; border-radius: 5px; }
                    .summary h3 { color: #4472C4; margin-top: 0; }
                    .summary-item { margin: 10px 0; }
                    .summary-label { font-weight: bold; display: inline-block; width: 200px; }
                    .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
                </style>
            </head>
            <body>
                <div class=\"header\">
                    <div class=\"title\">SAKIP System</div>
                    <div class=\"subtitle\">{$title}</div>
                    <div class=\"date\">Generated on: {$date}</div>
                </div>
        ";

        // Add summary section
        if (isset($data['summary'])) {
            $html .= "<div class=\"summary\">\n<h3>Executive Summary</h3>\n";
            foreach ($data['summary'] as $key => $value) {
                $label = ucfirst(str_replace('_', ' ', $key));
                $html .= "<div class=\"summary-item\"><span class=\"summary-label\">{$label}:</span> {$value}</div>\n";
            }
            $html .= "</div>\n";
        }

        $html .= "
                <div class=\"footer\">
                    <p>This report was generated automatically by the SAKIP System.</p>
                    <p>For inquiries, please contact the system administrator.</p>
                </div>
            </body>
            </html>
        ";

        return $html;
    }

    /**
     * Get report summary
     */
    protected function getReportSummary(array $data): array
    {
        $summary = [];
        
        if (isset($data['summary'])) {
            $summary = $data['summary'];
        }

        if (isset($data['record_count'])) {
            $summary['total_records'] = $data['record_count'];
        }

        return $summary;
    }

    /**
     * Process export request
     */
    protected function processExportRequest(array $request): array
    {
        $type = $request['type'] ?? 'performance_indicators';
        $filters = $request['filters'] ?? [];
        $format = $request['format'] ?? 'excel';
        $options = $request['options'] ?? [];

        $method = 'export' . ucfirst(camel_case($type));
        
        if (!method_exists($this, $method)) {
            return ['success' => false, 'error' => "Export type '{$type}' not supported"];
        }

        return $this->$method($filters, $format, $options);
    }

    /**
     * Create ZIP archive
     */
    protected function createZipArchive(array $files, string $zipPath): void
    {
        $zip = new \ZipArchive();
        
        if ($zip->open(Storage::path($zipPath), \ZipArchive::CREATE) !== true) {
            throw new Exception('Cannot create ZIP archive');
        }

        foreach ($files as $file) {
            if (Storage::exists($file)) {
                $zip->addFile(Storage::path($file), basename($file));
            }
        }

        $zip->close();
    }

    /**
     * Format file size
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Log activity
     */
    protected function logActivity(string $action, string $filename, string $description): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'instansi_id' => null,
            'module' => 'exports',
            'activity' => $action,
            'description' => $description,
            'old_values' => null,
            'new_values' => ['filename' => $filename],
        ]);
    }
}