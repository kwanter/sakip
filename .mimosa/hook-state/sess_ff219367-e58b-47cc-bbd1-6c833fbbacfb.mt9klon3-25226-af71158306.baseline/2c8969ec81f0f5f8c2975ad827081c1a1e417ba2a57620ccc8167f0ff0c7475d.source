<?php

namespace App\Services\Validation;

use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Data Quality Validation Service
 *
 * Comprehensive validation service for performance data quality.
 * Validates data completeness, accuracy, consistency, and reliability.
 *
 * Performs checks for:
 * - Missing required fields
 * - Out-of-range values
 * - Inconsistent data
 * - Logical validation
 * - Temporal consistency
 * - Evidence document completeness
 */
class DataQualityValidationService
{
    /**
     * Validation severity levels
     */
    public const SEVERITY_ERROR = 'error';

    public const SEVERITY_WARNING = 'warning';

    public const SEVERITY_INFO = 'info';

    /**
     * Validation error categories
     */
    public const CATEGORY_COMPLETENESS = 'completeness';

    public const CATEGORY_ACCURACY = 'accuracy';

    public const CATEGORY_CONSISTENCY = 'consistency';

    public const CATEGORY_LOGICAL = 'logical';

    public const CATEGORY_TEMPORAL = 'temporal';

    public const CATEGORY_EVIDENCE = 'evidence';

    /**
     * Validate performance data comprehensively
     *
     * @param  PerformanceData  $performanceData  The data to validate
     * @param  array  $options  Validation options (strict mode, etc.)
     * @return array Validation result with errors, warnings, and status
     */
    public function validatePerformanceData(
        PerformanceData $performanceData,
        array $options = []
    ): array {
        $errors = [];
        $warnings = [];
        $info = [];

        try {
            // Get the indicator for context
            $indicator = $performanceData->indicator;

            // 1. Completeness Validation
            $completenessResult = $this->validateCompleteness($performanceData, $indicator);
            $errors = array_merge($errors, $completenessResult['errors']);
            $warnings = array_merge($warnings, $completenessResult['warnings']);

            // 2. Accuracy Validation
            $accuracyResult = $this->validateAccuracy($performanceData, $indicator);
            $errors = array_merge($errors, $accuracyResult['errors']);
            $warnings = array_merge($warnings, $accuracyResult['warnings']);

            // 3. Consistency Validation
            $consistencyResult = $this->validateConsistency($performanceData, $indicator);
            $errors = array_merge($errors, $consistencyResult['errors']);
            $warnings = array_merge($warnings, $consistencyResult['warnings']);

            // 4. Logical Validation
            $logicalResult = $this->validateLogical($performanceData, $indicator);
            $errors = array_merge($errors, $logicalResult['errors']);
            $warnings = array_merge($warnings, $logicalResult['warnings']);

            // 5. Temporal Validation
            $temporalResult = $this->validateTemporal($performanceData, $indicator);
            $errors = array_merge($errors, $temporalResult['errors']);
            $warnings = array_merge($warnings, $temporalResult['warnings']);

            // 6. Evidence Validation
            $evidenceResult = $this->validateEvidence($performanceData, $indicator);
            $errors = array_merge($errors, $evidenceResult['errors']);
            $warnings = array_merge($warnings, $evidenceResult['warnings']);

            // Determine overall validation status
            $hasErrors = count($errors) > 0;
            $hasWarnings = count($warnings) > 0;

            $status = $hasErrors ? 'invalid' : ($hasWarnings ? 'warning' : 'valid');

            return [
                'has_errors' => $hasErrors,
                'has_warnings' => $hasWarnings,
                'status' => $status,
                'errors' => $errors,
                'warnings' => $warnings,
                'info' => $info,
                'validated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];
        } catch (\Exception $e) {
            Log::error("Validation error for performance data {$performanceData->id}: ".$e->getMessage());

            return [
                'has_errors' => true,
                'has_warnings' => false,
                'status' => 'error',
                'errors' => [
                    [
                        'category' => 'system',
                        'severity' => self::SEVERITY_ERROR,
                        'message' => 'Validation system error: '.$e->getMessage(),
                    ],
                ],
                'warnings' => [],
                'info' => [],
                'validated_at' => Carbon::now()->format('Y-m-d H:i:s'),
            ];
        }
    }

    /**
     * Validate data completeness
     *
     * Checks for missing required fields and values
     *
     * @return array Validation result
     */
    protected function validateCompleteness(
        PerformanceData $performanceData,
        PerformanceIndicator $indicator
    ): array {
        $errors = [];
        $warnings = [];

        // Check if actual value is present
        if (empty($performanceData->actual_value)) {
            $errors[] = [
                'category' => self::CATEGORY_COMPLETENESS,
                'severity' => self::SEVERITY_ERROR,
                'field' => 'actual_value',
                'message' => 'Nilai aktual wajib diisi.',
            ];
        }

        // Check if target value is present (if required)
        if ($indicator->requires_target && empty($performanceData->target_value)) {
            $errors[] = [
                'category' => self::CATEGORY_COMPLETENESS,
                'severity' => self::SEVERITY_ERROR,
                'field' => 'target_value',
                'message' => 'Nilai target wajib diisi untuk indikator ini.',
            ];
        }

        // Warning if notes are missing (recommended but not required)
        if (empty($performanceData->notes)) {
            $warnings[] = [
                'category' => self::CATEGORY_COMPLETENESS,
                'severity' => self::SEVERITY_WARNING,
                'field' => 'notes',
                'message' => 'Catatan kosong. Disarankan untuk mengisi penjelasan.',
            ];
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate data accuracy
     *
     * Checks for out-of-range values and invalid data types
     *
     * @return array Validation result
     */
    protected function validateAccuracy(
        PerformanceData $performanceData,
        PerformanceIndicator $indicator
    ): array {
        $errors = [];
        $warnings = [];

        // Check for negative values (unless indicator allows them)
        if ($performanceData->actual_value < 0 && ! $indicator->allows_negative) {
            $errors[] = [
                'category' => self::CATEGORY_ACCURACY,
                'severity' => self::SEVERITY_ERROR,
                'field' => 'actual_value',
                'message' => 'Nilai aktual tidak boleh negatif untuk indikator ini.',
            ];
        }

        // Check for unreasonably high values (potential data entry error)
        $maxThreshold = $indicator->max_threshold ?? config('sakip.validation.max_value', 1000000);
        if ($performanceData->actual_value > $maxThreshold) {
            $warnings[] = [
                'category' => self::CATEGORY_ACCURACY,
                'severity' => self::SEVERITY_WARNING,
                'field' => 'actual_value',
                'message' => "Nilai aktual melebihi batas wajar ({$maxThreshold}). Periksa kembali.",
            ];
        }

        // Validate performance percentage is within range
        $maxPercentage = config('sakip.performance.max_percentage', 200);
        if ($performanceData->performance_percentage < 0 || $performanceData->performance_percentage > $maxPercentage) {
            $errors[] = [
                'category' => self::CATEGORY_ACCURACY,
                'severity' => self::SEVERITY_ERROR,
                'field' => 'performance_percentage',
                'message' => "Persentase kinerja harus antara 0 dan {$maxPercentage}%.",
            ];
        }

        // Check if target value is reasonable
        if (! empty($performanceData->target_value) && $performanceData->target_value <= 0) {
            $warnings[] = [
                'category' => self::CATEGORY_ACCURACY,
                'severity' => self::SEVERITY_WARNING,
                'field' => 'target_value',
                'message' => 'Nilai target nol atau negatif. Pertimbangkan untuk memeriksa kembali.',
            ];
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate data consistency
     *
     * Checks for consistency across related data points
     *
     * @return array Validation result
     */
    protected function validateConsistency(
        PerformanceData $performanceData,
        PerformanceIndicator $indicator
    ): array {
        $errors = [];
        $warnings = [];

        // Check if performance percentage matches calculated value
        $expectedPercentage = $this->calculateExpectedPercentage($performanceData);
        $difference = abs($performanceData->performance_percentage - $expectedPercentage);

        if ($difference > 0.01) { // Allow small rounding differences
            $warnings[] = [
                'category' => self::CATEGORY_CONSISTENCY,
                'severity' => self::SEVERITY_WARNING,
                'field' => 'performance_percentage',
                'message' => "Persentase kinerja ({$performanceData->performance_percentage}%) tidak sesuai dengan perhitungan ({$expectedPercentage}%).",
            ];
        }

        // Check consistency with previous period (if exists)
        $previousData = PerformanceData::where('indicator_id', $indicator->id)
            ->where('period', '<', $performanceData->period)
            ->orderBy('period', 'desc')
            ->first();

        if ($previousData) {
            // Check for drastic changes (potential data error)
            $changeRate = abs(($performanceData->actual_value - $previousData->actual_value) / ($previousData->actual_value ?: 1)) * 100;

            if ($changeRate > 50) {
                $warnings[] = [
                    'category' => self::CATEGORY_CONSISTENCY,
                    'severity' => self::SEVERITY_WARNING,
                    'field' => 'actual_value',
                    'message' => "Perubahan nilai aktual drastis ({$changeRate}%) dibanding periode sebelumnya.",
                ];
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate logical correctness
     *
     * Checks for logical relationships and business rules
     *
     * @return array Validation result
     */
    protected function validateLogical(
        PerformanceData $performanceData,
        PerformanceIndicator $indicator
    ): array {
        $errors = [];
        $warnings = [];

        // If target is set, actual should ideally be <= target for achievement indicators
        // or >= target for reduction indicators
        $formulaType = $indicator->calculation_formula ?? 'standard';

        if (! empty($performanceData->target_value)) {
            if ($formulaType === 'reduction') {
                // For reduction goals (cost, time), lower is better
                if ($performanceData->actual_value > $performanceData->target_value * 1.5) {
                    $warnings[] = [
                        'category' => self::CATEGORY_LOGICAL,
                        'severity' => self::SEVERITY_WARNING,
                        'field' => 'actual_value',
                        'message' => 'Nilai aktual jauh di atas target untuk indikator reduksi.',
                    ];
                }
            } else {
                // For achievement indicators, higher is better
                if ($performanceData->actual_value > $performanceData->target_value * 2) {
                    $info[] = [
                        'category' => self::CATEGORY_LOGICAL,
                        'severity' => self::SEVERITY_INFO,
                        'field' => 'actual_value',
                        'message' => 'Nilai aktual jauh melebihi target. Pertimbangkan untuk meninjau ulang target.',
                    ];
                }
            }
        }

        // Check if period matches indicator frequency
        $period = Carbon::parse($performanceData->period);
        if (! $this->isValidPeriodForFrequency($period, $indicator->frequency)) {
            $errors[] = [
                'category' => self::CATEGORY_LOGICAL,
                'severity' => self::SEVERITY_ERROR,
                'field' => 'period',
                'message' => "Periode tidak sesuai dengan frekuensi indikator ({$indicator->frequency}).",
            ];
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate temporal consistency
     *
     * Checks for time-based validations
     *
     * @return array Validation result
     */
    protected function validateTemporal(
        PerformanceData $performanceData,
        PerformanceIndicator $indicator
    ): array {
        $errors = [];
        $warnings = [];

        $period = Carbon::parse($performanceData->period);
        $now = Carbon::now();

        // Check if period is in the future
        if ($period->gt($now)) {
            $errors[] = [
                'category' => self::CATEGORY_TEMPORAL,
                'severity' => self::SEVERITY_ERROR,
                'field' => 'period',
                'message' => 'Periode tidak boleh di masa depan.',
            ];
        }

        // Check if period is too old (more than 5 years)
        if ($period->lt($now->copy()->subYears(5))) {
            $warnings[] = [
                'category' => self::CATEGORY_TEMPORAL,
                'severity' => self::SEVERITY_WARNING,
                'field' => 'period',
                'message' => 'Periode sangat lama (lebih dari 5 tahun).',
            ];
        }

        // Check if data is being submitted too late
        if ($period->lt($now->copy()->subMonths(3))) {
            $warnings[] = [
                'category' => self::CATEGORY_TEMPORAL,
                'severity' => self::SEVERITY_INFO,
                'field' => 'period',
                'message' => 'Data disubmit terlambat (lebih dari 3 bulan setelah periode).',
            ];
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate evidence documents
     *
     * Checks for evidence completeness and validity
     *
     * @return array Validation result
     */
    protected function validateEvidence(
        PerformanceData $performanceData,
        PerformanceIndicator $indicator
    ): array {
        $errors = [];
        $warnings = [];

        // Check if evidence is required
        if ($indicator->requires_evidence) {
            if ($performanceData->evidenceDocuments->isEmpty()) {
                $errors[] = [
                    'category' => self::CATEGORY_EVIDENCE,
                    'severity' => self::SEVERITY_ERROR,
                    'field' => 'evidence_documents',
                    'message' => 'Dokumen pendukung wajib dilampirkan untuk indikator ini.',
                ];
            }
        }

        // Check evidence file count limits
        $minEvidence = config('sakip.validation.min_evidence_files', 0);
        $maxEvidence = config('sakip.validation.max_evidence_files', 10);
        $evidenceCount = $performanceData->evidenceDocuments->count();

        if ($evidenceCount < $minEvidence) {
            $errors[] = [
                'category' => self::CATEGORY_EVIDENCE,
                'severity' => self::SEVERITY_ERROR,
                'field' => 'evidence_documents',
                'message' => "Minimal {$minEvidence} dokumen pendukung wajib dilampirkan.",
            ];
        }

        if ($evidenceCount > $maxEvidence) {
            $warnings[] = [
                'category' => self::CATEGORY_EVIDENCE,
                'severity' => self::SEVERITY_WARNING,
                'field' => 'evidence_documents',
                'message' => "Jumlah dokumen melebihi batas (maksimal {$maxEvidence}).",
            ];
        }

        // Check file sizes and types
        foreach ($performanceData->evidenceDocuments as $document) {
            $maxFileSize = config('sakip.validation.max_file_size', 10240); // 10MB in KB
            $fileSizeKB = $document->file_size / 1024;

            if ($fileSizeKB > $maxFileSize) {
                $warnings[] = [
                    'category' => self::CATEGORY_EVIDENCE,
                    'severity' => self::SEVERITY_WARNING,
                    'field' => 'evidence_documents',
                    'message' => "Ukuran file {$document->file_name} melebihi batas ({$maxFileSize}KB).",
                ];
            }
        }

        return [
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Calculate expected performance percentage
     *
     * @return float Expected percentage
     */
    protected function calculateExpectedPercentage(PerformanceData $performanceData): float
    {
        if (empty($performanceData->target_value) || $performanceData->target_value == 0) {
            return $performanceData->actual_value > 0 ? 100 : 0;
        }

        return round(($performanceData->actual_value / $performanceData->target_value) * 100, 2);
    }

    /**
     * Check if period is valid for indicator frequency
     */
    protected function isValidPeriodForFrequency(Carbon $period, string $frequency): bool
    {
        switch ($frequency) {
            case 'monthly':
                return $period->day === 1;
            case 'quarterly':
                return $period->day === 1 && in_array($period->month, [1, 4, 7, 10]);
            case 'semester':
                return $period->day === 1 && in_array($period->month, [1, 7]);
            case 'annual':
                return $period->format('Y-m-d') === $period->copy()->startOfYear()->format('Y-m-d');
            default:
                return true;
        }
    }

    /**
     * Validate bulk imported data
     *
     * Optimized validation for bulk imports
     *
     * @param  array  $dataRows  Array of data rows to validate
     * @return array Validation results for all rows
     */
    public function validateBulkImport(array $dataRows, PerformanceIndicator $indicator): array
    {
        $results = [];
        $rowNumber = 1;

        foreach ($dataRows as $row) {
            try {
                // Create temporary PerformanceData object for validation
                $tempData = new PerformanceData([
                    'indicator_id' => $indicator->id,
                    'period' => $row['period'] ?? null,
                    'actual_value' => $row['actual_value'] ?? null,
                    'target_value' => $row['target_value'] ?? null,
                    'notes' => $row['notes'] ?? null,
                ]);

                $validationResult = $this->validatePerformanceData($tempData);
                $results[$rowNumber] = $validationResult;
            } catch (\Exception $e) {
                $results[$rowNumber] = [
                    'has_errors' => true,
                    'status' => 'error',
                    'errors' => [
                        [
                            'category' => 'system',
                            'severity' => self::SEVERITY_ERROR,
                            'message' => 'Validasi gagal: '.$e->getMessage(),
                        ],
                    ],
                ];
            }

            $rowNumber++;
        }

        return $results;
    }

    /**
     * Get validation summary statistics
     *
     * @param  array  $validationResults  Array of validation results
     * @return array Summary statistics
     */
    public function getValidationSummary(array $validationResults): array
    {
        $total = count($validationResults);
        $valid = 0;
        $warning = 0;
        $invalid = 0;
        $error = 0;

        foreach ($validationResults as $result) {
            switch ($result['status']) {
                case 'valid':
                    $valid++;
                    break;
                case 'warning':
                    $warning++;
                    break;
                case 'invalid':
                    $invalid++;
                    break;
                case 'error':
                    $error++;
                    break;
            }
        }

        return [
            'total' => $total,
            'valid' => $valid,
            'warning' => $warning,
            'invalid' => $invalid,
            'error' => $error,
            'validity_rate' => $total > 0 ? round((($valid + $warning) / $total) * 100, 2) : 0,
        ];
    }
}
