<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * PerformanceIndicator Model
 *
 * Represents performance indicators for SAKIP compliance.
 * Tracks institutional performance metrics with calculation formulas and measurement units.
 */
class PerformanceIndicator extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

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
     */
    public function calculatePerformance($actualValue, $targetValue)
    {
        if (empty($targetValue) || $targetValue == 0) {
            return 0;
        }

        // Parse calculation formula to determine calculation method
        $formula = $this->calculation_formula;

        // Default calculation: percentage of target achieved
        return round(($actualValue / $targetValue) * 100, 2);
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
