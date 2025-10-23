<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\PerformanceIndicator;
use App\Models\Target;
use App\Models\Instansi;
use App\Models\AuditLog;
use App\Http\Requests\StorePerformanceIndicatorRequest;
use App\Http\Requests\UpdatePerformanceIndicatorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Performance Indicator Controller
 *
 * Manages performance indicators (CRUD operations, target setting)
 * for the SAKIP module with comprehensive validation and authorization.
 */
class PerformanceIndicatorController extends Controller
{
    /**
     * Display a listing of performance indicators
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny", PerformanceIndicator::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;

            $query = PerformanceIndicator::with([
                "instansi",
                "targets",
                "creator",
                "updater",
            ])->when($instansiId, function ($q) use ($instansiId) {
                return $q->where("instansi_id", $instansiId);
            });

            // Apply filters
            if ($request->filled("search")) {
                $search = $request->get("search");
                $query->where(function ($q) use ($search) {
                    $q->where("name", "like", "%{$search}%")
                        ->orWhere("code", "like", "%{$search}%")
                        ->orWhere("description", "like", "%{$search}%");
                });
            }

            if ($request->filled("category")) {
                $query->where("category", $request->get("category"));
            }

            if ($request->filled("frequency")) {
                $query->where("frequency", $request->get("frequency"));
            }

            if ($request->filled("is_mandatory")) {
                $query->where("is_mandatory", $request->get("is_mandatory"));
            }

            if ($request->filled("status")) {
                $status = $request->get("status");
                if ($status === "with_targets") {
                    $query->has("targets");
                } elseif ($status === "without_targets") {
                    $query->doesntHave("targets");
                } elseif ($status === "with_data") {
                    $query->has("performanceData");
                } elseif ($status === "without_data") {
                    $query->doesntHave("performanceData");
                }
            }

            $indicators = $query
                ->orderBy("created_at", "desc")
                ->paginate(15)
                ->appends($request->query());

            // Get statistics
            $statistics = $this->getStatistics($instansiId);

            return view(
                "sakip.indicators.index",
                compact("indicators", "statistics"),
            );
        } catch (\Exception $e) {
            \Log::error(
                "Performance indicators index error: " . $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat daftar indikator kinerja.",
            );
        }
    }

    /**
     * Show the form for creating a new performance indicator
     */
    public function create()
    {
        $this->authorize("create", PerformanceIndicator::class);

        try {
            $user = Auth::user();

            // Get all active instansi for dropdown (especially for Super Admin)
            $instansis = Instansi::where("status", "aktif")
                ->orderBy("nama_instansi")
                ->get();

            // Get current user's instansi if exists
            $userInstansi = $user->instansi_id
                ? Instansi::find($user->instansi_id)
                : null;

            // Get available categories and frequencies
            $categories = $this->getCategories();
            $frequencies = $this->getFrequencies();
            $collectionMethods = $this->getCollectionMethods();

            return view(
                "sakip.indicators.create",
                compact(
                    "instansis",
                    "userInstansi",
                    "categories",
                    "frequencies",
                    "collectionMethods",
                ),
            );
        } catch (\Exception $e) {
            \Log::error(
                "Performance indicator create form error: " . $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat formulir pembuatan indikator.",
            );
        }
    }

    /**
     * Store a newly created performance indicator
     */
    public function store(StorePerformanceIndicatorRequest $request)
    {
        $this->authorize("create", PerformanceIndicator::class);

        DB::beginTransaction();
        try {
            $user = Auth::user();

            // Generate unique code if not provided
            $code =
                $request->get("code") ?:
                $this->generateCode($request->get("name"));

            // Create the indicator
            $indicator = PerformanceIndicator::create([
                "instansi_id" => $user->instansi_id,
                "code" => $code,
                "name" => $request->get("name"),
                "description" => $request->get("description"),
                "measurement_unit" => $request->get("measurement_unit"),
                "measurement_type" => $request->get("measurement_type"),
                "sasaran_strategis_id" => $request->get("sasaran_strategis_id"),
                "program_id" => $request->get("program_id"),
                "kegiatan_id" => $request->get("kegiatan_id"),
                "data_source" => $request->get("data_source"),
                "collection_method" => $request->get("collection_method"),
                "calculation_formula" => $request->get("calculation_formula"),
                "frequency" => $request->get("frequency"),
                "category" => $request->get("category"),
                "weight" => $request->get("weight", 0),
                "is_mandatory" => $request->get("is_mandatory", false),
                "metadata" => $request->get("metadata", []),
                "created_by" => $user->id,
                "updated_by" => $user->id,
            ]);

            // Create targets from the request
            if ($request->has("targets")) {
                foreach ($request->get("targets") as $targetData) {
                    // Skip empty target rows
                    if (
                        !isset($targetData["year"]) ||
                        !isset($targetData["target_value"])
                    ) {
                        continue;
                    }

                    Target::create([
                        "performance_indicator_id" => $indicator->id,
                        "year" => $targetData["year"],
                        "target_value" => $targetData["target_value"],
                        "minimum_value" => $targetData["minimum_value"] ?? null,
                        "justification" => $targetData["justification"] ?? null,
                        "status" => "draft",
                        "created_by" => $user->id,
                        "updated_by" => $user->id,
                    ]);
                }
            }

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "CREATE",
                "module" => "SAKIP",
                "description" => "Membuat indikator kinerja: {$indicator->name}",
                "old_values" => null,
                "new_values" => $indicator->toArray(),
            ]);

            DB::commit();

            return redirect()
                ->route("sakip.indicators.show", $indicator)
                ->with(
                    "success",
                    "Indikator kinerja dan target berhasil dibuat.",
                );
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error(
                "Store performance indicator error: " . $e->getMessage(),
            );
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Terjadi kesalahan saat menyimpan indikator kinerja: " .
                        $e->getMessage(),
                );
        }
    }

    /**
     * Display the specified performance indicator
     */
    public function show(PerformanceIndicator $indicator)
    {
        $this->authorize("view", $indicator);

        try {
            $indicator->load([
                "instansi",
                "targets" => function ($q) {
                    $q->orderBy("year", "desc")->orderBy("period");
                },
                "performanceData" => function ($q) {
                    $q->orderBy("period", "desc")->limit(12);
                },
                "creator",
                "updater",
            ]);

            // Get current year targets
            $currentYear = Carbon::now()->year;
            $currentTargets = $indicator
                ->targets()
                ->where("year", $currentYear)
                ->get();

            // Get recent performance data
            $recentData = $indicator
                ->performanceData()
                ->orderBy("period", "desc")
                ->limit(6)
                ->get();

            // Calculate performance trends
            $performanceTrend = $this->calculatePerformanceTrend($indicator);

            return view(
                "sakip.indicators.show",
                compact(
                    "indicator",
                    "currentTargets",
                    "recentData",
                    "performanceTrend",
                    "currentYear",
                ),
            );
        } catch (\Exception $e) {
            \Log::error(
                "Show performance indicator error: " . $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat detail indikator.",
            );
        }
    }

    /**
     * Show the form for editing the specified performance indicator
     */
    public function edit(PerformanceIndicator $indicator)
    {
        $this->authorize("update", $indicator);

        try {
            $user = Auth::user();
            $instansi = Instansi::find($user->instansi_id);

            // Get available categories and frequencies
            $categories = $this->getCategories();
            $frequencies = $this->getFrequencies();
            $collectionMethods = $this->getCollectionMethods();

            return view(
                "sakip.indicators.edit",
                compact(
                    "indicator",
                    "instansi",
                    "categories",
                    "frequencies",
                    "collectionMethods",
                ),
            );
        } catch (\Exception $e) {
            \Log::error(
                "Edit performance indicator form error: " . $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat formulir edit.",
            );
        }
    }

    /**
     * Update the specified performance indicator
     */
    public function update(
        UpdatePerformanceIndicatorRequest $request,
        PerformanceIndicator $indicator,
    ) {
        $this->authorize("update", $indicator);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $oldValues = $indicator->toArray();

            // Update the indicator
            $indicator->update([
                "name" => $request->get("name"),
                "description" => $request->get("description"),
                "measurement_unit" => $request->get("measurement_unit"),
                "data_source" => $request->get("data_source"),
                "collection_method" => $request->get("collection_method"),
                "calculation_formula" => $request->get("calculation_formula"),
                "frequency" => $request->get("frequency"),
                "category" => $request->get("category"),
                "weight" => $request->get("weight", 0),
                "is_mandatory" => $request->get("is_mandatory", false),
                "metadata" => $request->get("metadata", []),
                "updated_by" => $user->id,
            ]);

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "UPDATE",
                "module" => "SAKIP",
                "description" => "Memperbarui indikator kinerja: {$indicator->name}",
                "old_values" => $oldValues,
                "new_values" => $indicator->fresh()->toArray(),
            ]);

            DB::commit();

            return redirect()
                ->route("sakip.indicators.show", $indicator)
                ->with("success", "Indikator kinerja berhasil diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error(
                "Update performance indicator error: " . $e->getMessage(),
            );
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Terjadi kesalahan saat memperbarui indikator kinerja.",
                );
        }
    }

    /**
     * Remove the specified performance indicator
     */
    public function destroy(PerformanceIndicator $indicator)
    {
        $this->authorize("delete", $indicator);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $indicatorName = $indicator->name;

            // Check if indicator has related data
            if (
                $indicator->targets()->exists() ||
                $indicator->performanceData()->exists()
            ) {
                return back()->with(
                    "error",
                    "Indikator tidak dapat dihapus karena memiliki data terkait.",
                );
            }

            // Soft delete the indicator
            $indicator->delete();

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "DELETE",
                "module" => "SAKIP",
                "description" => "Menghapus indikator kinerja: {$indicatorName}",
                "old_values" => $indicator->toArray(),
                "new_values" => null,
            ]);

            DB::commit();

            return redirect()
                ->route("sakip.indicators.index")
                ->with("success", "Indikator kinerja berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error(
                "Delete performance indicator error: " . $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat menghapus indikator kinerja.",
            );
        }
    }

    /**
     * Get indicators by institution (API endpoint)
     */
    public function getByInstansi(Request $request, Instansi $instansi)
    {
        $this->authorize("viewAny", PerformanceIndicator::class);

        try {
            $indicators = PerformanceIndicator::where(
                "instansi_id",
                $instansi->id,
            )
                ->with([
                    "targets" => function ($q) {
                        $q->where("year", Carbon::now()->year);
                    },
                ])
                ->orderBy("name")
                ->get();

            return response()->json([
                "success" => true,
                "data" => $indicators->map(function ($indicator) {
                    return [
                        "id" => $indicator->id,
                        "code" => $indicator->code,
                        "name" => $indicator->name,
                        "category" => $indicator->category,
                        "frequency" => $indicator->frequency,
                        "measurement_unit" => $indicator->measurement_unit,
                        "weight" => $indicator->weight,
                        "is_mandatory" => $indicator->is_mandatory,
                        "targets" => $indicator->targets,
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            \Log::error(
                "Get indicators by institution error: " . $e->getMessage(),
            );
            return response()->json(
                [
                    "success" => false,
                    "message" => "Gagal memuat data indikator.",
                ],
                500,
            );
        }
    }

    /**
     * Get performance data for an indicator (API endpoint)
     */
    public function getPerformanceData(
        Request $request,
        PerformanceIndicator $indicator,
    ) {
        $this->authorize("view", $indicator);

        try {
            $year = $request->get("year", Carbon::now()->year);

            $performanceData = $indicator
                ->performanceData()
                ->whereYear("period", $year)
                ->orderBy("period")
                ->get();

            return response()->json([
                "success" => true,
                "data" => $performanceData,
                "indicator" => [
                    "id" => $indicator->id,
                    "name" => $indicator->name,
                    "measurement_unit" => $indicator->measurement_unit,
                    "calculation_formula" => $indicator->calculation_formula,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error("Get performance data error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" => "Gagal memuat data kinerja.",
                ],
                500,
            );
        }
    }

    /**
     * Bulk import indicators from CSV/Excel
     */
    public function import(Request $request)
    {
        $this->authorize("create", PerformanceIndicator::class);

        $validator = Validator::make($request->all(), [
            "file" => "required|file|mimes:csv,xlsx,xls|max:10240",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "File tidak valid.",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        DB::beginTransaction();
        try {
            $file = $request->file("file");
            $user = Auth::user();
            $importedCount = 0;
            $errors = [];

            // Process the file (simplified - in real implementation use Laravel Excel or similar)
            $fileContent = file_get_contents($file->getRealPath());
            $lines = explode("\n", $fileContent);

            foreach ($lines as $index => $line) {
                if ($index === 0) {
                    continue;
                } // Skip header

                $data = str_getcsv($line);
                if (count($data) < 8) {
                    continue;
                } // Skip invalid rows

                try {
                    PerformanceIndicator::create([
                        "instansi_id" => $user->instansi_id,
                        "code" => $data[0] ?: $this->generateCode($data[1]),
                        "name" => $data[1],
                        "description" => $data[2] ?? "",
                        "measurement_unit" => $data[3],
                        "data_source" => $data[4],
                        "collection_method" => $data[5],
                        "calculation_formula" => $data[6] ?? "",
                        "frequency" => $data[7],
                        "category" => $data[8] ?? "output",
                        "weight" => $data[9] ?? 0,
                        "is_mandatory" => $data[10] ?? false,
                        "created_by" => $user->id,
                        "updated_by" => $user->id,
                    ]);

                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] =
                        "Baris " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "IMPORT",
                "module" => "SAKIP",
                "description" => "Mengimpor {$importedCount} indikator kinerja",
                "old_values" => null,
                "new_values" => ["imported_count" => $importedCount],
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Berhasil mengimpor {$importedCount} indikator kinerja.",
                "errors" => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error(
                "Import performance indicators error: " . $e->getMessage(),
            );
            return response()->json(
                [
                    "success" => false,
                    "message" => "Terjadi kesalahan saat mengimpor data.",
                ],
                500,
            );
        }
    }

    /**
     * Export indicators to Excel/CSV
     */
    public function export(Request $request)
    {
        $this->authorize("viewAny", PerformanceIndicator::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;

            $indicators = PerformanceIndicator::where(
                "instansi_id",
                $instansiId,
            )
                ->with([
                    "targets" => function ($q) {
                        $q->where("year", Carbon::now()->year);
                    },
                ])
                ->orderBy("code")
                ->get();

            // Generate CSV content
            $csv =
                "Code,Name,Description,Measurement Unit,Data Source,Collection Method,Calculation Formula,Frequency,Category,Weight,Is Mandatory\n";

            foreach ($indicators as $indicator) {
                $csv .=
                    implode(",", [
                        $indicator->code,
                        '"' . str_replace('"', '""', $indicator->name) . '"',
                        '"' .
                        str_replace('"', '""', $indicator->description) .
                        '"',
                        $indicator->measurement_unit,
                        $indicator->data_source,
                        $indicator->collection_method,
                        '"' .
                        str_replace(
                            '"',
                            '""',
                            $indicator->calculation_formula,
                        ) .
                        '"',
                        $indicator->frequency,
                        $indicator->category,
                        $indicator->weight,
                        $indicator->is_mandatory ? "Yes" : "No",
                    ]) . "\n";
            }

            $filename = "indikator_kinerja_" . date("Y-m-d_H-i-s") . ".csv";

            return response($csv, 200, [
                "Content-Type" => "text/csv",
                "Content-Disposition" => "attachment; filename=\"{$filename}\"",
            ]);
        } catch (\Exception $e) {
            \Log::error(
                "Export performance indicators error: " . $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat mengekspor data.",
            );
        }
    }

    /**
     * Get statistics for the current institution
     */
    private function getStatistics($instansiId)
    {
        return [
            "total" => PerformanceIndicator::where(
                "instansi_id",
                $instansiId,
            )->count(),
            "mandatory" => PerformanceIndicator::where(
                "instansi_id",
                $instansiId,
            )
                ->where("is_mandatory", true)
                ->count(),
            "with_targets" => PerformanceIndicator::where(
                "instansi_id",
                $instansiId,
            )
                ->has("targets")
                ->count(),
            "with_data" => PerformanceIndicator::where(
                "instansi_id",
                $instansiId,
            )
                ->has("performanceData")
                ->count(),
            "by_category" => PerformanceIndicator::where(
                "instansi_id",
                $instansiId,
            )
                ->select("category", DB::raw("count(*) as count"))
                ->groupBy("category")
                ->pluck("count", "category"),
            "by_frequency" => PerformanceIndicator::where(
                "instansi_id",
                $instansiId,
            )
                ->select("frequency", DB::raw("count(*) as count"))
                ->groupBy("frequency")
                ->pluck("count", "frequency"),
        ];
    }

    /**
     * Calculate performance trend for an indicator
     */
    private function calculatePerformanceTrend(PerformanceIndicator $indicator)
    {
        $currentYear = Carbon::now()->year;
        $lastYear = $currentYear - 1;

        // Calculate performance based on actual vs target values
        $currentData = $indicator
            ->performanceData()
            ->where("period", "like", $currentYear . "%")
            ->with("target")
            ->get();

        $currentPerformance = 0;
        if ($currentData->isNotEmpty()) {
            $totalPerformance = 0;
            $count = 0;
            foreach ($currentData as $data) {
                $target = $data->performanceIndicator
                    ->targets()
                    ->where("year", $currentYear)
                    ->first();
                if ($target && $target->target_value > 0) {
                    $totalPerformance +=
                        ($data->actual_value / $target->target_value) * 100;
                    $count++;
                }
            }
            $currentPerformance = $count > 0 ? $totalPerformance / $count : 0;
        }

        $lastYearData = $indicator
            ->performanceData()
            ->where("period", "like", $lastYear . "%")
            ->with("target")
            ->get();

        $lastYearPerformance = 0;
        if ($lastYearData->isNotEmpty()) {
            $totalPerformance = 0;
            $count = 0;
            foreach ($lastYearData as $data) {
                $target = $data->performanceIndicator
                    ->targets()
                    ->where("year", $lastYear)
                    ->first();
                if ($target && $target->target_value > 0) {
                    $totalPerformance +=
                        ($data->actual_value / $target->target_value) * 100;
                    $count++;
                }
            }
            $lastYearPerformance = $count > 0 ? $totalPerformance / $count : 0;
        }

        $trend =
            $lastYearPerformance > 0
                ? round(
                    (($currentPerformance - $lastYearPerformance) /
                        $lastYearPerformance) *
                        100,
                    2,
                )
                : 0;

        return [
            "current" => round($currentPerformance, 2),
            "last_year" => round($lastYearPerformance, 2),
            "trend" => $trend,
            "trend_direction" =>
                $trend > 0 ? "up" : ($trend < 0 ? "down" : "stable"),
        ];
    }

    /**
     * Generate unique code for indicator
     */
    private function generateCode($name)
    {
        $prefix = strtoupper(substr(str_replace(" ", "", $name), 0, 3));
        $number =
            PerformanceIndicator::where("code", "like", "{$prefix}%")->count() +
            1;

        return $prefix . str_pad($number, 3, "0", STR_PAD_LEFT);
    }

    /**
     * Get available categories
     */
    private function getCategories()
    {
        return [
            "input" => "Input",
            "output" => "Output",
            "outcome" => "Outcome",
            "impact" => "Impact",
        ];
    }

    /**
     * Get available frequencies
     */
    private function getFrequencies()
    {
        return [
            "monthly" => "Bulanan",
            "quarterly" => "Triwulan",
            "semester" => "Semester",
            "annual" => "Tahunan",
        ];
    }

    /**
     * Get available collection methods
     */
    private function getCollectionMethods()
    {
        return [
            "manual" => "Manual",
            "automated" => "Otomatis",
            "survey" => "Survei",
            "interview" => "Wawancara",
            "observation" => "Observasi",
            "document_review" => "Telaah Dokumen",
        ];
    }
}
