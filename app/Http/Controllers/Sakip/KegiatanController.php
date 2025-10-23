<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\Kegiatan;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KegiatanController extends Controller
{
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

        $kegiatans = $query->orderBy("created_at", "desc")->paginate(15);

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
     */
    public function store(Request $request)
    {
        $this->authorize("create", Kegiatan::class);

        $validated = $request->validate([
            "program_id" => "required|exists:programs,id",
            "kode_kegiatan" =>
                "required|string|max:50|unique:kegiatans,kode_kegiatan",
            "nama_kegiatan" => "required|string|max:255",
            "deskripsi" => "nullable|string",
            "anggaran" => "required|numeric|min:0",
            "tanggal_mulai" => "nullable|date",
            "tanggal_selesai" => "nullable|date|after_or_equal:tanggal_mulai",
            "penanggung_jawab" => "nullable|string|max:255",
            "status" => "required|in:draft,aktif,selesai",
        ]);

        DB::beginTransaction();
        try {
            $kegiatan = Kegiatan::create($validated);

            DB::commit();

            return redirect()
                ->route("sakip.kegiatan.show", $kegiatan)
                ->with("success", "Kegiatan berhasil dibuat.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Create kegiatan error: " . $e->getMessage());
            return back()
                ->withInput()
                ->with("error", "Terjadi kesalahan saat membuat kegiatan.");
        }
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
     */
    public function update(Request $request, Kegiatan $kegiatan)
    {
        $this->authorize("update", $kegiatan);

        $validated = $request->validate([
            "program_id" => "required|exists:programs,id",
            "kode_kegiatan" =>
                "required|string|max:50|unique:kegiatans,kode_kegiatan," .
                $kegiatan->id,
            "nama_kegiatan" => "required|string|max:255",
            "deskripsi" => "nullable|string",
            "anggaran" => "required|numeric|min:0",
            "tanggal_mulai" => "nullable|date",
            "tanggal_selesai" => "nullable|date|after_or_equal:tanggal_mulai",
            "penanggung_jawab" => "nullable|string|max:255",
            "status" => "required|in:draft,aktif,selesai",
        ]);

        DB::beginTransaction();
        try {
            $kegiatan->update($validated);

            DB::commit();

            return redirect()
                ->route("sakip.kegiatan.show", $kegiatan)
                ->with("success", "Kegiatan berhasil diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Update kegiatan error: " . $e->getMessage());
            return back()
                ->withInput()
                ->with("error", "Terjadi kesalahan saat memperbarui kegiatan.");
        }
    }

    /**
     * Remove the specified kegiatan
     */
    public function destroy(Kegiatan $kegiatan)
    {
        $this->authorize("delete", $kegiatan);

        DB::beginTransaction();
        try {
            $kegiatanName = $kegiatan->nama_kegiatan;
            $programId = $kegiatan->program_id;

            $kegiatan->delete();

            DB::commit();

            return redirect()
                ->route("sakip.program.show", $programId)
                ->with("success", "Kegiatan berhasil dihapus.");
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Delete kegiatan error: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat menghapus kegiatan.",
            );
        }
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
