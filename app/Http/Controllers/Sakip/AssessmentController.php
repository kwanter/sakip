<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentCriterion;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\AuditLog;
use App\Services\AssessmentService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Assessment Controller
 *
 * Handles assessment workflows, evaluation forms, and approval processes
 * for the SAKIP module with comprehensive assessment management capabilities.
 */
class AssessmentController extends Controller
{
    protected AssessmentService $assessmentService;
    protected NotificationService $notificationService;

    /**
     * Constructor with dependency injection
     */
    public function __construct(
        AssessmentService $assessmentService,
        NotificationService $notificationService,
    ) {
        $this->assessmentService = $assessmentService;
        $this->notificationService = $notificationService;
    }

    /**
     * Display assessment dashboard
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny", Assessment::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;
            $currentYear = Carbon::now()->year;

            // Get assessments based on user role
            $query = Assessment::with([
                "indicator",
                "assessor",
                "reviewer",
            ])->whereYear("created_at", $currentYear);

            // Role-based filtering
            if ($user->hasRole("assessor")) {
                $query->where("assessor_id", $user->id);
            } elseif ($user->hasRole("reviewer")) {
                $query->where("reviewer_id", $user->id);
            } elseif (!$user->hasRole("superadmin")) {
                $query->whereHas("indicator", function ($q) use ($instansiId) {
                    $q->where(
                        "performance_indicators.instansi_id",
                        $instansiId,
                    );
                });
            }

            // Apply filters
            if ($request->filled("status")) {
                $query->where("status", $request->get("status"));
            }

            if ($request->filled("category")) {
                $query->whereHas("indicator", function ($q) use ($request) {
                    $q->where("category", $request->get("category"));
                });
            }

            if ($request->filled("priority")) {
                $query->where("priority", $request->get("priority"));
            }

            if ($request->filled("period")) {
                $query->whereYear("created_at", $request->get("period"));
            }

            $assessments = $query->orderBy("created_at", "desc")->paginate(15);

            // Get assessment statistics
            $statistics = $this->getAssessmentStatistics($user, $currentYear);

            // Get pending assessments
            $pendingAssessments = $this->getPendingAssessments(
                $user,
                $currentYear,
            );

            // Get recent activities
            $recentActivities = Assessment::with([
                "indicator",
                "assessor",
                "reviewer",
            ])
                ->whereYear("created_at", $currentYear)
                ->where(function ($q) use ($user, $instansiId) {
                    if (!$user->hasRole("superadmin")) {
                        $q->whereHas("indicator", function ($subQ) use (
                            $instansiId,
                        ) {
                            $subQ->where(
                                "performance_indicators.instansi_id",
                                $instansiId,
                            );
                        });
                    }
                })
                ->orderBy("updated_at", "desc")
                ->limit(10)
                ->get();

            // Get indicators for filter dropdown
            $indicators = PerformanceIndicator::query();
            if (!$user->hasRole("superadmin")) {
                $indicators->where("instansi_id", $instansiId);
            }
            $indicators = $indicators->orderBy("name")->get();

            return view(
                "sakip.assessments.index",
                compact(
                    "assessments",
                    "statistics",
                    "pendingAssessments",
                    "recentActivities",
                    "indicators",
                    "currentYear",
                ),
            );
        } catch (\Exception $e) {
            Log::error("Assessment index error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat halaman penilaian.",
            );
        }
    }

    /**
     * Show assessment form for a specific indicator
     */
    public function create(Request $request, PerformanceIndicator $indicator)
    {
        $this->authorize("create", Assessment::class);

        try {
            $user = Auth::user();
            $currentYear = Carbon::now()->year;

            // Get performance data for the indicator
            $performanceData = PerformanceData::where(
                "performance_indicator_id",
                $indicator->id,
            )
                ->whereYear("period", $currentYear)
                ->where("status", "approved")
                ->orderBy("period", "desc")
                ->get();

            if ($performanceData->isEmpty()) {
                return back()->with(
                    "warning",
                    "Tidak ada data kinerja yang tersedia untuk penilaian.",
                );
            }

            // Get assessment criteria
            $criteria = AssessmentCriterion::where(
                "category",
                $indicator->category,
            )
                ->orWhere("category", "general")
                ->orderBy("order")
                ->get();

            // Get available assessment periods
            $availablePeriods = $this->getAvailableAssessmentPeriods(
                $indicator,
            );

            return view(
                "sakip.assessments.create",
                compact(
                    "indicator",
                    "performanceData",
                    "criteria",
                    "availablePeriods",
                    "currentYear",
                ),
            );
        } catch (\Exception $e) {
            Log::error("Assessment create form error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat formulir penilaian.",
            );
        }
    }

    /**
     * Store assessment
     */
    public function store(Request $request)
    {
        $this->authorize("create", Assessment::class);

        $validator = Validator::make($request->all(), [
            "performance_data_id" => "required|exists:performance_data,id",
            "assessment_type" =>
                "required|in:monthly,quarterly,semester,annual",
            "priority" => "required|in:low,medium,high",
            "criteria_scores" => "required|array",
            "criteria_scores.*" => "required|numeric|between:0,100",
            "overall_score" => "required|numeric|between:0,100",
            "strengths" => "nullable|string|max:2000",
            "weaknesses" => "nullable|string|max:2000",
            "recommendations" => "nullable|string|max:2000",
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
            $performanceData = PerformanceData::findOrFail(
                $request->get("performance_data_id"),
            );

            // Check authorization for this specific performance data
            $this->authorize("assess", $performanceData);

            // Check for existing assessment for this performance data
            $existingAssessment = Assessment::where(
                "performance_data_id",
                $performanceData->id,
            )
                ->where("assessment_type", $request->get("assessment_type"))
                ->first();

            if ($existingAssessment) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Penilaian untuk data kinerja ini sudah ada.",
                    ],
                    422,
                );
            }

            // Create assessment
            $assessment = Assessment::create([
                "performance_data_id" => $performanceData->id,
                "assessed_by" => $user->id,
                "assessment_type" => $request->get("assessment_type"),
                "priority" => $request->get("priority"),
                "overall_score" => $request->get("overall_score"),
                "strengths" => $request->get("strengths"),
                "weaknesses" => $request->get("weaknesses"),
                "recommendations" => $request->get("recommendations"),
                "notes" => $request->get("notes"),
                "status" => "draft",
                "created_by" => $user->id,
                "updated_by" => $user->id,
            ]);

            // Store criteria scores
            foreach (
                $request->get("criteria_scores")
                as $criterionId => $score
            ) {
                $assessment->criteriaScores()->create([
                    "assessment_criterion_id" => $criterionId,
                    "score" => $score,
                    "created_by" => $user->id,
                ]);
            }

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "CREATE",
                "module" => "SAKIP",
                "description" => "Membuat penilaian untuk data kinerja ID: {$performanceData->id}",
                "old_values" => null,
                "new_values" => $assessment->toArray(),
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Penilaian berhasil dibuat.",
                "data" => [
                    "id" => $assessment->id,
                    "status" => $assessment->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Store assessment error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" => "Terjadi kesalahan saat membuat penilaian.",
                ],
                500,
            );
        }
    }

    /**
     * Show assessment details
     */
    public function show(Assessment $assessment)
    {
        $this->authorize("view", $assessment);

        try {
            $assessment->load([
                "indicator",
                "assessor",
                "reviewer",
                "criteriaScores.criterion",
                "performanceData",
                "evidenceDocuments",
                "approvalHistory",
            ]);

            // Get assessment criteria
            $criteria = AssessmentCriterion::where(
                "category",
                $assessment->indicator->category,
            )
                ->orWhere("category", "general")
                ->orderBy("order")
                ->get();

            // Calculate detailed scoring
            $scoring = $this->assessmentService->calculateDetailedScoring(
                $assessment,
            );

            // Get related assessments
            $relatedAssessments = Assessment::where(
                "performance_data_id",
                $assessment->performance_data_id,
            )
                ->where("id", "!=", $assessment->id)
                ->orderBy("created_at", "desc")
                ->limit(5)
                ->get();

            return view(
                "sakip.assessments.show",
                compact(
                    "assessment",
                    "criteria",
                    "scoring",
                    "relatedAssessments",
                ),
            );
        } catch (\Exception $e) {
            Log::error("Show assessment error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat detail penilaian.",
            );
        }
    }

    /**
     * Show edit assessment form
     */
    public function edit(Assessment $assessment)
    {
        $this->authorize("update", $assessment);

        try {
            $assessment->load(["indicator", "criteriaScores.criterion"]);

            // Check if assessment can be edited
            if (!$this->assessmentService->canEditAssessment($assessment)) {
                return back()->with(
                    "warning",
                    "Penilaian ini tidak dapat diubah karena sudah dalam proses persetujuan atau telah disetujui.",
                );
            }

            // Get assessment criteria
            $criteria = AssessmentCriterion::where(
                "category",
                $assessment->indicator->category,
            )
                ->orWhere("category", "general")
                ->orderBy("order")
                ->get();

            return view(
                "sakip.assessments.edit",
                compact("assessment", "criteria"),
            );
        } catch (\Exception $e) {
            Log::error("Edit assessment error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat formulir edit penilaian.",
            );
        }
    }

    /**
     * Update assessment
     */
    public function update(Request $request, Assessment $assessment)
    {
        $this->authorize("update", $assessment);

        $validator = Validator::make($request->all(), [
            "criteria_scores" => "required|array",
            "criteria_scores.*" => "required|numeric|between:0,100",
            "overall_score" => "required|numeric|between:0,100",
            "strengths" => "nullable|string|max:2000",
            "weaknesses" => "nullable|string|max:2000",
            "recommendations" => "nullable|string|max:2000",
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
            $oldValues = $assessment->toArray();

            // Check if assessment can be updated
            if (!$this->assessmentService->canEditAssessment($assessment)) {
                return response()->json(
                    [
                        "success" => false,
                        "message" => "Penilaian ini tidak dapat diubah.",
                    ],
                    422,
                );
            }

            // Update assessment
            $assessment->update([
                "overall_score" => $request->get("overall_score"),
                "strengths" => $request->get("strengths"),
                "weaknesses" => $request->get("weaknesses"),
                "recommendations" => $request->get("recommendations"),
                "notes" => $request->get("notes"),
                "updated_by" => $user->id,
            ]);

            // Update criteria scores
            foreach (
                $request->get("criteria_scores")
                as $criterionId => $score
            ) {
                $criteriaScore = $assessment
                    ->criteriaScores()
                    ->where("assessment_criterion_id", $criterionId)
                    ->first();

                if ($criteriaScore) {
                    $criteriaScore->update([
                        "score" => $score,
                        "updated_by" => $user->id,
                    ]);
                } else {
                    $assessment->criteriaScores()->create([
                        "assessment_criterion_id" => $criterionId,
                        "score" => $score,
                        "created_by" => $user->id,
                    ]);
                }
            }

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "UPDATE",
                "module" => "SAKIP",
                "description" => "Memperbarui penilaian untuk indikator: {$assessment->indicator->name}",
                "old_values" => $oldValues,
                "new_values" => $assessment->fresh()->toArray(),
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Penilaian berhasil diperbarui.",
                "data" => [
                    "id" => $assessment->id,
                    "status" => $assessment->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Update assessment error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Terjadi kesalahan saat memperbarui penilaian.",
                ],
                500,
            );
        }
    }

    /**
     * Submit assessment for review
     */
    public function submitForReview(Assessment $assessment)
    {
        $this->authorize("submitForReview", $assessment);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $oldStatus = $assessment->status;

            // Update assessment status
            $assessment->update([
                "status" => "pending_review",
                "submitted_at" => Carbon::now(),
                "submitted_by" => $user->id,
                "updated_by" => $user->id,
            ]);

            // Notify reviewer
            if ($assessment->reviewer) {
                $this->notificationService->notifyAssessmentSubmission(
                    $assessment->reviewer,
                    $assessment,
                );
            }

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "SUBMIT_FOR_REVIEW",
                "module" => "SAKIP",
                "description" => "Mengirim penilaian untuk direview: {$assessment->indicator->name}",
                "old_values" => ["status" => $oldStatus],
                "new_values" => ["status" => "pending_review"],
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Penilaian berhasil dikirim untuk direview.",
                "data" => [
                    "id" => $assessment->id,
                    "status" => $assessment->status,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error(
                "Submit assessment for review error: " . $e->getMessage(),
            );
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Terjadi kesalahan saat mengirim penilaian untuk direview.",
                ],
                500,
            );
        }
    }

    /**
     * Review assessment
     */
    public function review(Request $request, Assessment $assessment)
    {
        $this->authorize("review", $assessment);

        $validator = Validator::make($request->all(), [
            "review_decision" => "required|in:approved,rejected,needs_revision",
            "review_comments" => "required|string|max:2000",
            "review_score" => "nullable|numeric|between:0,100",
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
            $oldStatus = $assessment->status;
            $reviewDecision = $request->get("review_decision");

            // Update assessment based on review decision
            $updateData = [
                "reviewer_id" => $user->id,
                "review_comments" => $request->get("review_comments"),
                "review_score" => $request->get("review_score"),
                "reviewed_at" => Carbon::now(),
                "updated_by" => $user->id,
            ];

            switch ($reviewDecision) {
                case "approved":
                    $updateData["status"] = "approved";
                    $updateData["approved_at"] = Carbon::now();
                    $updateData["approved_by"] = $user->id;
                    break;
                case "rejected":
                    $updateData["status"] = "rejected";
                    break;
                case "needs_revision":
                    $updateData["status"] = "needs_revision";
                    break;
            }

            $assessment->update($updateData);

            // Notify assessor
            $this->notificationService->notifyAssessmentReview(
                $assessment->assessor,
                $assessment,
                $reviewDecision,
            );

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "REVIEW",
                "module" => "SAKIP",
                "description" => "Mereview penilaian: {$assessment->indicator->name} (Keputusan: {$reviewDecision})",
                "old_values" => ["status" => $oldStatus],
                "new_values" => ["status" => $updateData["status"]],
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Penilaian berhasil direview.",
                "data" => [
                    "id" => $assessment->id,
                    "status" => $assessment->status,
                    "decision" => $reviewDecision,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Review assessment error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" => "Terjadi kesalahan saat mereview penilaian.",
                ],
                500,
            );
        }
    }

    /**
     * Delete assessment
     */
    public function destroy(Assessment $assessment)
    {
        $this->authorize("delete", $assessment);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $indicatorName = $assessment->indicator->name;
            $oldValues = $assessment->toArray();

            // Check if assessment can be deleted
            if (!$this->assessmentService->canDeleteAssessment($assessment)) {
                return response()->json(
                    [
                        "success" => false,
                        "message" =>
                            "Penilaian ini tidak dapat dihapus karena sudah disetujui.",
                    ],
                    422,
                );
            }

            // Delete criteria scores
            $assessment->criteriaScores()->delete();

            // Delete evidence documents
            foreach ($assessment->evidenceDocuments as $document) {
                $this->deleteEvidenceDocument($document);
            }

            // Delete the assessment
            $assessment->delete();

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "DELETE",
                "module" => "SAKIP",
                "description" => "Menghapus penilaian untuk indikator: {$indicatorName}",
                "old_values" => $oldValues,
                "new_values" => null,
            ]);

            DB::commit();

            return response()->json([
                "success" => true,
                "message" => "Penilaian berhasil dihapus.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Delete assessment error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" => "Terjadi kesalahan saat menghapus penilaian.",
                ],
                500,
            );
        }
    }

    /**
     * Get assessment statistics
     */
    public function getStatistics(Request $request)
    {
        $this->authorize("viewAny", Assessment::class);

        try {
            $user = Auth::user();
            $year = $request->get("year", Carbon::now()->year);

            $statistics = $this->getAssessmentStatistics($user, $year);

            return response()->json([
                "success" => true,
                "data" => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error("Get assessment statistics error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Terjadi kesalahan saat mengambil statistik penilaian.",
                ],
                500,
            );
        }
    }

    /**
     * Get assessment statistics
     */
    private function getAssessmentStatistics($user, $year)
    {
        $query = Assessment::whereYear("created_at", $year);

        // Role-based filtering
        if ($user->hasRole("assessor")) {
            $query->where("assessor_id", $user->id);
        } elseif ($user->hasRole("reviewer")) {
            $query->where("reviewer_id", $user->id);
        } elseif (!$user->hasRole("superadmin")) {
            $query->whereHas("indicator", function ($q) use ($user) {
                $q->where(
                    "performance_indicators.instansi_id",
                    $user->instansi_id,
                );
            });
        }

        $totalAssessments = $query->count();
        $byStatus = $query
            ->select("status", DB::raw("count(*) as count"))
            ->groupBy("status")
            ->pluck("count", "status")
            ->toArray();

        $averageScore = $query->avg("overall_score");

        return [
            "total_assessments" => $totalAssessments,
            "by_status" => $byStatus,
            "average_score" => round($averageScore, 2),
            "pending_review" => $byStatus["pending_review"] ?? 0,
            "approved" => $byStatus["approved"] ?? 0,
            "rejected" => $byStatus["rejected"] ?? 0,
            "needs_revision" => $byStatus["needs_revision"] ?? 0,
        ];
    }

    /**
     * Get pending assessments
     */
    private function getPendingAssessments($user, $year)
    {
        $query = Assessment::with(["performanceData", "assessor"])->whereYear(
            "created_at",
            $year,
        );

        if ($user->hasRole("assessor")) {
            $query->where("assessor_id", $user->id)->where("status", "draft");
        } elseif ($user->hasRole("reviewer")) {
            $query
                ->where("reviewer_id", $user->id)
                ->where("status", "pending_review");
        } elseif (!$user->hasRole("superadmin")) {
            $query
                ->whereHas("indicator", function ($q) use ($user) {
                    $q->where(
                        "performance_indicators.instansi_id",
                        $user->instansi_id,
                    );
                })
                ->whereIn("status", ["draft", "pending_review"]);
        } else {
            $query->whereIn("status", ["draft", "pending_review"]);
        }

        return $query->orderBy("created_at", "desc")->limit(10)->get();
    }

    /**
     * Get available assessment periods
     */
    private function getAvailableAssessmentPeriods(
        PerformanceIndicator $indicator,
    ) {
        $currentYear = Carbon::now()->year;
        $periods = [];

        switch ($indicator->frequency) {
            case "monthly":
                for ($month = 1; $month <= 12; $month++) {
                    $periods[] = Carbon::create($currentYear, $month, 1);
                }
                break;
            case "quarterly":
                foreach ([1, 4, 7, 10] as $month) {
                    $periods[] = Carbon::create($currentYear, $month, 1);
                }
                break;
            case "semester":
                foreach ([1, 7] as $month) {
                    $periods[] = Carbon::create($currentYear, $month, 1);
                }
                break;
            case "annual":
                $periods[] = Carbon::create($currentYear, 1, 1);
                break;
        }

        return $periods;
    }

    /**
     * Delete evidence document
     */
    private function deleteEvidenceDocument($document)
    {
        try {
            // Validate and sanitize file path to prevent path traversal attacks
            $filePath = $document->file_path;

            // Remove any path traversal attempts
            $filePath = str_replace(["../", "..\\", "./"], "", $filePath);

            // Ensure the file path doesn't start with a slash (absolute path)
            $filePath = ltrim($filePath, "/\\");

            // Construct the full path
            $fullPath = storage_path("app/public/" . $filePath);

            // Verify the resolved path is still within the storage directory
            $storagePath = realpath(storage_path("app/public"));
            $resolvedPath = realpath(dirname($fullPath));

            if (
                $resolvedPath === false ||
                strpos($resolvedPath, $storagePath) !== 0
            ) {
                Log::warning(
                    "Attempted path traversal detected: " .
                        $document->file_path,
                );
                throw new \Exception("Invalid file path detected.");
            }

            // Delete physical file if it exists
            if (file_exists($fullPath) && is_file($fullPath)) {
                if (!unlink($fullPath)) {
                    throw new \Exception("Failed to delete file: " . $filePath);
                }
            }

            // Delete database record
            $document->delete();

            return true;
        } catch (\Exception $e) {
            Log::error("Delete evidence document error: " . $e->getMessage(), [
                "document_id" => $document->id ?? null,
                "file_path" => $document->file_path ?? null,
            ]);
            throw $e;
        }
    }
}
