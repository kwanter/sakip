<?php

namespace App\Services\Validation;

use App\Models\Assessment;
use App\Models\EvidenceDocument;
use App\Models\PerformanceData;
use App\Models\Target;

/**
 * Validation Orchestrator
 *
 * Main entry point for all validation operations.
 * Delegates to specialized validators for focused validation.
 *
 * @deprecated Use individual validators directly for better clarity.
 *             This class remains for backward compatibility.
 */
class ValidationOrchestrator
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
     */
    public function validatePerformanceData(PerformanceData $performanceData): array
    {
        return $this->performanceDataValidator->validate($performanceData);
    }

    /**
     * Batch validate performance data.
     */
    public function batchValidatePerformanceData(array $ids): array
    {
        return $this->performanceDataValidator->batchValidate($ids);
    }

    /**
     * Validate target.
     */
    public function validateTarget(Target $target): array
    {
        return $this->targetValidator->validate($target);
    }

    /**
     * Batch validate targets.
     */
    public function batchValidateTargets(array $ids): array
    {
        return $this->targetValidator->batchValidate($ids);
    }

    /**
     * Validate target from array data.
     */
    public function validateTargetFromArray(array $data): array
    {
        return $this->targetValidator->validateFromArray($data);
    }

    /**
     * Validate assessment.
     */
    public function validateAssessment(Assessment $assessment): array
    {
        return $this->assessmentValidator->validate($assessment);
    }

    /**
     * Batch validate assessments.
     */
    public function batchValidateAssessments(array $ids): array
    {
        return $this->assessmentValidator->batchValidate($ids);
    }

    /**
     * Validate assessment from array data.
     */
    public function validateAssessmentFromArray(array $data): array
    {
        return $this->assessmentValidator->validateFromArray($data);
    }

    /**
     * Validate evidence document.
     */
    public function validateEvidenceDocument(EvidenceDocument $document): array
    {
        return $this->evidenceValidator->validateDocument($document);
    }

    /**
     * Batch validate evidence documents.
     */
    public function batchValidateEvidenceDocuments(array $ids): array
    {
        return $this->evidenceValidator->batchValidate($ids);
    }

    /**
     * Validate evidence document from array data.
     */
    public function validateEvidenceDocumentFromArray(array $data): array
    {
        return $this->evidenceValidator->validateFromArray($data);
    }

    /**
     * Validate evidence completeness for performance data.
     */
    public function validateEvidenceCompleteness(PerformanceData $performanceData): array
    {
        return $this->evidenceValidator->validateCompleteness($performanceData);
    }

    /**
     * Check data integrity for an institution.
     */
    public function checkDataIntegrity(int $instansiId): array
    {
        return $this->integrityChecker->checkForInstansi($instansiId);
    }

    /**
     * Check data integrity system-wide.
     */
    public function checkSystemWideIntegrity(): array
    {
        return $this->integrityChecker->checkSystemWide();
    }

    /**
     * Find orphaned records.
     */
    public function findOrphanedRecords(?int $instansiId = null): array
    {
        return $this->integrityChecker->findOrphanedRecords($instansiId);
    }

    /**
     * Find duplicate records.
     */
    public function findDuplicateRecords(?int $instansiId = null): array
    {
        return $this->integrityChecker->findDuplicateRecords($instansiId);
    }

    /**
     * Find inconsistencies.
     */
    public function findInconsistencies(?int $instansiId = null): array
    {
        return $this->integrityChecker->findInconsistencies($instansiId);
    }

    /**
     * Get data quality metrics for an institution and period.
     */
    public function getDataQualityMetrics(int $instansiId, string $period): array
    {
        $performanceData = PerformanceData::where('instansi_id', $instansiId)
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

            if ($data->validated_at || $data->status === 'validated') {
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
     * Get the performance data validator instance.
     */
    public function performanceDataValidator(): PerformanceDataValidator
    {
        return $this->performanceDataValidator;
    }

    /**
     * Get the target validator instance.
     */
    public function targetValidator(): TargetValidator
    {
        return $this->targetValidator;
    }

    /**
     * Get the evidence validator instance.
     */
    public function evidenceValidator(): EvidenceValidator
    {
        return $this->evidenceValidator;
    }

    /**
     * Get the assessment validator instance.
     */
    public function assessmentValidator(): AssessmentValidator
    {
        return $this->assessmentValidator;
    }

    /**
     * Get the integrity checker instance.
     */
    public function integrityChecker(): DataIntegrityChecker
    {
        return $this->integrityChecker;
    }
}
