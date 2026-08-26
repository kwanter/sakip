<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sakip\KegiatanFormRequest;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Constants\Pagination;
use App\Traits\WithDatabaseTransactions;
use Illuminate\Http\Request;

/**
 * Kegiatan Controller
 *
 * Refactored to use Form Request validation and Transaction trait.
 * Eliminates duplicate validation and transaction handling code.
 */
class KegiatanController extends Controller
{
    use WithDatabaseTransactions;

    /**
     * Display a listing of kegiatan
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny", Kegiatan::class);

        $query = Kegiatan::with(["program", "program.instansi"]);

        // Search functionality
        if ($request->filled("search")) {
            $search = $request->get("search");
            $query->where(function ($q) use ($search) {
                $q->where("nama_kegiatan", "like", "%{$search}%")
                    ->orWhere("kode_kegiatan", "like", "%{$search}%")
                    ->orWhere("deskripsi", "like", "%{$search}%")
                    ->orWhereHas("program", function ($q) use ($search) {
                        $q->where("nama_program", "like", "%{$search}%");
                    });
            });
        }

        // Filter by program
        if ($request->filled("program_id")) {
            $query->where("program_id", $request->get("program_id"));
        }

        // Filter by status
        if ($request->filled("status")) {
            $query->where("status", $request->get("status"));
        }

        // REFACTORED: Use constant instead of magic number
        $kegiatans = $query
            ->orderBy("created_at", "desc")
            ->paginate(Pagination::DEFAULT);

        return view("sakip.kegiatan.index", compact("kegiatans"));
    }

    /**
     * Show the form for creating a new kegiatan
     */
    public function create(Request $request)
    {
        $this->authorize("create", Kegiatan::class);

        // Get program_id from query parameter if provided
        $programId = $request->get("program_id");
        $program = null;

        if ($programId) {
            $program = Program::find($programId);
            if (!$program) {
                return back()->with("error", "Program tidak ditemukan.");
            }
        }

        // Get all programs for dropdown if no specific program selected
        $programs = Program::where("status", "aktif")
            ->orderBy("nama_program")
            ->get();

        return view("sakip.kegiatan.create", compact("program", "programs"));
    }

    /**
     * Store a newly created kegiatan
     *
     * REFACTORED: Uses KegiatanFormRequest for validation and runInTransaction for transaction handling
     */
    public function store(KegiatanFormRequest $request)
    {
        $this->authorize("create", Kegiatan::class);

        // REFACTORED: Use trait to handle transactions automatically
        return $this->runInTransaction(function () use ($request) {
            $kegiatan = Kegiatan::create($request->validated());

            return redirect()
                ->route("sakip.kegiatan.show", $kegiatan)
                ->with("success", "Kegiatan berhasil dibuat.");
        }, "kegiatan.store");
    }

    /**
     * Display the specified kegiatan
     */
    public function show(Kegiatan $kegiatan)
    {
        $this->authorize("view", $kegiatan);

        $kegiatan->load([
            "program",
            "program.instansi",
            "program.sasaranStrategis",
        ]);

        return view("sakip.kegiatan.show", compact("kegiatan"));
    }

    /**
     * Show the form for editing the specified kegiatan
     */
    public function edit(Kegiatan $kegiatan)
    {
        $this->authorize("update", $kegiatan);

        $programs = Program::where("status", "aktif")
            ->orderBy("nama_program")
            ->get();

        return view("sakip.kegiatan.edit", compact("kegiatan", "programs"));
    }

    /**
     * Update the specified kegiatan
     *
     * REFACTORED: Uses KegiatanFormRequest for validation and runInTransaction for transaction handling
     */
    public function update(KegiatanFormRequest $request, Kegiatan $kegiatan)
    {
        $this->authorize("update", $kegiatan);

        // REFACTORED: Use trait to handle transactions automatically
        return $this->runInTransaction(function () use ($request, $kegiatan) {
            $kegiatan->update($request->validated());

            return redirect()
                ->route("sakip.kegiatan.show", $kegiatan)
                ->with("success", "Kegiatan berhasil diperbarui.");
        }, "kegiatan.update");
    }

    /**
     * Remove the specified kegiatan
     *
     * REFACTORED: Uses runInTransaction for transaction handling
     */
    public function destroy(Kegiatan $kegiatan)
    {
        $this->authorize("delete", $kegiatan);

        // REFACTORED: Use trait to handle transactions automatically
        return $this->runInTransaction(function () use ($kegiatan) {
            $kegiatanName = $kegiatan->nama_kegiatan;
            $programId = $kegiatan->program_id;

            $kegiatan->delete();

            return redirect()
                ->route("sakip.program.show", $programId)
                ->with("success", "Kegiatan berhasil dihapus.");
        }, "kegiatan.destroy");
    }

    /**
     * Get kegiatan by program (API endpoint)
     */
    public function byProgram(Program $program)
    {
        try {
            $kegiatans = Kegiatan::where("program_id", $program->id)
                ->where("status", "!=", "tunda")
                ->orderBy("nama_kegiatan")
                ->get();

            return response()->json($kegiatans);
        } catch (\Exception $e) {
            \Log::error("Get kegiatan by program error: " . $e->getMessage());
            return response()->json(
                ["error" => "Gagal memuat data kegiatan"],
                500,
            );
        }
    }
}
