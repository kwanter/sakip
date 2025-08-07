<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstansiController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\IndikatorKinerjaController;
use App\Http\Controllers\LaporanKinerjaController;
use App\Http\Controllers\PengaturanController;

// Dashboard Route
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Resource Routes untuk SAKIP
Route::resource('instansi', InstansiController::class);
Route::resource('program', ProgramController::class);
Route::resource('kegiatan', KegiatanController::class);
Route::resource('indikator-kinerja', IndikatorKinerjaController::class);
Route::resource('laporan-kinerja', LaporanKinerjaController::class);

// Additional Routes untuk relasi
Route::get('program/instansi/{instansi}', [ProgramController::class, 'byInstansi'])->name('program.by-instansi');
Route::get('kegiatan/program/{program}', [KegiatanController::class, 'byProgram'])->name('kegiatan.by-program');
Route::get('indikator-kinerja/kegiatan/{kegiatan}', [IndikatorKinerjaController::class, 'byKegiatan'])->name('indikator-kinerja.by-kegiatan');
Route::get('laporan-kinerja/indikator/{indikator}', [LaporanKinerjaController::class, 'byIndikator'])->name('laporan-kinerja.by-indikator');
Route::post('laporan-kinerja/quarterly-aggregation', [LaporanKinerjaController::class, 'quarterlyAggregation'])->name('laporan-kinerja.quarterly-aggregation');
Route::post('laporan-kinerja/create-from-monthly', [LaporanKinerjaController::class, 'createFromMonthly'])->name('laporan-kinerja.create-from-monthly');

// Pengaturan Routes
Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
Route::post('pengaturan/clear-cache', [PengaturanController::class, 'clearCache'])->name('pengaturan.clear-cache');
Route::post('pengaturan/optimize', [PengaturanController::class, 'optimizeApp'])->name('pengaturan.optimize');
Route::post('pengaturan/backup', [PengaturanController::class, 'backupDatabase'])->name('pengaturan.backup');

// API Routes untuk AJAX
Route::prefix('api')->group(function () {
    Route::get('program/by-instansi/{instansi}', [ProgramController::class, 'apiByInstansi']);
    Route::get('kegiatan/by-program/{program}', [KegiatanController::class, 'apiByProgram']);
    Route::get('indikator/by-kegiatan/{kegiatan}', [IndikatorKinerjaController::class, 'apiByKegiatan']);
});
