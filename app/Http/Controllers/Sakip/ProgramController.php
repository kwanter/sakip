<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\Program;
use App\Models\Instansi;
use App\Models\SasaranStrategis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny", Program::class);

        $query = Program::with(["instansi", "sasaranStrategis"]);

        // Search functionality
        if ($request->filled("search")) {
            $search = $request->get("search");
            $query->where(function ($q) use ($search) {
                $q->where("nama_program", "like", "%{$search}%")
                    ->orWhere("kode_program", "like", "%{$search}%")
                    ->orWhereHas("instansi", function ($q) use ($search) {
                        $q->where("nama_instansi", "like", "%{$search}%");
                    });
            });
        }

        // Filter by instansi
        if ($request->filled("instansi_id")) {
            $query->where("instansi_id", $request->get("instansi_id"));
        }

        // Filter by sasaran strategis
        if ($request->filled("sasaran_strategis_id")) {
            $query->where(
                "sasaran_strategis_id",
                $request->get("sasaran_strategis_id"),
            );
        }

        // Filter by status
        if ($request->filled("status")) {
            $query->where("status", $request->get("status"));
        }

        // Filter by tahun
        if ($request->filled("tahun")) {
            $query->where("tahun", $request->get("tahun"));
        }

        $programs = $query->orderBy("created_at", "desc")->paginate(15);
        $instansis = Instansi::where("status", "aktif")
            ->orderBy("nama_instansi")
            ->get();
        $sasaranStrategis = SasaranStrategis::where("status", "aktif")
            ->orderBy("nama_strategis")
            ->get();

        return view(
            "sakip.program.index",
            compact("programs", "instansis", "sasaranStrategis"),
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize("create", Program::class);

        $instansis = Instansi::where("status", "aktif")
            ->orderBy("nama_instansi")
            ->get();

        // Check if there are any sasaran strategis
        $sasaranStrategis = SasaranStrategis::where("status", "aktif")
            ->orderBy("nama_strategis")
            ->get();

        if ($sasaranStrategis->isEmpty()) {
            return redirect()
                ->route("sakip.sasaran-strategis.index")
                ->with(
                    "warning",
                    "Silakan tambahkan Sasaran Strategis terlebih dahulu sebelum membuat Program.",
                );
        }

        return view(
            "sakip.program.create",
            compact("instansis", "sasaranStrategis"),
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize("create", Program::class);

        $validated = $request->validate([
            "instansi_id" => "required|exists:instansis,id",
            "sasaran_strategis_id" => "required|exists:sasaran_strategis,id",
            "kode_program" =>
                "required|string|max:255|unique:programs,kode_program",
            "nama_program" => "required|string|max:255",
            "deskripsi" => "nullable|string",
            "anggaran" => "nullable|numeric|min:0",
            "tahun" => "required|integer|min:2000|max:2100",
            "penanggung_jawab" => "nullable|string|max:255",
            "status" => "required|in:draft,aktif,selesai",
        ]);

        try {
            $program = Program::create($validated);

            return redirect()
                ->route("sakip.program.index")
                ->with("success", "Program berhasil ditambahkan.");
        } catch (\Exception $e) {
            \Log::error("Error creating program: " . $e->getMessage());
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Terjadi kesalahan saat menyimpan data program.",
                );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Program $program)
    {
        $this->authorize("view", $program);

        $program->load([
            "instansi",
            "sasaranStrategis",
            "kegiatans",
            "performanceIndicators",
        ]);

        return view("sakip.program.show", compact("program"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Program $program)
    {
        $this->authorize("update", $program);

        $instansis = Instansi::where("status", "aktif")
            ->orderBy("nama_instansi")
            ->get();
        $sasaranStrategis = SasaranStrategis::where(
            "instansi_id",
            $program->instansi_id,
        )
            ->where("status", "aktif")
            ->orderBy("nama_strategis")
            ->get();

        return view(
            "sakip.program.edit",
            compact("program", "instansis", "sasaranStrategis"),
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Program $program)
    {
        $this->authorize("update", $program);

        $validated = $request->validate([
            "instansi_id" => "required|exists:instansis,id",
            "sasaran_strategis_id" => "required|exists:sasaran_strategis,id",
            "kode_program" =>
                "required|string|max:255|unique:programs,kode_program," .
                $program->id,
            "nama_program" => "required|string|max:255",
            "deskripsi" => "nullable|string",
            "anggaran" => "nullable|numeric|min:0",
            "tahun" => "required|integer|min:2000|max:2100",
            "penanggung_jawab" => "nullable|string|max:255",
            "status" => "required|in:draft,aktif,selesai",
        ]);

        try {
            $program->update($validated);

            return redirect()
                ->route("sakip.program.index")
                ->with("success", "Program berhasil diperbarui.");
        } catch (\Exception $e) {
            \Log::error("Error updating program: " . $e->getMessage());
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Terjadi kesalahan saat memperbarui data program.",
                );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program)
    {
        $this->authorize("delete", $program);

        try {
            // Check if program has related kegiatans
            $hasKegiatans = $program->kegiatans()->count() > 0;

            if ($hasKegiatans) {
                return back()->with(
                    "error",
                    "Program tidak dapat dihapus karena memiliki Kegiatan terkait.",
                );
            }

            $program->delete();

            return redirect()
                ->route("sakip.program.index")
                ->with("success", "Program berhasil dihapus.");
        } catch (\Exception $e) {
            \Log::error("Error deleting program: " . $e->getMessage());
            return back()->with(
                "error",
                "Terjadi kesalahan saat menghapus program.",
            );
        }
    }

    /**
     * Get programs by sasaran strategis (for AJAX)
     */
    public function bySasaranStrategis($sasaranStrategisId)
    {
        $programs = Program::where("sasaran_strategis_id", $sasaranStrategisId)
            ->where("status", "aktif")
            ->orderBy("nama_program")
            ->get(["id", "kode_program", "nama_program", "tahun"]);

        return response()->json($programs);
    }
}
