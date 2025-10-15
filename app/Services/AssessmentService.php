<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\AssessmentCriterion;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\Instansi;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class AssessmentService
{
    protected $cacheTimeout = 3600; // 1 hour

    /**
     * Create new assessment
     */
    public function createAssessment(array $data): Assessment
    {
        return DB::transaction(function () use ($data) {
            // Validate data
            $validator = $this->validateAssessmentData($data);
            if ($validator->fails()) {
                throw new Exception('Validation failed: ' . $validator->errors()->first());
            }

            // Check if assessment already exists for this period
            $existingAssessment = $this->getExistingAssessment(
                $data['instansi_id'],
                $data['assessment_period'],
                $data['assessment_year']
            );

            if ($existingAssessment) {
                throw new Exception('Assessment already exists for this period');
            }

            // Create assessment
            $assessment = Assessment::create([
                'instansi_id' => $data['instansi_id'],
                'assessment_period' => $data['assessment_period'],
                'assessment_year' => $data['assessment_year'],
                'assessment_type' => $data['assessment_type'] ?? 'regular',
                'overall_score' => 0,
                'overall_rating' => 'pending',
                'status' => 'draft',
                'assessed_by' => auth()->id(),
                'assessment_date' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            // Create assessment criteria based on performance indicators
            $this->createAssessmentCriteria($assessment);

            // Log activity
            $this->logActivity('create', $assessment, 'Assessment created');

            // Clear cache
            $this->clearAssessmentCache($data['instansi_id']);

            return $assessment->fresh(['criteria', 'instansi']);
        });
    }

    /**
     * Update assessment scoring
     */
    public function updateAssessmentScoring(Assessment $assessment, array $criteriaData): Assessment
    {
        return DB::transaction(function () use ($assessment, $criteriaData) {
            if (!in_array($assessment->status, ['draft', 'in_review'])) {
                throw new Exception('Cannot update scoring for assessment in current status');
            }

            $totalScore = 0;
            $totalWeight = 0;

            foreach ($criteriaData as $criterionData) {
                $criterion = AssessmentCriterion::find($criterionData['id']);
                if (!$criterion || $criterion->assessment_id !== $assessment->id) {
                    throw new Exception('Invalid criterion ID');
                }

                // Update criterion scoring
                $criterion->update([
                    'score' => $criterionData['score'],
                    'rating' => $this->calculateRating($criterionData['score']),
                    'evidence' => $criterionData['evidence'] ?? null,
                    'notes' => $criterionData['notes'] ?? null,
                    'assessed_by' => auth()->id(),
                ]);

                $totalScore += $criterionData['score'] * $criterion->weight;
                $totalWeight += $criterion->weight;
            }

            // Calculate overall score and rating
            $overallScore = $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : 0;
            $overallRating = $this->calculateRating($overallScore);

            // Update assessment
            $assessment->update([
                'overall_score' => $overallScore,
                'overall_rating' => $overallRating,
                'status' => 'in_review',
                'updated_by' => auth()->id(),
            ]);

            // Log activity
            $this->logActivity('update_scoring', $assessment, 'Assessment scoring updated');

            // Clear cache
            $this->clearAssessmentCache($assessment->instansi_id);

            return $assessment->fresh(['criteria']);
        });
    }

    /**
     * Submit assessment for review
     */
    public function submitAssessment(Assessment $assessment): Assessment
    {
        return DB::transaction(function () use ($assessment) {
            if ($assessment->status !== 'in_review') {
                throw new Exception('Assessment must be in review status before submission');
            }

            if ($assessment->criteria->whereNull('score')->count() > 0) {
                throw new Exception('All criteria must be scored before submission');
            }

            // Update status
            $assessment->update([
                'status' => 'submitted',
                'submission_date' => now(),
                'submitted_by' => auth()->id(),
            ]);

            // Log activity
            $this->logActivity('submit', $assessment, 'Assessment submitted for review');

            // Clear cache
            $this->clearAssessmentCache($assessment->instansi_id);

            return $assessment->fresh(['criteria', 'instansi']);
        });
    }

    /**
     * Review assessment (for supervisor/approver)
     */
    public function reviewAssessment(Assessment $assessment, array $reviewData): Assessment
    {
        return DB::transaction(function () use ($assessment, $reviewData) {
            if (!in_array($assessment->status, ['submitted', 'in_approval'])) {
                throw new Exception('Assessment cannot be reviewed in current status');
            }

            $validator = Validator::make($reviewData, [
                'review_decision' => 'required|in:approved,rejected,needs_revision',
                'review_notes' => 'required_if:review_decision,rejected,needs_revision|string|max:1000',
            ]);

            if ($validator->fails()) {
                throw new Exception('Validation failed: ' . $validator->errors()->first());
            }

            $newStatus = $reviewData['review_decision'] === 'approved' ? 'approved' : 
                        ($reviewData['review_decision'] === 'rejected' ? 'rejected' : 'draft');

            $assessment->update([
                'status' => $newStatus,
                'review_notes' => $reviewData['review_notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'review_date' => now(),
            ]);

            // Log activity
            $this->logActivity('review', $assessment, 'Assessment reviewed: ' . $reviewData['review_decision']);

            // Clear cache
            $this->clearAssessmentCache($assessment->instansi_id);

            return $assessment->fresh(['criteria', 'instansi']);
        });
    }

    /**
     * Get assessments with filters
     */
    public function getAssessments(array $filters = [], $perPage = 15)
    {
        $query = Assessment::with(['instansi', 'criteria', 'assessedBy', 'reviewedBy']);

        // Apply filters
        if (isset($filters['instansi_id'])) {
            $query->where('instansi_id', $filters['instansi_id']);
        }

        if (isset($filters['assessment_period'])) {
            $query->where('assessment_period', $filters['assessment_period']);
        }

        if (isset($filters['assessment_year'])) {
            $query->where('assessment_year', $filters['assessment_year']);
        }

        if (isset($filters['assessment_type'])) {
            $query->where('assessment_type', $filters['assessment_type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['rating'])) {
            $query->where('overall_rating', $filters['rating']);
        }

        if (isset($filters['assessed_by'])) {
            $query->where('assessed_by', $filters['assessed_by']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereHas('instansi', function ($q2) use ($filters) {
                    $q2->where('name', 'like', '%' . $filters['search'] . '%');
                });
            });
        }

        // Date range filters
        if (isset($filters['date_from'])) {
            $query->whereDate('assessment_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('assessment_date', '<=', $filters['date_to']);
        }

        // Score range filters
        if (isset($filters['min_score'])) {
            $query->where('overall_score', '>=', $filters['min_score']);
        }

        if (isset($filters['max_score'])) {
            $query->where('overall_score', '<=', $filters['max_score']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'assessment_date';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get assessment by ID with relationships
     */
    public function getAssessmentById($id): ?Assessment
    {
        return Cache::remember("assessment_{$id}", $this->cacheTimeout, function () use ($id) {
            return Assessment::with([
                'instansi',
                'criteria.performanceIndicator',
                'criteria.evidence',
                'assessedBy',
                'reviewedBy',
                'submittedBy',
            ])->find($id);
        });
    }

    /**
     * Get assessment statistics
     */
    public function getAssessmentStatistics($instansiId = null, $year = null): array
    {
        $year = $year ?? date('Y');
        $cacheKey = "assessment_statistics_{$instansiId}_{$year}";

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use ($instansiId, $year) {
            $query = Assessment::query();

            if ($instansiId) {
                $query->where('instansi_id', $instansiId);
            }

            if ($year) {
                $query->where('assessment_year', $year);
            }

            $totalAssessments = $query->count();
            $assessmentsByStatus = $query->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $assessmentsByRating = $query->select('overall_rating', DB::raw('count(*) as count'))
                ->whereNotNull('overall_rating')
                ->groupBy('overall_rating')
                ->pluck('count', 'overall_rating')
                ->toArray();

            $avgScore = $query->whereNotNull('overall_score')->avg('overall_score');

            return [
                'total_assessments' => $totalAssessments,
                'assessments_by_status' => $assessmentsByStatus,
                'assessments_by_rating' => $assessmentsByRating,
                'average_score' => round($avgScore, 2),
                'completion_rate' => $this->calculateAssessmentCompletionRate($instansiId, $year),
            ];
        });
    }

    /**
     * Get pending assessments
     */
    public function getPendingAssessments($instansiId = null, $year = null): array
    {
        $year = $year ?? date('Y');
        $query = Assessment::with(['instansi', 'criteria'])
            ->where('assessment_year', $year)
            ->whereIn('status', ['draft', 'in_review']);

        if ($instansiId) {
            $query->where('instansi_id', $instansiId);
        }

        return $query->get()
            ->map(function ($assessment) {
                return [
                    'id' => $assessment->id,
                    'instansi_name' => $assessment->instansi->name,
                    'period' => $assessment->assessment_period,
                    'status' => $assessment->status,
                    'progress' => $this->calculateAssessmentProgress($assessment),
                    'due_date' => $this->getAssessmentDueDate($assessment),
                    'days_overdue' => $this->calculateDaysOverdue($assessment),
                ];
            })
            ->toArray();
    }

    /**
     * Create assessment criteria based on performance indicators
     */
    protected function createAssessmentCriteria(Assessment $assessment): void
    {
        $indicators = PerformanceIndicator::where('instansi_id', $assessment->instansi_id)
            ->where('is_mandatory', true)
            ->orWhere(function ($query) {
                $query->where('is_mandatory', false)
                    ->whereHas('performanceData');
            })
            ->get();

        foreach ($indicators as $indicator) {
            AssessmentCriterion::create([
                'assessment_id' => $assessment->id,
                'performance_indicator_id' => $indicator->id,
                'criterion_name' => $indicator->name,
                'criterion_code' => $indicator->code,
                'weight' => $indicator->weight ?? 1,
                'max_score' => 100,
                'score' => null,
                'rating' => null,
                'evidence' => null,
                'notes' => null,
            ]);
        }
    }

    /**
     * Calculate rating based on score
     */
    protected function calculateRating($score): string
    {
        if ($score >= 90) return 'excellent';
        if ($score >= 80) return 'good';
        if ($score >= 70) return 'fair';
        if ($score >= 60) return 'poor';
        return 'very_poor';
    }

    /**
     * Calculate assessment progress
     */
    protected function calculateAssessmentProgress(Assessment $assessment): int
    {
        $totalCriteria = $assessment->criteria->count();
        $scoredCriteria = $assessment->criteria->whereNotNull('score')->count();

        return $totalCriteria > 0 ? round(($scoredCriteria / $totalCriteria) * 100) : 0;
    }

    /**
     * Calculate assessment completion rate
     */
    protected function calculateAssessmentCompletionRate($instansiId = null, $year = null): float
    {
        $query = Assessment::query();

        if ($instansiId) {
            $query->where('instansi_id', $instansiId);
        }

        if ($year) {
            $query->where('assessment_year', $year);
        }

        $total = $query->count();
        $completed = $query->where('status', 'approved')->count();

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    /**
     * Get assessment due date
     */
    protected function getAssessmentDueDate(Assessment $assessment): Carbon
    {
        // Default due date: end of assessment period + 30 days
        $baseDate = Carbon::create($assessment->assessment_year, 6, 30);
        if ($assessment->assessment_period === 'second_semester') {
            $baseDate = Carbon::create($assessment->assessment_year, 12, 31);
        }

        return $baseDate->addDays(30);
    }

    /**
     * Calculate days overdue
     */
    protected function calculateDaysOverdue(Assessment $assessment): int
    {
        $dueDate = $this->getAssessmentDueDate($assessment);
        return max(0, Carbon::now()->diffInDays($dueDate, false));
    }

    /**
     * Get existing assessment for period
     */
    protected function getExistingAssessment($instansiId, $period, $year): ?Assessment
    {
        return Assessment::where('instansi_id', $instansiId)
            ->where('assessment_period', $period)
            ->where('assessment_year', $year)
            ->first();
    }

    /**
     * Validate assessment data
     */
    protected function validateAssessmentData(array $data): \Illuminate\Contracts\Validation\Validator
    {
        return Validator::make($data, [
            'instansi_id' => 'required|exists:instansi,id',
            'assessment_period' => 'required|in:first_semester,second_semester',
            'assessment_year' => 'required|integer|min:2020|max:2030',
            'assessment_type' => 'nullable|in:regular,extraordinary,compliance',
            'notes' => 'nullable|string|max:1000',
        ]);
    }

    /**
     * Log activity
     */
    protected function logActivity(string $action, Assessment $assessment, string $description): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'instansi_id' => $assessment->instansi_id,
            'module' => 'sakip',
            'activity' => $action . '_assessment',
            'description' => $description,
            'old_values' => $action === 'update' ? $assessment->getOriginal() : null,
            'new_values' => $action !== 'delete' ? $assessment->toArray() : null,
        ]);
    }

    /**
     * Clear assessment cache
     */
    protected function clearAssessmentCache($instansiId): void
    {
        Cache::forget("assessment_statistics_{$instansiId}_" . date('Y'));
        
        // Clear all assessment caches for this instansi
        $keys = Cache::getRedis()->keys("assessment_*");
        foreach ($keys as $key) {
            if (strpos($key, "_{$instansiId}_") !== false) {
                Cache::forget($key);
            }
        }
    }
}