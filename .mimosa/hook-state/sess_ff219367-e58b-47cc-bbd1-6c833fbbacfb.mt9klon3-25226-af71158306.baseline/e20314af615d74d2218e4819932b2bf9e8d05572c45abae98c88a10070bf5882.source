<?php

namespace App\Services\Export;

use Illuminate\Support\Facades\Storage;

/**
 * JSON Export Service
 *
 * Handles JSON export functionality for SAKIP data.
 * Extracted from SakipExportService for better separation of concerns.
 */
class JsonExportService
{
    protected string $exportPath = 'exports/sakip/';

    protected array $reportTypes = [
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
     * Export data to JSON format.
     *
     * @param  array  $data  The data to export
     * @param  string  $filename  The output filename
     * @param  array  $options  Additional options (pretty_print, etc.)
     */
    public function export(array $data, string $filename, array $options = []): void
    {
        $filePath = $this->getFilePath($filename);
        $this->ensureDirectoryExists($filePath);

        $jsonData = [
            'exported_at' => now()->toDateTimeString(),
            'record_count' => count($data),
            'data' => $data,
        ];

        $flags = JSON_UNESCAPED_UNICODE;
        if (! isset($options['pretty_print']) || $options['pretty_print']) {
            $flags |= JSON_PRETTY_PRINT;
        }

        file_put_contents($filePath, json_encode($jsonData, $flags));
    }

    /**
     * Export report data to JSON format with report-specific metadata.
     *
     * @param  array  $data  The report data to export
     * @param  string  $reportType  The type of report
     * @param  string  $filename  The output filename
     * @param  array  $options  Additional options
     */
    public function exportReport(
        array $data,
        string $reportType,
        string $filename,
        array $options = []
    ): void {
        $filePath = $this->getFilePath($filename);
        $this->ensureDirectoryExists($filePath);

        $jsonData = [
            'report_type' => $reportType,
            'report_name' => $this->reportTypes[$reportType] ?? $reportType,
            'generated_at' => now()->toDateTimeString(),
            'filters' => $options['filters'] ?? [],
            'data' => $data,
        ];

        $flags = JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;

        file_put_contents($filePath, json_encode($jsonData, $flags));
    }

    /**
     * Export data with custom metadata.
     *
     * @param  array  $data  The data to export
     * @param  string  $filename  The output filename
     * @param  array  $metadata  Additional metadata to include
     * @param  array  $options  Additional options
     */
    public function exportWithMetadata(
        array $data,
        string $filename,
        array $metadata = [],
        array $options = []
    ): void {
        $filePath = $this->getFilePath($filename);
        $this->ensureDirectoryExists($filePath);

        $jsonData = array_merge([
            'exported_at' => now()->toDateTimeString(),
            'record_count' => count($data),
            'data' => $data,
        ], $metadata);

        $flags = JSON_UNESCAPED_UNICODE;
        if (! isset($options['pretty_print']) || $options['pretty_print']) {
            $flags |= JSON_PRETTY_PRINT;
        }

        file_put_contents($filePath, json_encode($jsonData, $flags));
    }

    /**
     * Get the full file path for a given filename.
     *
     * @param  string  $filename  The filename
     * @return string The full file path
     */
    protected function getFilePath(string $filename): string
    {
        return Storage::path($this->exportPath.$filename);
    }

    /**
     * Ensure the directory for the file path exists.
     *
     * @param  string  $filePath  The file path
     */
    protected function ensureDirectoryExists(string $filePath): void
    {
        $directory = dirname($filePath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }

    /**
     * Set the export path.
     *
     * @param  string  $path  The export path
     */
    public function setExportPath(string $path): self
    {
        $this->exportPath = $path;

        return $this;
    }

    /**
     * Get the current export path.
     *
     * @return string The export path
     */
    public function getExportPath(): string
    {
        return $this->exportPath;
    }
}
