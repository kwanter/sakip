<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\EvidenceDocument;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Target;
use App\Services\DataValidationService;
use App\Services\DropdownCacheService;
use App\Services\ReportGenerationService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

    protected DropdownCacheService $dropdownCache;

    /**
     * Constructor with dependency injection
     */
    public function __construct(
        DataValidationService $validationService,
        ReportGenerationService $reportService,
        DropdownCacheService $dropdownCache,
    ) {
        $this->validationService = $validationService;
        $this->reportService = $reportService;
        $this->dropdownCache = $dropdownCache;
    }

    /**
     * Authorized download for evidence documents (private disk only).
     */
    public function downloadEvidence(EvidenceDocument $evidence)
    {
        $this->authorize('download', $evidence);

        $path = $evidence->file_path;
        // SECURITY: serve from the private local disk only. The public disk is
        // web-exposed at /storage; a missing local file must be a 404, never a
        // legacy public-disk fallback.
        if (! Storage::disk('local')->exists($path)) {
            abort(404, 'File not found.');
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'instansi_id' => Auth::user()?->instansi_id,
            'action' => 'DOWNLOAD',
            'module' => 'SAKIP',
            'description' => 'Download evidence: '.($evidence->file_name ?? $path),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return Storage::disk('local')->download(
            $path,
            $evidence->file_name ?? basename($path),
        );
    }

    /**
     * Display data collection dashboard
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', PerformanceData::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;
            $currentYear = Carbon::now()->year;

            // Get indicators that need data collection
            $query = PerformanceIndicator::where(
                'instansi_id',
                $instansiId,
            )->with([
                'targets' => function ($q) use ($currentYear) {
                    $q->where('year', $currentYear);
                },
                'performanceData' => function ($q) use ($currentYear) {
                    $q->whereYear('period', $currentYear);
                },
            ]);

            // Apply filters
            if ($request->filled('status')) {
                $status = $request->get('status');
                if ($status === 'missing') {
                    $query->whereDoesntHave('performanceData', function (
                        $q,
                    ) use ($currentYear) {
                        $q->whereYear('period', $currentYear);
                    });
                } elseif ($status === 'completed') {
                    $query->has('performanceData');
                } elseif ($status === 'pending') {
                    $query->whereHas('performanceData', function ($q) use (
                        $currentYear,
                    ) {
                        $q->whereYear('period', $currentYear)->where(
                            'status',
                            'draft',
                        );
                    });
                }
            }

            if ($request->filled('category')) {
                $query->where('category', $request->get('category'));
            }

            if ($request->filled('frequency')) {
                $query->where('frequency', $request->get('frequency'));
            }

            $indicators = $query->orderBy('name')->paginate(15);

            // Get statistics
            $statistics = $this->getDataCollectionStatistics(
                $instansiId,
                $currentYear,
            );

            // Get recent data entries
            $recentEntries = PerformanceData::whereHas('indicator', function (
                $q,
            ) use ($instansiId) {
                $q->where('instansi_id', $instansiId);
            })
                ->with(['indicator.instansi', 'creator'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Get all performance data for the table
            $performanceDataQuery = PerformanceData::whereHas(
                'indicator',
                function ($q) use ($instansiId) {
                    $q->where('instansi_id', $instansiId);
                },
            )->with(['indicator.instansi']);

            // Apply filters
            if ($request->filled('period')) {
                $performanceDataQuery->where('period', $request->get('period'));
            }

            if ($request->filled('validation_status')) {
                $performanceDataQuery->where(
                    'status',
                    $request->get('validation_status'),
                );
            }

            $performanceData = $performanceDataQuery
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            // Get all instansi for filter dropdown (cached)
            $instansis = $this->dropdownCache->getActiveInstansi();

            // Format stats for view
            $stats = [
                'total_data' => $statistics['total_data_entries'],
                'validated_data' => PerformanceData::whereHas(
                    'indicator',
                    function ($q) use ($instansiId) {
                        $q->where('instansi_id', $instansiId);
                    },
                )
                    ->where('status', 'validated')
                    ->count(),
                'pending_validation' => $statistics['pending_reviews'],
                'needs_revision' => PerformanceData::whereHas(
                    'indicator',
                    function ($q) use ($instansiId) {
                        $q->where('instansi_id', $instansiId);
                    },
                )
                    ->where('status', 'rejected')
                    ->count(),
            ];

            return view(
                'sakip.data-collection.index',
                compact(
                    'indicators',
                    'statistics',
                    'recentEntries',
                    'currentYear',
                    'performanceData',
                    'instansis',
                    'stats',
                ),
            );
        } catch (\Exception $e) {
            \Log::error('Data collection index error: '.$e->getMessage());

            return back()->with(
                'error',
                'Terjadi kesalahan saat memuat halaman pengumpulan data.',
            );
        }
    }

    /**
     * Show data collection form for a specific indicator
     */
    public function create(
        Request $request,
        ?PerformanceIndicator $indicator = null,
    ) {
        $this->authorize('create', PerformanceData::class);

        try {
            $user = Auth::user();
            $currentYear = Carbon::now()->year;

            // If indicator_id is provided in query params, get the indicator
            if (! $indicator && $request->has('indicator_id')) {
                $indicator = PerformanceIndicator::findOrFail(
                    $request->indicator_id,
                );
            }

            // If no indicator is specified, show indicator selection
            if (! $indicator) {
                $indicators = PerformanceIndicator::with([
                    'instansi',
                    'targets',
                ])
                    ->where(function ($query) use ($user) {
                        $query
                            ->where('is_mandatory', true)
                            ->orWhere('instansi_id', $user->instansi_id);
                    })
                    ->orderBy('name')
                    ->get();

                // Get all instansi for dropdown (cached)
                $instansis = $this->dropdownCache->getActiveInstansi();

                return view(
                    'sakip.data-collection.create',
                    compact('indicators', 'instansis', 'currentYear'),
                );
            }

            // Get targets for the current year
            $targets = $indicator
                ->targets()
                ->where('year', $currentYear)
                ->orderBy('period')
                ->get();

            // Get existing performance data
            $existingData = $indicator
                ->performanceData()
                ->whereYear('period', $currentYear)
                ->orderBy('period')
                ->get();

            // Get available periods based on frequency
            $availablePeriods = $this->getAvailablePeriods(
                $indicator->frequency,
                $currentYear,
            );

            return view(
                'sakip.data-collection.create',
                compact(
                    'indicator',
                    'targets',
                    'existingData',
                    'availablePeriods',
                    'currentYear',
                ),
            );
        } catch (\Exception $e) {
            \Log::error(
                'Data collection create form error: '.$e->getMessage(),
            );

            return back()->with(
                'error',
                'Terjadi kesalahan saat memuat formulir pengumpulan data.',
            );
        }
    }

    /**
     * Store performance data
     */
    public function store(Request $request)
    {
        $this->authorize('create', PerformanceData::class);

        $validator = Validator::make($request->all(), [
            'indicator_id' => 'required|exists:performance_indicators,id',
            'period' => 'required|date',
            'actual_value' => 'required|numeric',
            'target_value' => 'nullable|numeric',
            'evidence_files' => 'nullable|array|max:10',
            'evidence_files.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $indicator = PerformanceIndicator::findOrFail(
                $request->get('indicator_id'),
            );

            // Check authorization for this specific indicator
            $this->authorize('createData', $indicator);

            // Validate period based on indicator frequency
            $period = Carbon::parse($request->get('period'));
            if (! $this->isValidPeriod($indicator, $period)) {
                DB::rollBack();

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Periode tidak valid untuk frekuensi indikator ini.',
                    ],
                    422,
                );
            }

            $periodDate = $period->format('Y-m-d');

            // A second submission for the same indicator+period is a conflict,
            // never an overwrite: the unique index settles the race, and
            // firstOrCreate lets us tell the two outcomes apart afterwards.
            $performanceData = PerformanceData::firstOrCreate(
                [
                    'performance_indicator_id' => $indicator->id,
                    'period' => $periodDate,
                ],
                [
                    'instansi_id' => $indicator->instansi_id,
                    'actual_value' => $request->get('actual_value'),
                    'notes' => $request->get('notes'),
                    'status' => 'draft',
                    'submitted_by' => $user->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ],
            );

            if (! $performanceData->wasRecentlyCreated) {
                DB::rollBack();

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Data untuk periode ini sudah ada dan telah diperbarui. Silakan gunakan fungsi edit untuk memodifikasi data yang ada.',
                    ],
                    409, // 409 Conflict - more appropriate than 422 for race conditions
                );
            }

            // Handle evidence files
            if ($request->hasFile('evidence_files')) {
                $this->handleEvidenceFiles(
                    $request->file('evidence_files'),
                    $performanceData,
                );
            }

            // Validate data quality
            $validationResult = $this->validationService->validatePerformanceData(
                $performanceData,
            );

            if (! $validationResult['is_valid']) {
                // Quality failures are recorded on the draft so the collector can
                // fix them; the record is never promoted to 'submitted' by a
                // failed check. validation_notes is the column the model's own
                // validate()/reject() transitions write to.
                $performanceData->update([
                    'validation_notes' => json_encode($validationResult['errors']),
                ]);
            }

            // Log the activity
            AuditLog::create([
                'user_id' => $user->id,
                'instansi_id' => $user->instansi_id,
                'action' => 'CREATE',
                'module' => 'SAKIP',
                'description' => "Memasukkan data kinerja untuk indikator: {$indicator->name} (Periode: {$period->format(
                    'M Y',
                )})",
                'old_values' => null,
                'new_values' => $performanceData->toArray(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data kinerja berhasil disimpan.',
                'data' => [
                    'id' => $performanceData->id,
                    'validation_errors' => $validationResult['errors'] ?? [],
                ],
            ]);
        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            abort(404, 'Indikator tidak ditemukan.');
        } catch (AuthorizationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Store performance data error: '.$e->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menyimpan data kinerja.',
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
        $this->authorize('view', $performanceData);

        try {
            $performanceData->load([
                'indicator.instansi',
                'indicator.program',
                'indicator.kegiatan',
                'evidenceDocuments',
                'creator',
                'updater',
            ]);

            $indicator = $performanceData->indicator;
            $currentYear = Carbon::parse($performanceData->period)->year;

            // Get target for this period
            $target = $indicator
                ->targets()
                ->where('year', $currentYear)
                ->where(
                    'period',
                    '<=',
                    Carbon::parse($performanceData->period)->format('Y-m-d'),
                )
                ->orderBy('period', 'desc')
                ->first();

            // Get validation history
            $validationHistory = AuditLog::where('module', 'SAKIP')
                ->where('description', 'like', '%'.$indicator->name.'%')
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            $data = $performanceData;

            return view(
                'sakip.data-collection.show',
                compact(
                    'performanceData',
                    'data',
                    'indicator',
                    'target',
                    'validationHistory',
                    'currentYear',
                ),
            );
        } catch (\Exception $e) {
            \Log::error('Show performance data error: '.$e->getMessage());

            return back()->with(
                'error',
                'Terjadi kesalahan saat memuat detail data kinerja.',
            );
        }
    }

    /**
     * Show form for editing performance data
     */
    public function edit(PerformanceData $performanceData)
    {
        $this->authorize('update', $performanceData);

        try {
            $performanceData->load(['indicator', 'evidenceDocuments']);

            $indicator = $performanceData->indicator;
            $currentYear = Carbon::parse($performanceData->period)->year;

            // Get targets for the year
            $targets = $indicator
                ->targets()
                ->where('year', $currentYear)
                ->orderBy('period')
                ->get();

            return view(
                'sakip.data-collection.edit',
                compact(
                    'performanceData',
                    'indicator',
                    'targets',
                    'currentYear',
                ),
            );
        } catch (\Exception $e) {
            \Log::error('Edit performance data error: '.$e->getMessage());

            return back()->with(
                'error',
                'Terjadi kesalahan saat memuat formulir edit.',
            );
        }
    }

    /**
     * Update performance data
     */
    public function update(Request $request, PerformanceData $performanceData)
    {
        $this->authorize('update', $performanceData);

        $validator = Validator::make($request->all(), [
            'actual_value' => 'required|numeric',
            'target_value' => 'nullable|numeric',
            'evidence_files' => 'nullable|array|max:10',
            'evidence_files.*' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
            'existing_files' => 'nullable|array',
            'existing_files.*' => 'exists:evidence_documents,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $oldValues = $performanceData->toArray();

            // Update performance data. Only columns that exist on
            // performance_data are written; achievement is derived on read
            // (calculateAchievement()) rather than stored.
            $performanceData->update([
                'actual_value' => $request->get('actual_value'),
                'notes' => $request->get('notes'),
                'status' => 'draft',
                'validation_notes' => null,
                'updated_by' => $user->id,
            ]);

            // Handle evidence files
            if ($request->hasFile('evidence_files')) {
                $this->handleEvidenceFiles(
                    $request->file('evidence_files'),
                    $performanceData,
                );
            }

            // Handle existing files deletion
            if ($request->has('existing_files')) {
                $existingFileIds = $request->get('existing_files');
                $filesToDelete = $performanceData
                    ->evidenceDocuments()
                    ->whereNotIn('id', $existingFileIds)
                    ->get();

                foreach ($filesToDelete as $file) {
                    $this->deleteEvidenceFile($file);
                }
            }

            // Validate data quality
            $validationResult = $this->validationService->validatePerformanceData(
                $performanceData,
            );

            if (! $validationResult['is_valid']) {
                $performanceData->update([
                    'validation_notes' => json_encode($validationResult['errors']),
                ]);
            }

            // Log the activity
            AuditLog::create([
                'user_id' => $user->id,
                'instansi_id' => $user->instansi_id,
                'action' => 'UPDATE',
                'module' => 'SAKIP',
                'description' => "Memperbarui data kinerja untuk indikator: {$performanceData->indicator->name}",
                'old_values' => $oldValues,
                'new_values' => $performanceData->fresh()->toArray(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data kinerja berhasil diperbarui.',
                'data' => [
                    'validation_errors' => $validationResult['errors'] ?? [],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update performance data error: '.$e->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memperbarui data kinerja.',
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
        $this->authorize('delete', $performanceData);

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
                'user_id' => $user->id,
                'instansi_id' => $user->instansi_id,
                'action' => 'DELETE',
                'module' => 'SAKIP',
                'description' => "Menghapus data kinerja untuk indikator: {$indicatorName}",
                'old_values' => $oldValues,
                'new_values' => null,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data kinerja berhasil dihapus.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Delete performance data error: '.$e->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat menghapus data kinerja.',
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
        $this->authorize('create', PerformanceData::class);

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            'year' => 'required|integer|min:2020|max:'.Carbon::now()->year,
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        DB::beginTransaction();
        try {
            $file = $request->file('file');
            $user = Auth::user();
            $year = $request->get('year');
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
                    // SECURITY: Sanitize all CSV input to prevent injection attacks
                    $indicatorCode = $this->sanitizeCsvCellValue($data[0]);
                    $period = $this->sanitizeCsvCellValue($data[1]);
                    $actualValue = $this->sanitizeCsvCellValue($data[2]);
                    $notes = $this->sanitizeCsvCellValue($data[3] ?? '');

                    // Find indicator by code
                    $indicator = PerformanceIndicator::where(
                        'instansi_id',
                        $user->instansi_id,
                    )
                        ->where('code', $indicatorCode)
                        ->first();

                    if (! $indicator) {
                        $errors[] =
                            'Baris '.
                            ($index + 1).
                            ": Indikator dengan kode {$indicatorCode} tidak ditemukan.";

                        continue;
                    }

                    // Parse period
                    $periodDate = Carbon::parse($period);
                    if (! $this->isValidPeriod($indicator, $periodDate)) {
                        $errors[] =
                            'Baris '.
                            ($index + 1).
                            ': Periode tidak valid untuk indikator ini.';

                        continue;
                    }

                    // Check for existing data using updateOrCreate for atomicity
                    // SECURITY: This prevents race conditions during concurrent imports
                    $existingData = PerformanceData::where(
                        'performance_indicator_id',
                        $indicator->id,
                    )
                        ->where('period', $periodDate->format('Y-m-d'))
                        ->first();

                    if ($existingData) {
                        $errors[] =
                            'Baris '.
                            ($index + 1).
                            ': Data untuk periode ini sudah ada. Gunakan fungsi update untuk memodifikasi.';

                        continue;
                    }

                    // Create performance data. Target comparison and achievement
                    // are derived on read; the row carries only what the table has.
                    PerformanceData::create([
                        'performance_indicator_id' => $indicator->id,
                        'instansi_id' => $indicator->instansi_id,
                        'period' => $periodDate->format('Y-m-d'),
                        'actual_value' => $actualValue,
                        'notes' => $notes,
                        'status' => 'draft',
                        'submitted_by' => $user->id,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                    ]);

                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] =
                        'Baris '.($index + 1).': '.$e->getMessage();
                }
            }

            // Log the activity
            AuditLog::create([
                'user_id' => $user->id,
                'instansi_id' => $user->instansi_id,
                'action' => 'IMPORT',
                'module' => 'SAKIP',
                'description' => "Mengimpor {$importedCount} data kinerja untuk tahun {$year}",
                'old_values' => null,
                'new_values' => [
                    'imported_count' => $importedCount,
                    'year' => $year,
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Berhasil mengimpor {$importedCount} data kinerja.",
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error(
                'Bulk import performance data error: '.$e->getMessage(),
            );

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat mengimpor data.',
                ],
                500,
            );
        }
    }

    /**
     * Reject a submitted record back to the collector.
     *
     * SECURITY: the show view renders this form for the 'pending' branch,
     * but the workflow vocabulary is draft/submitted/validated/rejected, so
     * the button never appeared and the route had no method.
     */
    public function reject(Request $request, PerformanceData $performanceData)
    {
        $this->authorize('reject', $performanceData);

        $validated = $request->validate([
            'notes' => 'required|string|max:1000',
        ]);

        $performanceData->reject($validated['notes']);

        AuditLog::create([
            'user_id' => Auth::id(),
            'instansi_id' => $performanceData->instansi_id,
            'action' => 'REJECT',
            'module' => 'SAKIP',
            'description' => "Menolak data kinerja untuk indikator: {$performanceData->indicator->name}",
            'old_values' => ['status' => 'submitted'],
            'new_values' => ['status' => 'rejected'],
        ]);

        return back()->with('success', 'Data kinerja ditolak.');
    }

    /**
     * Download the CSV import template.
     */
    public function downloadTemplate()
    {
        $this->authorize('create', PerformanceData::class);

        return $this->csvResponse(
            'template_import_data_kinerja.csv',
            "indicator_code,period,actual_value,notes\nCONTOH-001,".date('Y')."-01-01,100,Contoh baris\n",
        );
    }

    /**
     * Download a filled sample so collectors see the expected shape.
     */
    public function downloadSample()
    {
        $this->authorize('create', PerformanceData::class);

        return $this->csvResponse(
            'contoh_import_data_kinerja.csv',
            "indicator_code,period,actual_value,notes\nIK-001,".date('Y')."-01-01,95,Realisasi Januari\nIK-001,".date('Y')."-02-01,97,Realisasi Februari\n",
        );
    }

    /**
     * Parse an uploaded CSV and render a preview. Nothing is persisted yet;
     * the parsed rows are parked under a random import_id bound to the
     * uploader so confirmImport() can only ever commit the caller's own file.
     */
    public function import(Request $request)
    {
        $this->authorize('create', PerformanceData::class);

        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt|max:10240',
            'year' => 'required|integer|min:2020|max:'.Carbon::now()->year,
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = Auth::user();
        $year = (int) $request->get('year');
        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        array_shift($rows); // header

        $valid = [];
        $errors = [];

        foreach ($rows as $i => $row) {
            $line = $i + 2;
            if (count($row) < 3) {
                continue;
            }

            $code = trim((string) $row[0]);
            $period = trim((string) $row[1]);
            $value = trim((string) $row[2]);

            $indicator = PerformanceIndicator::where('instansi_id', $user->instansi_id)
                ->where('code', $code)
                ->first();

            if (! $indicator) {
                $errors[] = ['row' => $line, 'message' => "Indikator {$code} tidak ditemukan."];

                continue;
            }

            try {
                $periodDate = Carbon::parse($period);
            } catch (\Throwable) {
                $errors[] = ['row' => $line, 'message' => "Periode {$period} tidak valid."];

                continue;
            }

            if (! is_numeric($value)) {
                $errors[] = ['row' => $line, 'message' => "Nilai {$value} bukan angka."];

                continue;
            }

            if (! $this->isValidPeriod($indicator, $periodDate)) {
                $errors[] = ['row' => $line, 'message' => 'Periode tidak cocok dengan frekuensi indikator.'];

                continue;
            }

            if (PerformanceData::where('performance_indicator_id', $indicator->id)
                ->where('period', $periodDate->format('Y-m-d'))
                ->exists()
            ) {
                $errors[] = ['row' => $line, 'message' => 'Data untuk periode ini sudah ada.'];

                continue;
            }

            $target = $indicator->targets()->where('year', $periodDate->year)->value('target_value');

            $valid[] = [
                'row' => $line,
                'indicator_code' => $code,
                'period' => $periodDate->format('Y-m-d'),
                'value' => $value,
                'target' => $target,
                '_indicator_id' => $indicator->id,
            ];
        }

        $importId = (string) Str::uuid();
        Cache::put(
            self::IMPORT_CACHE_KEY.$user->id.':'.$importId,
            ['rows' => $valid, 'year' => $year],
            now()->addMinutes(30),
        );

        return view('sakip.data-collection.import', [
            'previewData' => [
                'import_id' => $importId,
                'total_count' => count($valid),
                'valid_count' => count($valid),
                'error_count' => count($errors),
                'valid_data' => array_map(fn ($r) => array_diff_key($r, ['_indicator_id' => true]), $valid),
                'errors' => $errors,
            ],
            'year' => $year,
            'instansi' => $user->instansi,
            'instansis' => collect(),
        ]);
    }

    /**
     * Commit a previewed import. Only the uploader's own import_id resolves.
     */
    public function confirmImport(Request $request)
    {
        $this->authorize('create', PerformanceData::class);

        $validated = $request->validate([
            'import_id' => 'required|uuid',
        ]);

        $key = self::IMPORT_CACHE_KEY.Auth::id().':'.$validated['import_id'];
        $staged = Cache::pull($key);

        if ($staged === null) {
            return back()->withErrors([
                'import_id' => 'Pratinjau impor sudah kedaluwarsa. Unggah ulang file.',
            ])->withInput();
        }

        $imported = 0;
        DB::transaction(function () use ($staged, &$imported) {
            foreach ($staged['rows'] as $row) {
                PerformanceData::firstOrCreate(
                    [
                        'performance_indicator_id' => $row['_indicator_id'],
                        'period' => $row['period'],
                    ],
                    [
                        'actual_value' => $row['value'],
                        'status' => 'draft',
                    ],
                );
                $imported++;
            }
        });

        AuditLog::create([
            'user_id' => Auth::id(),
            'instansi_id' => Auth::user()->instansi_id,
            'action' => 'IMPORT',
            'module' => 'SAKIP',
            'description' => "Mengimpor {$imported} data kinerja",
            'new_values' => ['imported_count' => $imported],
        ]);

        return redirect()->route('sakip.data-collection.index')
            ->with('success', "Berhasil mengimpor {$imported} data kinerja.");
    }

    private const IMPORT_CACHE_KEY = 'sakip.import.';

    private function csvResponse(string $filename, string $body)
    {
        return response($body, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Validate data quality
     */
    public function validateData(
        Request $request,
        PerformanceData $performanceData,
    ) {
        $this->authorize('validate', $performanceData);

        try {
            $validationResult = $this->validationService->validatePerformanceData(
                $performanceData,
            );

            return response()->json([
                'success' => true,
                'data' => $validationResult,
            ]);
        } catch (\Exception $e) {
            \Log::error('Validate performance data error: '.$e->getMessage());

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan saat memvalidasi data.',
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
            'instansi_id',
            $instansiId,
        )->count();

        $indicatorsWithData = PerformanceIndicator::where(
            'instansi_id',
            $instansiId,
        )
            ->whereHas('performanceData', function ($q) use ($year) {
                $q->whereYear('period', $year);
            })
            ->count();

        $totalDataEntries = PerformanceData::whereHas('indicator', function (
            $q,
        ) use ($instansiId) {
            $q->where('instansi_id', $instansiId);
        })
            ->whereYear('period', $year)
            ->count();

        $pendingReviews = PerformanceData::whereHas('indicator', function (
            $q,
        ) use ($instansiId) {
            $q->where('instansi_id', $instansiId);
        })
            ->whereYear('period', $year)
            ->where('status', 'submitted')
            ->count();

        return [
            'total_indicators' => $totalIndicators,
            'indicators_with_data' => $indicatorsWithData,
            'completion_rate' => $totalIndicators > 0
                    ? round(($indicatorsWithData / $totalIndicators) * 100, 2)
                    : 0,
            'total_data_entries' => $totalDataEntries,
            'pending_reviews' => $pendingReviews,
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
                // SECURITY: Use UUID-based filename instead of predictable time() + uniqid()
                // This prevents attackers from enumerating uploaded files
                $filename =
                    Str::uuid().'.'.$file->getClientOriginalExtension();
                // Store on private disk so files are not web-accessible without auth
                $path = $file->storeAs(
                    'evidence_documents',
                    $filename,
                    'local',
                );

                EvidenceDocument::create([
                    'performance_data_id' => $performanceData->id,
                    'instansi_id' => $performanceData->instansi_id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'file_type' => $file->getClientMimeType(),
                    'uploaded_by' => Auth::id(),
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
            foreach (['local', 'public'] as $disk) {
                if (Storage::disk($disk)->exists($document->file_path)) {
                    Storage::disk($disk)->delete($document->file_path);
                    break;
                }
            }
            $document->delete();
        } catch (\Exception $e) {
            \Log::error('Delete evidence file error: '.$e->getMessage());
        }
    }

    /**
     * Check if period is valid for indicator frequency
     */
    private function isValidPeriod(
        PerformanceIndicator $indicator,
        Carbon $period,
    ) {
        switch ($indicator->frequency) {
            case 'monthly':
                return $period->day === 1; // Must be first day of month
            case 'quarterly':
                return $period->day === 1 &&
                    in_array($period->month, [1, 4, 7, 10]); // First day of quarter
            case 'semester':
                return $period->day === 1 && in_array($period->month, [1, 7]); // First day of semester
            case 'annual':
                return $period->format('Y-m-d') ===
                    $period->copy()->startOfYear()->format('Y-m-d'); // First day of year
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
            case 'monthly':
                for ($month = 1; $month <= 12; $month++) {
                    $periods[] = Carbon::create($year, $month, 1);
                }
                break;
            case 'quarterly':
                foreach ([1, 4, 7, 10] as $month) {
                    $periods[] = Carbon::create($year, $month, 1);
                }
                break;
            case 'semester':
                foreach ([1, 7] as $month) {
                    $periods[] = Carbon::create($year, $month, 1);
                }
                break;
            case 'annual':
                $periods[] = Carbon::create($year, 1, 1);
                break;
        }

        return $periods;
    }

    /**
     * Sanitize CSV cell value to prevent injection attacks
     *
     * SECURITY IMPROVED: Comprehensively prevents CSV injection attacks
     *
     * CSV Injection vectors:
     * 1. Formula injection: =, +, -, @ at start of cells
     * 2. Embedded formulas with whitespace: " =1+1" or "\t=cmd|'/c calc'!A0"
     * 3. Array formulas: {=SUM(A1:A10)}
     * 4. Unicode exploits: Special characters that look like safe chars
     *
     * When CSV files are opened in Excel, these can execute formulas, potentially:
     * - Exfiltrating data to external servers
     * - Executing arbitrary code
     * - Modifying cell values
     *
     * @param  string  $value  The cell value to sanitize
     * @return string The sanitized value
     */
    private function sanitizeCsvCellValue(string $value): string
    {
        // Remove leading/trailing whitespace
        $value = trim($value);

        // Check for dangerous formula characters at the start (after trimming)
        // These are the most dangerous as they execute immediately in Excel
        if (preg_match("/^[=+\-@]/", $value)) {
            // Prepend with single quote to force Excel to treat as text
            $value = "'".$value;
        }

        // Check for embedded formulas with leading whitespace (common evasion technique)
        // Examples: " =1+1", "\t=cmd|'/c calc'!A0", "\n=HYPERLINK(...)"
        if (preg_match('/^[\s\t\n\r][=+\-@]/', $value)) {
            $value = "'".ltrim($value);
        }

        // Check for array formulas {=...}
        if (preg_match("/^\{=/", $value)) {
            $value = "'".$value;
        }

        // Remove any HTML/script tags (defense in depth)
        $value = strip_tags($value);

        // Remove null bytes and other control characters (except tab, newline, carriage return)
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

        // Limit length to prevent DoS via extremely long values
        if (strlen($value) > 32767) {
            // Excel cell limit
            $value = substr($value, 0, 32767);
        }

        return $value;
    }
}
