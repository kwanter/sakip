<?php

namespace App\Services\Export;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Excel Export Service
 *
 * Handles Excel (.xlsx) export functionality for SAKIP data using PhpSpreadsheet.
 * Extracted from SakipExportService for better separation of concerns.
 */
class ExcelExportService
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
     * Export data to Excel format.
     *
     * @param  array  $data  The data to export (array of associative arrays)
     * @param  string  $filename  The output filename
     * @param  array  $options  Additional options for styling
     */
    public function export(array $data, string $filename, array $options = []): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if (! empty($data)) {
            $headers = array_keys(reset($data));
            $sheet->fromArray($headers, null, 'A1');
            $sheet->fromArray($data, null, 'A2');

            $this->applyStyling($sheet, count($headers), count($data), $options);
        }

        $this->saveFile($spreadsheet, $filename);
    }

    /**
     * Export report data to Excel format with report-specific formatting.
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
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Report');

        // Add report header
        $sheet->setCellValue('A1', 'SAKIP Report');
        $sheet->setCellValue(
            'A2',
            $this->reportTypes[$reportType] ?? $reportType,
        );
        $sheet->setCellValue(
            'A3',
            'Generated: '.now()->format('Y-m-d H:i:s'),
        );

        // Add summary section if available
        if (isset($data['summary'])) {
            $row = 5;
            $sheet->setCellValue("A{$row}", 'Summary');
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $row++;

            foreach ($data['summary'] as $key => $value) {
                $sheet->setCellValue(
                    "A{$row}",
                    ucfirst(str_replace('_', ' ', $key)),
                );
                $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                $sheet->setCellValue("B{$row}", $value);
                $row++;
            }
        }

        // Add data table if available
        if (isset($data['data']) && ! empty($data['data'])) {
            $startRow = isset($data['summary']) ? 15 : 5;
            $tableData = $data['data'];

            if (! empty($tableData)) {
                $headers = array_keys(reset($tableData));
                $sheet->fromArray($headers, null, "A{$startRow}");
                $sheet->fromArray($tableData, null, 'A'.($startRow + 1));

                $this->applyStyling($sheet, count($headers), count($tableData), [], $startRow);
            }
        }

        // Auto-size all columns
        foreach ($sheet->getColumnIterator() as $column) {
            $column->setAutoSize(true);
        }

        $this->saveFile($spreadsheet, $filename);
    }

    /**
     * Apply styling to the Excel sheet.
     *
     * @param  \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet  $sheet  The worksheet
     * @param  int  $columnCount  Number of columns
     * @param  int  $rowCount  Number of data rows
     * @param  array  $options  Additional styling options
     * @param  int  $startRow  Starting row for data (default: 1)
     */
    protected function applyStyling(
        $sheet,
        int $columnCount,
        int $rowCount,
        array $options = [],
        int $startRow = 1
    ): void {
        $headerColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnCount);
        $headerRange = "A{$startRow}:{$headerColumn}{$startRow}";
        $dataRange = 'A'.($startRow + 1).":{$headerColumn}".($startRow + $rowCount);

        // Header styling
        $headerColor = $options['header_color'] ?? '4472C4';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => $headerColor],
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
    }

    /**
     * Save the spreadsheet to a file.
     *
     * @param  \PhpOffice\PhpSpreadsheet\Spreadsheet  $spreadsheet  The spreadsheet to save
     * @param  string  $filename  The output filename
     */
    protected function saveFile(Spreadsheet $spreadsheet, string $filename): void
    {
        $writer = new Xlsx($spreadsheet);
        $filePath = $this->getFilePath($filename);

        $this->ensureDirectoryExists($filePath);
        $writer->save($filePath);
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
