<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * Target Model
 *
 * Represents performance targets for specific years and indicators.
 * Tracks target values, minimum thresholds, and justifications.
 */
class Target extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Indicates if the model should be auto-discovered for policies.
     *
     * @var bool
     */
    protected static $discoverPolicies = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "targets";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        "performance_indicator_id",
        "year",
        "target_value",
        "minimum_value",
        "justification",
        "status",
        "approved_by",
        "approved_at",
        "notes",
        "metadata",
        "created_by",
        "updated_by",
    ];

    // Protected fields - set automatically
    protected $guarded = ["id", "created_at", "updated_at", "deleted_at"];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "target_value" => "decimal:2",
        "minimum_value" => "decimal:2",
        "approved_at" => "datetime",
        "metadata" => "array",
        "created_at" => "datetime",
        "updated_at" => "datetime",
        "deleted_at" => "datetime",
    ];

    /**
     * Get the performance indicator that owns the target.
     */
    public function performanceIndicator()
    {
        return $this->belongsTo(PerformanceIndicator::class);
    }

    /**
     * Get the user who approved the target.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, "approved_by");
    }

    /**
     * Get the user who created the target.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /**
     * Get the user who last updated the target.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, "updated_by");
    }

    /**
     * Scope to get targets for a specific year.
     */
    public function scopeForYear($query, int $year)
    {
        return $query->where("year", $year);
    }

    /**
     * Scope to get targets by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where("status", $status);
    }

    /**
     * Scope to get approved targets.
     */
    public function scopeApproved($query)
    {
        return $query->where("status", "approved");
    }

    /**
     * Check if the target is approved.
     */
    public function isApproved()
    {
        return $this->status === "approved";
    }

    /**
     * Get the achievement percentage against actual value.
     */
    public function getAchievementPercentage($actualValue)
    {
        if (empty($this->target_value) || $this->target_value == 0) {
            return 0;
        }

        return round(($actualValue / $this->target_value) * 100, 2);
    }

    /**
     * Check if the actual value meets the minimum threshold.
     */
    public function meetsMinimumThreshold($actualValue)
    {
        if (empty($this->minimum_value)) {
            return true; // No minimum threshold set
        }

        return $actualValue >= $this->minimum_value;
    }

    /**
     * Get the target status based on actual value.
     */
    public function getTargetStatus($actualValue)
    {
        if ($this->target_value == 0) {
            return "no_target";
        }

        $achievement = $this->getAchievementPercentage($actualValue);

        if ($achievement >= 100) {
            return "achieved";
        } elseif ($achievement >= 80) {
            return "partially_achieved";
        } elseif ($this->meetsMinimumThreshold($actualValue)) {
            return "minimum_met";
        } else {
            return "not_achieved";
        }
    }

    /**
     * Get the formatted target value with unit.
     */
    public function getFormattedTargetAttribute()
    {
        $unit = $this->performanceIndicator->measurement_unit ?? "";
        return number_format($this->target_value, 2) .
            ($unit ? " " . $unit : "");
    }

    /**
     * Get the formatted minimum value with unit.
     */
    public function getFormattedMinimumAttribute()
    {
        if (empty($this->minimum_value)) {
            return null;
        }

        $unit = $this->performanceIndicator->measurement_unit ?? "";
        return number_format($this->minimum_value, 2) .
            ($unit ? " " . $unit : "");
    }

    /**
     * Get the policy class for this model.
     * Override to prevent auto-discovery conflicts.
     */
    public function resolvePolicy()
    {
        return \App\Policies\TargetPolicy::class;
    }
}
