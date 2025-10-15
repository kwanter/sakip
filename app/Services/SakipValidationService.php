<?php

namespace App\Services;

use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Target;
use App\Models\Assessment;
use App\Models\EvidenceDocument;
use App\Models\Report;
use App\Models\Instansi;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class SakipValidationService
{
    protected $cacheTimeout = 3600; // 1 hour
    
    // Validation rules for different data types
    protected $validationRules = [
        'performance_data' => [
            'value' => 'required|numeric',
            'period_month' => 'required|integer|between:1,12',
            'period_year' => 'required|integer|min:2020|max:2030',
            'data_source' => 'required|string|max:255',
            'collection_method' => 'required|string|max:255',
            'evidence_documents' => 'nullable|array',
            'evidence_documents.*' => 'exists:evidence_documents,id',
        ],
        'target' => [
            'target_value' => 'required|numeric|min:0',
            'baseline_value' => 'nullable|numeric',
            'stretch_value' => 'nullable|numeric',
            'minimum_value' => 'nullable|numeric',
            'maximum_value' => 'nullable|numeric',
            'weight' => 'required|numeric|between:0,100',
        ],
        'assessment' => [
            'score' => 'required|numeric|between:0,100',
            'notes' => 'nullable|string|max:2000',
            'evidence_documents' => 'nullable|array',
            'evidence_documents.*' => 'exists:evidence_documents,id',
        ],
        'evidence_document' => [
            'file_size' => 'max:10485760', // 10MB
            'file_type' => 'in:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif,bmp,tiff,csv,txt,zip,rar',
            'validation_status' => 'in:pending,validated,rejected',
        ],
    ];

    /**
     * Validate performance data
     */
    public function validatePerformanceData(array $data, PerformanceData $performanceData = null): array
    {
        $validator = Validator::make($data, $this->validationRules['performance_data']);
        
        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray(),
                'warnings' => [],
            ];
        }

        $errors = [];
        $warnings = [];

        // Validate indicator exists and is active
        if (isset($data['performance_indicator_id'])) {
            $indicator = PerformanceIndicator::find($data['performance_indicator_id']);
            if (!$indicator) {
                $errors['performance_indicator_id'] = 'Performance indicator not found';
            } elseif ($indicator->deleted_at !== null) {
                $errors['performance_indicator_id'] = 'Performance indicator is deleted';
            }
        }

        // Validate target exists for the period
        if (isset($data['performance_indicator_id']) && isset($data['period_year']) && isset($data['period_month'])) {
            $target = $this->getTargetForPeriod($data['performance_indicator_id'], $data['period_year'], $data['period_month']);
            if (!$target) {
                $warnings['target'] = 'No target set for this period';
            }
        }

        // Validate data consistency
        $this->validateDataConsistency($data, $errors, $warnings);

        // Validate against historical data
        $this->validateAgainstHistory($data, $errors, $warnings);

        // Validate evidence documents
        if (isset($data['evidence_documents'])) {
            $this->validateEvidenceDocuments($data['evidence_documents'], $errors, $warnings);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate target data
     */
    public function validateTarget(array $data, Target $target = null): array
    {
        $validator = Validator::make($data, $this->validationRules['target']);
        
        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray(),
                'warnings' => [],
            ];
        }

        $errors = [];
        $warnings = [];

        // Validate indicator exists and is active
        if (isset($data['performance_indicator_id'])) {
            $indicator = PerformanceIndicator::find($data['performance_indicator_id']);
            if (!$indicator) {
                $errors['performance_indicator_id'] = 'Performance indicator not found';
            } else {
                // Validate target value against indicator constraints
                $this->validateTargetAgainstIndicator($data, $indicator, $errors, $warnings);
            }
        }

        // Validate target consistency
        $this->validateTargetConsistency($data, $errors, $warnings);

        // Validate against historical targets
        $this->validateTargetAgainstHistory($data, $errors, $warnings, $target);

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate assessment data
     */
    public function validateAssessment(array $data, Assessment $assessment = null): array
    {
        $validator = Validator::make($data, $this->validationRules['assessment']);
        
        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray(),
                'warnings' => [],
            ];
        }

        $errors = [];
        $warnings = [];

        // Validate performance data exists and is validated
        if (isset($data['performance_data_id'])) {
            $performanceData = PerformanceData::find($data['performance_data_id']);
            if (!$performanceData) {
                $errors['performance_data_id'] = 'Performance data not found';
            } elseif ($performanceData->validation_status !== 'validated') {
                $errors['performance_data_id'] = 'Performance data must be validated before assessment';
            }
        }

        // Validate assessment score against criteria
        if (isset($data['score'])) {
            $this->validateAssessmentScore($data['score'], $data, $errors, $warnings);
        }

        // Validate evidence documents
        if (isset($data['evidence_documents'])) {
            $this->validateEvidenceDocuments($data['evidence_documents'], $errors, $warnings);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate evidence document
     */
    public function validateEvidenceDocument(array $data, EvidenceDocument $document = null): array
    {
        $validator = Validator::make($data, $this->validationRules['evidence_document']);
        
        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray(),
                'warnings' => [],
            ];
        }

        $errors = [];
        $warnings = [];

        // Validate file integrity
        if (isset($data['file_path'])) {
            $this->validateFileIntegrity($data['file_path'], $errors, $warnings);
        }

        // Validate document relevance
        if (isset($data['performance_data_id']) || isset($data['assessment_id'])) {
            $this->validateDocumentRelevance($data, $errors, $warnings);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Bulk validate performance data
     */
    public function bulkValidatePerformanceData(array $performanceDataIds): array
    {
        $results = [];
        $errors = [];
        $warnings = [];

        foreach ($performanceDataIds as $id) {
            try {
                $performanceData = PerformanceData::find($id);
                if (!$performanceData) {
                    $errors[$id] = 'Performance data not found';
                    continue;
                }

                $validationResult = $this->validatePerformanceData($performanceData->toArray(), $performanceData);
                $results[$id] = $validationResult;

                if (!$validationResult['valid']) {
                    $errors[$id] = $validationResult['errors'];
                }

                if (!empty($validationResult['warnings'])) {
                    $warnings[$id] = $validationResult['warnings'];
                }
            } catch (Exception $e) {
                $errors[$id] = $e->getMessage();
            }
        }

        return [
            'results' => $results,
            'errors' => $errors,
            'warnings' => $warnings,
            'total' => count($performanceDataIds),
            'valid' => count(array_filter($results, fn($r) => $r['valid'])),
            'invalid' => count(array_filter($results, fn($r) => !$r['valid'])),
        ];
    }

    /**
     * Validate data integrity across the system
     */
    public function validateDataIntegrity($instansiId = null): array
    {
        $issues = [];

        // Check for orphaned performance data
        $orphanedPerformanceData = $this->checkOrphanedPerformanceData($instansiId);
        if (!empty($orphanedPerformanceData)) {
            $issues['orphaned_performance_data'] = $orphanedPerformanceData;
        }

        // Check for orphaned targets
        $orphanedTargets = $this->checkOrphanedTargets($instansiId);
        if (!empty($orphanedTargets)) {
            $issues['orphaned_targets'] = $orphanedTargets;
        }

        // Check for inconsistent assessment scores
        $inconsistentAssessments = $this->checkInconsistentAssessments($instansiId);
        if (!empty($inconsistentAssessments)) {
            $issues['inconsistent_assessments'] = $inconsistentAssessments;
        }

        // Check for missing evidence documents
        $missingEvidence = $this->checkMissingEvidence($instansiId);
        if (!empty($missingEvidence)) {
            $issues['missing_evidence'] = $missingEvidence;
        }

        // Check for duplicate entries
        $duplicates = $this->checkDuplicates($instansiId);
        if (!empty($duplicates)) {
            $issues['duplicates'] = $duplicates;
        }

        // Check for data completeness
        $incompleteData = $this->checkDataCompleteness($instansiId);
        if (!empty($incompleteData)) {
            $issues['incomplete_data'] = $incompleteData;
        }

        return [
            'issues' => $issues,
            'total_issues' => count($issues),
            'severity' => $this->calculateSeverity($issues),
        ];
    }

    /**
     * Validate cross-references between entities
     */
    public function validateCrossReferences(): array
    {
        $issues = [];

        // Check performance data -> performance indicator references
        $invalidIndicatorRefs = DB::table('performance_data')
            ->leftJoin('performance_indicators', 'performance_data.performance_indicator_id', '=', 'performance_indicators.id')
            ->whereNull('performance_indicators.id')
            ->orWhereNotNull('performance_indicators.deleted_at')
            ->pluck('performance_data.id');

        if ($invalidIndicatorRefs->isNotEmpty()) {
            $issues['invalid_indicator_references'] = $invalidIndicatorRefs->toArray();
        }

        // Check targets -> performance indicator references
        $invalidTargetRefs = DB::table('targets')
            ->leftJoin('performance_indicators', 'targets.performance_indicator_id', '=', 'performance_indicators.id')
            ->whereNull('performance_indicators.id')
            ->orWhereNotNull('performance_indicators.deleted_at')
            ->pluck('targets.id');

        if ($invalidTargetRefs->isNotEmpty()) {
            $issues['invalid_target_references'] = $invalidTargetRefs->toArray();
        }

        // Check evidence documents -> performance data/assessment references
        $invalidEvidenceRefs = DB::table('evidence_documents')
            ->where(function ($query) {
                $query->whereNotNull('performance_data_id')
                    ->where(function ($q) {
                        $q->whereNotExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('performance_data')
                                ->whereColumn('performance_data.id', 'evidence_documents.performance_data_id')
                                ->whereNull('performance_data.deleted_at');
                        });
                    });
            })
            ->orWhere(function ($query) {
                $query->whereNotNull('assessment_id')
                    ->where(function ($q) {
                        $q->whereNotExists(function ($sub) {
                            $sub->select(DB::raw(1))
                                ->from('assessments')
                                ->whereColumn('assessments.id', 'evidence_documents.assessment_id')
                                ->whereNull('assessments.deleted_at');
                        });
                    });
            })
            ->pluck('evidence_documents.id');

        if ($invalidEvidenceRefs->isNotEmpty()) {
            $issues['invalid_evidence_references'] = $invalidEvidenceRefs->toArray();
        }

        return [
            'issues' => $issues,
            'total_issues' => count($issues),
        ];
    }

    /**
     * Validate calculation consistency
     */
    public function validateCalculationConsistency($instansiId = null): array
    {
        $inconsistencies = [];

        // Check achievement calculations
        $performanceData = PerformanceData::when($instansiId, fn($q) => $q->whereHas('performanceIndicator', fn($q2) => $q2->where('instansi_id', $instansiId)))
            ->where('validation_status', 'validated')
            ->get();

        foreach ($performanceData as $data) {
            $calculatedAchievement = $this->calculateAchievement($data);
            if (abs($calculatedAchievement - $data->achievement_percentage) > 0.01) {
                $inconsistencies['achievement_calculation'][] = [
                    'id' => $data->id,
                    'stored_achievement' => $data->achievement_percentage,
                    'calculated_achievement' => $calculatedAchievement,
                ];
            }
        }

        // Check assessment score calculations
        $assessments = Assessment::when($instansiId, fn($q) => $q->where('instansi_id', $instansiId))
            ->where('status', 'completed')
            ->get();

        foreach ($assessments as $assessment) {
            $calculatedScore = $this->calculateAssessmentScore($assessment);
            if (abs($calculatedScore - $assessment->score) > 0.01) {
                $inconsistencies['assessment_score_calculation'][] = [
                    'id' => $assessment->id,
                    'stored_score' => $assessment->score,
                    'calculated_score' => $calculatedScore,
                ];
            }
        }

        return [
            'inconsistencies' => $inconsistencies,
            'total_inconsistencies' => count($inconsistencies),
        ];
    }

    /**
     * Get target for period
     */
    protected function getTargetForPeriod($indicatorId, $year, $month): ?Target
    {
        $period = $this->getPeriodFromMonth($month);
        
        return Target::where('performance_indicator_id', $indicatorId)
            ->where('target_year', $year)
            ->where('target_period', $period)
            ->where('approval_status', 'approved')
            ->first();
    }

    /**
     * Get period from month
     */
    protected function getPeriodFromMonth($month): string
    {
        return $month <= 6 ? 'first_semester' : 'second_semester';
    }

    /**
     * Validate data consistency
     */
    protected function validateDataConsistency(array $data, array &$errors, array &$warnings): void
    {
        // Check for logical consistency in values
        if (isset($data['value']) && isset($data['target_value'])) {
            if ($data['value'] < 0) {
                $errors['value'] = 'Value cannot be negative';
            }
        }

        // Check period consistency
        if (isset($data['period_month']) && isset($data['period_year'])) {
            if ($data['period_year'] < 2020 || $data['period_year'] > 2030) {
                $errors['period_year'] = 'Year must be between 2020 and 2030';
            }
        }
    }

    /**
     * Validate against historical data
     */
    protected function validateAgainstHistory(array $data, array &$errors, array &$warnings): void
    {
        if (!isset($data['performance_indicator_id']) || !isset($data['period_year']) || !isset($data['period_month'])) {
            return;
        }

        // Check for unusual variations
        $historicalData = PerformanceData::where('performance_indicator_id', $data['performance_indicator_id'])
            ->where('validation_status', 'validated')
            ->where(function ($query) use ($data) {
                $query->where('period_year', '<', $data['period_year'])
                    ->orWhere(function ($q) use ($data) {
                        $q->where('period_year', $data['period_year'])
                            ->where('period_month', '<', $data['period_month']);
                    });
            })
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->limit(6)
            ->get();

        if ($historicalData->count() >= 3) {
            $values = $historicalData->pluck('value')->toArray();
            $average = array_sum($values) / count($values);
            $stdDev = $this->calculateStandardDeviation($values);

            if (isset($data['value'])) {
                $zScore = abs(($data['value'] - $average) / ($stdDev ?: 1));
                if ($zScore > 3) {
                    $warnings['value'] = 'Value is significantly different from historical average';
                }
            }
        }
    }

    /**
     * Validate target against indicator
     */
    protected function validateTargetAgainstIndicator(array $data, PerformanceIndicator $indicator, array &$errors, array &$warnings): void
    {
        // Validate measurement unit consistency
        if (isset($data['measurement_unit']) && $data['measurement_unit'] !== $indicator->measurement_unit) {
            $warnings['measurement_unit'] = 'Target measurement unit differs from indicator';
        }

        // Validate target value reasonableness
        if (isset($data['target_value'])) {
            if ($data['target_value'] > 100 && str_contains(strtolower($indicator->measurement_unit), 'percentage')) {
                $warnings['target_value'] = 'Target value exceeds 100% for percentage indicator';
            }
        }
    }

    /**
     * Validate target consistency
     */
    protected function validateTargetConsistency(array $data, array &$errors, array &$warnings): void
    {
        // Check baseline vs target
        if (isset($data['baseline_value']) && isset($data['target_value'])) {
            if ($data['baseline_value'] > $data['target_value']) {
                $warnings['target_value'] = 'Target value is less than baseline';
            }
        }

        // Check stretch vs target
        if (isset($data['stretch_value']) && isset($data['target_value'])) {
            if ($data['stretch_value'] < $data['target_value']) {
                $warnings['stretch_value'] = 'Stretch value is less than target';
            }
        }

        // Check min/max consistency
        if (isset($data['minimum_value']) && isset($data['maximum_value'])) {
            if ($data['minimum_value'] > $data['maximum_value']) {
                $errors['minimum_value'] = 'Minimum value cannot be greater than maximum value';
            }
        }
    }

    /**
     * Validate target against history
     */
    protected function validateTargetAgainstHistory(array $data, array &$errors, array &$warnings, Target $target = null): void
    {
        if (!isset($data['performance_indicator_id']) || !isset($data['target_year']) || !isset($data['target_period'])) {
            return;
        }

        // Check for significant changes from previous periods
        $previousTargets = Target::where('performance_indicator_id', $data['performance_indicator_id'])
            ->where('approval_status', 'approved')
            ->where(function ($query) use ($data, $target) {
                $query->where('target_year', '<', $data['target_year'])
                    ->orWhere(function ($q) use ($data) {
                        $q->where('target_year', $data['target_year'])
                            ->where('target_period', '!=', $data['target_period']);
                    });
                
                if ($target) {
                    $query->where('id', '!=', $target->id);
                }
            })
            ->orderBy('target_year', 'desc')
            ->orderBy('target_period', 'desc')
            ->limit(3)
            ->get();

        if ($previousTargets->count() > 0 && isset($data['target_value'])) {
            $averageTarget = $previousTargets->avg('target_value');
            $change = abs($data['target_value'] - $averageTarget) / ($averageTarget ?: 1) * 100;
            
            if ($change > 50) {
                $warnings['target_value'] = 'Target value has significant change from historical values';
            }
        }
    }

    /**
     * Validate assessment score
     */
    protected function validateAssessmentScore($score, array $data, array &$errors, array &$warnings): void
    {
        if ($score < 0 || $score > 100) {
            $errors['score'] = 'Score must be between 0 and 100';
        }

        // Validate score against performance data achievement
        if (isset($data['performance_data_id'])) {
            $performanceData = PerformanceData::find($data['performance_data_id']);
            if ($performanceData && $performanceData->validation_status === 'validated') {
                $expectedScore = $this->calculateExpectedScore($performanceData);
                $difference = abs($score - $expectedScore);
                
                if ($difference > 20) {
                    $warnings['score'] = 'Assessment score significantly differs from expected score based on performance data';
                }
            }
        }
    }

    /**
     * Validate evidence documents
     */
    protected function validateEvidenceDocuments(array $documentIds, array &$errors, array &$warnings): void
    {
        $documents = EvidenceDocument::whereIn('id', $documentIds)->get();
        
        if ($documents->count() !== count($documentIds)) {
            $errors['evidence_documents'] = 'Some evidence documents not found';
        }

        foreach ($documents as $document) {
            if ($document->validation_status !== 'validated') {
                $errors['evidence_documents'] = 'All evidence documents must be validated';
            }
        }
    }

    /**
     * Validate file integrity
     */
    protected function validateFileIntegrity($filePath, array &$errors, array &$warnings): void
    {
        // This would typically check if file exists, is readable, etc.
        // For now, we'll just check if the path is provided
        if (empty($filePath)) {
            $errors['file_path'] = 'File path is required';
        }
    }

    /**
     * Validate document relevance
     */
    protected function validateDocumentRelevance(array $data, array &$errors, array &$warnings): void
    {
        // Check if document is relevant to its associated entities
        if (isset($data['performance_data_id']) && isset($data['instansi_id'])) {
            $performanceData = PerformanceData::find($data['performance_data_id']);
            if ($performanceData && $performanceData->instansi_id != $data['instansi_id']) {
                $errors['instansi_id'] = 'Document institution does not match performance data institution';
            }
        }
    }

    /**
     * Check for orphaned performance data
     */
    protected function checkOrphanedPerformanceData($instansiId = null)
    {
        return PerformanceData::when($instansiId, fn($q) => $q->whereHas('performanceIndicator', fn($q2) => $q2->where('instansi_id', $instansiId)))
            ->where(function ($query) {
                $query->whereNull('performance_indicator_id')
                    ->orWhereHas('performanceIndicator', function ($q) {
                        $q->whereNotNull('deleted_at');
                    });
            })
            ->pluck('id')
            ->toArray();
    }

    /**
     * Check for orphaned targets
     */
    protected function checkOrphanedTargets($instansiId = null)
    {
        return Target::when($instansiId, fn($q) => $q->where('instansi_id', $instansiId))
            ->where(function ($query) {
                $query->whereNull('performance_indicator_id')
                    ->orWhereHas('performanceIndicator', function ($q) {
                        $q->whereNotNull('deleted_at');
                    });
            })
            ->pluck('id')
            ->toArray();
    }

    /**
     * Check for inconsistent assessments
     */
    protected function checkInconsistentAssessments($instansiId = null)
    {
        return Assessment::when($instansiId, fn($q) => $q->where('instansi_id', $instansiId))
            ->where('status', 'completed')
            ->where(function ($query) {
                $query->whereNull('performance_data_id')
                    ->orWhereHas('performanceData', function ($q) {
                        $q->where('validation_status', '!=', 'validated');
                    });
            })
            ->pluck('id')
            ->toArray();
    }

    /**
     * Check for missing evidence
     */
    protected function checkMissingEvidence($instansiId = null)
    {
        $missingEvidence = [];

        // Performance data without evidence documents
        $performanceDataWithoutEvidence = PerformanceData::when($instansiId, fn($q) => $q->whereHas('performanceIndicator', fn($q2) => $q2->where('instansi_id', $instansiId)))
            ->where('validation_status', 'validated')
            ->whereDoesntHave('evidenceDocuments')
            ->pluck('id')
            ->toArray();

        if (!empty($performanceDataWithoutEvidence)) {
            $missingEvidence['performance_data'] = $performanceDataWithoutEvidence;
        }

        // Assessments without evidence documents
        $assessmentsWithoutEvidence = Assessment::when($instansiId, fn($q) => $q->where('instansi_id', $instansiId))
            ->where('status', 'completed')
            ->whereDoesntHave('evidenceDocuments')
            ->pluck('id')
            ->toArray();

        if (!empty($assessmentsWithoutEvidence)) {
            $missingEvidence['assessments'] = $assessmentsWithoutEvidence;
        }

        return $missingEvidence;
    }

    /**
     * Check for duplicates
     */
    protected function checkDuplicates($instansiId = null)
    {
        $duplicates = [];

        // Duplicate performance data
        $duplicatePerformanceData = DB::table('performance_data')
            ->when($instansiId, function ($query) use ($instansiId) {
                $query->whereIn('performance_indicator_id', function ($sub) use ($instansiId) {
                    $sub->select('id')->from('performance_indicators')->where('instansi_id', $instansiId);
                });
            })
            ->select('performance_indicator_id', 'period_year', 'period_month', DB::raw('COUNT(*) as count'))
            ->groupBy('performance_indicator_id', 'period_year', 'period_month')
            ->having('count', '>', 1)
            ->pluck('performance_indicator_id')
            ->toArray();

        if (!empty($duplicatePerformanceData)) {
            $duplicates['performance_data'] = $duplicatePerformanceData;
        }

        // Duplicate targets
        $duplicateTargets = DB::table('targets')
            ->when($instansiId, fn($query) => $query->where('instansi_id', $instansiId))
            ->select('performance_indicator_id', 'target_year', 'target_period', DB::raw('COUNT(*) as count'))
            ->groupBy('performance_indicator_id', 'target_year', 'target_period')
            ->having('count', '>', 1)
            ->pluck('performance_indicator_id')
            ->toArray();

        if (!empty($duplicateTargets)) {
            $duplicates['targets'] = $duplicateTargets;
        }

        return $duplicates;
    }

    /**
     * Check for incomplete data
     */
    protected function checkDataCompleteness($instansiId = null)
    {
        $incompleteData = [];

        // Performance data with missing required fields
        $incompletePerformanceData = PerformanceData::when($instansiId, fn($q) => $q->whereHas('performanceIndicator', fn($q2) => $q2->where('instansi_id', $instansiId)))
            ->where(function ($query) {
                $query->whereNull('value')
                    ->orWhereNull('data_source')
                    ->orWhereNull('collection_method');
            })
            ->pluck('id')
            ->toArray();

        if (!empty($incompletePerformanceData)) {
            $incompleteData['performance_data'] = $incompletePerformanceData;
        }

        // Targets with missing required fields
        $incompleteTargets = Target::when($instansiId, fn($q) => $q->where('instansi_id', $instansiId))
            ->where(function ($query) {
                $query->whereNull('target_value')
                    ->orWhereNull('target_year')
                    ->orWhereNull('target_period');
            })
            ->pluck('id')
            ->toArray();

        if (!empty($incompleteTargets)) {
            $incompleteData['targets'] = $incompleteTargets;
        }

        return $incompleteData;
    }

    /**
     * Calculate achievement
     */
    protected function calculateAchievement(PerformanceData $performanceData): float
    {
        $target = $this->getTargetForPeriod(
            $performanceData->performance_indicator_id,
            $performanceData->period_year,
            $performanceData->period_month
        );

        if (!$target) {
            return 0;
        }

        if ($target->target_value == 0) {
            return 0;
        }

        return round(($performanceData->value / $target->target_value) * 100, 2);
    }

    /**
     * Calculate expected score
     */
    protected function calculateExpectedScore(PerformanceData $performanceData): float
    {
        $achievement = $this->calculateAchievement($performanceData);
        
        // Simple mapping: 0-100% achievement = 0-100 score
        // This could be more sophisticated based on your scoring criteria
        return min(100, max(0, $achievement));
    }

    /**
     * Calculate assessment score
     */
    protected function calculateAssessmentScore(Assessment $assessment): float
    {
        // This would typically recalculate the score based on criteria
        // For now, we'll return the stored score as we don't have the calculation logic
        return $assessment->score;
    }

    /**
     * Calculate standard deviation
     */
    protected function calculateStandardDeviation(array $values): float
    {
        $count = count($values);
        if ($count < 2) {
            return 0;
        }

        $mean = array_sum($values) / $count;
        $squaredDiffs = array_map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values);

        return sqrt(array_sum($squaredDiffs) / $count);
    }

    /**
     * Calculate severity of issues
     */
    protected function calculateSeverity(array $issues): string
    {
        $totalIssues = count($issues);
        
        if ($totalIssues === 0) {
            return 'none';
        } elseif ($totalIssues <= 5) {
            return 'low';
        } elseif ($totalIssues <= 15) {
            return 'medium';
        } else {
            return 'high';
        }
    }
}