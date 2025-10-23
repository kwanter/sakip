<?php

namespace App\Services;

use App\Models\PerformanceIndicator;
use App\Models\Target;
use App\Models\PerformanceData;
use App\Models\Instansi;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class PerformanceIndicatorService
{
    protected $cacheTimeout = 3600; // 1 hour

    /**
     * Create a new performance indicator
     */
    public function createIndicator(array $data): PerformanceIndicator
    {
        return DB::transaction(function () use ($data) {
            // Validate data
            $validator = $this->validateIndicatorData($data);
            if ($validator->fails()) {
                throw new Exception(
                    "Validation failed: " . $validator->errors()->first(),
                );
            }

            // Create indicator
            $indicator = PerformanceIndicator::create([
                "instansi_id" => $data["instansi_id"],
                "code" => $this->generateIndicatorCode(
                    $data["instansi_id"],
                    $data["category"],
                ),
                "name" => $data["name"],
                "description" => $data["description"] ?? null,
                "measurement_unit" => $data["measurement_unit"],
                "data_source" => $data["data_source"],
                "collection_method" => $data["collection_method"],
                "calculation_formula" => $data["calculation_formula"] ?? null,
                "frequency" => $data["frequency"] ?? "monthly",
                "category" => $data["category"],
                "weight" => $data["weight"] ?? 1,
                "is_mandatory" => $data["is_mandatory"] ?? false,
                "created_by" => auth()->id(),
            ]);

            // Create initial target if provided
            if (isset($data["target_value"]) && isset($data["target_year"])) {
                $this->createTarget(
                    $indicator,
                    $data["target_year"],
                    $data["target_value"],
                );
            }

            // Log activity
            $this->logActivity(
                "create",
                $indicator,
                "Performance indicator created",
            );

            // Clear cache
            $this->clearIndicatorCache($indicator->instansi_id);

            return $indicator->fresh(["targets"]);
        });
    }

    /**
     * Update performance indicator
     */
    public function updateIndicator(
        PerformanceIndicator $indicator,
        array $data,
    ): PerformanceIndicator {
        return DB::transaction(function () use ($indicator, $data) {
            // Validate data
            $validator = $this->validateIndicatorData($data, $indicator->id);
            if ($validator->fails()) {
                throw new Exception(
                    "Validation failed: " . $validator->errors()->first(),
                );
            }

            // Check if changes affect historical data
            $affectsHistoricalData = $this->changesAffectHistoricalData(
                $indicator,
                $data,
            );

            // Update indicator
            $indicator->update([
                "name" => $data["name"] ?? $indicator->name,
                "description" =>
                    $data["description"] ?? $indicator->description,
                "measurement_unit" =>
                    $data["measurement_unit"] ?? $indicator->measurement_unit,
                "data_source" =>
                    $data["data_source"] ?? $indicator->data_source,
                "collection_method" =>
                    $data["collection_method"] ?? $indicator->collection_method,
                "calculation_formula" =>
                    $data["calculation_formula"] ??
                    $indicator->calculation_formula,
                "frequency" => $data["frequency"] ?? $indicator->frequency,
                "category" => $data["category"] ?? $indicator->category,
                "weight" => $data["weight"] ?? $indicator->weight,
                "is_mandatory" =>
                    $data["is_mandatory"] ?? $indicator->is_mandatory,
                "updated_by" => auth()->id(),
            ]);

            // Handle historical data if needed
            if ($affectsHistoricalData) {
                $this->handleHistoricalDataChanges($indicator, $data);
            }

            // Log activity
            $this->logActivity(
                "update",
                $indicator,
                "Performance indicator updated",
            );

            // Clear cache
            $this->clearIndicatorCache($indicator->instansi_id);

            return $indicator->fresh(["targets"]);
        });
    }

    /**
     * Delete performance indicator
     */
    public function deleteIndicator(PerformanceIndicator $indicator): bool
    {
        return DB::transaction(function () use ($indicator) {
            // Check if indicator has associated data
            if ($indicator->performanceData()->exists()) {
                throw new Exception(
                    "Cannot delete indicator with existing performance data",
                );
            }

            // Log activity before deletion
            $this->logActivity(
                "delete",
                $indicator,
                "Performance indicator deleted",
            );

            // Delete targets
            $indicator->targets()->delete();

            // Delete indicator
            $result = $indicator->delete();

            // Clear cache
            $this->clearIndicatorCache($indicator->instansi_id);

            return $result;
        });
    }

    /**
     * Bulk create indicators from import
     */
    public function bulkCreateIndicators(array $indicatorsData): array
    {
        $results = [
            "success" => 0,
            "failed" => 0,
            "errors" => [],
        ];

        DB::transaction(function () use ($indicatorsData, &$results) {
            foreach ($indicatorsData as $index => $data) {
                try {
                    $this->createIndicator($data);
                    $results["success"]++;
                } catch (Exception $e) {
                    $results["failed"]++;
                    $results["errors"][] = [
                        "row" => $index + 1,
                        "error" => $e->getMessage(),
                    ];
                }
            }
        });

        return $results;
    }

    /**
     * Get indicators with filters and pagination
     */
    public function getIndicators(array $filters = [], $perPage = 15)
    {
        $query = PerformanceIndicator::with([
            "instansi",
            "targets",
            "performanceData",
        ]);

        // Apply filters
        if (isset($filters["instansi_id"])) {
            $query->where("instansi_id", $filters["instansi_id"]);
        }

        if (isset($filters["category"])) {
            $query->where("category", $filters["category"]);
        }

        if (isset($filters["is_mandatory"])) {
            $query->where("is_mandatory", $filters["is_mandatory"]);
        }

        if (isset($filters["frequency"])) {
            $query->where("frequency", $filters["frequency"]);
        }

        if (isset($filters["search"])) {
            $query->where(function ($q) use ($filters) {
                $q->where("name", "like", "%" . $filters["search"] . "%")
                    ->orWhere("code", "like", "%" . $filters["search"] . "%")
                    ->orWhere(
                        "description",
                        "like",
                        "%" . $filters["search"] . "%",
                    );
            });
        }

        if (isset($filters["has_data"])) {
            if ($filters["has_data"]) {
                $query->has("performanceData");
            } else {
                $query->doesntHave("performanceData");
            }
        }

        // Sorting
        $sortBy = $filters["sort_by"] ?? "created_at";
        $sortOrder = $filters["sort_order"] ?? "desc";
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get indicator by ID with relationships
     */
    public function getIndicatorById($id): ?PerformanceIndicator
    {
        return Cache::remember(
            "indicator_{$id}",
            $this->cacheTimeout,
            function () use ($id) {
                return PerformanceIndicator::with([
                    "instansi",
                    "targets" => function ($query) {
                        $query->orderBy("year", "desc");
                    },
                    "performanceData" => function ($query) {
                        $query->orderBy("period", "desc");
                    },
                    "evidenceDocuments" => function ($query) {
                        $query->orderBy("created_at", "desc");
                    },
                    "createdBy",
                    "updatedBy",
                ])->find($id);
            },
        );
    }

    /**
     * Validate indicator data
     */
    protected function validateIndicatorData(
        array $data,
        $excludeId = null,
    ): \Illuminate\Contracts\Validation\Validator {
        $rules = [
            "instansi_id" => "required|exists:instansi,id",
            "name" => "required|string|max:255",
            "description" => "nullable|string|max:1000",
            "measurement_unit" => "required|string|max:100",
            "data_source" => "required|string|max:255",
            "collection_method" =>
                "required|in:manual,automatic,semi_automatic",
            "calculation_formula" => "nullable|string|max:500",
            "frequency" => "required|in:monthly,quarterly,semester,yearly",
            "category" => "required|in:kegiatan,program,komponen,subkomponen",
            "weight" => "nullable|numeric|min:0|max:100",
            "is_mandatory" => "boolean",
            "target_value" => "nullable|numeric|min:0",
            "target_year" => "nullable|integer|min:2020|max:2030",
        ];

        if ($excludeId) {
            $rules["code"] =
                "nullable|string|max:50|unique:performance_indicators,code," .
                $excludeId;
        } else {
            $rules["code"] =
                "nullable|string|max:50|unique:performance_indicators,code";
        }

        return Validator::make($data, $rules);
    }

    /**
     * Generate unique indicator code
     */
    protected function generateIndicatorCode($instansiId, $category): string
    {
        $instansi = Instansi::find($instansiId);
        $instansiCode = strtoupper(
            substr($instansi->kode_instansi ?? "UNK", 0, 3),
        );
        $categoryCode = strtoupper(substr($category, 0, 3));

        $lastIndicator = PerformanceIndicator::where("instansi_id", $instansiId)
            ->where("category", $category)
            ->orderBy("id", "desc")
            ->first();

        $sequence = $lastIndicator
            ? intval(substr($lastIndicator->code, -4)) + 1
            : 1;

        return sprintf("%s-%s-%04d", $instansiCode, $categoryCode, $sequence);
    }

    /**
     * Create target for indicator
     */
    protected function createTarget(
        PerformanceIndicator $indicator,
        $year,
        $targetValue,
    ): Target {
        return $indicator->targets()->create([
            "year" => $year,
            "target_value" => $targetValue,
            "created_by" => auth()->id(),
        ]);
    }

    /**
     * Check if changes affect historical data
     */
    protected function changesAffectHistoricalData(
        PerformanceIndicator $indicator,
        array $data,
    ): bool {
        $criticalFields = [
            "measurement_unit",
            "calculation_formula",
            "data_source",
        ];

        foreach ($criticalFields as $field) {
            if (isset($data[$field]) && $data[$field] !== $indicator->$field) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle historical data changes
     */
    protected function handleHistoricalDataChanges(
        PerformanceIndicator $indicator,
        array $data,
    ): void {
        // Log the change for audit purposes
        $this->logActivity(
            "historical_change",
            $indicator,
            "Critical indicator properties changed - historical data may be affected",
        );

        // Mark existing performance data as requiring review
        $indicator->performanceData()->update([
            "status" => "draft",
            "notes" => "Indicator properties changed - requires review",
        ]);
    }

    /**
     * Get indicator statistics
     */
    public function getIndicatorStatistics(
        $instansiId = null,
        $year = null,
    ): array {
        $year = $year ?? date("Y");
        $cacheKey = "indicator_statistics_{$instansiId}_{$year}";

        return Cache::remember($cacheKey, $this->cacheTimeout, function () use (
            $instansiId,
            $year,
        ) {
            $query = PerformanceIndicator::query();

            if ($instansiId) {
                $query->where("instansi_id", $instansiId);
            }

            $totalIndicators = $query->count();
            $mandatoryIndicators = (clone $query)
                ->where("is_mandatory", true)
                ->count();
            $indicatorsWithTargets = (clone $query)
                ->whereHas("targets", function ($q) use ($year) {
                    $q->where("year", $year);
                })
                ->count();
            $indicatorsWithData = (clone $query)
                ->whereHas("performanceData", function ($q) use ($year) {
                    $q->where("period", "like", $year . "%");
                })
                ->count();

            return [
                "total_indicators" => $totalIndicators,
                "mandatory_indicators" => $mandatoryIndicators,
                "indicators_with_targets" => $indicatorsWithTargets,
                "indicators_with_data" => $indicatorsWithData,
                "target_coverage" =>
                    $totalIndicators > 0
                        ? round(
                            ($indicatorsWithTargets / $totalIndicators) * 100,
                            2,
                        )
                        : 0,
                "data_coverage" =>
                    $totalIndicators > 0
                        ? round(
                            ($indicatorsWithData / $totalIndicators) * 100,
                            2,
                        )
                        : 0,
            ];
        });
    }

    /**
     * Get indicator categories
     */
    public function getIndicatorCategories(): array
    {
        return [
            "kegiatan" => "Kegiatan",
            "program" => "Program",
            "komponen" => "Komponen",
            "subkomponen" => "Sub Komponen",
        ];
    }

    /**
     * Get indicator frequencies
     */
    public function getIndicatorFrequencies(): array
    {
        return [
            "monthly" => "Bulanan",
            "quarterly" => "Triwulan",
            "semester" => "Semester",
            "yearly" => "Tahunan",
        ];
    }

    /**
     * Get collection methods
     */
    public function getCollectionMethods(): array
    {
        return [
            "manual" => "Manual",
            "automatic" => "Otomatis",
            "semi_automatic" => "Semi Otomatis",
        ];
    }

    /**
     * Check if indicator can be deleted
     */
    public function canDeleteIndicator(PerformanceIndicator $indicator): array
    {
        $canDelete = true;
        $reasons = [];

        if ($indicator->performanceData()->exists()) {
            $canDelete = false;
            $reasons[] = "Indikator memiliki data kinerja yang tersedia";
        }

        if ($indicator->targets()->exists()) {
            $canDelete = false;
            $reasons[] = "Indikator memiliki target yang ditetapkan";
        }

        if ($indicator->evidenceDocuments()->exists()) {
            $canDelete = false;
            $reasons[] = "Indikator memiliki dokumen bukti yang terkait";
        }

        if ($indicator->is_mandatory) {
            $canDelete = false;
            $reasons[] = "Indikator merupakan indikator wajib";
        }

        return [
            "can_delete" => $canDelete,
            "reasons" => $reasons,
        ];
    }

    /**
     * Log activity
     */
    protected function logActivity(
        string $action,
        PerformanceIndicator $indicator,
        string $description,
    ): void {
        AuditLog::create([
            "user_id" => auth()->id(),
            "instansi_id" => $indicator->instansi_id,
            "module" => "sakip",
            "activity" => $action,
            "description" => $description,
            "old_values" =>
                $action === "update" ? $indicator->getOriginal() : null,
            "new_values" => $action !== "delete" ? $indicator->toArray() : null,
        ]);
    }

    /**
     * Clear indicator cache
     */
    protected function clearIndicatorCache($instansiId): void
    {
        Cache::forget("indicator_statistics_{$instansiId}_" . date("Y"));

        // Clear all indicator caches for this instansi
        $keys = Cache::getRedis()->keys("indicator_*");
        foreach ($keys as $key) {
            if (strpos($key, "_{$instansiId}_") !== false) {
                Cache::forget($key);
            }
        }
    }
}
