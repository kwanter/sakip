<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PerformanceData Model
 * 
 * Represents actual performance data submissions.
 * Tracks real performance values, submission status, and validation information.
 */
class PerformanceData extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'performance_data';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'performance_indicator_id',
        'instansi_id',
        'submitted_by',
        'period',
        'actual_value',
        'notes',
        'status',
        'data_quality',
        'validation_notes',
        'validated_by',
        'validated_at',
        'submitted_at',
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
        'instansi_id' => 'string',
        'submitted_by' => 'string',
        'validated_by' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
        'actual_value' => 'decimal:2',
        'validated_at' => 'datetime',
        'submitted_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Get the performance indicator that owns the performance data.
     */
    public function performanceIndicator()
    {
        return $this->belongsTo(PerformanceIndicator::class);
    }

    /**
     * Get the instansi that owns the performance data.
     */
    public function instansi()
    {
        return $this->belongsTo(Instansi::class);
    }

    /**
     * Get the user who submitted the performance data.
     */
    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the user who validated the performance data.
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    /**
     * Get the user who created the performance data.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the performance data.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the evidence documents for the performance data.
     */
    public function evidenceDocuments()
    {
        return $this->hasMany(EvidenceDocument::class);
    }

    /**
     * Get the assessment for the performance data.
     */
    public function assessment()
    {
        return $this->hasOne(Assessment::class);
    }

    /**
     * Scope to get performance data for a specific period.
     */
    public function scopeForPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Scope to get performance data by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get performance data for a specific instansi.
     */
    public function scopeForInstansi($query, int $instansiId)
    {
        return $query->where('instansi_id', $instansiId);
    }

    /**
     * Scope to get performance data for a specific indicator.
     */
    public function scopeForIndicator($query, int $indicatorId)
    {
        return $query->where('performance_indicator_id', $indicatorId);
    }

    /**
     * Scope to get submitted performance data.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope to get validated performance data.
     */
    public function scopeValidated($query)
    {
        return $query->where('status', 'validated');
    }

    /**
     * Check if the performance data is submitted.
     */
    public function isSubmitted()
    {
        return $this->status === 'submitted';
    }

    /**
     * Check if the performance data is validated.
     */
    public function isValidated()
    {
        return $this->status === 'validated';
    }

    /**
     * Check if the performance data has been assessed.
     */
    public function isAssessed()
    {
        return $this->assessment !== null;
    }

    /**
     * Get the target for this performance data.
     */
    public function getTarget()
    {
        $year = substr($this->period, 0, 4);
        return $this->performanceIndicator->getTargetForYear((int)$year);
    }

    /**
     * Calculate the achievement percentage against target.
     */
    public function calculateAchievement()
    {
        $target = $this->getTarget();
        if (!$target || empty($target->target_value)) {
            return null;
        }

        return $target->getAchievementPercentage($this->actual_value);
    }

    /**
     * Get the performance status based on target achievement.
     */
    public function getPerformanceStatus()
    {
        $target = $this->getTarget();
        if (!$target) {
            return 'no_target';
        }

        return $target->getTargetStatus($this->actual_value);
    }

    /**
     * Get the formatted actual value with unit.
     */
    public function getFormattedActualValueAttribute()
    {
        $unit = $this->performanceIndicator->measurement_unit ?? '';
        return number_format($this->actual_value, 2) . ($unit ? ' ' . $unit : '');
    }

    /**
     * Submit the performance data.
     */
    public function submit()
    {
        $this->update([
            'status' => 'submitted',
            'submitted_at' => now(),
            'submitted_by' => auth()->id()
        ]);
    }

    /**
     * Validate the performance data.
     */
    public function validate($notes = null)
    {
        $this->update([
            'status' => 'validated',
            'validation_notes' => $notes,
            'validated_at' => now(),
            'validated_by' => auth()->id()
        ]);
    }

    /**
     * Reject the performance data.
     */
    public function reject($notes = null)
    {
        $this->update([
            'status' => 'rejected',
            'validation_notes' => $notes,
            'validated_at' => now(),
            'validated_by' => auth()->id()
        ]);
    }
}