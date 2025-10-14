<?php

namespace App\Http\Controllers;

use App\Models\LaporanKinerja;
use App\Models\IndikatorKinerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LaporanKinerjaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->authorizeResource(\App\Models\LaporanKinerja::class, 'laporan_kinerja');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = LaporanKinerja::with(['indikatorKinerja.kegiatan.program.instansi']);
        
        // Filter by year
        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }
        
        // Filter by periode
        if ($request->filled('periode')) {
            $query->where('periode', $request->periode);
        }
        
        // Filter by status
        if ($request->filled('status_verifikasi')) {
            $query->where('status_verifikasi', $request->status_verifikasi);
        }
        
        // Filter by periode type (monthly/quarterly)
        if ($request->filled('periode_type')) {
            if ($request->periode_type === 'monthly') {
                $query->monthly();
            } elseif ($request->periode_type === 'quarterly') {
                $query->quarterly();
            }
        }
        
        $laporanKinerjas = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('laporan-kinerja.index', compact('laporanKinerjas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $indikatorKinerjas = IndikatorKinerja::with(['kegiatan.program.instansi'])
                                            ->where('status', 'aktif')
                                            ->get();
        
        $selectedIndikator = null;
        if ($request->has('indikator_kinerja_id')) {
            $selectedIndikator = IndikatorKinerja::with(['kegiatan.program.instansi'])
                                                ->find($request->indikator_kinerja_id);
        }
        
        return view('laporan-kinerja.create', compact('indikatorKinerjas', 'selectedIndikator'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'indikator_kinerja_id' => 'required|exists:indikator_kinerjas,id',
            'tahun' => 'required|integer|min:2020|max:' . (date('Y') + 5),
            'periode' => 'required|in:januari,februari,maret,april,mei,juni,juli,agustus,september,oktober,november,desember,triwulan1,triwulan2,triwulan3,triwulan4,tahunan',
            'input' => 'nullable|numeric|min:0',
            'nilai_realisasi' => 'required|numeric|min:0',
            'persentase_capaian' => 'nullable|numeric|min:0|max:100',
            'kendala' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
            'file_pendukung' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'status_verifikasi' => 'required|in:draft,submitted,verified,rejected'
        ]);
        
        // Cek duplikasi laporan untuk indikator, tahun, dan periode yang sama
        $existingLaporan = LaporanKinerja::where('indikator_kinerja_id', $request->indikator_kinerja_id)
                                        ->where('tahun', $request->tahun)
                                        ->where('periode', $request->periode)
                                        ->first();
        
        if ($existingLaporan) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Laporan untuk indikator, tahun, dan periode ini sudah ada.');
        }
        
        $data = $request->except('file_pendukung');
        
        // Handle file upload
        if ($request->hasFile('file_pendukung')) {
            $file = $request->file('file_pendukung');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('laporan-kinerja', $filename, 'public');
            $data['file_pendukung'] = $path;
        }
        
        LaporanKinerja::create($data);
        
        return redirect()->route('laporan-kinerja.index')
                       ->with('success', 'Laporan kinerja berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(LaporanKinerja $laporanKinerja)
    {
        $laporanKinerja->load(['indikatorKinerja.kegiatan.program.instansi']);
        
        return view('laporan-kinerja.show', compact('laporanKinerja'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LaporanKinerja $laporanKinerja)
    {
        $laporanKinerja->load(['indikatorKinerja.kegiatan.program.instansi']);
        
        $indikatorKinerjas = IndikatorKinerja::with(['kegiatan.program.instansi'])
                                            ->where('status', 'aktif')
                                            ->get();
        
        return view('laporan-kinerja.edit', compact('laporanKinerja', 'indikatorKinerjas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LaporanKinerja $laporanKinerja)
    {
        $request->validate([
            'indikator_kinerja_id' => 'required|exists:indikator_kinerjas,id',
            'tahun' => 'required|integer|min:2020|max:' . (date('Y') + 5),
            'periode' => 'required|in:januari,februari,maret,april,mei,juni,juli,agustus,september,oktober,november,desember,triwulan1,triwulan2,triwulan3,triwulan4,tahunan',
            'input' => 'nullable|numeric|min:0',
            'nilai_realisasi' => 'required|numeric|min:0',
            'persentase_capaian' => 'nullable|numeric|min:0|max:100',
            'kendala' => 'nullable|string',
            'tindak_lanjut' => 'nullable|string',
            'file_pendukung' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
            'status_verifikasi' => 'required|in:draft,submitted,verified,rejected',
            'catatan_verifikasi' => 'nullable|string'
        ]);
        
        // Cek duplikasi laporan untuk indikator, tahun, dan periode yang sama (kecuali laporan saat ini)
        $existingLaporan = LaporanKinerja::where('indikator_kinerja_id', $request->indikator_kinerja_id)
                                        ->where('tahun', $request->tahun)
                                        ->where('periode', $request->periode)
                                        ->where('id', '!=', $laporanKinerja->id)
                                        ->first();
        
        if ($existingLaporan) {
            return redirect()->back()
                           ->withInput()
                           ->with('error', 'Laporan untuk indikator, tahun, dan periode ini sudah ada.');
        }
        
        $data = $request->except('file_pendukung');
        
        // Handle file upload
        if ($request->hasFile('file_pendukung')) {
            // Hapus file lama jika ada
            if ($laporanKinerja->file_pendukung) {
                Storage::disk('public')->delete($laporanKinerja->file_pendukung);
            }
            
            $file = $request->file('file_pendukung');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('laporan-kinerja', $filename, 'public');
            $data['file_pendukung'] = $path;
        }
        
        $laporanKinerja->update($data);
        
        return redirect()->route('laporan-kinerja.index')
                       ->with('success', 'Laporan kinerja berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LaporanKinerja $laporanKinerja)
    {
        // Hapus file pendukung jika ada
        if ($laporanKinerja->file_pendukung) {
            Storage::disk('public')->delete($laporanKinerja->file_pendukung);
        }
        
        $laporanKinerja->delete();
        
        return redirect()->route('laporan-kinerja.index')
                       ->with('success', 'Laporan kinerja berhasil dihapus.');
    }
    
    /**
     * Get laporan by indikator kinerja (API endpoint)
     */
    public function byIndikator($indikatorId)
    {
        $laporanKinerjas = LaporanKinerja::where('indikator_kinerja_id', $indikatorId)
                                        ->where('status_verifikasi', 'verified')
                                        ->orderBy('tahun', 'desc')
                                        ->orderBy('periode', 'desc')
                                        ->get();
        
        return response()->json($laporanKinerjas);
    }
    
    /**
     * Get quarterly aggregated data from monthly reports
     */
    public function quarterlyAggregation(Request $request)
    {
        $request->validate([
            'indikator_kinerja_id' => 'required|exists:indikator_kinerjas,id',
            'tahun' => 'required|integer',
            'quarter' => 'required|in:triwulan1,triwulan2,triwulan3,triwulan4'
        ]);
        
        $indikatorId = $request->indikator_kinerja_id;
        $tahun = $request->tahun;
        $quarter = $request->quarter;
        
        // Get months in the selected quarter
        $months = LaporanKinerja::getMonthsInQuarter($quarter);
        
        // Get monthly reports for this quarter
        $monthlyReports = LaporanKinerja::where('indikator_kinerja_id', $indikatorId)
                                       ->where('tahun', $tahun)
                                       ->whereIn('periode', $months)
                                       ->get();
        
        if ($monthlyReports->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data bulanan untuk triwulan ini'
            ]);
        }
        
        // Calculate aggregated values
        $totalRealisasi = $monthlyReports->sum('nilai_realisasi');
        $avgPersentase = $monthlyReports->avg('persentase_capaian');
        
        // Combine kendala and tindak lanjut
        $kendalaList = $monthlyReports->pluck('kendala')->filter()->toArray();
        $tindakLanjutList = $monthlyReports->pluck('tindak_lanjut')->filter()->toArray();
        
        $aggregatedData = [
            'indikator_kinerja_id' => $indikatorId,
            'tahun' => $tahun,
            'periode' => $quarter,
            'nilai_realisasi' => $totalRealisasi,
            'persentase_capaian' => round($avgPersentase, 2),
            'kendala' => implode('; ', $kendalaList),
            'tindak_lanjut' => implode('; ', $tindakLanjutList),
            'monthly_reports' => $monthlyReports,
            'months_covered' => $months
        ];
        
        return response()->json([
            'success' => true,
            'data' => $aggregatedData
        ]);
    }
    
    /**
     * Create quarterly report from monthly data
     */
    public function createFromMonthly(Request $request)
    {
        $request->validate([
            'indikator_kinerja_id' => 'required|exists:indikator_kinerjas,id',
            'tahun' => 'required|integer',
            'quarter' => 'required|in:triwulan1,triwulan2,triwulan3,triwulan4'
        ]);
        
        $indikatorId = $request->indikator_kinerja_id;
        $tahun = $request->tahun;
        $quarter = $request->quarter;
        
        // Check if quarterly report already exists
        $existingQuarterly = LaporanKinerja::where('indikator_kinerja_id', $indikatorId)
                                          ->where('tahun', $tahun)
                                          ->where('periode', $quarter)
                                          ->first();
        
        if ($existingQuarterly) {
            return redirect()->back()->with('error', 'Laporan triwulan sudah ada untuk periode ini.');
        }
        
        // Get aggregated data
        $aggregationResponse = $this->quarterlyAggregation($request);
        $aggregationData = $aggregationResponse->getData(true);
        
        if (!$aggregationData['success']) {
            return redirect()->back()->with('error', $aggregationData['message']);
        }
        
        $data = $aggregationData['data'];
        
        // Create quarterly report
        $quarterlyReport = LaporanKinerja::create([
            'indikator_kinerja_id' => $data['indikator_kinerja_id'],
            'tahun' => $data['tahun'],
            'periode' => $data['periode'],
            'nilai_realisasi' => $data['nilai_realisasi'],
            'persentase_capaian' => $data['persentase_capaian'],
            'kendala' => $data['kendala'],
            'tindak_lanjut' => $data['tindak_lanjut'],
            'status_verifikasi' => 'draft'
        ]);
        
        return redirect()->route('laporan-kinerja.show', $quarterlyReport)
                        ->with('success', 'Laporan triwulan berhasil dibuat dari data bulanan.');
    }
}
