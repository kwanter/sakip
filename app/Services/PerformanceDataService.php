<?php

namespace App\Services;

use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\EvidenceDocument;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class PerformanceDataService
{
    protected $cacheTimeout = 3600; // 1 hour

    /**
     * Submit performance data
     */
    public function submitPerformanceData(array $data): PerformanceData
    {
        return DB::transaction(function () use ($data) {
            // Validate data
            $validator = $this->validatePerformanceData($data);
            if ($validator->fails()) {
                throw new Exception('Validation failed: ' . $validator->errors()->first());
            }

            // Check if data already exists for this period
            $existingData = $this->getExistingData(
                $data['performance_indicator_id'],
                $data['period_year'],
                $data['period_month'] ?? null,
                $data['period_quarter'] ?? null
            );

            if ($existingData && $existingData->validation_status === 'validated') {
                throw new Exception('Cannot modify validated data. Please contact administrator.');
            }

            // Calculate achievement if formula is available
            $achievement = $this->calculateAchievement($data);

            // Create or update performance data
            if ($existingData) {
                $performanceData = $this->updateExistingData($existingData, $data, $achievement);
            } else {
                $performanceData = $this->createNewData($data, $achievement);
            }

            // Handle evidence documents
            if (isset($data['evidence_documents']) && is_array($data['evidence_documents'])) {
                $this->attachEvidenceDocuments($performanceData, $data['evidence_documents']);
            }

            // Log activity
            $this->logActivity('submit', $performanceData, 'Performance data submitted');

            // Clear cache
            $this->clearPerformanceDataCache($performanceData->performanceIndicator->instansi_id);

            return $performanceData->fresh(['performanceIndicator', 'evidenceDocuments']);
        });
    }

    /**
     * Validate performance data
     */
    public function validatePerformanceData(PerformanceData $performanceData): array
    {
        $indicator = $performanceData->performanceIndicator;
        $issues = [];

        // Check data completeness
        if (empty($performanceData->actual_value) && $performanceData->actual_value !== 0) {
            $issues[] = 'Nilai aktual tidak boleh kosong';
        }

        // Check data consistency with indicator properties
        if ($indicator->measurement_unit && $performanceData->measurement_unit !== $indicator->measurement_unit) {
            $issues[] = 'Satuan pengukuran tidak sesuai dengan indikator';
        }

        // Check against target if available
        $target = $indicator->targets->where('year', $performanceData->period_year)->first();
        if ($target && $target->target_value > 0) {
            $achievement = $this->calculateAchievementFromValues($performanceData->actual_value, $target->target_value);
            
            if ($achievement > 150) {
                $issues[] = 'Capaian melebihi 150% dari target - memerlukan verifikasi';
            }
        }

        // Check data timeliness
        $deadline = $this->getSubmissionDeadline($performanceData->period_year, $performanceData->period_month);
        if (Carbon::parse($performanceData->created_at)->gt($deadline)) {
            $issues[] = 'Data disampaikan melebihi batas waktu';
        }

        // Check evidence requirements
        if ($indicator->is_mandatory && !$performanceData->evidenceDocuments()->exists()) {
            $issues[] = 'Dokumen bukti wajib untuk indikator mandatory';
        }

        // Validate calculation formula if present
        if ($indicator->calculation_formula) {
            $formulaValidation = $this->validateFormulaCalculation($performanceData, $indicator);
            if (!$formulaValidation['valid']) {
                $issues[] = $formulaValidation['message'];
            }
        }

        return [
            'is_valid' => empty($issues),
            'issues' => $issues,
            'severity' => $this->determineSeverity($issues),
        ];
    }

    /**
     * Bulk import performance data
     */
    public function bulkImportPerformanceData(array $dataArray): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        DB::transaction(function () use ($dataArray, &$results) {
            foreach ($dataArray as $index => $data) {
                try {
                    $this->submitPerformanceData($data);
                    $results['success']++;
                } catch (Exception $e) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'row' => $index + 1,
                        'error' => $e->getMessage(),
                    ];
                }
            }
        });

        return $results;
    }

    /**
     * Get performance data with filters
     */
    public function getPerformanceData(array $filters = [], $perPage = 15)
    {
        $query = PerformanceData::with([
            'performanceIndicator.instansi',
            'evidenceDocuments',
            'validatedBy',
            'submittedBy'
        ]);

        // Apply filters
        if (isset($filters['instansi_id'])) {
            $query->whereHas('performanceIndicator', function ($q) use ($filters) {
                $q->where('instansi_id', $filters['instansi_id']);
            });
        }

        if (isset($filters['performance_indicator_id'])) {
            $query->where('performance_indicator_id', $filters['performance_indicator_id']);
        }

        if (isset($filters['period_year'])) {
            $query->where('period_year', $filters['period_year']);
        }

        if (isset($filters['period_month'])) {
            $query->where('period_month', $filters['period_month']);
        }

        if (isset($filters['period_quarter'])) {
            $query->where('period_quarter', $filters['period_quarter']);
        }

        if (isset($filters['validation_status'])) {
            $query->where('validation_status', $filters['validation_status']);
        }

        if (isset($filters['submitted_by'])) {
            $query->where('submitted_by', $filters['submitted_by']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('performanceIndicator', function ($q2) use ($filters) {
                    $q2->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('code', 'like', '%' . $filters['search'] . '%');
                });
            });
        }

        if (isset($filters['has_evidence'])) {
            if ($filters['has_evidence']) {
                $query->has('evidenceDocuments');
            } else {
                $query->doesntHave('evidenceDocuments');
            }
        }

        // Date range filters
        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Validate performance data submission
     */
    public function validateSubmission(array $data): array
    {
        $validator = $this->validatePerformanceData($data);
        
        if ($validator->fails()) {
            return [
                'valid' => false,
                'errors' => $validator->errors()->toArray(),
            ];
        }

        // Additional business logic validation
        $businessValidation = $this->validateBusinessRules($data);
        
        return [
            'valid' => $businessValidation['valid'],
            'errors' => array_merge($validator->errors()->toArray(), $businessValidation['errors']),
            'warnings' => $businessValidation['warnings'] ?? [],
        ];
    }

    /**
     * Get data collection progress
     */
    public function getDataCollectionProgress($instansiId, $year, $month = null): array
    {
        $cacheKey = "data_collection_progress_{$instansiId}_{$year}_{$month}";

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($instansiId, $year, $month) {
            $totalIndicators = PerformanceIndicator::where('instansi_id', $instansiId)->count();
            
            $submittedQuery = PerformanceData::whereHas('performanceIndicator', function ($q) use ($instansiId) {
                $q->where('instansi_id', $instansiId);
            })->where('period_year', $year);

            if ($month) {
                $submittedQuery->where('period_month', $month);
            }

            $submittedIndicators = $submittedQuery->distinct('performance_indicator_id')->count();
            $validatedIndicators = (clone $submittedQuery)->where('validation_status', 'validated')->distinct('performance_indicator_id')->count();

            return [
                'total_indicators' => $totalIndicators,
                'submitted_indicators' => $submittedIndicators,
                'validated_indicators' => $validatedIndicators,
                'submission_rate' => $totalIndicators > 0 ? round(($submittedIndicators / $totalIndicators) * 100, 2) : 0,
                'validation_rate' => $submittedIndicators > 0 ? round(($validatedIndicators / $submittedIndicators) * 100, 2) : 0,
                'pending_indicators' => $totalIndicators - $submittedIndicators,
            ];
        });
    }

    /**
     * Get performance trends
     */
    public function getPerformanceTrends($indicatorId, $periods = 12): array
    {
        $cacheKey = "performance_trends_{$indicatorId}_{$periods}";

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($indicatorId, $periods) {
            $performanceData = PerformanceData::where('performance_indicator_id', $indicatorId)
                ->where('validation_status', 'validated')
                ->orderBy('period_year', 'desc')
                ->orderBy('period_month', 'desc')
                ->take($periods)
                ->get();

            return $performanceData->map(function ($data) {
                return [
                    'period' => $this->formatPeriod($data->period_year, $data->period_month, $data->period_quarter),
                    'actual_value' => $data->actual_value,
                    'target_value' => $data->target_value,
                    'achievement' => $this->calculateAchievementPercentage($data->actual_value, $data->target_value),
                    'validation_status' => $data->validation_status,
                ];
            })->reverse()->values()->toArray();
        });
    }

    /**
     * Create new performance data
     */
    protected function createNewData(array $data, $achievement): PerformanceData
    {
        return PerformanceData::create([
            'performance_indicator_id' => $data['performance_indicator_id'],
            'period_year' => $data['period_year'],
            'period_month' => $data['period_month'] ?? null,
            'period_quarter' => $data['period_quarter'] ?? null,
            'actual_value' => $data['actual_value'],
            'target_value' => $data['target_value'] ?? null,
            'measurement_unit' => $data['measurement_unit'] ?? null,
            'achievement_percentage' => $achievement,
            'data_source' => $data['data_source'] ?? null,
            'collection_method' => $data['collection_method'] ?? null,
            'notes' => $data['notes'] ?? null,
            'validation_status' => 'pending',
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);
    }

    /**
     * Update existing performance data
     */
    protected function updateExistingData(PerformanceData $existingData, array $data, $achievement): PerformanceData
    {
        $existingData->update([
            'actual_value' => $data['actual_value'],
            'target_value' => $data['target_value'] ?? $existingData->target_value,
            'measurement_unit' => $data['measurement_unit'] ?? $existingData->measurement_unit,
            'achievement_percentage' => $achievement,
            'data_source' => $data['data_source'] ?? $existingData->data_source,
            'collection_method' => $data['collection_method'] ?? $existingData->collection_method,
            'notes' => $data['notes'] ?? $existingData->notes,
            'validation_status' => 'pending',
            'validation_notes' => null,
            'validated_by' => null,
            'validated_at' => null,
            'updated_by' => auth()->id(),
        ]);

        return $existingData;
    }

    /**
     * Calculate achievement percentage
     */
    protected function calculateAchievement(array $data)
    {
        if (!isset($data['target_value']) || $data['target_value'] == 0) {
            return null;
        }

        return round(($data['actual_value'] / $data['target_value']) * 100, 2);
    }

    /**
     * Calculate achievement from values
     */
    protected function calculateAchievementFromValues($actual, $target): ?float
    {
        if ($target == 0) {
            return null;
        }

        return round(($actual / $target) * 100, 2);
    }

    /**
     * Calculate achievement percentage for display
     */
    protected function calculateAchievementPercentage($actual, $target): ?float
    {
        return $this->calculateAchievementFromValues($actual, $target);
    }

    /**
     * Get existing data for period
     */
    protected function getExistingData($indicatorId, $year, $month = null, $quarter = null): ?PerformanceData
    {
        $query = PerformanceData::where('performance_indicator_id', $indicatorId)
            ->where('period_year', $year);

        if ($month) {
            $query->where('period_month', $month);
        } elseif ($quarter) {
            $query->where('period_quarter', $quarter);
        }

        return $query->first();
    }

    /**
     * Attach evidence documents
     */
    protected function attachEvidenceDocuments(PerformanceData $performanceData, array $documentIds): void
    {
        $documents = EvidenceDocument::whereIn('id', $documentIds)
            ->where('instansi_id', $performanceData->performanceIndicator->instansi_id)
            ->get();

        foreach ($documents as $document) {
            $performanceData->evidenceDocuments()->attach($document->id, [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Validate performance data rules
     */
    protected function validatePerformanceDataRules(array $data): array
    {
        $errors = [];
        $warnings = [];

        // Check indicator exists and is active
        $indicator = PerformanceIndicator::find($data['performance_indicator_id']);
        if (!$indicator) {
            $errors[] = 'Indikator tidak ditemukan';
            return ['valid' => false, 'errors' => $errors];
        }

        // Check data collection deadline
        $deadline = $this->getSubmissionDeadline($data['period_year'], $data['period_month'] ?? null);
        if (now()->gt($deadline)) {
            $warnings[] = 'Data disampaikan setelah batas waktu pengumpulan';
        }

        // Check for duplicate submissions
        $existingData = $this->getExistingData(
            $data['performance_indicator_id'],
            $data['period_year'],
            $data['period_month'] ?? null,
            $data['period_quarter'] ?? null
        );

        if ($existingData && $existingData->validation_status === 'validated') {
            $errors[] = 'Data untuk periode ini sudah divalidasi dan tidak dapat diubah';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate formula calculation
     */
    protected function validateFormulaCalculation(PerformanceData $performanceData, PerformanceIndicator $indicator): array
    {
        // This is a simplified validation - in real implementation, you would
        // parse and evaluate the formula
        if (strpos($indicator->calculation_formula, $performanceData->actual_value) === false) {
            return [
                'valid' => false,
                'message' => 'Nilai aktual tidak sesuai dengan rumus perhitungan',
            ];
        }

        return ['valid' => true];
    }

    /**
     * Determine severity of validation issues
     */
    protected function determineSeverity(array $issues): string
    {
        if (empty($issues)) {
            return 'none';
        }

        $criticalIssues = ['Nilai aktual tidak boleh kosong', 'Dokumen bukti wajib untuk indikator mandatory'];
        
        foreach ($issues as $issue) {
            if (in_array($issue, $criticalIssues)) {
                return 'critical';
            }
        }

        return 'warning';
    }

    /**
     * Get submission deadline
     */
    protected function getSubmissionDeadline($year, $month = null): Carbon
    {
        if ($month) {
            // Monthly deadline: 7th of next month
            return Carbon::create($year, $month, 7)->addMonth();
        }
        
        // Default deadline: end of year
        return Carbon::create($year, 12, 31);
    }

    /**
     * Format period for display
     */
    protected function formatPeriod($year, $month = null, $quarter = null): string
    {
        if ($month) {
            return Carbon::create($year, $month)->format('F Y');
        } elseif ($quarter) {
            return sprintf('Q%s %s', $quarter, $year);
        }
        
        return (string) $year;
    }

    /**
     * Log activity
     */
    protected function logActivity(string $action, PerformanceData $performanceData, string $description): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'instansi_id' => $performanceData->performanceIndicator->instansi_id,
            'module' => 'sakip',
            'activity' => $action . '_performance_data',
            'description' => $description,
            'old_values' => $action === 'update' ? $performanceData->getOriginal() : null,
            'new_values' => $action !== 'delete' ? $performanceData->toArray() : null,
        ]);
    }

    /**
     * Clear performance data cache
     */
    protected function clearPerformanceDataCache($instansiId): void
    {
        $cacheKeys = [
            "data_collection_progress_{$instansiId}_" . date('Y') . '_' . date('n'),
            "performance_trends_*",
        ];

        foreach ($cacheKeys as $key) {
            if (strpos($key, '*') !== false) {
                // Clear pattern-based cache keys
                $keys = Cache::getRedis()->keys(str_replace('*', '', $key));
                foreach ($keys as $k) {
                    Cache::forget($k);
                }
            } else {
                Cache::forget($key);
            }
        }
    }
}