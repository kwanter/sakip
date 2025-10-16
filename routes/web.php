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

// Email verification routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'notice'])
        ->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');

    Route::post('/email/resend', [\App\Http\Controllers\Auth\EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.resend');
});

// Protected Routes untuk SAKIP
Route::middleware(['auth', 'verified'])->group(function () {
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

    // Pengaturan legacy redirects (preserve URLs; forward to unified admin settings)
    Route::middleware(['auth','verified','role:admin'])->group(function () {
        Route::get('pengaturan', function() { return redirect()->route('admin.settings.index'); })->name('pengaturan.index');
        Route::put('pengaturan', function(\Illuminate\Http\Request $request) { return redirect()->route('admin.settings.update'); })->name('pengaturan.update');
        Route::post('pengaturan/clear-cache', function() { return redirect()->route('admin.settings.clear-cache'); })->name('pengaturan.clear-cache');
        Route::post('pengaturan/optimize', function() { return redirect()->route('admin.settings.optimize'); })->name('pengaturan.optimize');
        Route::post('pengaturan/backup', function() { return redirect()->route('admin.settings.backup'); })->name('pengaturan.backup');
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
            // Separate submission routes for roles and permissions management
            Route::post('/{user}/roles', [AdminController::class, 'updateRoles'])->name('admin.users.roles.update');
            Route::post('/{user}/permissions', [AdminController::class, 'updatePermissions'])->name('admin.users.permissions.update');
            Route::delete('/{user}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
        });
        
        // Audit Logs
        Route::get('/audit-logs', [AdminController::class, 'auditLogs'])->name('admin.audit-logs');
        
        // System Settings + Maintenance (Unified)
        Route::middleware('can:admin.settings')->group(function () {
            Route::get('/settings', [AdminController::class, 'systemSettings'])->name('admin.settings.index');
            Route::post('/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
            Route::post('/settings/clear-cache', [AdminController::class, 'clearCache'])->name('admin.settings.clear-cache');
            Route::post('/settings/optimize', [AdminController::class, 'optimizeApp'])->name('admin.settings.optimize');
            Route::post('/settings/backup', [AdminController::class, 'backupDatabase'])->name('admin.settings.backup');
        });
    });
});

// Include SAKIP routes
require __DIR__.'/web_sakip.php';

// SAKIP Test Routes (Development only)
if (app()->environment('local', 'development')) {
    Route::prefix('sakip-test')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\SakipTestController::class, 'testDashboard'])->name('sakip.test.dashboard');
        Route::get('/datatable', [\App\Http\Controllers\SakipTestController::class, 'testDataTable'])->name('sakip.test.datatable');
        Route::get('/notification', [\App\Http\Controllers\SakipTestController::class, 'testNotification'])->name('sakip.test.notification');
        Route::get('/configuration', [\App\Http\Controllers\SakipTestController::class, 'testConfiguration'])->name('sakip.test.configuration');
        Route::get('/helpers', [\App\Http\Controllers\SakipTestController::class, 'testHelpers'])->name('sakip.test.helpers');
    });
}

// API Routes untuk AJAX
Route::prefix('api')->middleware('auth')->group(function () {
    Route::get('program/by-instansi/{instansi}', [ProgramController::class, 'apiByInstansi']);
    Route::get('kegiatan/by-program/{program}', [KegiatanController::class, 'apiByProgram']);
    Route::get('indikator/by-kegiatan/{kegiatan}', [IndikatorKinerjaController::class, 'apiByKegiatan']);
});
