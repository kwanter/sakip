<?php

namespace App\Services;

use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Target;
use App\Models\EvidenceDocument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Data Validation Service
 *
 * Handles data validation, quality checking, and integrity validation for SAKIP module
 */
class DataValidationService
{
    /**
     * Validate performance data
     *
     * @param PerformanceData $performanceData
     * @return array
     */
    public function validatePerformanceData(
        PerformanceData $performanceData,
    ): array {
        $validationResults = [
            "is_valid" => true,
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
            "quality_score" => 100,
        ];

        try {
            // Basic validation
            $basicValidation = $this->validateBasicData($performanceData);
            $validationResults = array_merge(
                $validationResults,
                $basicValidation,
            );

            // Indicator-specific validation
            $indicatorValidation = $this->validateIndicatorSpecificData(
                $performanceData,
            );
            $validationResults = $this->mergeValidationResults(
                $validationResults,
                $indicatorValidation,
            );

            // Target validation
            $targetValidation = $this->validateTargetConsistency(
                $performanceData,
            );
            $validationResults = $this->mergeValidationResults(
                $validationResults,
                $targetValidation,
            );

            // Evidence validation
            $evidenceValidation = $this->validateEvidenceDocuments(
                $performanceData,
            );
            $validationResults = $this->mergeValidationResults(
                $validationResults,
                $evidenceValidation,
            );

            // Temporal validation
            $temporalValidation = $this->validateTemporalConsistency(
                $performanceData,
            );
            $validationResults = $this->mergeValidationResults(
                $validationResults,
                $temporalValidation,
            );

            // Calculate final quality score
            $validationResults["quality_score"] = $this->calculateQualityScore(
                $validationResults,
            );

            // Determine final validity
            $validationResults["is_valid"] =
                $validationResults["quality_score"] >= 70;
        } catch (\Exception $e) {
            Log::error(
                "Error validating performance data: " . $e->getMessage(),
            );
            $validationResults["errors"][] = "System error during validation";
            $validationResults["is_valid"] = false;
        }

        return $validationResults;
    }

    /**
     * Validate basic data requirements
     */
    private function validateBasicData(PerformanceData $performanceData): array
    {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        // Check required fields
        if (is_null($performanceData->actual_value)) {
            $results["errors"][] = "Actual value is required";
        }

        if (empty($performanceData->data_source)) {
            $results["warnings"][] = "Data source is recommended";
            $results["suggestions"][] = "Provide data source for traceability";
        }

        if (empty($performanceData->collection_method)) {
            $results["warnings"][] = "Collection method is recommended";
            $results["suggestions"][] = "Specify how data was collected";
        }

        if (!$performanceData->collected_at) {
            $results["errors"][] = "Collection date is required";
        }

        // Validate actual value
        if ($performanceData->actual_value !== null) {
            $valueValidation = $this->validateActualValue($performanceData);
            $results = $this->mergeValidationResults(
                $results,
                $valueValidation,
            );
        }

        return $results;
    }

    /**
     * Validate actual value based on indicator type
     */
    private function validateActualValue(
        PerformanceData $performanceData,
    ): array {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        $actualValue = $performanceData->actual_value;
        $indicator = $performanceData->performanceIndicator;

        // Validate based on measurement type
        switch ($indicator->measurement_type) {
            case "percentage":
                if ($actualValue < 0 || $actualValue > 100) {
                    $results["errors"][] =
                        "Percentage value must be between 0 and 100";
                }
                break;

            case "ratio":
                if ($actualValue < 0) {
                    $results["errors"][] = "Ratio value cannot be negative";
                }
                break;

            case "count":
                if (
                    !is_int($actualValue) &&
                    $actualValue != (int) $actualValue
                ) {
                    $results["warnings"][] = "Count value should be an integer";
                    $results["suggestions"][] =
                        "Consider rounding to nearest integer";
                }
                if ($actualValue < 0) {
                    $results["errors"][] = "Count value cannot be negative";
                }
                break;

            case "index":
                if ($actualValue < 0) {
                    $results["errors"][] = "Index value cannot be negative";
                }
                if (
                    $actualValue > 100 &&
                    $indicator->measurement_unit === "scale_100"
                ) {
                    $results["errors"][] =
                        "Index value exceeds maximum scale of 100";
                }
                break;
        }

        // Validate against historical data
        $historicalValidation = $this->validateAgainstHistoricalData(
            $performanceData,
        );
        if ($historicalValidation) {
            $results = $this->mergeValidationResults(
                $results,
                $historicalValidation,
            );
        }

        return $results;
    }

    /**
     * Validate against historical data for consistency
     */
    private function validateAgainstHistoricalData(
        PerformanceData $performanceData,
    ): ?array {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        // Get historical data for the same indicator
        $historicalData = PerformanceData::where(
            "performance_indicator_id",
            $performanceData->performance_indicator_id,
        )
            ->where("instansi_id", $performanceData->instansi_id)
            ->where("period", "<", $performanceData->period)
            ->orderBy("period", "desc")
            ->limit(3)
            ->get();

        if ($historicalData->isEmpty()) {
            return null;
        }

        $currentValue = $performanceData->actual_value;
        $historicalValues = $historicalData
            ->pluck("actual_value")
            ->filter()
            ->toArray();

        if (empty($historicalValues)) {
            return null;
        }

        $averageHistorical =
            array_sum($historicalValues) / count($historicalValues);
        $deviation =
            abs($currentValue - $averageHistorical) /
            max($averageHistorical, 1);

        // Check for significant deviation
        if ($deviation > 0.5) {
            $results["warnings"][] =
                "Significant deviation from historical average";
            $results["suggestions"][] =
                "Verify data accuracy and provide explanation for variation";
        }

        // Check for unrealistic growth/decline
        $lastValue = $historicalValues[0];
        if ($lastValue > 0) {
            $change = abs($currentValue - $lastValue) / $lastValue;
            if ($change > 1.0) {
                $results["warnings"][] = "Unusual change from previous period";
                $results["suggestions"][] =
                    "Provide justification for significant change";
            }
        }

        return $results;
    }

    /**
     * Validate indicator-specific data
     */
    private function validateIndicatorSpecificData(
        PerformanceData $performanceData,
    ): array {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        $indicator = $performanceData->performanceIndicator;

        // Validate based on indicator category
        switch ($indicator->category) {
            case "input":
                $inputValidation = $this->validateInputIndicator(
                    $performanceData,
                );
                $results = $this->mergeValidationResults(
                    $results,
                    $inputValidation,
                );
                break;

            case "output":
                $outputValidation = $this->validateOutputIndicator(
                    $performanceData,
                );
                $results = $this->mergeValidationResults(
                    $results,
                    $outputValidation,
                );
                break;

            case "outcome":
                $outcomeValidation = $this->validateOutcomeIndicator(
                    $performanceData,
                );
                $results = $this->mergeValidationResults(
                    $results,
                    $outcomeValidation,
                );
                break;

            case "impact":
                $impactValidation = $this->validateImpactIndicator(
                    $performanceData,
                );
                $results = $this->mergeValidationResults(
                    $results,
                    $impactValidation,
                );
                break;
        }

        return $results;
    }

    /**
     * Validate input indicators
     */
    private function validateInputIndicator(
        PerformanceData $performanceData,
    ): array {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        // Input indicators should have reasonable values
        if ($performanceData->actual_value < 0) {
            $results["errors"][] = "Input indicator value cannot be negative";
        }

        // Check if evidence is provided (important for input indicators)
        if ($performanceData->evidenceDocuments()->count() === 0) {
            $results["warnings"][] =
                "Input indicators should have supporting evidence";
            $results["suggestions"][] =
                "Upload evidence documents (budget reports, procurement records, etc.)";
        }

        return $results;
    }

    /**
     * Validate output indicators
     */
    private function validateOutputIndicator(
        PerformanceData $performanceData,
    ): array {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        // Output indicators should have evidence of activities
        if ($performanceData->evidenceDocuments()->count() < 2) {
            $results["warnings"][] =
                "Output indicators should have multiple evidence documents";
            $results["suggestions"][] =
                "Upload activity reports, completion certificates, etc.";
        }

        return $results;
    }

    /**
     * Validate outcome indicators
     */
    private function validateOutcomeIndicator(
        PerformanceData $performanceData,
    ): array {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        // Outcome indicators should have comprehensive evidence
        if ($performanceData->evidenceDocuments()->count() < 3) {
            $results["warnings"][] =
                "Outcome indicators should have comprehensive evidence";
            $results["suggestions"][] =
                "Upload surveys, evaluation reports, impact assessments, etc.";
        }

        // Outcome indicators may require longer timeframes
        $indicator = $performanceData->performanceIndicator;
        if ($indicator->frequency === "monthly") {
            $results["warnings"][] =
                "Outcome indicators measured monthly may not capture true outcomes";
            $results["suggestions"][] =
                "Consider measuring outcomes quarterly or annually";
        }

        return $results;
    }

    /**
     * Validate impact indicators
     */
    private function validateImpactIndicator(
        PerformanceData $performanceData,
    ): array {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        // Impact indicators should have extensive evidence
        if ($performanceData->evidenceDocuments()->count() < 4) {
            $results["warnings"][] =
                "Impact indicators should have extensive evidence";
            $results["suggestions"][] =
                "Upload comprehensive impact assessments, third-party evaluations, etc.";
        }

        // Impact indicators should not be measured too frequently
        $indicator = $performanceData->performanceIndicator;
        if (in_array($indicator->frequency, ["monthly", "quarterly"])) {
            $results["warnings"][] =
                "Impact indicators measured frequently may not reflect true impact";
            $results["suggestions"][] =
                "Consider measuring impacts annually or bi-annually";
        }

        return $results;
    }

    /**
     * Validate target consistency
     */
    private function validateTargetConsistency(
        PerformanceData $performanceData,
    ): array {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        $indicator = $performanceData->performanceIndicator;
        $year = substr($performanceData->period, 0, 4);

        // Get target for the period
        $target = Target::where("performance_indicator_id", $indicator->id)
            ->where("year", $year)
            ->first();

        if (!$target) {
            $results["warnings"][] = "No target set for this period";
            $results["suggestions"][] =
                "Set target for better performance tracking";
            return $results;
        }

        // Validate target reasonableness
        $targetValue = $target->target_value;
        $actualValue = $performanceData->actual_value;

        if ($targetValue <= 0) {
            $results["errors"][] = "Target value must be positive";
        }

        // Check if target is realistic based on historical data
        $historicalValidation = $this->validateTargetRealism(
            $target,
            $indicator,
        );
        if ($historicalValidation) {
            $results = $this->mergeValidationResults(
                $results,
                $historicalValidation,
            );
        }

        return $results;
    }

    /**
     * Validate target realism based on historical data
     */
    private function validateTargetRealism(
        Target $target,
        PerformanceIndicator $indicator,
    ): ?array {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        // Get historical performance data
        $historicalData = PerformanceData::where(
            "performance_indicator_id",
            $indicator->id,
        )
            ->where("period", "<", $target->year . "-01-01")
            ->orderBy("period", "desc")
            ->limit(3)
            ->get();

        if ($historicalData->isEmpty()) {
            return null;
        }

        $historicalValues = $historicalData
            ->pluck("actual_value")
            ->filter()
            ->toArray();

        if (empty($historicalValues)) {
            return null;
        }

        $averageHistorical =
            array_sum($historicalValues) / count($historicalValues);
        $targetValue = $target->target_value;

        // Check if target is too ambitious
        if ($targetValue > $averageHistorical * 1.5) {
            $results["warnings"][] = "Target may be too ambitious";
            $results["suggestions"][] =
                "Consider more realistic target based on historical performance";
        }

        // Check if target is too conservative
        if ($targetValue < $averageHistorical * 0.8) {
            $results["warnings"][] = "Target may be too conservative";
            $results["suggestions"][] =
                "Consider more challenging target to drive improvement";
        }

        return $results;
    }

    /**
     * Validate evidence documents
     */
    private function validateEvidenceDocuments(
        PerformanceData $performanceData,
    ): array {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        $evidenceDocuments = $performanceData->evidenceDocuments;

        if ($evidenceDocuments->isEmpty()) {
            $results["warnings"][] = "No evidence documents provided";
            $results["suggestions"][] =
                "Upload evidence documents to support data validity";
            return $results;
        }

        // Validate each evidence document
        foreach ($evidenceDocuments as $document) {
            $documentValidation = $this->validateEvidenceDocument($document);
            if ($documentValidation) {
                $results = $this->mergeValidationResults(
                    $results,
                    $documentValidation,
                );
            }
        }

        // Check evidence completeness
        $evidenceCompleteness = $this->validateEvidenceCompleteness(
            $performanceData,
            $evidenceDocuments,
        );
        if ($evidenceCompleteness) {
            $results = $this->mergeValidationResults(
                $results,
                $evidenceCompleteness,
            );
        }

        return $results;
    }

    /**
     * Validate individual evidence document
     */
    private function validateEvidenceDocument(EvidenceDocument $document): array
    {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        // Check file size
        if ($document->file_size > 10 * 1024 * 1024) {
            // 10MB
            $results["warnings"][] = "Evidence document file size is large";
            $results["suggestions"][] = "Consider compressing large documents";
        }

        // Check file type
        $allowedTypes = ["pdf", "doc", "docx", "xls", "xlsx", "jpg", "png"];
        $extension = strtolower(
            pathinfo($document->file_path, PATHINFO_EXTENSION),
        );

        if (!in_array($extension, $allowedTypes)) {
            $results["warnings"][] =
                "Evidence document file type may not be supported";
            $results["suggestions"][] =
                "Use standard document formats (PDF, DOC, XLS, JPG, PNG)";
        }

        // Check if document is recent
        if ($document->uploaded_at) {
            $uploadDate = Carbon::parse($document->uploaded_at);
            $dataPeriod = Carbon::parse($document->performanceData->period);

            if ($uploadDate->diffInMonths($dataPeriod) > 6) {
                $results["warnings"][] = "Evidence document may be outdated";
                $results["suggestions"][] =
                    "Ensure evidence is current and relevant to the reporting period";
            }
        }

        return $results;
    }

    /**
     * Validate evidence completeness based on indicator type
     */
    private function validateEvidenceCompleteness(
        PerformanceData $performanceData,
        $evidenceDocuments,
    ): array {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        $indicator = $performanceData->performanceIndicator;
        $documentCount = $evidenceDocuments->count();

        // Check evidence types
        $documentTypes = $evidenceDocuments->pluck("document_type")->unique();
        $requiredTypes = $this->getRequiredEvidenceTypes($indicator);

        foreach ($requiredTypes as $requiredType) {
            if (!$documentTypes->contains($requiredType)) {
                $results[
                    "warnings"
                ][] = "Missing evidence type: {$requiredType}";
                $results["suggestions"][] = "Upload {$requiredType} documents";
            }
        }

        // Check minimum document count based on indicator category
        $minDocuments = $this->getMinimumEvidenceCount($indicator);

        if ($documentCount < $minDocuments) {
            $results[
                "warnings"
            ][] = "Insufficient evidence documents ({$documentCount} of {$minDocuments} minimum)";
            $results[
                "suggestions"
            ][] = "Upload at least {$minDocuments} evidence documents";
        }

        return $results;
    }

    /**
     * Get required evidence types based on indicator
     */
    private function getRequiredEvidenceTypes(
        PerformanceIndicator $indicator,
    ): array {
        $types = ["other"]; // Default type

        switch ($indicator->category) {
            case "input":
                $types = array_merge($types, [
                    "budget_report",
                    "procurement_record",
                ]);
                break;
            case "output":
                $types = array_merge($types, [
                    "activity_report",
                    "completion_certificate",
                ]);
                break;
            case "outcome":
                $types = array_merge($types, ["survey", "evaluation_report"]);
                break;
            case "impact":
                $types = array_merge($types, [
                    "impact_assessment",
                    "third_party_evaluation",
                ]);
                break;
        }

        return $types;
    }

    /**
     * Get minimum evidence count based on indicator
     */
    private function getMinimumEvidenceCount(
        PerformanceIndicator $indicator,
    ): int {
        switch ($indicator->category) {
            case "input":
                return 1;
            case "output":
                return 2;
            case "outcome":
                return 3;
            case "impact":
                return 4;
            default:
                return 1;
        }
    }

    /**
     * Validate temporal consistency
     */
    private function validateTemporalConsistency(
        PerformanceData $performanceData,
    ): array {
        $results = [
            "errors" => [],
            "warnings" => [],
            "suggestions" => [],
        ];

        $period = $performanceData->period;
        $collectedAt = $performanceData->collected_at;
        $validatedAt = $performanceData->validated_at;

        // Validate collection date
        if ($collectedAt) {
            $periodDate = Carbon::parse($period);
            $collectionDate = Carbon::parse($collectedAt);

            // Check if collection date is reasonable
            if ($collectionDate->diffInMonths($periodDate) > 6) {
                $results["warnings"][] =
                    "Data collection date is far from reporting period";
                $results["suggestions"][] =
                    "Ensure data collection timing is appropriate";
            }

            // Check if collection date is in the future
            if ($collectionDate->isFuture()) {
                $results["errors"][] =
                    "Data collection date cannot be in the future";
            }
        }

        // Validate validation date
        if ($validatedAt) {
            $validationDate = Carbon::parse($validatedAt);

            // Check if validation is after collection
            if (
                $collectedAt &&
                $validationDate->lt(Carbon::parse($collectedAt))
            ) {
                $results["errors"][] =
                    "Validation date cannot be before collection date";
            }

            // Check if validation date is reasonable
            if ($validationDate->diffInMonths(now()) > 12) {
                $results["warnings"][] = "Validation date is old";
                $results["suggestions"][] = "Consider re-validating data";
            }
        }

        return $results;
    }

    /**
     * Calculate quality score based on validation results
     */
    private function calculateQualityScore(array $validationResults): float
    {
        $baseScore = 100;

        // Deduct points for errors
        $errorDeduction = count($validationResults["errors"]) * 20;

        // Deduct points for warnings
        $warningDeduction = count($validationResults["warnings"]) * 5;

        $finalScore = $baseScore - $errorDeduction - $warningDeduction;

        return max(0, min(100, $finalScore));
    }

    /**
     * Merge validation results
     */
    private function mergeValidationResults(
        array $results1,
        array $results2,
    ): array {
        return [
            "errors" => array_merge(
                $results1["errors"] ?? [],
                $results2["errors"] ?? [],
            ),
            "warnings" => array_merge(
                $results1["warnings"] ?? [],
                $results2["warnings"] ?? [],
            ),
            "suggestions" => array_merge(
                $results1["suggestions"] ?? [],
                $results2["suggestions"] ?? [],
            ),
        ];
    }

    /**
     * Batch validate multiple performance data
     *
     * @param array $performanceDataIds
     * @return array
     */
    public function batchValidate(array $performanceDataIds): array
    {
        $results = [
            "total" => count($performanceDataIds),
            "valid" => 0,
            "invalid" => 0,
            "warnings" => 0,
            "details" => [],
        ];

        foreach ($performanceDataIds as $id) {
            $performanceData = PerformanceData::find($id);

            if (!$performanceData) {
                $results["details"][$id] = [
                    "status" => "not_found",
                    "validation" => null,
                ];
                continue;
            }

            $validation = $this->validatePerformanceData($performanceData);

            if ($validation["is_valid"]) {
                $results["valid"]++;
                $status = "valid";
            } else {
                $results["invalid"]++;
                $status = "invalid";
            }

            if (!empty($validation["warnings"])) {
                $results["warnings"]++;
            }

            $results["details"][$id] = [
                "status" => $status,
                "validation" => $validation,
            ];
        }

        return $results;
    }

    /**
     * Get data quality metrics for institution
     *
     * @param int $institutionId
     * @param string $period
     * @return array
     */
    public function getDataQualityMetrics(
        int $institutionId,
        string $period,
    ): array {
        $performanceData = PerformanceData::where("instansi_id", $institutionId)
            ->where("period", $period)
            ->with(["performanceIndicator", "evidenceDocuments"])
            ->get();

        $totalRecords = $performanceData->count();

        if ($totalRecords === 0) {
            return [
                "total_records" => 0,
                "valid_records" => 0,
                "data_quality_score" => 0,
                "completion_rate" => 0,
                "validation_rate" => 0,
                "evidence_coverage" => 0,
                "by_category" => [],
            ];
        }

        $validRecords = 0;
        $totalQualityScore = 0;
        $validatedRecords = 0;
        $recordsWithEvidence = 0;
        $byCategory = [];

        foreach ($performanceData as $data) {
            $validation = $this->validatePerformanceData($data);

            if ($validation["is_valid"]) {
                $validRecords++;
            }

            $totalQualityScore += $validation["quality_score"];

            if ($data->validated_at) {
                $validatedRecords++;
            }

            if ($data->evidenceDocuments()->count() > 0) {
                $recordsWithEvidence++;
            }

            $category = $data->performanceIndicator->category;
            if (!isset($byCategory[$category])) {
                $byCategory[$category] = [
                    "total" => 0,
                    "valid" => 0,
                    "average_quality_score" => 0,
                ];
            }

            $byCategory[$category]["total"]++;
            if ($validation["is_valid"]) {
                $byCategory[$category]["valid"]++;
            }
            $byCategory[$category]["average_quality_score"] +=
                $validation["quality_score"];
        }

        // Calculate category averages
        foreach ($byCategory as $category => &$metrics) {
            $metrics["average_quality_score"] = round(
                $metrics["average_quality_score"] / $metrics["total"],
                2,
            );
            $metrics["validity_rate"] = round(
                ($metrics["valid"] / $metrics["total"]) * 100,
                2,
            );
        }

        return [
            "total_records" => $totalRecords,
            "valid_records" => $validRecords,
            "data_quality_score" => round(
                $totalQualityScore / $totalRecords,
                2,
            ),
            "completion_rate" => round(
                ($validRecords / $totalRecords) * 100,
                2,
            ),
            "validation_rate" => round(
                ($validatedRecords / $totalRecords) * 100,
                2,
            ),
            "evidence_coverage" => round(
                ($recordsWithEvidence / $totalRecords) * 100,
                2,
            ),
            "by_category" => $byCategory,
        ];
    }

    /**
     * Check data integrity across the system
     *
     * @param int $institutionId
     * @return array
     */
    public function checkDataIntegrity(int $institutionId): array
    {
        $integrityIssues = [];

        // Check for duplicate performance data
        $duplicates = $this->findDuplicatePerformanceData($institutionId);
        if (!empty($duplicates)) {
            $integrityIssues["duplicates"] = $duplicates;
        }

        // Check for orphaned records
        $orphans = $this->findOrphanedRecords($institutionId);
        if (!empty($orphans)) {
            $integrityIssues["orphans"] = $orphans;
        }

        // Check for inconsistent data
        $inconsistencies = $this->findDataInconsistencies($institutionId);
        if (!empty($inconsistencies)) {
            $integrityIssues["inconsistencies"] = $inconsistencies;
        }

        return $integrityIssues;
    }

    /**
     * Find duplicate performance data
     */
    private function findDuplicatePerformanceData(int $institutionId): array
    {
        $duplicates = DB::table("performance_data")
            ->select(
                "performance_indicator_id",
                "period",
                DB::raw("COUNT(*) as count"),
            )
            ->where("instansi_id", $institutionId)
            ->groupBy("performance_indicator_id", "period")
            ->having("count", ">", 1)
            ->get();

        return $duplicates
            ->map(function ($duplicate) {
                return [
                    "performance_indicator_id" =>
                        $duplicate->performance_indicator_id,
                    "period" => $duplicate->period,
                    "count" => $duplicate->count,
                ];
            })
            ->toArray();
    }

    /**
     * Find orphaned records
     */
    private function findOrphanedRecords(int $institutionId): array
    {
        $orphans = [];

        // Find performance data without indicators
        $orphanedPerformanceData = PerformanceData::where(
            "instansi_id",
            $institutionId,
        )
            ->whereDoesntHave("performanceIndicator")
            ->pluck("id")
            ->toArray();

        if (!empty($orphanedPerformanceData)) {
            $orphans["performance_data"] = $orphanedPerformanceData;
        }

        // Find evidence documents without performance data
        $orphanedEvidence = EvidenceDocument::whereHas(
            "performanceData",
            function ($query) use ($institutionId) {
                $query->where("instansi_id", $institutionId);
            },
        )
            ->whereDoesntHave("performanceData")
            ->pluck("id")
            ->toArray();

        if (!empty($orphanedEvidence)) {
            $orphans["evidence_documents"] = $orphanedEvidence;
        }

        return $orphans;
    }

    /**
     * Find data inconsistencies
     */
    private function findDataInconsistencies(int $institutionId): array
    {
        $inconsistencies = [];

        // Find assessments without completed performance data
        $inconsistentAssessments = DB::table("assessments")
            ->join(
                "performance_data",
                "assessments.performance_data_id",
                "=",
                "performance_data.id",
            )
            ->where("performance_data.instansi_id", $institutionId)
            ->where("assessments.status", "completed")
            ->where("performance_data.status", "draft")
            ->pluck("assessments.id")
            ->toArray();

        if (!empty($inconsistentAssessments)) {
            $inconsistencies[
                "assessments_without_valid_data"
            ] = $inconsistentAssessments;
        }

        return $inconsistencies;
    }
}
