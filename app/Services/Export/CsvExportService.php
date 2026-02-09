<?php

namespace App\Services\Export;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

/**
 * CSV Export Service
 *
 * Handles CSV export functionality for SAKIP data using PhpSpreadsheet.
 * Extracted from SakipExportService for better separation of concerns.
 */
class CsvExportService
{
    protected string $exportPath = 'exports/sakip/';

    /**
     * CSV delimiter character.
     */
    protected string $delimiter = ',';

    /**
     * CSV enclosure character.
     */
    protected string $enclosure = '"';

    /**
     * CSV line ending character.
     */
    protected string $lineEnding = "\r\n";

    /**
     * Export data to CSV format.
     *
     * @param  array  $data  The data to export (array of associative arrays)
     * @param  string  $filename  The output filename
     * @param  array  $options  Additional options (delimiter, enclosure, etc.)
     */
    public function export(array $data, string $filename, array $options = []): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        if (! empty($data)) {
            $sheet->fromArray($data, null, 'A1');
        }

        $this->saveFile($spreadsheet, $filename, $options);
    }

    /**
     * Save the spreadsheet to a CSV file.
     *
     * @param  \PhpOffice\PhpSpreadsheet\Spreadsheet  $spreadsheet  The spreadsheet to save
     * @param  string  $filename  The output filename
     * @param  array  $options  Additional options for CSV formatting
     */
    protected function saveFile(Spreadsheet $spreadsheet, string $filename, array $options = []): void
    {
        $writer = new Csv($spreadsheet);
        $writer->setDelimiter($options['delimiter'] ?? $this->delimiter);
        $writer->setEnclosure($options['enclosure'] ?? $this->enclosure);
        $writer->setLineEnding($options['line_ending'] ?? $this->lineEnding);
        $writer->setSheetIndex(0);

        // Enable UTF-8 BOM for Excel compatibility with special characters
        if (isset($options['include_bom']) && $options['include_bom']) {
            $writer->setUseBOM(true);
        }

        $filePath = $this->getFilePath($filename);
        $this->ensureDirectoryExists($filePath);

        $writer->save($filePath);
    }

    /**
     * Export data to CSV format with custom BOM for Excel compatibility.
     *
     * @param  array  $data  The data to export
     * @param  string  $filename  The output filename
     */
    public function exportWithBom(array $data, string $filename): void
    {
        $this->export($data, $filename, ['include_bom' => true]);
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

    /**
     * Set the CSV delimiter.
     *
     * @param  string  $delimiter  The delimiter character
     */
    public function setDelimiter(string $delimiter): self
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    /**
     * Set the CSV enclosure.
     *
     * @param  string  $enclosure  The enclosure character
     */
    public function setEnclosure(string $enclosure): self
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * Set the CSV line ending.
     *
     * @param  string  $lineEnding  The line ending character
     */
    public function setLineEnding(string $lineEnding): self
    {
        $this->lineEnding = $lineEnding;

        return $this;
    }
}
