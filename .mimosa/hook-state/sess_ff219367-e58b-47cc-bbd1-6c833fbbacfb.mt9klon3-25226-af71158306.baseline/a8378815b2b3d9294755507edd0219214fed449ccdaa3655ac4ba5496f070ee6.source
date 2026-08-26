<?php

namespace App\Services\Import;

use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\Target;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * CSV Import Service
 *
 * Handles bulk import of performance data from CSV files with proper sanitization,
 * validation, and error handling. Extracted from DataCollectionController.
 */
class CsvImportService
{
    /**
     * Import performance data from CSV file
     *
     * @param \Illuminate\Http\UploadedFile $file The uploaded CSV file
     * @param int $year The target year for import
     * @param string $instansiId The institution ID
     * @param string $userId The user ID performing the import
     * @return array Import result with success status, counts, and errors
     */
    public function importPerformanceData($file, int $year, string $instansiId, string $userId): array
    {
        DB::beginTransaction();
        try {
            $importedCount = 0;
            $errors = [];

            // Process CSV file
            $fileContent = file_get_contents($file->getRealPath());
            $lines = explode("\n", $fileContent);

            foreach ($lines as $index => $line) {
                if ($index === 0) {
                    continue; // Skip header
                }

                $data = str_getcsv($line);
                if (count($data) < 4) {
                    continue; // Skip invalid rows
                }

                try {
                    // Sanitize all CSV input to prevent injection attacks
                    $indicatorCode = $this->sanitizeCsvCellValue($data[0]);
                    $period = $this->sanitizeCsvCellValue($data[1]);
                    $actualValue = $this->sanitizeCsvCellValue($data[2]);
                    $notes = $this->sanitizeCsvCellValue($data[3] ?? "");

                    // Find indicator by code
                    $indicator = PerformanceIndicator::where("instansi_id", $instansiId)
                        ->where("code", $indicatorCode)
                        ->first();

                    if (!$indicator) {
                        $errors[] = "Baris " . ($index + 1) . ": Indikator dengan kode {$indicatorCode} tidak ditemukan.";
                        continue;
                    }

                    // Parse period
                    $periodDate = Carbon::parse($period);
                    if (!$this->isValidPeriod($indicator, $periodDate)) {
                        $errors[] = "Baris " . ($index + 1) . ": Periode tidak valid untuk indikator ini.";
                        continue;
                    }

                    // Check for existing data
                    $existingData = PerformanceData::where("performance_indicator_id", $indicator->id)
                        ->where("period", $periodDate->format("Y-m"))
                        ->first();

                    if ($existingData) {
                        $errors[] = "Baris " . ($index + 1) . ": Data untuk periode ini sudah ada.";
                        continue;
                    }

                    // Get target for the period
                    $target = $this->getTargetForPeriod($indicator, $periodDate);
                    $targetValue = $target ? $target->target_value : null;

                    // Calculate performance
                    $performancePercentage = $this->calculatePerformancePercentage(
                        $actualValue,
                        $targetValue,
                        $indicator->calculation_formula
                    );

                    // Create performance data
                    PerformanceData::create([
                        "performance_indicator_id" => $indicator->id,
                        "instansi_id" => $instansiId,
                        "period" => $periodDate->format("Y-m"),
                        "actual_value" => $actualValue,
                        "target_value" => $targetValue,
                        "notes" => $notes,
                        "status" => "draft",
                        "created_by" => $userId,
                        "updated_by" => $userId,
                    ]);

                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Baris " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            // Log the activity
            AuditLog::create([
                "user_id" => $userId,
                "instansi_id" => $instansiId,
                "action" => "IMPORT",
                "module" => "SAKIP",
                "description" => "Mengimpor {$importedCount} data kinerja untuk tahun {$year}",
                "old_values" => null,
                "new_values" => [
                    "imported_count" => $importedCount,
                    "year" => $year,
                ],
            ]);

            DB::commit();

            return [
                'success' => true,
                'imported_count' => $importedCount,
                'errors' => $errors,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Sanitize CSV cell value to prevent injection attacks
     *
     * SECURITY: Prevents CSV injection attacks that can execute formulas in Excel
     *
     * CSV Injection vectors:
     * 1. Formula injection: =, +, -, @ at start of cells
     * 2. Embedded formulas with whitespace
     * 3. Array formulas: {=SUM(A1:A10)}
     *
     * @param string $value The cell value to sanitize
     * @return string The sanitized value
     */
    public function sanitizeCsvCellValue(string $value): string
    {
        // Remove leading/trailing whitespace
        $value = trim($value);

        // Check for dangerous formula characters at the start
        if (preg_match("/^[=+\-@]/", $value)) {
            $value = "'" . $value; // Prepend quote to force Excel to treat as text
        }

        // Check for embedded formulas with leading whitespace
        if (preg_match('/^[\s\t\n\r][=+\-@]/', $value)) {
            $value = "'" . ltrim($value);
        }

        // Check for array formulas {=...}
        if (preg_match("/^\{=/", $value)) {
            $value = "'" . $value;
        }

        // Remove any HTML/script tags (defense in depth)
        $value = strip_tags($value);

        // Remove null bytes and control characters
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', "", $value);

        // Limit length to prevent DoS
        if (strlen($value) > 32767) {
            $value = substr($value, 0, 32767);
        }

        return $value;
    }

    /**
     * Check if the period is valid for the given indicator
     *
     * @param PerformanceIndicator $indicator The performance indicator
     * @param Carbon $period The period to check
     * @return bool True if period is valid
     */
    public function isValidPeriod(PerformanceIndicator $indicator, Carbon $period): bool
    {
        $month = $period->month;
        $frequency = $indicator->frequency;

        return match ($frequency) {
            'monthly' => true, // Any month is valid
            'quarterly' => in_array($month, [1, 4, 7, 10], true), // Quarter start months
            'semester' => in_array($month, [1, 7], true), // Semester start months
            'annual' => $month === 1, // January only
            default => false,
        };
    }

    /**
     * Get target value for a specific period
     *
     * @param PerformanceIndicator $indicator The performance indicator
     * @param Carbon $period The period to get target for
     * @return Target|null The target model or null
     */
    public function getTargetForPeriod(PerformanceIndicator $indicator, Carbon $period): ?Target
    {
        return $indicator->targets()
            ->where("year", $period->year)
            ->where(function ($q) use ($period) {
                // Match period based on frequency
                $month = $period->month;
                $q->where("period", $month) // monthly by month number
                  ->orWhere("period", "all"); // or "all" periods
            })
            ->first();
    }

    /**
     * Calculate performance percentage based on actual vs target
     *
     * @param float $actual The actual value
     * @param float|null $target The target value
     * @param string|null $formula Optional calculation formula
     * @return float The calculated performance percentage
     */
    public function calculatePerformancePercentage(
        float $actual,
        ?float $target,
        ?string $formula = null
    ): float {
        // If no target or target is zero, return actual as percentage
        if (!$target || $target == 0) {
            return min($actual, 100);
        }

        // Standard percentage calculation
        $percentage = ($actual / $target) * 100;

        // Cap at 100% if actual exceeds target (good performance)
        return min(max($percentage, 0), 100);
    }

    /**
     * Parse CSV row into associative array
     *
     * @param array $headers CSV headers
     * @param array $row CSV row data
     * @return array Associative array of row data
     */
    public function parseCsvRow(array $headers, array $row): array
    {
        $data = [];
        foreach ($headers as $index => $header) {
            $data[$header] = $this->sanitizeCsvCellValue($row[$index] ?? '');
        }
        return $data;
    }

    /**
     * Validate CSV file structure
     *
     * @param string $filePath Path to the CSV file
     * @param array $requiredHeaders Expected headers
     * @return array Validation result with success and errors
     */
    public function validateCsvStructure(string $filePath, array $requiredHeaders): array
    {
        $file = fopen($filePath, 'r');
        if (!$file) {
            return [
                'valid' => false,
                'errors' => ['Cannot open file'],
            ];
        }

        $headers = fgetcsv($file);
        fclose($file);

        if (!$headers) {
            return [
                'valid' => false,
                'errors' => ['Empty file or invalid CSV format'],
            ];
        }

        $missingHeaders = array_diff($requiredHeaders, $headers);
        if (!empty($missingHeaders)) {
            return [
                'valid' => false,
                'errors' => ['Missing required columns: ' . implode(', ', $missingHeaders)],
            ];
        }

        return ['valid' => true];
    }
}
