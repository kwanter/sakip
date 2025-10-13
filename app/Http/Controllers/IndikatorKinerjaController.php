<?php

namespace App\Http\Controllers;

use App\Models\IndikatorKinerja;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndikatorKinerjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = IndikatorKinerja::with(['kegiatan.program.instansi', 'laporanKinerjas']);
        
        // Filter by kegiatan if provided
        if ($request->filled('kegiatan_id')) {
            $query->where('kegiatan_id', $request->kegiatan_id);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_indikator', 'like', "%{$search}%")
                  ->orWhere('definisi', 'like', "%{$search}%")
                  ->orWhere('satuan', 'like', "%{$search}%");
            });
        }
        
        $indikators = $query->orderBy('created_at', 'desc')->paginate(10);
        $kegiatans = Kegiatan::with('program.instansi')->where('status', 'berjalan')->get();
        
        return view('indikator-kinerja.index', compact('indikators', 'kegiatans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $kegiatans = Kegiatan::with('program.instansi')->where('status', 'berjalan')->get();
        $selectedKegiatan = null;
        
        if ($request->filled('kegiatan_id')) {
            $selectedKegiatan = Kegiatan::find($request->kegiatan_id);
        }
        
        return view('indikator-kinerja.create', compact('kegiatans', 'selectedKegiatan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kegiatan_id' => 'required|exists:kegiatans,id',
            'nama_indikator' => 'required|string|max:255',
            'definisi' => 'nullable|string',
            'target' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:100',
            'jenis' => 'required|in:output,outcome,impact',
            'status' => 'required|in:aktif,nonaktif'
        ]);
        
        try {
            IndikatorKinerja::create($request->all());
            
            return redirect()->route('indikator-kinerja.index')
                           ->with('success', 'Indikator kinerja berhasil ditambahkan.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal menambahkan indikator kinerja: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(IndikatorKinerja $indikatorKinerja)
    {
        $indikatorKinerja->load(['kegiatan.program.instansi', 'laporanKinerjas' => function($query) {
            $query->orderBy('periode', 'desc');
        }]);
        
        return view('indikator-kinerja.show', compact('indikatorKinerja'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(IndikatorKinerja $indikatorKinerja)
    {
        $kegiatans = Kegiatan::with('program.instansi')->where('status', 'berjalan')->get();
        
        return view('indikator-kinerja.edit', compact('indikatorKinerja', 'kegiatans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, IndikatorKinerja $indikatorKinerja)
    {
        $request->validate([
            'kegiatan_id' => 'required|exists:kegiatans,id',
            'nama_indikator' => 'required|string|max:255',
            'definisi' => 'nullable|string',
            'target' => 'required|numeric|min:0',
            'satuan' => 'required|string|max:100',
            'jenis' => 'required|in:output,outcome,impact',
            'status' => 'required|in:aktif,nonaktif'
        ]);
        
        try {
            $indikatorKinerja->update($request->all());
            
            return redirect()->route('indikator-kinerja.show', $indikatorKinerja)
                           ->with('success', 'Indikator kinerja berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Gagal memperbarui indikator kinerja: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(IndikatorKinerja $indikatorKinerja)
    {
        try {
            // Check if there are related performance reports
            if ($indikatorKinerja->laporanKinerjas()->count() > 0) {
                return redirect()->back()
                                ->with('error', 'Tidak dapat menghapus indikator kinerja yang memiliki laporan kinerja.');
            }
            
            $indikatorKinerja->delete();
            
            return redirect()->route('indikator-kinerja.index')
                           ->with('success', 'Indikator kinerja berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                           ->with('error', 'Gagal menghapus indikator kinerja: ' . $e->getMessage());
        }
    }
    
    /**
     * Get indicators by activity for AJAX requests
     */
    public function apiByKegiatan(Request $request)
    {
        $kegiatanId = $request->get('kegiatan_id');
        
        if (!$kegiatanId) {
            return response()->json([]);
        }
        
        $indikators = IndikatorKinerja::where('kegiatan_id', $kegiatanId)
                                    ->where('status', 'aktif')
                                    ->select('id', 'nama_indikator', 'target', 'satuan')
                                    ->get();
        
        return response()->json($indikators);
    }
}
