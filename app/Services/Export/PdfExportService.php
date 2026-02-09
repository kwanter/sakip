<?php

namespace App\Services\Export;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

/**
 * PDF Export Service
 *
 * Handles PDF export functionality for SAKIP data using Dompdf.
 * Extracted from SakipExportService for better separation of concerns.
 */
class PdfExportService
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
     * Export data to PDF format.
     *
     * @param  array  $data  The data to export
     * @param  string  $filename  The output filename
     * @param  array  $options  Additional options (title, subtitle, etc.)
     */
    public function export(array $data, string $filename, array $options = []): void
    {
        $dompdfOptions = new Options;
        $dompdfOptions->set('defaultFont', 'Arial');
        $dompdfOptions->set('isHtml5ParserEnabled', true);
        $dompdfOptions->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($dompdfOptions);

        $html = $this->generateHtml($data, $options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper($options['paper'] ?? 'A4', $options['orientation'] ?? 'landscape');
        $dompdf->render();

        $filePath = $this->getFilePath($filename);
        $this->ensureDirectoryExists($filePath);

        file_put_contents($filePath, $dompdf->output());
    }

    /**
     * Export report data to PDF format with report-specific formatting.
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
        $dompdfOptions = new Options;
        $dompdfOptions->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($dompdfOptions);

        $html = $this->generateReportHtml($data, $reportType, $options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filePath = $this->getFilePath($filename);
        $this->ensureDirectoryExists($filePath);

        file_put_contents($filePath, $dompdf->output());
    }

    /**
     * Generate HTML for PDF export.
     *
     * @param  array  $data  The data to convert to HTML
     * @param  array  $options  Additional options for styling
     * @return string The generated HTML
     */
    protected function generateHtml(array $data, array $options = []): string
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

        if (! empty($data)) {
            $html .= "<table>\n<thead>\n<tr>";
            $headers = array_keys(reset($data));
            foreach ($headers as $header) {
                $html .= "<th>{$header}</th>";
            }
            $html .= "</tr>\n</thead>\n<tbody>\n";

            foreach ($data as $row) {
                $html .= '<tr>';
                foreach ($row as $value) {
                    $html .= '<td>'.htmlspecialchars($value).'</td>';
                }
                $html .= "</tr>\n";
            }

            $html .= "</tbody>\n</table>";
        }

        $html .= '
                <div class="footer">
                    <p>Total Records: '.count($data).'</p>
                    <p>This document was generated automatically by the SAKIP System.</p>
                </div>
            </body>
            </html>
        ';

        return $html;
    }

    /**
     * Generate HTML for report PDF export.
     *
     * @param  array  $data  The report data to convert to HTML
     * @param  string  $reportType  The type of report
     * @param  array  $options  Additional options for styling
     * @return string The generated HTML
     */
    protected function generateReportHtml(
        array $data,
        string $reportType,
        array $options = []
    ): string {
        $title = $this->reportTypes[$reportType] ?? $reportType;
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
                    .date { font-size: 12px; color: #999; }
                    .section { margin: 20px 0; }
                    .section-title { font-size: 18px; font-weight: bold; margin-bottom: 10px; color: #4472C4; }
                    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #4472C4; color: white; font-weight: bold; }
                    .summary-row { display: flex; justify-content: space-between; margin: 5px 0; }
                    .summary-label { font-weight: bold; }
                    .footer { margin-top: 30px; font-size: 12px; color: #666; text-align: center; }
                </style>
            </head>
            <body>
                <div class=\"header\">
                    <div class=\"title\">{$title}</div>
                    <div class=\"date\">Generated on: {$date}</div>
                </div>
        ";

        // Add summary section if available
        if (isset($data['summary'])) {
            $html .= '<div class="section">
                <div class="section-title">Summary</div>';

            foreach ($data['summary'] as $key => $value) {
                $label = ucfirst(str_replace('_', ' ', $key));
                $html .= "<div class=\"summary-row\">
                    <span class=\"summary-label\">{$label}:</span>
                    <span>{$value}</span>
                </div>";
            }

            $html .= '</div>';
        }

        // Add data table if available
        if (isset($data['data']) && ! empty($data['data'])) {
            $html .= '<div class="section">
                <div class="section-title">Detailed Data</div>
                <table>
                    <thead>
                        <tr>';

            $headers = array_keys(reset($data['data']));
            foreach ($headers as $header) {
                $html .= "<th>{$header}</th>";
            }
            $html .= '</tr>
                    </thead>
                    <tbody>';

            foreach ($data['data'] as $row) {
                $html .= '<tr>';
                foreach ($row as $value) {
                    $html .= '<td>'.htmlspecialchars($value).'</td>';
                }
                $html .= '</tr>';
            }

            $html .= '</tbody>
                </table>
            </div>';
        }

        $html .= "
                <div class=\"footer\">
                    <p>This document was generated automatically by the SAKIP System.</p>
                    <p>Generated on: {$date}</p>
                </div>
            </body>
            </html>
        ";

        return $html;
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
