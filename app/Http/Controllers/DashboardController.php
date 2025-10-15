<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Instansi;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\IndikatorKinerja;
use App\Models\LaporanKinerja;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik untuk dashboard
        $stats = [
            'total_instansi' => Instansi::count(),
            'total_program' => Program::count(),
            'total_kegiatan' => Kegiatan::count(),
            'total_indikator' => IndikatorKinerja::count(),
            'total_laporan' => LaporanKinerja::count(),
        ];

        // Data untuk chart/grafik
        $programByInstansi = Instansi::withCount('programs')->get();
        $kegiatanByStatus = Kegiatan::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->get();
        
        // Laporan terbaru
        $laporanTerbaru = LaporanKinerja::with(['indikatorKinerja.kegiatan.program.instansi'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Indikator dengan capaian tertinggi
        $indikatorTerbaik = IndikatorKinerja::whereNotNull('realisasi')
            ->whereNotNull('target')
            ->where('target', '>', 0)
            ->get()
            ->map(function ($indikator) {
                // Validasi dan casting ke numeric untuk mencegah TypeError
                $realisasi = is_numeric($indikator->realisasi) ? (float) $indikator->realisasi : 0.0;
                $target = is_numeric($indikator->target) ? (float) $indikator->target : 0.0;

                $indikator->persentase = $target > 0 ? ($realisasi / $target) * 100 : 0.0;
                return $indikator;
            })
            ->sortByDesc('persentase')
            ->take(5);

        return view('dashboard', compact(
            'stats',
            'programByInstansi',
            'kegiatanByStatus',
            'laporanTerbaru',
            'indikatorTerbaik'
        ));
    }
}
