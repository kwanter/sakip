<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * AssessmentCriterion Model
 * 
 * Represents detailed assessment criteria and scores.
 * Stores individual criterion scores with weights and justifications.
 */
class AssessmentCriterion extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'assessment_criteria';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'assessment_id',
        'criteria_name',
        'score',
        'weight',
        'justification',
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
        'score' => 'decimal:2',
        'weight' => 'decimal:2',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the assessment that owns the criterion.
     */
    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    /**
     * Get the user who created the criterion.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the criterion.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the weighted score for this criterion.
     */
    public function getWeightedScoreAttribute()
    {
        return round($this->score * $this->weight, 2);
    }

    /**
     * Get the score grade based on score range.
     */
    public function getGradeAttribute()
    {
        if ($this->score >= 90) {
            return 'A';
        } elseif ($this->score >= 80) {
            return 'B';
        } elseif ($this->score >= 70) {
            return 'C';
        } elseif ($this->score >= 60) {
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
        if ($this->score >= 90) {
            return 'Excellent';
        } elseif ($this->score >= 80) {
            return 'Good';
        } elseif ($this->score >= 70) {
            return 'Satisfactory';
        } elseif ($this->score >= 60) {
            return 'Needs Improvement';
        } else {
            return 'Poor';
        }
    }

    /**
     * Scope to get criteria by assessment.
     */
    public function scopeByAssessment($query, int $assessmentId)
    {
        return $query->where('assessment_id', $assessmentId);
    }

    /**
     * Scope to get criteria by name.
     */
    public function scopeByName($query, string $name)
    {
        return $query->where('criteria_name', $name);
    }

    /**
     * Scope to get high-scoring criteria.
     */
    public function scopeHighScore($query, float $threshold = 80)
    {
        return $query->where('score', '>=', $threshold);
    }

    /**
     * Scope to get low-scoring criteria.
     */
    public function scopeLowScore($query, float $threshold = 60)
    {
        return $query->where('score', '<', $threshold);
    }

    /**
     * Update the criterion score and recalculate assessment overall score.
     */
    public function updateScore($newScore, $justification = null)
    {
        $this->update([
            'score' => $newScore,
            'justification' => $justification ?? $this->justification
        ]);

        // Recalculate assessment overall score
        if ($this->assessment) {
            $newOverallScore = $this->assessment->calculateWeightedScore();
            $this->assessment->update(['overall_score' => $newOverallScore]);
        }
    }

    /**
     * Check if the criterion score is excellent.
     */
    public function isExcellent()
    {
        return $this->score >= 90;
    }

    /**
     * Check if the criterion score is good.
     */
    public function isGood()
    {
        return $this->score >= 80 && $this->score < 90;
    }

    /**
     * Check if the criterion score needs improvement.
     */
    public function needsImprovement()
    {
        return $this->score < 70;
    }

    /**
     * Get the formatted score with percentage.
     */
    public function getFormattedScoreAttribute()
    {
        return number_format($this->score, 2) . '%';
    }

    /**
     * Get the formatted weight.
     */
    public function getFormattedWeightAttribute()
    {
        return number_format($this->weight, 2);
    }
}