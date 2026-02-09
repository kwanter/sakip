<?php

namespace App\Services\Validation;

use App\Models\EvidenceDocument;
use App\Models\PerformanceData;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * Evidence Validator
 *
 * Handles validation of evidence documents including:
 * - File size and type validation
 * - Document completeness checks
 * - Relevance to associated data
 * - Required evidence type validation
 */
class EvidenceValidator
{
    /**
     * Allowed file types for evidence documents.
     */
    protected array $allowedFileTypes = [
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'ppt',
        'pptx',
        'jpg',
        'jpeg',
        'png',
        'gif',
        'bmp',
        'tiff',
        'csv',
        'txt',
        'zip',
        'rar',
    ];

    /**
     * Maximum file size in bytes (10MB).
     */
    protected int $maxFileSize = 10 * 1024 * 1024;

    /**
     * Validation rules for evidence documents.
     */
    protected array $rules = [
        'file_name' => 'required|string|max:255',
        'file_path' => 'required|string|max:500',
        'file_size' => 'required|integer',
        'document_type' => 'nullable|string|max:100',
        'description' => 'nullable|string|max:1000',
    ];

    /**
     * Required evidence types by indicator category.
     */
    protected array $requiredEvidenceTypes = [
        'input' => ['budget_report', 'procurement_record'],
        'output' => ['activity_report', 'completion_certificate'],
        'outcome' => ['survey', 'evaluation_report'],
        'impact' => ['impact_assessment', 'third_party_evaluation'],
    ];

    /**
     * Minimum evidence count by indicator category.
     */
    protected array $minEvidenceCount = [
        'input' => 1,
        'output' => 2,
        'outcome' => 3,
        'impact' => 4,
    ];

    /**
     * Validate evidence document.
     */
    public function validateDocument(EvidenceDocument $document, array $additionalRules = []): array
    {
        $result = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        // Basic validation
        $basicResult = $this->validateBasicFields($document, $additionalRules);
        $result = $this->mergeResults($result, $basicResult);

        // File validation
        $fileResult = $this->validateFile($document);
        $result = $this->mergeResults($result, $fileResult);

        // Relevance validation
        if ($document->performanceData || $document->assessment) {
            $relevanceResult = $this->validateRelevance($document);
            $result = $this->mergeResults($result, $relevanceResult);
        }

        $result['is_valid'] = empty($result['errors']);

        return $result;
    }

    /**
     * Validate basic fields.
     */
    protected function validateBasicFields(EvidenceDocument $document, array $additionalRules): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $data = [
            'file_name' => $document->file_name,
            'file_path' => $document->file_path,
            'file_size' => $document->file_size,
            'document_type' => $document->document_type,
            'description' => $document->description,
        ];

        $rules = array_merge($this->rules, $additionalRules);
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $result['errors'][] = $error;
            }
        }

        return $result;
    }

    /**
     * Validate file characteristics.
     */
    protected function validateFile(EvidenceDocument $document): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        // Check file size
        if ($document->file_size > $this->maxFileSize) {
            $result['warnings'][] = 'File size exceeds 10MB';
            $result['suggestions'][] = 'Consider compressing the file';
        }

        // Check file type
        $extension = strtolower(pathinfo($document->file_path, PATHINFO_EXTENSION));

        if (! in_array($extension, $this->allowedFileTypes)) {
            $result['warnings'][] = "File type '{$extension}' may not be supported";
            $result['suggestions'][] = 'Use standard document formats (PDF, DOC, XLS, JPG, PNG)';
        }

        // Check if file exists
        if (! Storage::exists($document->file_path)) {
            $result['errors'][] = 'File not found at specified path';
        }

        // Check if document is recent
        if ($document->uploaded_at || $document->created_at) {
            $uploadDate = Carbon::parse($document->uploaded_at ?? $document->created_at);

            if ($document->performanceData) {
                $dataPeriod = Carbon::parse($document->performanceData->period);

                if ($uploadDate->diffInMonths($dataPeriod) > 6) {
                    $result['warnings'][] = 'Evidence document may be outdated';
                    $result['suggestions'][] = 'Ensure evidence is current and relevant to reporting period';
                }
            }
        }

        return $result;
    }

    /**
     * Validate document relevance to associated data.
     */
    protected function validateRelevance(EvidenceDocument $document): array
    {
        $result = [
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        // Check institution match
        if ($document->performanceData) {
            if (
                $document->instansi_id &&
                $document->performanceData->instansi_id !== $document->instansi_id
            ) {
                $result['errors'][] = 'Document institution does not match performance data institution';
            }
        }

        // Check document type is appropriate for category
        if ($document->performanceData && $document->document_type) {
            $indicator = $document->performanceData->performanceIndicator;
            if ($indicator) {
                $category = $indicator->category ?? 'unknown';
                $requiredTypes = $this->requiredEvidenceTypes[$category] ?? [];

                if (
                    ! empty($requiredTypes) &&
                    ! in_array($document->document_type, ['other', ...$requiredTypes])
                ) {
                    $result['warnings'][] = "Document type may not be appropriate for {$category} indicators";
                }
            }
        }

        return $result;
    }

    /**
     * Validate evidence completeness for performance data.
     */
    public function validateCompleteness(PerformanceData $performanceData): array
    {
        $result = [
            'is_complete' => true,
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
            'missing_types' => [],
        ];

        $documents = $performanceData->evidenceDocuments;
        $documentCount = $documents->count();

        if ($documentCount === 0) {
            $result['warnings'][] = 'No evidence documents provided';
            $result['suggestions'][] = 'Upload evidence documents to support data validity';
            $result['is_complete'] = false;

            return $result;
        }

        $indicator = $performanceData->performanceIndicator;
        if (! $indicator) {
            return $result;
        }

        $category = $indicator->category ?? 'unknown';

        // Check minimum count
        $minRequired = $this->minEvidenceCount[$category] ?? 1;
        if ($documentCount < $minRequired) {
            $result['warnings'][] = "Insufficient evidence documents ({$documentCount} of {$minRequired} minimum)";
            $result['suggestions'][] = "Upload at least {$minRequired} evidence documents";
            $result['is_complete'] = false;
        }

        // Check required types
        $requiredTypes = $this->requiredEvidenceTypes[$category] ?? [];
        $documentTypes = $documents->pluck('document_type')->unique()->toArray();

        foreach ($requiredTypes as $requiredType) {
            if (! in_array($requiredType, $documentTypes)) {
                $result['missing_types'][] = $requiredType;
                $result['suggestions'][] = "Consider adding {$requiredType} documents";
            }
        }

        if (! empty($result['missing_types'])) {
            $result['is_complete'] = false;
        }

        return $result;
    }

    /**
     * Validate array data for evidence creation/update.
     */
    public function validateFromArray(array $data): array
    {
        $result = [
            'is_valid' => true,
            'errors' => [],
            'warnings' => [],
            'suggestions' => [],
        ];

        $validator = Validator::make($data, $this->rules);

        if ($validator->fails()) {
            $result['is_valid'] = false;
            foreach ($validator->errors()->all() as $error) {
                $result['errors'][] = $error;
            }
        }

        // Validate file exists if path provided
        if (isset($data['file_path']) && ! Storage::exists($data['file_path'])) {
            $result['errors'][] = 'File not found at specified path';
            $result['is_valid'] = false;
        }

        return $result;
    }

    /**
     * Merge validation results.
     */
    protected function mergeResults(array $result1, array $result2): array
    {
        return [
            'errors' => array_merge($result1['errors'] ?? [], $result2['errors'] ?? []),
            'warnings' => array_merge($result1['warnings'] ?? [], $result2['warnings'] ?? []),
            'suggestions' => array_merge($result1['suggestions'] ?? [], $result2['suggestions'] ?? []),
        ];
    }

    /**
     * Batch validate multiple evidence documents.
     */
    public function batchValidate(array $documentIds): array
    {
        $results = [
            'total' => count($documentIds),
            'valid' => 0,
            'invalid' => 0,
            'warnings' => 0,
            'details' => [],
        ];

        foreach ($documentIds as $id) {
            $document = EvidenceDocument::find($id);

            if (! $document) {
                $results['details'][$id] = [
                    'status' => 'not_found',
                    'validation' => null,
                ];

                continue;
            }

            $validation = $this->validateDocument($document);

            if ($validation['is_valid']) {
                $results['valid']++;
                $status = 'valid';
            } else {
                $results['invalid']++;
                $status = 'invalid';
            }

            if (! empty($validation['warnings'])) {
                $results['warnings']++;
            }

            $results['details'][$id] = [
                'status' => $status,
                'validation' => $validation,
            ];
        }

        return $results;
    }

    /**
     * Get required evidence types for an indicator category.
     */
    public function getRequiredTypesForCategory(string $category): array
    {
        return $this->requiredEvidenceTypes[$category] ?? [];
    }

    /**
     * Get minimum evidence count for an indicator category.
     */
    public function getMinCountForCategory(string $category): int
    {
        return $this->minEvidenceCount[$category] ?? 1;
    }
}
