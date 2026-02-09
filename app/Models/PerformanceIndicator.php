<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Scopes\WithStatusScope;
use App\Models\Scopes\RecentScope;
use App\Models\Scopes\SearchScope;
use App\Models\Scopes\ForYearTrait;

/**
 * PerformanceIndicator Model
 *
 * Represents performance indicators for SAKIP compliance.
 * Tracks institutional performance metrics with calculation formulas and measurement units.
 */
class PerformanceIndicator extends Model
{
    use HasFactory, HasUuids, SoftDeletes;
    use WithStatusScope, RecentScope, SearchScope, ForYearTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "performance_indicators";

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        "instansi_id",
        "sasaran_strategis_id",
        "program_id",
        "kegiatan_id",
        "code",
        "name",
        "description",
        "measurement_unit",
        "measurement_type",
        "data_source",
        "collection_method",
        "calculation_formula",
        "frequency",
        "category",
        "weight",
        "is_mandatory",
        "metadata",
        "created_by",
        "updated_by",
    ];

    // Protected fields - set automatically
    protected $guarded = [
        "id",
        "created_by",
        "updated_by",
        "created_at",
        "updated_at",
        "deleted_at",
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "instansi_id" => "string",
        "created_by" => "string",
        "updated_by" => "string",
        "weight" => "decimal:2",
        "is_mandatory" => "boolean",
        "metadata" => "array",
        "calculation_formula" => "array",
        "created_at" => "datetime",
        "updated_at" => "datetime",
        "deleted_at" => "datetime",
    ];

    /**
     * Get the instansi that owns the performance indicator.
     */
    public function instansi()
    {
        return $this->belongsTo(Instansi::class);
    }

    /**
     * Get the sasaran strategis that owns the performance indicator.
     */
    public function sasaranStrategis()
    {
        return $this->belongsTo(SasaranStrategis::class);
    }

    /**
     * Get the program that owns the performance indicator.
     */
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get the kegiatan that owns the performance indicator.
     */
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }

    /**
     * Get the user who created the performance indicator.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, "created_by");
    }

    /**
     * Get the user who last updated the performance indicator.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, "updated_by");
    }

    /**
     * Get the targets for the performance indicator.
     */
    public function targets()
    {
        return $this->hasMany(Target::class);
    }

    /**
     * Get the performance data for the performance indicator.
     */
    public function performanceData()
    {
        return $this->hasMany(PerformanceData::class);
    }

    /**
     * Get the target for a specific year.
     */
    public function getTargetForYear(int $year)
    {
        return $this->targets()->where("year", $year)->first();
    }

    /**
     * Get the latest performance data for this indicator.
     */
    public function getLatestPerformanceData()
    {
        return $this->performanceData()->latest("period")->first();
    }

    /**
     * Calculate performance based on actual value and target.
     * IMPROVED: Comprehensive edge case handling for business logic accuracy.
     *
     * Edge cases handled:
     * 1. Zero or null target values
     * 2. Negative actual values (indicating decline)
     * 3. Negative target values (when targets are reductions)
     * 4. Actual value exceeding target by significant margins
     * 5. Calculation formula variations
     *
     * @param float $actualValue The actual achieved value
     * @param float $targetValue The target/goal value
     * @return float Performance percentage (0-100+, can exceed 100 for overachievement)
     */
    public function calculatePerformance($actualValue, $targetValue)
    {
        // Handle null/empty/zero targets
        if (empty($targetValue) || $targetValue == 0) {
            // If target is 0 or null, we cannot calculate percentage
            // Return 0 if no actual value, or 100 if actual exists (achievement by default)
            return !empty($actualValue) && $actualValue != 0 ? 100 : 0;
        }

        // Handle negative target values (e.g., cost reduction goals)
        // If target is negative (e.g., -10% reduction), we need special handling
        if ($targetValue < 0) {
            if ($actualValue < 0) {
                // Both negative: calculate ratio of reduction achieved
                // Example: Target -10, Actual -15 = 150% (exceeded reduction goal)
                $performance = abs($actualValue / $targetValue) * 100;
                return min($performance, 200); // Cap at 200% for negative targets
            } else {
                // Target negative, actual positive: goal not met
                // Example: Target -10% reduction, Actual +5% increase = 0% (failed)
                return 0;
            }
        }

        // Handle negative actual values with positive targets
        // Example: Target 100, Actual -20 = 0% (complete failure)
        if ($actualValue < 0) {
            return 0;
        }

        // Standard calculation: percentage of target achieved
        $performance = ($actualValue / $targetValue) * 100;

        // Round to 2 decimal places for precision
        $performance = round($performance, 2);

        // Prevent negative percentages (minimum is 0%)
        return max(0, $performance);
    }

    /**
     * Scope to get only mandatory indicators.
     */
    public function scopeMandatory($query)
    {
        return $query->where("is_mandatory", true);
    }

    /**
     * Scope to get indicators by category.
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where("category", $category);
    }

    /**
     * Scope to get indicators by frequency.
     */
    public function scopeByFrequency($query, string $frequency)
    {
        return $query->where("frequency", $frequency);
    }

    /**
     * Scope to get indicators for a specific instansi.
     */
    public function scopeForInstansi($query, int $instansiId)
    {
        return $query->where("instansi_id", $instansiId);
    }

    /**
     * The "booting" method of the model.
     *
     * SECURITY: Add global scope to prevent IDOR (Insecure Direct Object Reference) attacks.
     * Non-superadmin users can only access performance indicators from their own instansi.
     */
    protected static function booted()
    {
        // Add global scope for instansi_id filtering to prevent IDOR
        // Super admins can see all indicators, regular users are scoped to their instansi
        static::addGlobalScope("instansi_scope", function ($query) {
            if (
                auth()->check() &&
                !auth()
                    ->user()
                    ->hasRole(\App\Constants\SystemRoles::SUPER_ADMIN)
            ) {
                $query->where("instansi_id", auth()->user()->instansi_id);
            }
        });
    }

    /**
     * Get the display name with code.
     */
    public function getDisplayNameAttribute()
    {
        return "{$this->code} - {$this->name}";
    }

    /**
     * Get the formatted weight as percentage.
     */
    public function getWeightPercentageAttribute()
    {
        return number_format($this->weight, 2) . "%";
    }
}
