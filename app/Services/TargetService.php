<?php

namespace App\Services;

use App\Models\Target;
use App\Models\PerformanceIndicator;
use App\Models\Instansi;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class TargetService
{
    protected $cacheTimeout = 3600; // 1 hour

    /**
     * Create target
     */
    public function createTarget(array $data): Target
    {
        return DB::transaction(function () use ($data) {
            // Validate data
            $validator = $this->validateTargetData($data);
            if ($validator->fails()) {
                throw new Exception('Validation failed: ' . $validator->errors()->first());
            }

            // Check if target already exists for the same indicator and period
            if ($this->targetExists($data['performance_indicator_id'], $data['target_year'], $data['target_period'])) {
                throw new Exception('Target already exists for this indicator and period');
            }

            // Get performance indicator
            $indicator = PerformanceIndicator::find($data['performance_indicator_id']);
            if (!$indicator) {
                throw new Exception('Performance indicator not found');
            }

            // Validate target value based on indicator type
            $this->validateTargetValue($data['target_value'], $indicator);

            // Create target
            $target = Target::create([
                'performance_indicator_id' => $data['performance_indicator_id'],
                'instansi_id' => $indicator->instansi_id,
                'target_year' => $data['target_year'],
                'target_period' => $data['target_period'],
                'target_value' => $data['target_value'],
                'target_description' => $data['target_description'] ?? null,
                'measurement_unit' => $data['measurement_unit'] ?? $indicator->measurement_unit,
                'calculation_method' => $data['calculation_method'] ?? 'percentage',
                'baseline_value' => $data['baseline_value'] ?? 0,
                'stretch_value' => $data['stretch_value'] ?? null,
                'minimum_value' => $data['minimum_value'] ?? null,
                'maximum_value' => $data['maximum_value'] ?? null,
                'weight' => $data['weight'] ?? 1,
                'is_mandatory' => $data['is_mandatory'] ?? false,
                'set_by' => auth()->id(),
                'set_at' => now(),
                'approved_by' => $data['approved_by'] ?? null,
                'approved_at' => $data['approved_at'] ?? null,
                'approval_status' => $data['approval_status'] ?? 'pending',
                'approval_notes' => $data['approval_notes'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            // Log activity
            $this->logActivity('create', $target, 'Target created');

            // Clear cache
            $this->clearTargetCache($indicator->instansi_id);

            // Notify relevant users
            $this->notifyTargetCreated($target);

            return $target->fresh(['performanceIndicator', 'instansi', 'setBy', 'approvedBy']);
        });
    }

    /**
     * Update target
     */
    public function updateTarget(Target $target, array $data): Target
    {
        return DB::transaction(function () use ($target, $data) {
            // Validate data
            $validator = $this->validateTargetUpdateData($data, $target);
            if ($validator->fails()) {
                throw new Exception('Validation failed: ' . $validator->errors()->first());
            }

            // Get performance indicator
            $indicator = $target->performanceIndicator;

            // Validate target value if provided
            if (isset($data['target_value'])) {
                $this->validateTargetValue($data['target_value'], $indicator);
            }

            // Check if target period is being changed and if new period already has a target
            if (isset($data['target_year']) || isset($data['target_period'])) {
                $newYear = $data['target_year'] ?? $target->target_year;
                $newPeriod = $data['target_period'] ?? $target->target_period;
                
                if ($this->targetExists($target->performance_indicator_id, $newYear, $newPeriod, $target->id)) {
                    throw new Exception('Target already exists for this indicator and period');
                }
            }

            // Store old values for logging
            $oldValues = $target->toArray();

            // Update target
            $target->update($data);

            // Log activity
            $this->logActivity('update', $target, 'Target updated', $oldValues, $target->toArray());

            // Clear cache
            $this->clearTargetCache($target->instansi_id);

            // Notify relevant users
            $this->notifyTargetUpdated($target);

            return $target->fresh(['performanceIndicator', 'instansi', 'setBy', 'approvedBy']);
        });
    }

    /**
     * Approve target
     */
    public function approveTarget(Target $target, string $approvalStatus, string $notes = null): Target
    {
        return DB::transaction(function () use ($target, $approvalStatus, $notes) {
            if (!in_array($approvalStatus, ['approved', 'rejected', 'pending'])) {
                throw new Exception('Invalid approval status');
            }

            $oldStatus = $target->approval_status;

            $target->update([
                'approval_status' => $approvalStatus,
                'approval_notes' => $notes,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Log activity
            $this->logActivity('approve', $target, "Target {$approvalStatus}: {$notes}");

            // Clear cache
            $this->clearTargetCache($target->instansi_id);

            // Notify relevant users
            $this->notifyTargetApproved($target, $oldStatus, $approvalStatus);

            return $target->fresh(['performanceIndicator', 'instansi', 'setBy', 'approvedBy']);
        });
    }

    /**
     * Delete target
     */
    public function deleteTarget(Target $target): bool
    {
        return DB::transaction(function () use ($target) {
            // Check if target can be deleted
            if ($this->hasAssociatedData($target)) {
                throw new Exception('Cannot delete target that has associated performance data');
            }

            // Log activity
            $this->logActivity('delete', $target, 'Target deleted');

            // Delete target
            $result = $target->delete();

            // Clear cache
            $this->clearTargetCache($target->instansi_id);

            return $result;
        });
    }

    /**
     * Get target by ID
     */
    public function getTarget($id): ?Target
    {
        return Cache::remember("target_{$id}", $this->cacheTimeout, function () use ($id) {
            return Target::with(['performanceIndicator', 'instansi', 'setBy', 'approvedBy'])->find($id);
        });
    }

    /**
     * Get targets with filters
     */
    public function getTargets(array $filters = [], $perPage = 15)
    {
        $query = Target::with(['performanceIndicator', 'instansi', 'setBy', 'approvedBy']);

        // Apply filters
        if (isset($filters['instansi_id'])) {
            $query->where('instansi_id', $filters['instansi_id']);
        }

        if (isset($filters['performance_indicator_id'])) {
            $query->where('performance_indicator_id', $filters['performance_indicator_id']);
        }

        if (isset($filters['target_year'])) {
            $query->where('target_year', $filters['target_year']);
        }

        if (isset($filters['target_period'])) {
            $query->where('target_period', $filters['target_period']);
        }

        if (isset($filters['approval_status'])) {
            $query->where('approval_status', $filters['approval_status']);
        }

        if (isset($filters['is_mandatory'])) {
            $query->where('is_mandatory', $filters['is_mandatory']);
        }

        if (isset($filters['set_by'])) {
            $query->where('set_by', $filters['set_by']);
        }

        if (isset($filters['approved_by'])) {
            $query->where('approved_by', $filters['approved_by']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('target_description', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('notes', 'like', '%' . $filters['search'] . '%')
                    ->orWhereHas('performanceIndicator', function ($q2) use ($filters) {
                        $q2->where('name', 'like', '%' . $filters['search'] . '%')
                            ->orWhere('code', 'like', '%' . $filters['search'] . '%');
                    })
                    ->orWhereHas('instansi', function ($q2) use ($filters) {
                        $q2->where('name', 'like', '%' . $filters['search'] . '%');
                    });
            });
        }

        // Date range filters
        if (isset($filters['set_from'])) {
            $query->whereDate('set_at', '>=', $filters['set_from']);
        }

        if (isset($filters['set_to'])) {
            $query->whereDate('set_at', '<=', $filters['set_to']);
        }

        if (isset($filters['approved_from'])) {
            $query->whereDate('approved_at', '>=', $filters['approved_from']);
        }

        if (isset($filters['approved_to'])) {
            $query->whereDate('approved_at', '<=', $filters['approved_to']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'set_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Create targets from template
     */
    public function createTargetsFromTemplate(array $templateData): array
    {
        $results = [];
        $errors = [];

        foreach ($templateData['targets'] as $targetData) {
            try {
                $data = array_merge($targetData, [
                    'target_year' => $templateData['target_year'],
                    'target_period' => $templateData['target_period'],
                    'approval_status' => 'pending',
                ]);

                $results[] = $this->createTarget($data);
            } catch (Exception $e) {
                $errors[] = [
                    'indicator_id' => $targetData['performance_indicator_id'] ?? null,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $results,
            'errors' => $errors,
            'total' => count($templateData['targets']),
            'success_count' => count($results),
            'error_count' => count($errors),
        ];
    }

    /**
     * Copy targets from previous period
     */
    public function copyTargetsFromPreviousPeriod($instansiId, $sourceYear, $sourcePeriod, $targetYear, $targetPeriod): array
    {
        $results = [];
        $errors = [];

        // Get existing targets for the source period
        $sourceTargets = Target::where('instansi_id', $instansiId)
            ->where('target_year', $sourceYear)
            ->where('target_period', $sourcePeriod)
            ->where('approval_status', 'approved')
            ->get();

        foreach ($sourceTargets as $sourceTarget) {
            try {
                // Check if target already exists for target period
                if ($this->targetExists($sourceTarget->performance_indicator_id, $targetYear, $targetPeriod)) {
                    throw new Exception('Target already exists for this indicator and period');
                }

                // Create new target based on source
                $newTarget = Target::create([
                    'performance_indicator_id' => $sourceTarget->performance_indicator_id,
                    'instansi_id' => $sourceTarget->instansi_id,
                    'target_year' => $targetYear,
                    'target_period' => $targetPeriod,
                    'target_value' => $sourceTarget->target_value,
                    'target_description' => $sourceTarget->target_description . ' (Copied from ' . $sourceYear . ' ' . $sourcePeriod . ')',
                    'measurement_unit' => $sourceTarget->measurement_unit,
                    'calculation_method' => $sourceTarget->calculation_method,
                    'baseline_value' => $sourceTarget->baseline_value,
                    'stretch_value' => $sourceTarget->stretch_value,
                    'minimum_value' => $sourceTarget->minimum_value,
                    'maximum_value' => $sourceTarget->maximum_value,
                    'weight' => $sourceTarget->weight,
                    'is_mandatory' => $sourceTarget->is_mandatory,
                    'set_by' => auth()->id(),
                    'set_at' => now(),
                    'approval_status' => 'pending',
                    'notes' => 'Copied from ' . $sourceYear . ' ' . $sourcePeriod,
                ]);

                $results[] = $newTarget;
            } catch (Exception $e) {
                $errors[] = [
                    'indicator_id' => $sourceTarget->performance_indicator_id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'success' => $results,
            'errors' => $errors,
            'total' => $sourceTargets->count(),
            'success_count' => count($results),
            'error_count' => count($errors),
        ];
    }

    /**
     * Get target statistics
     */
    public function getTargetStatistics($instansiId = null, $year = null): array
    {
        $cacheKey = "target_statistics_{$instansiId}_{$year}";
        $year = $year ?? date('Y');
        
        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($instansiId, $year) {
            $query = Target::where('target_year', $year);
            
            if ($instansiId) {
                $query->where('instansi_id', $instansiId);
            }

            return [
                'total_targets' => $query->count(),
                'approved' => $query->where('approval_status', 'approved')->count(),
                'rejected' => $query->where('approval_status', 'rejected')->count(),
                'pending' => $query->where('approval_status', 'pending')->count(),
                'mandatory' => $query->where('is_mandatory', true)->count(),
                'by_period' => $query->select('target_period', DB::raw('count(*) as count'))
                    ->groupBy('target_period')
                    ->pluck('count', 'target_period')
                    ->toArray(),
                'by_approval_status' => $query->select('approval_status', DB::raw('count(*) as count'))
                    ->groupBy('approval_status')
                    ->pluck('count', 'approval_status')
                    ->toArray(),
            ];
        });
    }

    /**
     * Check if target exists
     */
    protected function targetExists($indicatorId, $year, $period, $excludeId = null): bool
    {
        $query = Target::where('performance_indicator_id', $indicatorId)
            ->where('target_year', $year)
            ->where('target_period', $period);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    /**
     * Validate target value
     */
    protected function validateTargetValue($value, PerformanceIndicator $indicator): void
    {
        if (!is_numeric($value) || $value < 0) {
            throw new Exception('Target value must be a positive number');
        }

        // Additional validation based on indicator type could be added here
        // For example, percentage indicators should have values between 0-100
    }

    /**
     * Check if target has associated data
     */
    protected function hasAssociatedData(Target $target): bool
    {
        return $target->performanceData()->exists();
    }

    /**
     * Validate target data
     */
    protected function validateTargetData(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'performance_indicator_id' => 'required|exists:performance_indicators,id',
            'target_year' => 'required|integer|min:2020|max:2030',
            'target_period' => 'required|string|in:first_semester,second_semester,quarterly',
            'target_value' => 'required|numeric|min:0',
            'target_description' => 'nullable|string|max:1000',
            'measurement_unit' => 'nullable|string|max:100',
            'calculation_method' => 'nullable|string|in:percentage,absolute,difference,ratio',
            'baseline_value' => 'nullable|numeric',
            'stretch_value' => 'nullable|numeric',
            'minimum_value' => 'nullable|numeric',
            'maximum_value' => 'nullable|numeric',
            'weight' => 'nullable|numeric|min:0|max:100',
            'is_mandatory' => 'nullable|boolean',
            'notes' => 'nullable|string|max:2000',
        ]);
    }

    /**
     * Validate target update data
     */
    protected function validateTargetUpdateData(array $data, Target $target): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'target_value' => 'nullable|numeric|min:0',
            'target_description' => 'nullable|string|max:1000',
            'measurement_unit' => 'nullable|string|max:100',
            'calculation_method' => 'nullable|string|in:percentage,absolute,difference,ratio',
            'baseline_value' => 'nullable|numeric',
            'stretch_value' => 'nullable|numeric',
            'minimum_value' => 'nullable|numeric',
            'maximum_value' => 'nullable|numeric',
            'weight' => 'nullable|numeric|min:0|max:100',
            'is_mandatory' => 'nullable|boolean',
            'notes' => 'nullable|string|max:2000',
            'target_year' => 'nullable|integer|min:2020|max:2030',
            'target_period' => 'nullable|string|in:first_semester,second_semester,quarterly',
        ]);
    }

    /**
     * Notify target created
     */
    protected function notifyTargetCreated(Target $target): void
    {
        // This would typically use a notification service
        // For now, we'll just log the notification
        \Log::info('Target created notification', [
            'target_id' => $target->id,
            'indicator_id' => $target->performance_indicator_id,
            'instansi_id' => $target->instansi_id,
            'set_by' => auth()->id(),
        ]);
    }

    /**
     * Notify target updated
     */
    protected function notifyTargetUpdated(Target $target): void
    {
        // This would typically use a notification service
        \Log::info('Target updated notification', [
            'target_id' => $target->id,
            'indicator_id' => $target->performance_indicator_id,
            'instansi_id' => $target->instansi_id,
            'updated_by' => auth()->id(),
        ]);
    }

    /**
     * Notify target approved
     */
    protected function notifyTargetApproved(Target $target, string $oldStatus, string $newStatus): void
    {
        // This would typically use a notification service
        \Log::info('Target approved notification', [
            'target_id' => $target->id,
            'indicator_id' => $target->performance_indicator_id,
            'instansi_id' => $target->instansi_id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'approved_by' => auth()->id(),
        ]);
    }

    /**
     * Clear target cache
     */
    protected function clearTargetCache($instansiId): void
    {
        Cache::forget("target_statistics_{$instansiId}_" . date('Y'));
        
        // Clear all target caches for this instansi
        $keys = Cache::getRedis()->keys("target_*");
        foreach ($keys as $key) {
            if (strpos($key, "_{$instansiId}") !== false) {
                Cache::forget($key);
            }
        }
    }

    /**
     * Log activity
     */
    protected function logActivity(string $action, Target $target, string $description, array $oldValues = null, array $newValues = null): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'instansi_id' => $target->instansi_id,
            'module' => 'sakip',
            'activity' => $action . '_target',
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}