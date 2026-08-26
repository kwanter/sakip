<?php

namespace App\Services\Validation;

use App\Models\Assessment;
use App\Models\EvidenceDocument;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Target;
use Illuminate\Support\Facades\DB;

/**
 * Data Integrity Checker
 *
 * Checks data integrity across the SAKIP system including:
 * - Orphaned records detection
 * - Duplicate detection
 * - Reference integrity
 * - Cross-entity consistency
 * - Data completeness
 */
class DataIntegrityChecker
{
    /**
     * Check data integrity for an institution.
     */
    public function checkForInstansi(int $instansiId): array
    {
        return [
            'instansi_id' => $instansiId,
            'issues' => [
                'orphans' => $this->findOrphanedRecords($instansiId),
                'duplicates' => $this->findDuplicateRecords($instansiId),
                'inconsistencies' => $this->findInconsistencies($instansiId),
                'missing_references' => $this->findMissingReferences($instansiId),
                'incomplete_data' => $this->findIncompleteData($instansiId),
            ],
            'severity' => null, // Calculated below
            'checked_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Check data integrity for the entire system.
     */
    public function checkSystemWide(): array
    {
        return [
            'issues' => [
                'orphans' => $this->findOrphanedRecords(),
                'duplicates' => $this->findDuplicateRecords(),
                'inconsistencies' => $this->findInconsistencies(),
                'missing_references' => $this->findMissingReferences(),
                'incomplete_data' => $this->findIncompleteData(),
            ],
            'severity' => null, // Calculated below
            'checked_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Find orphaned records.
     */
    public function findOrphanedRecords(?int $instansiId = null): array
    {
        $orphans = [];

        // Performance data without indicators
        $orphanedPerformanceData = PerformanceData::query()
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->where(function ($query) {
                $query
                    ->whereNull('performance_indicator_id')
                    ->orWhereDoesntHave('performanceIndicator');
            })
            ->pluck('id')
            ->toArray();

        if (! empty($orphanedPerformanceData)) {
            $orphans['performance_data'] = [
                'count' => count($orphanedPerformanceData),
                'ids' => array_slice($orphanedPerformanceData, 0, 100),
            ];
        }

        // Targets without indicators
        $orphanedTargets = Target::query()
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->whereHas('performanceIndicator', function ($q) use (
                    $instansiId
                ) {
                    $q->where('instansi_id', $instansiId);
                });
            })
            ->where(function ($query) {
                $query
                    ->whereNull('performance_indicator_id')
                    ->orWhereDoesntHave('performanceIndicator');
            })
            ->pluck('id')
            ->toArray();

        if (! empty($orphanedTargets)) {
            $orphans['targets'] = [
                'count' => count($orphanedTargets),
                'ids' => array_slice($orphanedTargets, 0, 100),
            ];
        }

        // Evidence documents without valid associations
        $orphanedEvidence = EvidenceDocument::query()
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->where(function ($query) {
                $query
                    ->whereNull('performance_data_id')
                    ->whereNull('assessment_id');
            })
            ->pluck('id')
            ->toArray();

        if (! empty($orphanedEvidence)) {
            $orphans['evidence_documents'] = [
                'count' => count($orphanedEvidence),
                'ids' => array_slice($orphanedEvidence, 0, 100),
            ];
        }

        // Assessments without performance data
        $orphanedAssessments = Assessment::query()
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->where(function ($query) {
                $query
                    ->whereNull('performance_data_id')
                    ->orWhereDoesntHave('performanceData');
            })
            ->pluck('id')
            ->toArray();

        if (! empty($orphanedAssessments)) {
            $orphans['assessments'] = [
                'count' => count($orphanedAssessments),
                'ids' => array_slice($orphanedAssessments, 0, 100),
            ];
        }

        return $orphans;
    }

    /**
     * Find duplicate records.
     */
    public function findDuplicateRecords(?int $instansiId = null): array
    {
        $duplicates = [];

        // Duplicate performance data
        $duplicatePerformanceData = DB::table('performance_data')
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->select(
                'performance_indicator_id',
                'instansi_id',
                'period',
                DB::raw('COUNT(*) as count'),
            )
            ->groupBy('performance_indicator_id', 'instansi_id', 'period')
            ->having('count', '>', 1)
            ->get();

        if ($duplicatePerformanceData->isNotEmpty()) {
            $duplicates['performance_data'] = [
                'count' => $duplicatePerformanceData->count(),
                'details' => $duplicatePerformanceData
                    ->map(function ($item) {
                        return [
                            'indicator_id' => $item->performance_indicator_id,
                            'instansi_id' => $item->instansi_id,
                            'period' => $item->period,
                            'duplicate_count' => $item->count,
                        ];
                    })
                    ->toArray(),
            ];
        }

        // Duplicate targets
        $duplicateTargets = DB::table('targets')
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->select('performance_indicator_id', 'instansi_id', 'year', DB::raw('COUNT(*) as count'))
            ->groupBy('performance_indicator_id', 'instansi_id', 'year')
            ->having('count', '>', 1)
            ->get();

        if ($duplicateTargets->isNotEmpty()) {
            $duplicates['targets'] = [
                'count' => $duplicateTargets->count(),
                'details' => $duplicateTargets
                    ->map(function ($item) {
                        return [
                            'indicator_id' => $item->performance_indicator_id,
                            'instansi_id' => $item->instansi_id,
                            'year' => $item->year,
                            'duplicate_count' => $item->count,
                        ];
                    })
                    ->toArray(),
            ];
        }

        return $duplicates;
    }

    /**
     * Find inconsistencies between related entities.
     */
    public function findInconsistencies(?int $instansiId = null): array
    {
        $inconsistencies = [];

        // Assessments marked complete but data not validated
        $inconsistentAssessments = Assessment::query()
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->where('status', 'completed')
            ->whereHas('performanceData', function ($query) {
                $query->where('status', '!=', 'validated');
            })
            ->pluck('id')
            ->toArray();

        if (! empty($inconsistentAssessments)) {
            $inconsistencies['assessments_without_valid_data'] = [
                'count' => count($inconsistentAssessments),
                'ids' => array_slice($inconsistentAssessments, 0, 100),
            ];
        }

        // Performance data marked validated but missing required fields
        $incompleteValidatedData = PerformanceData::query()
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->where('status', 'validated')
            ->where(function ($query) {
                $query
                    ->whereNull('actual_value')
                    ->orWhereNull('data_source')
                    ->orWhereNull('collected_at');
            })
            ->pluck('id')
            ->toArray();

        if (! empty($incompleteValidatedData)) {
            $inconsistencies['validated_data_with_missing_fields'] = [
                'count' => count($incompleteValidatedData),
                'ids' => array_slice($incompleteValidatedData, 0, 100),
            ];
        }

        return $inconsistencies;
    }

    /**
     * Find missing references between entities.
     */
    public function findMissingReferences(?int $instansiId = null): array
    {
        $missing = [];

        // Performance data without evidence when required
        $performanceDataWithoutEvidence = PerformanceData::query()
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->where('status', 'validated')
            ->whereDoesntHave('evidenceDocuments')
            ->pluck('id')
            ->toArray();

        if (! empty($performanceDataWithoutEvidence)) {
            $missing['performance_data_without_evidence'] = [
                'count' => count($performanceDataWithoutEvidence),
                'ids' => array_slice($performanceDataWithoutEvidence, 0, 100),
            ];
        }

        // Indicators without targets for current year
        $indicatorsWithoutTargets = PerformanceIndicator::query()
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->whereDoesntHave('targets', function ($query) {
                $query->where('year', '>=', date('Y') - 1);
            })
            ->pluck('id')
            ->toArray();

        if (! empty($indicatorsWithoutTargets)) {
            $missing['indicators_without_targets'] = [
                'count' => count($indicatorsWithoutTargets),
                'ids' => array_slice($indicatorsWithoutTargets, 0, 100),
            ];
        }

        return $missing;
    }

    /**
     * Find incomplete data records.
     */
    public function findIncompleteData(?int $instansiId = null): array
    {
        $incomplete = [];

        // Performance data with null required fields
        $incompletePerformanceData = PerformanceData::query()
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->where(function ($query) {
                $query
                    ->whereNull('actual_value')
                    ->orWhereNull('period')
                    ->orWhereNull('status');
            })
            ->pluck('id')
            ->toArray();

        if (! empty($incompletePerformanceData)) {
            $incomplete['performance_data'] = [
                'count' => count($incompletePerformanceData),
                'ids' => array_slice($incompletePerformanceData, 0, 100),
            ];
        }

        // Targets with null required fields
        $incompleteTargets = Target::query()
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->where(function ($query) {
                $query->whereNull('target_value')->orWhereNull('year');
            })
            ->pluck('id')
            ->toArray();

        if (! empty($incompleteTargets)) {
            $incomplete['targets'] = [
                'count' => count($incompleteTargets),
                'ids' => array_slice($incompleteTargets, 0, 100),
            ];
        }

        // Evidence documents with null required fields
        $incompleteEvidence = EvidenceDocument::query()
            ->when($instansiId, function ($query) use ($instansiId) {
                return $query->where('instansi_id', $instansiId);
            })
            ->where(function ($query) {
                $query
                    ->whereNull('file_name')
                    ->orWhereNull('file_path')
                    ->orWhereNull('file_size');
            })
            ->pluck('id')
            ->toArray();

        if (! empty($incompleteEvidence)) {
            $incomplete['evidence_documents'] = [
                'count' => count($incompleteEvidence),
                'ids' => array_slice($incompleteEvidence, 0, 100),
            ];
        }

        return $incomplete;
    }

    /**
     * Calculate severity level based on issues.
     */
    public function calculateSeverity(array $issues): string
    {
        $totalIssues = 0;

        foreach ($issues as $category) {
            if (is_array($category)) {
                foreach ($category as $item) {
                    if (isset($item['count'])) {
                        $totalIssues += $item['count'];
                    }
                }
            }
        }

        if ($totalIssues === 0) {
            return 'none';
        } elseif ($totalIssues <= 10) {
            return 'low';
        } elseif ($totalIssues <= 50) {
            return 'medium';
        } else {
            return 'high';
        }
    }

    /**
     * Get summary of integrity issues.
     */
    public function getSummary(array $integrityResult): array
    {
        $summary = [
            'total_issues' => 0,
            'by_category' => [],
            'severity' => 'none',
        ];

        foreach ($integrityResult['issues'] as $category => $issues) {
            $categoryCount = 0;
            foreach ($issues as $type => $data) {
                if (isset($data['count'])) {
                    $categoryCount += $data['count'];
                }
            }
            $summary['by_category'][$category] = $categoryCount;
            $summary['total_issues'] += $categoryCount;
        }

        $summary['severity'] = $this->calculateSeverity($integrityResult['issues']);

        return $summary;
    }
}
