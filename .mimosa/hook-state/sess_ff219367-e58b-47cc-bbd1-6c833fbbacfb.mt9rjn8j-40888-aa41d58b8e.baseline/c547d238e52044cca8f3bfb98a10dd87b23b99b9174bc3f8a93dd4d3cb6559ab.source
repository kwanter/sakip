<?php

namespace App\Services;

use App\Models\PerformanceData;
use App\Services\Validation\AssessmentValidator;
use App\Services\Validation\DataIntegrityChecker;
use App\Services\Validation\EvidenceValidator;
use App\Services\Validation\PerformanceDataValidator;
use App\Services\Validation\TargetValidator;
use Illuminate\Support\Facades\Log;

/**
 * Data Validation Service
 *
 * Facade service for data validation operations.
 * Delegates to specialized validators for focused validation.
 *
 * @see \App\Services\Validation\PerformanceDataValidator
 * @see \App\Services\Validation\TargetValidator
 * @see \App\Services\Validation\EvidenceValidator
 * @see \App\Services\Validation\AssessmentValidator
 * @see \App\Services\Validation\DataIntegrityChecker
 */
class DataValidationService
{
    protected PerformanceDataValidator $performanceDataValidator;

    protected TargetValidator $targetValidator;

    protected EvidenceValidator $evidenceValidator;

    protected AssessmentValidator $assessmentValidator;

    protected DataIntegrityChecker $integrityChecker;

    public function __construct(
        PerformanceDataValidator $performanceDataValidator,
        TargetValidator $targetValidator,
        EvidenceValidator $evidenceValidator,
        AssessmentValidator $assessmentValidator,
        DataIntegrityChecker $integrityChecker,
    ) {
        $this->performanceDataValidator = $performanceDataValidator;
        $this->targetValidator = $targetValidator;
        $this->evidenceValidator = $evidenceValidator;
        $this->assessmentValidator = $assessmentValidator;
        $this->integrityChecker = $integrityChecker;
    }

    /**
     * Validate performance data.
     *
     * @return array Validation result with is_valid, errors, warnings, suggestions, quality_score
     */
    public function validatePerformanceData(PerformanceData $performanceData): array
    {
        try {
            return $this->performanceDataValidator->validate($performanceData);
        } catch (\Exception $e) {
            Log::error('Error validating performance data: '.$e->getMessage());

            return [
                'is_valid' => false,
                'errors' => ['System error during validation'],
                'warnings' => [],
                'suggestions' => [],
                'quality_score' => 0,
            ];
        }
    }

    /**
     * Validate basic data requirements.
     *
     * @deprecated Use PerformanceDataValidator::validate() directly
     */
    private function validateBasicData(PerformanceData $performanceData): array
    {
        return $this->performanceDataValidator->validate($performanceData);
    }

    /**
     * Batch validate multiple performance data entries.
     */
    public function batchValidate(array $performanceDataIds): array
    {
        return $this->performanceDataValidator->batchValidate($performanceDataIds);
    }

    /**
     * Get data quality metrics for an institution and period.
     */
    public function getDataQualityMetrics(int $institutionId, string $period): array
    {
        $performanceData = PerformanceData::where('instansi_id', $institutionId)
            ->where('period', 'like', $period.'%')
            ->with(['performanceIndicator', 'evidenceDocuments'])
            ->get();

        $totalRecords = $performanceData->count();

        if ($totalRecords === 0) {
            return [
                'total_records' => 0,
                'valid_records' => 0,
                'data_quality_score' => 0,
                'completion_rate' => 0,
                'validation_rate' => 0,
                'evidence_coverage' => 0,
                'by_category' => [],
            ];
        }

        $validRecords = 0;
        $totalQualityScore = 0;
        $validatedRecords = 0;
        $recordsWithEvidence = 0;
        $byCategory = [];

        foreach ($performanceData as $data) {
            $validation = $this->performanceDataValidator->validate($data);

            if ($validation['is_valid']) {
                $validRecords++;
            }

            $totalQualityScore += $validation['quality_score'];

            if ($data->validated_at) {
                $validatedRecords++;
            }

            if ($data->evidenceDocuments()->count() > 0) {
                $recordsWithEvidence++;
            }

            $category = $data->performanceIndicator?->category ?? 'unknown';
            if (! isset($byCategory[$category])) {
                $byCategory[$category] = [
                    'total' => 0,
                    'valid' => 0,
                    'average_quality_score' => 0,
                ];
            }

            $byCategory[$category]['total']++;
            if ($validation['is_valid']) {
                $byCategory[$category]['valid']++;
            }
            $byCategory[$category]['average_quality_score'] += $validation['quality_score'];
        }

        // Calculate category averages
        foreach ($byCategory as $category => &$metrics) {
            $metrics['average_quality_score'] = round(
                $metrics['average_quality_score'] / $metrics['total'],
                2,
            );
            $metrics['validity_rate'] = round(
                ($metrics['valid'] / $metrics['total']) * 100,
                2,
            );
        }

        return [
            'total_records' => $totalRecords,
            'valid_records' => $validRecords,
            'data_quality_score' => round($totalQualityScore / $totalRecords, 2),
            'completion_rate' => round(($validRecords / $totalRecords) * 100, 2),
            'validation_rate' => round(($validatedRecords / $totalRecords) * 100, 2),
            'evidence_coverage' => round(($recordsWithEvidence / $totalRecords) * 100, 2),
            'by_category' => $byCategory,
        ];
    }

    /**
     * Check data integrity for an institution.
     */
    public function checkDataIntegrity(int $institutionId): array
    {
        return $this->integrityChecker->checkForInstansi($institutionId);
    }

    /**
     * Find duplicate performance data.
     *
     * @deprecated Use DataIntegrityChecker::findDuplicateRecords() directly
     */
    private function findDuplicatePerformanceData(int $institutionId): array
    {
        $duplicates = $this->integrityChecker->findDuplicateRecords($institutionId);

        return $duplicates['performance_data'] ?? [];
    }

    /**
     * Find orphaned records.
     *
     * @deprecated Use DataIntegrityChecker::findOrphanedRecords() directly
     */
    private function findOrphanedRecords(int $institutionId): array
    {
        return $this->integrityChecker->findOrphanedRecords($institutionId);
    }

    /**
     * Find data inconsistencies.
     *
     * @deprecated Use DataIntegrityChecker::findInconsistencies() directly
     */
    private function findDataInconsistencies(int $institutionId): array
    {
        return $this->integrityChecker->findInconsistencies($institutionId);
    }

    /**
     * Get the performance data validator.
     */
    public function performanceDataValidator(): PerformanceDataValidator
    {
        return $this->performanceDataValidator;
    }

    /**
     * Get the target validator.
     */
    public function targetValidator(): TargetValidator
    {
        return $this->targetValidator;
    }

    /**
     * Get the evidence validator.
     */
    public function evidenceValidator(): EvidenceValidator
    {
        return $this->evidenceValidator;
    }

    /**
     * Get the assessment validator.
     */
    public function assessmentValidator(): AssessmentValidator
    {
        return $this->assessmentValidator;
    }

    /**
     * Get the integrity checker.
     */
    public function integrityChecker(): DataIntegrityChecker
    {
        return $this->integrityChecker;
    }
}
