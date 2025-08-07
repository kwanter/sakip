<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KegiatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Kegiatan::with(['program.instansi'])
            ->withCount('indikatorKinerjas');

        // Filter by program
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_kegiatan', 'like', "%{$search}%")
                  ->orWhere('kode_kegiatan', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$search}%");
            });
        }

        $kegiatans = $query->orderBy('created_at', 'desc')->paginate(10);
        $programs = Program::with('instansi')->where('status', 'aktif')->get();

        return view('kegiatan.index', compact('kegiatans', 'programs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $programs = Program::with('instansi')->where('status', 'aktif')->get();
        $selectedProgram = null;
        
        if ($request->filled('program_id')) {
            $selectedProgram = Program::find($request->program_id);
        }
        
        return view('kegiatan.create', compact('programs', 'selectedProgram'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'kode_kegiatan' => 'required|string|max:20|unique:kegiatans',
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'anggaran' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'penanggung_jawab' => 'required|string|max:255',
            'status' => 'required|in:aktif,selesai,nonaktif'
        ]);

        try {
            // Convert status from form values to database values
            if ($validated['status'] === 'aktif') {
                $validated['status'] = 'berjalan';
            } elseif ($validated['status'] === 'nonaktif') {
                $validated['status'] = 'tunda';
            }
            // 'selesai' remains the same
            
            $kegiatan = Kegiatan::create($validated);
            
            return redirect()->route('kegiatan.show', $kegiatan)
                ->with('success', 'Kegiatan berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal menambahkan kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Kegiatan $kegiatan)
    {
        $kegiatan->load(['program.instansi', 'indikatorKinerjas.laporanKinerjas']);
        
        return view('kegiatan.show', compact('kegiatan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Kegiatan $kegiatan)
    {
        $programs = Program::with('instansi')->where('status', 'aktif')->get();
        $kegiatan->load('program');
        
        return view('kegiatan.edit', compact('kegiatan', 'programs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Kegiatan $kegiatan)
    {
        $validated = $request->validate([
            'program_id' => 'required|exists:programs,id',
            'kode_kegiatan' => 'required|string|max:20|unique:kegiatans,kode_kegiatan,' . $kegiatan->id,
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'anggaran' => 'required|numeric|min:0',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'penanggung_jawab' => 'required|string|max:255',
            'status' => 'required|in:aktif,selesai,nonaktif'
        ]);

        try {
            // Convert status from form values to database values
            if ($validated['status'] === 'aktif') {
                $validated['status'] = 'berjalan';
            } elseif ($validated['status'] === 'nonaktif') {
                $validated['status'] = 'tunda';
            }
            // 'selesai' remains the same
            
            $kegiatan->update($validated);
            
            return redirect()->route('kegiatan.show', $kegiatan)
                ->with('success', 'Kegiatan berhasil diperbarui.');
        } catch (\Exception $e) {
            return back()->withInput()
                ->with('error', 'Gagal memperbarui kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Kegiatan $kegiatan)
    {
        try {
            // Check if kegiatan has related indikator kinerja
            if ($kegiatan->indikatorKinerjas()->count() > 0) {
                return back()->with('error', 'Tidak dapat menghapus kegiatan yang memiliki indikator kinerja.');
            }

            $kegiatan->delete();
            
            return redirect()->route('kegiatan.index')
                ->with('success', 'Kegiatan berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus kegiatan: ' . $e->getMessage());
        }
    }

    /**
     * Get kegiatans by program for AJAX
     */
    public function byProgram(Program $program)
    {
        $kegiatans = $program->kegiatans()->with('indikatorKinerjas')->get();
        
        return view('kegiatan.by-program', compact('kegiatans', 'program'));
    }

    /**
     * API endpoint for getting kegiatans by program
     */
    public function apiByProgram(Program $program)
    {
        $kegiatans = $program->kegiatans()->select('id', 'kode_kegiatan', 'nama_kegiatan', 'status')->get();
        
        return response()->json($kegiatans);
    }
}
