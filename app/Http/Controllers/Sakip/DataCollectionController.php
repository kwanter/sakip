<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\EvidenceDocument;
use App\Models\Target;
use App\Models\AuditLog;
use App\Services\DataValidationService;
use App\Services\ReportGenerationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Data Collection Controller
 *
 * Handles data collection forms, bulk import, validation, and evidence management
 * for the SAKIP module with comprehensive validation and file upload handling.
 */
class DataCollectionController extends Controller
{
    protected DataValidationService $validationService;
    protected ReportGenerationService $reportService;

    /**
     * Constructor with dependency injection
     */
    public function __construct(
        DataValidationService $validationService,
        ReportGenerationService $reportService,
    ) {
        $this->validationService = $validationService;
        $this->reportService = $reportService;
    }

    /**
     * Display data collection dashboard
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny", PerformanceData::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;
            $currentYear = Carbon::now()->year;

            // Get indicators that need data collection
            $query = PerformanceIndicator::where(
                "instansi_id",
                $instansiId,
            )->with([
                "targets" => function ($q) use ($currentYear) {
                    $q->where("year", $currentYear);
                },
                "performanceData" => function ($q) use ($currentYear) {
                    $q->whereYear("period", $currentYear);
                },
            ]);

            // Apply filters
            if ($request->filled("status")) {
                $status = $request->get("status");
                if ($status === "missing") {
                    $query->whereDoesntHave("performanceData", function (
                        $q,
                    ) use ($currentYear) {
                        $q->whereYear("period", $currentYear);
                    });
                } elseif ($status === "completed") {
                    $query->has("performanceData");
                } elseif ($status === "pending") {
                    $query->whereHas("performanceData", function ($q) use (
                        $currentYear,
                    ) {
                        $q->whereYear("period", $currentYear)->where(
                            "status",
                            "draft",
                        );
                    });
                }
            }

            if ($request->filled("category")) {
                $query->where("category", $request->get("category"));
            }

            if ($request->filled("frequency")) {
                $query->where("frequency", $request->get("frequency"));
            }

            $indicators = $query->orderBy("name")->paginate(15);

            // Get statistics
            $statistics = $this->getDataCollectionStatistics(
                $instansiId,
                $currentYear,
            );

            // Get recent data entries
            $recentEntries = PerformanceData::whereHas("indicator", function (
                $q,
            ) use ($instansiId) {
                $q->where("instansi_id", $instansiId);
            })
                ->with(["indicator", "creator"])
                ->orderBy("created_at", "desc")
                ->limit(10)
                ->get();

            // Get all performance data for the table
            $performanceDataQuery = PerformanceData::whereHas(
                "indicator",
                function ($q) use ($instansiId) {
                    $q->where("instansi_id", $instansiId);
                },
            )->with(["indicator.instansi"]);

            // Apply filters
            if ($request->filled("period")) {
                $performanceDataQuery->where("period", $request->get("period"));
            }

            if ($request->filled("validation_status")) {
                $performanceDataQuery->where(
                    "status",
                    $request->get("validation_status"),
                );
            }

            $performanceData = $performanceDataQuery
                ->orderBy("created_at", "desc")
                ->paginate(15);

            // Get all instansi for filter dropdown
            $instansis = \App\Models\Instansi::orderBy("nama_instansi")->get();

            // Format stats for view
            $stats = [
                "total_data" => $statistics["total_data_entries"],
                "validated_data" => PerformanceData::whereHas(
                    "indicator",
                    function ($q) use ($instansiId) {
                        $q->where("instansi_id", $instansiId);
                    },
                )
                    ->where("status", "validated")
                    ->count(),
                "pending_validation" => $statistics["pending_reviews"],
                "needs_revision" => PerformanceData::whereHas(
                    "indicator",
                    function ($q) use ($instansiId) {
                        $q->where("instansi_id", $instansiId);
                    },
                )
                    ->where("status", "rejected")
                    ->count(),
            ];

            return view(
                "sakip.data-collection.index",
                compact(
                    "indicators",
                    "statistics",
                    "recentEntries",
                    "currentYear",
                    "performanceData",
                    "instansis",
                    "stats",
                ),
            );
        } catch (\Exception $e) {
            \Log::error("Data collection index error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat halaman pengumpulan data.",
            );
        }
    }

    /**
     * Show data collection form for a specific indicator
     */
    public function create(
        Request $request,
        PerformanceIndicator $indicator = null,
    ) {
        $this->authorize("create", PerformanceData::class);

        try {
            $user = Auth::user();
            $currentYear = Carbon::now()->year;

            // If indicator_id is provided in query params, get the indicator
            if (!$indicator && $request->has("indicator_id")) {
                $indicator = PerformanceIndicator::findOrFail(
                    $request->indicator_id,
                );
            }

            // If no indicator is specified, show indicator selection
            if (!$indicator) {
                $indicators = PerformanceIndicator::with([
                    "instansi",
                    "targets",
                ])
                    ->where(function ($query) use ($user) {
                        $query
                            ->where("is_mandatory", true)
                            ->orWhere("instansi_id", $user->instansi_id);
                    })
                    ->orderBy("name")
                    ->get();

                // Get all instansi for dropdown
                $instansis = \App\Models\Instansi::orderBy(
                    "nama_instansi",
                )->get();

                return view(
                    "sakip.data-collection.create",
                    compact("indicators", "instansis", "currentYear"),
                );
            }

            // Get targets for the current year
            $targets = $indicator
                ->targets()
                ->where("year", $currentYear)
                ->orderBy("period")
                ->get();

            // Get existing performance data
            $existingData = $indicator
                ->performanceData()
                ->whereYear("period", $currentYear)
                ->orderBy("period")
                ->get();

            // Get available periods based on frequency
            $availablePeriods = $this->getAvailablePeriods(
                $indicator->frequency,
                $currentYear,
            );

            return view(
                "sakip.data-collection.create",
                compact(
                    "indicator",
                    "targets",
                    "existingData",
                    "availablePeriods",
                    "currentYear",
                ),
            );
        } catch (\Exception $e) {
            \Log::error(
                "Data collection create form error: " . $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat formulir pengumpulan data.",
            );
        }
    }

    /**
     * Store performance data
     */
    public function store(Request $request)
    {
        $this->authorize("create", PerformanceData::class);

        $validator = Validator::make($request->all(), [
            "indicator_id" => "required|exists:performance_indicators,id",
            "period" => "required|date",
            "actual_value" => "required|numeric",
            "target_value" => "nullable|numeric",
            "evidence_files.*" =>
                "nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png",
            "notes" => "nullable|string|max:1000",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal.",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $indicator = PerformanceIndicator::findOrFail(
                $request->get("indicator_id"),
            );

            // Check authorization for this specific indicator
            $this->authorize("createData", $indicator);

            // Validate period based on indicator frequency
            $period = Carbon::parse($request->get("period"));
            if (!$this->isValidPeriod($indicator, $period)) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Periode tidak valid untuk frekuensi indikator ini.",
                    ],
                    422,
                );
            }

            // Check for existing data in the same period
            $existingData = PerformanceData::where(
                "indicator_id",
                $indicator->id,
            )
                ->where("period", $period->format("Y-m-d"))
                ->first();

            if ($existingData) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Data untuk periode ini sudah ada.",
                    ],
                    422,
                );
            }

            // Get target value for this period
            $target = $this->getTargetForPeriod($indicator, $period);
            $targetValue =
                $request->get("target_value") ?:
                ($target
                    ? $target->target_value
                    : null);

            // Calculate performance percentage
            $performancePercentage = $this->calculatePerformancePercentage(
                $request->get("actual_value"),
                $targetValue,
                $indicator->calculation_formula,
            );

            // Create performance data
            $performanceData = PerformanceData::create([
                "indicator_id" => $indicator->id,
                "period" => $period->format("Y-m-d"),
                "actual_value" => $request->get("actual_value"),
                "target_value" => $targetValue,
                "performance_percentage" => $performancePercentage,
                "notes" => $request->get("notes"),
                "status" => "draft",
                "created_by" => $user->id,
                "updated_by" => $user->id,
            ]);

            // Handle evidence files
            if ($request->hasFile("evidence_files")) {
                $this->handleEvidenceFiles(
                    $request->file("evidence_files"),
                    $performanceData,
                );
            }

            // Validate data quality
            $validationResult = $this->validationService->validatePerformanceData(
                $performanceData,
            );

            if ($validationResult["has_errors"]) {
                $performanceData->update([
                    "status" => "submitted",
                    "validation_errors" => $validationResult["errors"],
                ]);
            }

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "CREATE",
                "module" => "SAKIP",
                "description" => "Memasukkan data kinerja untuk indikator: {$indicator->name} (Periode: {$period->format(
                    "M Y",
                )})",
                "old_values" => null,
                "new_values" => $performanceData->toArray(),
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Data kinerja berhasil disimpan.",
                "data" => [
                    "id" => $performanceData->id,
                    "performance_percentage" => $performancePercentage,
                    "validation_errors" => $validationResult["errors"] ?? [],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Store performance data error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Terjadi kesalahan saat menyimpan data kinerja.",
                ],
                500,
            );
        }
    }

    /**
     * Show performance data details
     */
    public function show(PerformanceData $performanceData)
    {
        $this->authorize("view", $performanceData);

        try {
            $performanceData->load([
                "indicator.instansi",
                "indicator.program",
                "indicator.kegiatan",
                "evidenceDocuments",
                "creator",
                "updater",
            ]);

            $indicator = $performanceData->indicator;
            $currentYear = Carbon::parse($performanceData->period)->year;

            // Get target for this period
            $target = $indicator
                ->targets()
                ->where("year", $currentYear)
                ->where(
                    "period",
                    "<=",
                    Carbon::parse($performanceData->period)->format("Y-m-d"),
                )
                ->orderBy("period", "desc")
                ->first();

            // Get validation history
            $validationHistory = AuditLog::where("module", "SAKIP")
                ->where("description", "like", "%" . $indicator->name . "%")
                ->orderBy("created_at", "desc")
                ->limit(10)
                ->get();

            return view(
                "sakip.data-collection.show",
                compact(
                    "performanceData",
                    "indicator",
                    "target",
                    "validationHistory",
                    "currentYear",
                ),
            );
        } catch (\Exception $e) {
            \Log::error("Show performance data error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat detail data kinerja.",
            );
        }
    }

    /**
     * Show form for editing performance data
     */
    public function edit(PerformanceData $performanceData)
    {
        $this->authorize("update", $performanceData);

        try {
            $performanceData->load(["indicator", "evidenceDocuments"]);

            $indicator = $performanceData->indicator;
            $currentYear = Carbon::parse($performanceData->period)->year;

            // Get targets for the year
            $targets = $indicator
                ->targets()
                ->where("year", $currentYear)
                ->orderBy("period")
                ->get();

            return view(
                "sakip.data-collection.edit",
                compact(
                    "performanceData",
                    "indicator",
                    "targets",
                    "currentYear",
                ),
            );
        } catch (\Exception $e) {
            \Log::error("Edit performance data error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat formulir edit.",
            );
        }
    }

    /**
     * Update performance data
     */
    public function update(Request $request, PerformanceData $performanceData)
    {
        $this->authorize("update", $performanceData);

        $validator = Validator::make($request->all(), [
            "actual_value" => "required|numeric",
            "target_value" => "nullable|numeric",
            "evidence_files.*" =>
                "nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png",
            "existing_files" => "nullable|array",
            "existing_files.*" => "exists:evidence_documents,id",
            "notes" => "nullable|string|max:1000",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal.",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $oldValues = $performanceData->toArray();

            // Get target value
            $targetValue =
                $request->get("target_value") ?: $performanceData->target_value;

            // Calculate performance percentage
            $performancePercentage = $this->calculatePerformancePercentage(
                $request->get("actual_value"),
                $targetValue,
                $performanceData->indicator->calculation_formula,
            );

            // Update performance data
            $performanceData->update([
                "actual_value" => $request->get("actual_value"),
                "target_value" => $targetValue,
                "performance_percentage" => $performancePercentage,
                "notes" => $request->get("notes"),
                "status" => "draft",
                "validation_errors" => null,
                "updated_by" => $user->id,
            ]);

            // Handle evidence files
            if ($request->hasFile("evidence_files")) {
                $this->handleEvidenceFiles(
                    $request->file("evidence_files"),
                    $performanceData,
                );
            }

            // Handle existing files deletion
            if ($request->has("existing_files")) {
                $existingFileIds = $request->get("existing_files");
                $filesToDelete = $performanceData
                    ->evidenceDocuments()
                    ->whereNotIn("id", $existingFileIds)
                    ->get();

                foreach ($filesToDelete as $file) {
                    $this->deleteEvidenceFile($file);
                }
            }

            // Validate data quality
            $validationResult = $this->validationService->validatePerformanceData(
                $performanceData,
            );

            if ($validationResult["has_errors"]) {
                $performanceData->update([
                    "status" => "submitted",
                    "validation_errors" => $validationResult["errors"],
                ]);
            }

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "UPDATE",
                "module" => "SAKIP",
                "description" => "Memperbarui data kinerja untuk indikator: {$performanceData->indicator->name}",
                "old_values" => $oldValues,
                "new_values" => $performanceData->fresh()->toArray(),
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Data kinerja berhasil diperbarui.",
                "data" => [
                    "performance_percentage" => $performancePercentage,
                    "validation_errors" => $validationResult["errors"] ?? [],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Update performance data error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Terjadi kesalahan saat memperbarui data kinerja.",
                ],
                500,
            );
        }
    }

    /**
     * Delete performance data
     */
    public function destroy(PerformanceData $performanceData)
    {
        $this->authorize("delete", $performanceData);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $indicatorName = $performanceData->indicator->name;
            $oldValues = $performanceData->toArray();

            // Delete evidence files
            foreach ($performanceData->evidenceDocuments as $document) {
                $this->deleteEvidenceFile($document);
            }

            // Delete the performance data
            $performanceData->delete();

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "DELETE",
                "module" => "SAKIP",
                "description" => "Menghapus data kinerja untuk indikator: {$indicatorName}",
                "old_values" => $oldValues,
                "new_values" => null,
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Data kinerja berhasil dihapus.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Delete performance data error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Terjadi kesalahan saat menghapus data kinerja.",
                ],
                500,
            );
        }
    }

    /**
     * Bulk import performance data
     */
    public function bulkImport(Request $request)
    {
        $this->authorize("create", PerformanceData::class);

        $validator = Validator::make($request->all(), [
            "file" => "required|file|mimes:csv,xlsx,xls|max:10240",
            "year" => "required|integer|min:2020|max:" . Carbon::now()->year,
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal.",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        DB::beginTransaction();
        try {
            $file = $request->file("file");
            $user = Auth::user();
            $year = $request->get("year");
            $importedCount = 0;
            $errors = [];

            // Process CSV file
            $fileContent = file_get_contents($file->getRealPath());
            $lines = explode("\n", $fileContent);

            foreach ($lines as $index => $line) {
                if ($index === 0) {
                    continue;
                } // Skip header

                $data = str_getcsv($line);
                if (count($data) < 4) {
                    continue;
                } // Skip invalid rows

                try {
                    $indicatorCode = $data[0];
                    $period = $data[1];
                    $actualValue = $data[2];
                    $notes = $data[3] ?? "";

                    // Find indicator by code
                    $indicator = PerformanceIndicator::where(
                        "instansi_id",
                        $user->instansi_id,
                    )
                        ->where("code", $indicatorCode)
                        ->first();

                    if (!$indicator) {
                        $errors[] =
                            "Baris " .
                            ($index + 1) .
                            ": Indikator dengan kode {$indicatorCode} tidak ditemukan.";
                        continue;
                    }

                    // Parse period
                    $periodDate = Carbon::parse($period);
                    if (!$this->isValidPeriod($indicator, $periodDate)) {
                        $errors[] =
                            "Baris " .
                            ($index + 1) .
                            ": Periode tidak valid untuk indikator ini.";
                        continue;
                    }

                    // Check for existing data
                    $existingData = PerformanceData::where(
                        "indicator_id",
                        $indicator->id,
                    )
                        ->where("period", $periodDate->format("Y-m-d"))
                        ->first();

                    if ($existingData) {
                        $errors[] =
                            "Baris " .
                            ($index + 1) .
                            ": Data untuk periode ini sudah ada.";
                        continue;
                    }

                    // Get target for the period
                    $target = $this->getTargetForPeriod(
                        $indicator,
                        $periodDate,
                    );
                    $targetValue = $target ? $target->target_value : null;

                    // Calculate performance
                    $performancePercentage = $this->calculatePerformancePercentage(
                        $actualValue,
                        $targetValue,
                        $indicator->calculation_formula,
                    );

                    // Create performance data
                    PerformanceData::create([
                        "indicator_id" => $indicator->id,
                        "period" => $periodDate->format("Y-m-d"),
                        "actual_value" => $actualValue,
                        "target_value" => $targetValue,
                        "performance_percentage" => $performancePercentage,
                        "notes" => $notes,
                        "status" => "draft",
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
                "description" => "Mengimpor {$importedCount} data kinerja untuk tahun {$year}",
                "old_values" => null,
                "new_values" => [
                    "imported_count" => $importedCount,
                    "year" => $year,
                ],
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Berhasil mengimpor {$importedCount} data kinerja.",
                "errors" => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error(
                "Bulk import performance data error: " . $e->getMessage(),
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
     * Validate data quality
     */
    public function validateData(
        Request $request,
        PerformanceData $performanceData,
    ) {
        $this->authorize("validate", $performanceData);

        try {
            $validationResult = $this->validationService->validatePerformanceData(
                $performanceData,
            );

            return response()->json([
                "success" => true,
                "data" => $validationResult,
            ]);
        } catch (\Exception $e) {
            \Log::error("Validate performance data error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" => "Terjadi kesalahan saat memvalidasi data.",
                ],
                500,
            );
        }
    }

    /**
     * Get data collection statistics
     */
    private function getDataCollectionStatistics($instansiId, $year)
    {
        $totalIndicators = PerformanceIndicator::where(
            "instansi_id",
            $instansiId,
        )->count();

        $indicatorsWithData = PerformanceIndicator::where(
            "instansi_id",
            $instansiId,
        )
            ->whereHas("performanceData", function ($q) use ($year) {
                $q->whereYear("period", $year);
            })
            ->count();

        $totalDataEntries = PerformanceData::whereHas("indicator", function (
            $q,
        ) use ($instansiId) {
            $q->where("instansi_id", $instansiId);
        })
            ->whereYear("period", $year)
            ->count();

        $pendingReviews = PerformanceData::whereHas("indicator", function (
            $q,
        ) use ($instansiId) {
            $q->where("instansi_id", $instansiId);
        })
            ->whereYear("period", $year)
            ->where("status", "submitted")
            ->count();

        return [
            "total_indicators" => $totalIndicators,
            "indicators_with_data" => $indicatorsWithData,
            "completion_rate" =>
                $totalIndicators > 0
                    ? round(($indicatorsWithData / $totalIndicators) * 100, 2)
                    : 0,
            "total_data_entries" => $totalDataEntries,
            "pending_reviews" => $pendingReviews,
        ];
    }

    /**
     * Handle evidence file uploads
     */
    private function handleEvidenceFiles(
        $files,
        PerformanceData $performanceData,
    ) {
        foreach ($files as $file) {
            if ($file->isValid()) {
                $filename =
                    time() .
                    "_" .
                    uniqid() .
                    "." .
                    $file->getClientOriginalExtension();
                $path = $file->storeAs(
                    "evidence_documents",
                    $filename,
                    "public",
                );

                EvidenceDocument::create([
                    "performance_data_id" => $performanceData->id,
                    "file_name" => $file->getClientOriginalName(),
                    "file_path" => $path,
                    "file_size" => $file->getSize(),
                    "file_type" => $file->getClientMimeType(),
                    "uploaded_by" => Auth::id(),
                ]);
            }
        }
    }

    /**
     * Delete evidence file
     */
    private function deleteEvidenceFile(EvidenceDocument $document)
    {
        try {
            Storage::disk("public")->delete($document->file_path);
            $document->delete();
        } catch (\Exception $e) {
            \Log::error("Delete evidence file error: " . $e->getMessage());
        }
    }

    /**
     * Calculate performance percentage
     */
    private function calculatePerformancePercentage(
        $actualValue,
        $targetValue,
        $calculationFormula,
    ) {
        if (empty($targetValue) || $targetValue == 0) {
            return 0;
        }

        // Simple percentage calculation (can be enhanced based on formula)
        return round(($actualValue / $targetValue) * 100, 2);
    }

    /**
     * Get target for a specific period
     */
    private function getTargetForPeriod(
        PerformanceIndicator $indicator,
        Carbon $period,
    ) {
        return $indicator
            ->targets()
            ->where("year", $period->year)
            ->where("period", "<=", $period->format("Y-m-d"))
            ->orderBy("period", "desc")
            ->first();
    }

    /**
     * Check if period is valid for indicator frequency
     */
    private function isValidPeriod(
        PerformanceIndicator $indicator,
        Carbon $period,
    ) {
        switch ($indicator->frequency) {
            case "monthly":
                return $period->day === 1; // Must be first day of month
            case "quarterly":
                return $period->day === 1 &&
                    in_array($period->month, [1, 4, 7, 10]); // First day of quarter
            case "semester":
                return $period->day === 1 && in_array($period->month, [1, 7]); // First day of semester
            case "annual":
                return $period->format("Y-m-d") ===
                    $period->copy()->startOfYear()->format("Y-m-d"); // First day of year
            default:
                return true;
        }
    }

    /**
     * Get available periods based on frequency and year
     */
    private function getAvailablePeriods($frequency, $year)
    {
        $periods = [];

        switch ($frequency) {
            case "monthly":
                for ($month = 1; $month <= 12; $month++) {
                    $periods[] = Carbon::create($year, $month, 1);
                }
                break;
            case "quarterly":
                foreach ([1, 4, 7, 10] as $month) {
                    $periods[] = Carbon::create($year, $month, 1);
                }
                break;
            case "semester":
                foreach ([1, 7] as $month) {
                    $periods[] = Carbon::create($year, $month, 1);
                }
                break;
            case "annual":
                $periods[] = Carbon::create($year, 1, 1);
                break;
        }

        return $periods;
    }
}
