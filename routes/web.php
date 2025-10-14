<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InstansiController;
use App\Http\Controllers\ProgramController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\IndikatorKinerjaController;
use App\Http\Controllers\LaporanKinerjaController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\AdminController;

// Dashboard Route
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Auth routes
Route::get('/login', [\App\Http\Controllers\Auth\LoginController::class, 'show'])->name('login');
Route::post('/login', [\App\Http\Controllers\Auth\LoginController::class, 'login'])->name('auth.login');
Route::post('/logout', [\App\Http\Controllers\Auth\LogoutController::class, 'logout'])->name('auth.logout');

// Protected Routes untuk SAKIP
Route::middleware('auth')->group(function () {
    // Resources
    Route::resource('instansi', InstansiController::class);
    Route::resource('program', ProgramController::class);
    Route::resource('kegiatan', KegiatanController::class);
    Route::resource('indikator-kinerja', IndikatorKinerjaController::class);
    Route::resource('laporan-kinerja', LaporanKinerjaController::class);

    // Additional Relations
    Route::get('program/instansi/{instansi}', [ProgramController::class, 'byInstansi'])->name('program.by-instansi');
    Route::get('kegiatan/program/{program}', [KegiatanController::class, 'byProgram'])->name('kegiatan.by-program');
    Route::get('indikator-kinerja/kegiatan/{kegiatan}', [IndikatorKinerjaController::class, 'byKegiatan'])->name('indikator-kinerja.by-kegiatan');
    Route::get('laporan-kinerja/indikator/{indikator}', [LaporanKinerjaController::class, 'byIndikator'])->name('laporan-kinerja.by-indikator');
    Route::post('laporan-kinerja/quarterly-aggregation', [LaporanKinerjaController::class, 'quarterlyAggregation'])->name('laporan-kinerja.quarterly-aggregation');
    Route::post('laporan-kinerja/create-from-monthly', [LaporanKinerjaController::class, 'createFromMonthly'])->name('laporan-kinerja.create-from-monthly');

    // Pengaturan (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
        Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
        Route::post('pengaturan/clear-cache', [PengaturanController::class, 'clearCache'])->name('pengaturan.clear-cache');
        Route::post('pengaturan/optimize', [PengaturanController::class, 'optimizeApp'])->name('pengaturan.optimize');
        Route::post('pengaturan/backup', [PengaturanController::class, 'backupDatabase'])->name('pengaturan.backup');
    });

    // Admin Routes (Comprehensive Admin System)
    Route::prefix('admin')->middleware('can:admin.dashboard')->group(function () {
        // Dashboard
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        
        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminController::class, 'users'])->name('admin.users.index');
            Route::get('/create', [AdminController::class, 'createUser'])->name('admin.users.create');
            Route::post('/', [AdminController::class, 'storeUser'])->name('admin.users.store');
            Route::get('/{user}', [AdminController::class, 'showUser'])->name('admin.users.show');
            Route::get('/{user}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
            Route::put('/{user}', [AdminController::class, 'updateUser'])->name('admin.users.update');
            Route::delete('/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
        });
        
        // Audit Logs
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('admin.audit-logs');
        
        // System Settings
        Route::middleware('can:admin.settings')->group(function () {
            Route::get('/settings', [AdminController::class, 'systemSettings'])->name('admin.settings.index');
            Route::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
        });
    });
});

// API Routes untuk AJAX
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('program/by-instansi/{instansi}', [ProgramController::class, 'apiByInstansi']);
    Route::get('kegiatan/by-program/{program}', [KegiatanController::class, 'apiByProgram']);
    Route::get('indikator/by-kegiatan/{kegiatan}', [IndikatorKinerjaController::class, 'apiByKegiatan']);
});
