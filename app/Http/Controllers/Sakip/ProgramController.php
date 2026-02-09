<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sakip\ProgramFormRequest;
use App\Models\Program;
use App\Models\Instansi;
use App\Models\SasaranStrategis;
use App\Constants\Pagination;
use App\Constants\Status;
use App\Constants\ValidationRules;
use App\Traits\WithDatabaseTransactions;
use Illuminate\Http\Request;

/**
 * Program Controller
 *
 * Refactored to use Form Request validation and Transaction trait.
 * Eliminates duplicate validation and transaction handling code.
 */
class ProgramController extends Controller
{
    use WithDatabaseTransactions;

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

        // REFACTORED: Use constant instead of magic number
        $programs = $query
            ->orderBy("created_at", "desc")
            ->paginate(Pagination::DEFAULT);

        // REFACTORED: Use constant for status filter
        $instansis = Instansi::where("status", Status::ACTIVE)
            ->orderBy("nama_instansi")
            ->get();

        $sasaranStrategis = SasaranStrategis::where("status", Status::ACTIVE)
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

        // REFACTORED: Use constant for status filter
        $instansis = Instansi::where("status", Status::ACTIVE)
            ->orderBy("nama_instansi")
            ->get();

        // Check if there are any sasaran strategis
        $sasaranStrategis = SasaranStrategis::where("status", Status::ACTIVE)
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
     *
     * REFACTORED: Uses ProgramFormRequest for validation and runInTransaction for transaction handling
     */
    public function store(ProgramFormRequest $request)
    {
        $this->authorize("create", Program::class);

        // REFACTORED: Use trait to handle transactions and errors automatically
        return $this->runInTransactionWithErrorHandling(
            function () use ($request) {
                $program = Program::create($request->validated());
                return $program;
            },
            "program.store",
            "Program berhasil ditambahkan.",
            "Terjadi kesalahan saat menyimpan data program.",
        );
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

        // REFACTORED: Use constant for status filter
        $instansis = Instansi::where("status", Status::ACTIVE)
            ->orderBy("nama_instansi")
            ->get();

        $sasaranStrategis = SasaranStrategis::where(
            "instansi_id",
            $program->instansi_id,
        )
            ->where("status", Status::ACTIVE)
            ->orderBy("nama_strategis")
            ->get();

        return view(
            "sakip.program.edit",
            compact("program", "instansis", "sasaranStrategis"),
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * REFACTORED: Uses ProgramFormRequest for validation and runInTransaction for transaction handling
     */
    public function update(ProgramFormRequest $request, Program $program)
    {
        $this->authorize("update", $program);

        // REFACTORED: Use trait to handle transactions and errors automatically
        return $this->runInTransactionWithErrorHandling(
            function () use ($request, $program) {
                $program->update($request->validated());
                return $program;
            },
            "program.update",
            "Program berhasil diperbarui.",
            "Terjadi kesalahan saat memperbarui data program.",
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * REFACTORED: Uses runInTransaction for transaction handling
     */
    public function destroy(Program $program)
    {
        $this->authorize("delete", $program);

        // REFACTORED: Use trait to handle transactions automatically
        return $this->runInTransaction(function () use ($program) {
            // Check if program has related kegiatans
            $hasKegiatans = $program->kegiatans()->count() > 0;

            if ($hasKegiatans) {
                throw new \Exception(
                    "Program tidak dapat dihapus karena memiliki Kegiatan terkait.",
                );
            }

            $program->delete();

            return redirect()
                ->route("sakip.program.index")
                ->with("success", "Program berhasil dihapus.");
        }, "program.destroy");
    }

    /**
     * Get programs by sasaran strategis (for AJAX)
     *
     * REFACTORED: Use constant for status filter
     */
    public function bySasaranStrategis($sasaranStrategisId)
    {
        $programs = Program::where("sasaran_strategis_id", $sasaranStrategisId)
            ->where("status", Status::ACTIVE)
            ->orderBy("nama_program")
            ->get(["id", "kode_program", "nama_program", "tahun"]);

        return response()->json($programs);
    }
}
