<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\Target;
use App\Models\PerformanceIndicator;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Target Controller
 *
 * Manages targets for performance indicators including CRUD operations
 * and approval workflow (validate, approve, reject)
 */
class TargetController extends Controller
{
    /**
     * Display a listing of targets for a specific indicator
     */
    public function index(PerformanceIndicator $indicator)
    {
        $this->authorize("view", $indicator);

        try {
            $targets = $indicator
                ->targets()
                ->orderBy("year", "desc")
                ->paginate(10);

            return view("sakip.targets.index", compact("indicator", "targets"));
        } catch (\Exception $e) {
            \Log::error("Target index error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat daftar target.",
            );
        }
    }

    /**
     * Show the form for creating a new target
     */
    public function create(PerformanceIndicator $indicator)
    {
        $this->authorize("update", $indicator);

        try {
            // Get years that don't have targets yet
            $existingYears = $indicator->targets()->pluck("year")->toArray();
            $currentYear = Carbon::now()->year;
            $availableYears = [];

            for ($year = $currentYear; $year <= $currentYear + 5; $year++) {
                if (!in_array($year, $existingYears)) {
                    $availableYears[] = $year;
                }
            }

            return view(
                "sakip.targets.create",
                compact("indicator", "availableYears"),
            );
        } catch (\Exception $e) {
            \Log::error("Target create form error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat formulir.",
            );
        }
    }

    /**
     * Store a newly created target
     */
    public function store(Request $request, PerformanceIndicator $indicator)
    {
        $this->authorize("update", $indicator);

        $validator = Validator::make(
            $request->all(),
            [
                "year" =>
                    "required|integer|min:" .
                    Carbon::now()->year .
                    "|unique:targets,year,NULL,id,performance_indicator_id," .
                    $indicator->id,
                "target_value" => "required|numeric|min:0",
                "minimum_value" => "nullable|numeric|min:0",
                "justification" => "nullable|string|max:1000",
            ],
            [
                "year.required" => "Tahun wajib diisi",
                "year.unique" => "Target untuk tahun ini sudah ada",
                "target_value.required" => "Nilai target wajib diisi",
                "target_value.numeric" => "Nilai target harus berupa angka",
            ],
        );

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();

            $target = Target::create([
                "performance_indicator_id" => $indicator->id,
                "year" => $request->year,
                "target_value" => $request->target_value,
                "minimum_value" => $request->minimum_value,
                "justification" => $request->justification,
                "status" => "draft",
                "created_by" => $user->id,
                "updated_by" => $user->id,
            ]);

            // Log activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "CREATE",
                "module" => "SAKIP",
                "description" => "Membuat target tahun {$target->year} untuk indikator {$indicator->name}",
                "old_values" => null,
                "new_values" => $target->toArray(),
            ]);

            DB::commit();

            return redirect()
                ->route("sakip.indicators.show", $indicator)
                ->with("success", "Target berhasil ditambahkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Target store error: " . $e->getMessage());
            return back()
                ->withInput()
                ->with("error", "Terjadi kesalahan saat menyimpan target.");
        }
    }

    /**
     * Show the form for editing the specified target
     */
    public function edit(PerformanceIndicator $indicator, Target $target)
    {
        $this->authorize("update", $indicator);

        // Allow editing for all statuses including approved
        try {
            return view("sakip.targets.edit", compact("indicator", "target"));
        } catch (\Exception $e) {
            \Log::error("Target edit form error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat formulir edit.",
            );
        }
    }

    /**
     * Update the specified target
     */
    public function update(
        Request $request,
        PerformanceIndicator $indicator,
        Target $target,
    ) {
        $this->authorize("update", $indicator);

        // Allow editing for all statuses including approved
        $validator = Validator::make($request->all(), [
            "target_value" => "required|numeric|min:0",
            "minimum_value" => "nullable|numeric|min:0",
            "justification" => "nullable|string|max:1000",
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $oldValues = $target->toArray();
            $previousStatus = $target->status;

            // If target was approved, reset to draft when edited
            $newStatus =
                $previousStatus === "approved" ? "draft" : $previousStatus;

            $target->update([
                "target_value" => $request->target_value,
                "minimum_value" => $request->minimum_value,
                "justification" => $request->justification,
                "status" => $newStatus,
                "updated_by" => $user->id,
            ]);

            // Log activity
            $description = "Memperbarui target tahun {$target->year} untuk indikator {$indicator->name}";
            if ($previousStatus === "approved") {
                $description .= " (status direset dari approved ke draft)";
            }

            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "UPDATE",
                "module" => "SAKIP",
                "description" => $description,
                "old_values" => $oldValues,
                "new_values" => $target->fresh()->toArray(),
            ]);

            DB::commit();

            $message = "Target berhasil diperbarui.";
            if ($previousStatus === "approved") {
                $message .=
                    " Status target direset ke Draft dan perlu disetujui kembali.";
            }

            return redirect()
                ->route("sakip.indicators.show", $indicator)
                ->with("success", $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Target update error: " . $e->getMessage());
            return back()
                ->withInput()
                ->with("error", "Terjadi kesalahan saat memperbarui target.");
        }
    }

    /**
     * Remove the specified target
     */
    public function destroy(PerformanceIndicator $indicator, Target $target)
    {
        $this->authorize("update", $indicator);

        // Only allow deletion if status is draft or rejected
        if (!in_array($target->status, ["draft", "rejected"])) {
            return back()->with(
                "error",
                "Target yang sudah disetujui tidak dapat dihapus.",
            );
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $year = $target->year;

            $target->delete();

            // Log activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "DELETE",
                "module" => "SAKIP",
                "description" => "Menghapus target tahun {$year} untuk indikator {$indicator->name}",
                "old_values" => $target->toArray(),
                "new_values" => null,
            ]);

            DB::commit();

            return redirect()
                ->route("sakip.indicators.show", $indicator)
                ->with("success", "Target berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Target destroy error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat menghapus target.",
            );
        }
    }

    /**
     * Approve the specified target
     */
    public function approve(PerformanceIndicator $indicator, Target $target)
    {
        // Check if user has permission to approve
        $user = Auth::user();
        if (!$user->can("approve-targets")) {
            return back()->with(
                "error",
                "Anda tidak memiliki izin untuk menyetujui target.",
            );
        }

        if ($target->status === "approved") {
            return back()->with("info", "Target sudah disetujui sebelumnya.");
        }

        DB::beginTransaction();
        try {
            $oldValues = $target->toArray();

            $target->update([
                "status" => "approved",
                "approved_by" => $user->id,
                "approved_at" => Carbon::now(),
                "updated_by" => $user->id,
            ]);

            // Log activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "APPROVE",
                "module" => "SAKIP",
                "description" => "Menyetujui target tahun {$target->year} untuk indikator {$indicator->name}",
                "old_values" => $oldValues,
                "new_values" => $target->fresh()->toArray(),
            ]);

            DB::commit();

            return back()->with("success", "Target berhasil disetujui.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Target approve error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat menyetujui target.",
            );
        }
    }

    /**
     * Reject the specified target
     */
    public function reject(
        Request $request,
        PerformanceIndicator $indicator,
        Target $target,
    ) {
        // Check if user has permission to reject
        $user = Auth::user();
        if (!$user->can("approve-targets")) {
            return back()->with(
                "error",
                "Anda tidak memiliki izin untuk menolak target.",
            );
        }

        $validator = Validator::make(
            $request->all(),
            [
                "notes" => "required|string|max:1000",
            ],
            [
                "notes.required" => "Alasan penolakan wajib diisi",
            ],
        );

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $oldValues = $target->toArray();

            $target->update([
                "status" => "rejected",
                "approved_by" => $user->id,
                "approved_at" => Carbon::now(),
                "notes" => $request->notes,
                "updated_by" => $user->id,
            ]);

            // Log activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "REJECT",
                "module" => "SAKIP",
                "description" => "Menolak target tahun {$target->year} untuk indikator {$indicator->name}",
                "old_values" => $oldValues,
                "new_values" => $target->fresh()->toArray(),
            ]);

            DB::commit();

            return back()->with("success", "Target berhasil ditolak.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Target reject error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat menolak target.",
            );
        }
    }

    /**
     * Request revision for the specified target
     */
    public function revise(
        Request $request,
        PerformanceIndicator $indicator,
        Target $target,
    ) {
        // Check if user has permission
        $user = Auth::user();
        if (!$user->can("approve-targets")) {
            return back()->with(
                "error",
                "Anda tidak memiliki izin untuk meminta revisi target.",
            );
        }

        $validator = Validator::make(
            $request->all(),
            [
                "notes" => "required|string|max:1000",
            ],
            [
                "notes.required" => "Catatan revisi wajib diisi",
            ],
        );

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        DB::beginTransaction();
        try {
            $oldValues = $target->toArray();

            $target->update([
                "status" => "revised",
                "notes" => $request->notes,
                "updated_by" => $user->id,
            ]);

            // Log activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "REVISE",
                "module" => "SAKIP",
                "description" => "Meminta revisi target tahun {$target->year} untuk indikator {$indicator->name}",
                "old_values" => $oldValues,
                "new_values" => $target->fresh()->toArray(),
            ]);

            DB::commit();

            return back()->with("success", "Target diminta untuk direvisi.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Target revise error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat meminta revisi target.",
            );
        }
    }
}
