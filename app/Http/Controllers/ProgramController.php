<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Instansi;
use Illuminate\Support\Facades\Validator;

class ProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Program::with('instansi')->withCount('kegiatans');
        
        // Filter by instansi if provided
        if ($request->filled('instansi_id')) {
            $query->where('instansi_id', $request->instansi_id);
        }
        
        // Filter by status if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_program', 'like', "%{$search}%")
                  ->orWhere('kode_program', 'like', "%{$search}%")
                  ->orWhereHas('instansi', function($q) use ($search) {
                      $q->where('nama_instansi', 'like', "%{$search}%");
                  });
            });
        }
        
        $programs = $query->paginate(10);
        $instansis = Instansi::where('status', 'aktif')->get();
        
        return view('program.index', compact('programs', 'instansis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $instansis = Instansi::where('status', 'aktif')->get();
        return view('program.create', compact('instansis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'instansi_id' => 'required|exists:instansis,id',
            'kode_program' => 'required|max:20|unique:programs,kode_program',
            'nama_program' => 'required|max:255',
            'deskripsi' => 'nullable',
            'anggaran' => 'required|numeric|min:0',
            'tahun_mulai' => 'required|integer|min:2020|max:2030',
            'tahun_selesai' => 'required|integer|min:2020|max:2030|gte:tahun_mulai',
            'penanggung_jawab' => 'required|max:255',
            'status' => 'required|in:aktif,selesai,nonaktif'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Use tahun_mulai as the main year for the program
        $data = $request->all();
        $data['tahun'] = $request->tahun_mulai;
        
        // Remove fields that don't exist in database
        unset($data['tahun_mulai'], $data['tahun_selesai']);
        
        // Convert status to match database enum
        if ($data['status'] === 'nonaktif') {
            $data['status'] = 'draft';
        }

        Program::create($data);

        return redirect()->route('program.index')
            ->with('success', 'Program berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Program $program)
    {
        $program->load(['instansi', 'kegiatans.indikatorKinerjas']);
        return view('program.show', compact('program'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Program $program)
    {
        $instansis = Instansi::where('status', 'aktif')->get();
        return view('program.edit', compact('program', 'instansis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Program $program)
    {
        $validator = Validator::make($request->all(), [
            'instansi_id' => 'required|exists:instansis,id',
            'kode_program' => 'required|max:20|unique:programs,kode_program,' . $program->id,
            'nama_program' => 'required|max:255',
            'deskripsi' => 'nullable',
            'anggaran' => 'required|numeric|min:0',
            'tahun_mulai' => 'required|integer|min:2020|max:2030',
            'tahun_selesai' => 'required|integer|min:2020|max:2030|gte:tahun_mulai',
            'penanggung_jawab' => 'required|max:255',
            'status' => 'required|in:aktif,selesai,nonaktif'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Use tahun_mulai as the main year for the program
        $data = $request->all();
        $data['tahun'] = $request->tahun_mulai;
        
        // Remove fields that don't exist in database
        unset($data['tahun_mulai'], $data['tahun_selesai']);
        
        // Convert status to match database enum
        if ($data['status'] === 'nonaktif') {
            $data['status'] = 'draft';
        }

        $program->update($data);

        return redirect()->route('program.index')
            ->with('success', 'Program berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program)
    {
        try {
            $program->delete();
            return redirect()->route('program.index')
                ->with('success', 'Program berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('program.index')
                ->with('error', 'Program tidak dapat dihapus karena masih memiliki data terkait.');
        }
    }

    /**
     * Get programs by instansi
     */
    public function byInstansi(Instansi $instansi)
    {
        $programs = $instansi->programs()->withCount('kegiatans')->paginate(10);
        return view('program.by-instansi', compact('programs', 'instansi'));
    }

    /**
     * API: Get programs by instansi for AJAX
     */
    public function apiByInstansi(Instansi $instansi)
    {
        $programs = $instansi->programs()->select('id', 'kode_program', 'nama_program')->get();
        return response()->json($programs);
    }
}
