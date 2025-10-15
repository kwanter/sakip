<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Assessment Model
 * 
 * Represents performance assessments and evaluations.
 * Stores overall scores, comments, and approval status for performance data.
 */
class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assessments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'performance_data_id',
        'assessed_by',
        'overall_score',
        'comments',
        'recommendations',
        'status',
        'assessed_at',
        'approved_at',
        'metadata',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'assessed_by' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
        'overall_score' => 'decimal:2',
        'assessed_at' => 'datetime',
        'approved_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the performance data that owns the assessment.
     */
    public function performanceData()
    {
        return $this->belongsTo(PerformanceData::class);
    }

    /**
     * Get the user who assessed the performance.
     */
    public function assessor()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }

    /**
     * Get the user who created the assessment.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the assessment.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the assessment criteria for the assessment.
     */
    public function assessmentCriteria()
    {
        return $this->hasMany(AssessmentCriterion::class);
    }

    /**
     * Scope to get assessments by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get assessments by assessor.
     */
    public function scopeByAssessor($query, int $assessorId)
    {
        return $query->where('assessed_by', $assessorId);
    }

    /**
     * Scope to get pending assessments.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get completed assessments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get approved assessments.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Check if the assessment is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the assessment is completed.
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the assessment is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Get the performance grade based on score.
     */
    public function getGradeAttribute()
    {
        if ($this->overall_score === null) {
            return null;
        }

        if ($this->overall_score >= 90) {
            return 'A';
        } elseif ($this->overall_score >= 80) {
            return 'B';
        } elseif ($this->overall_score >= 70) {
            return 'C';
        } elseif ($this->overall_score >= 60) {
            return 'D';
        } else {
            return 'E';
        }
    }

    /**
     * Get the performance level description.
     */
    public function getPerformanceLevelAttribute()
    {
        if ($this->overall_score === null) {
            return null;
        }

        if ($this->overall_score >= 90) {
            return 'Excellent';
        } elseif ($this->overall_score >= 80) {
            return 'Good';
        } elseif ($this->overall_score >= 70) {
            return 'Satisfactory';
        } elseif ($this->overall_score >= 60) {
            return 'Needs Improvement';
        } else {
            return 'Poor';
        }
    }

    /**
     * Complete the assessment.
     */
    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'assessed_at' => now()
        ]);
    }

    /**
     * Approve the assessment.
     */
    public function approve()
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now()
        ]);
    }

    /**
     * Reject the assessment.
     */
    public function reject()
    {
        $this->update([
            'status' => 'rejected'
        ]);
    }

    /**
     * Calculate weighted score from criteria.
     */
    public function calculateWeightedScore()
    {
        $criteria = $this->assessmentCriteria;
        if ($criteria->isEmpty()) {
            return null;
        }

        $totalWeightedScore = 0;
        $totalWeight = 0;

        foreach ($criteria as $criterion) {
            $totalWeightedScore += $criterion->score * $criterion->weight;
            $totalWeight += $criterion->weight;
        }

        return $totalWeight > 0 ? round($totalWeightedScore / $totalWeight, 2) : null;
    }

    /**
     * Add assessment criterion.
     */
    public function addCriterion($name, $score, $weight = 1.0, $justification = null)
    {
        return $this->assessmentCriteria()->create([
            'criteria_name' => $name,
            'score' => $score,
            'weight' => $weight,
            'justification' => $justification
        ]);
    }
}