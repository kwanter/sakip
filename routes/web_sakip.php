<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sakip\SakipDashboardController;
use App\Http\Controllers\Sakip\PerformanceIndicatorController;
use App\Http\Controllers\Sakip\DataCollectionController;
use App\Http\Controllers\Sakip\PerformanceMeasurementController;
use App\Http\Controllers\Sakip\AssessmentController;
use App\Http\Controllers\Sakip\ReportController;
use App\Http\Controllers\Sakip\SakipAuditController;

// SAKIP Routes
Route::prefix('sakip')->middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/', [SakipDashboardController::class, 'index'])->name('sakip.dashboard');
    
    // Performance Indicators
    Route::resource('indicators', PerformanceIndicatorController::class)
        ->names([
            'index' => 'sakip.indicators.index',
            'create' => 'sakip.indicators.create',
            'store' => 'sakip.indicators.store',
            'show' => 'sakip.indicators.show',
            'edit' => 'sakip.indicators.edit',
            'update' => 'sakip.indicators.update',
            'destroy' => 'sakip.indicators.destroy'
        ]);
    
    // Performance Data
    Route::prefix('performance-data')->group(function () {
        Route::get('/', [DataCollectionController::class, 'index'])->name('sakip.performance-data.index');
        Route::get('/create', [DataCollectionController::class, 'create'])->name('sakip.performance-data.create');
        Route::post('/', [DataCollectionController::class, 'store'])->name('sakip.performance-data.store');
        Route::get('/{performanceData}', [DataCollectionController::class, 'show'])->name('sakip.performance-data.show');
        Route::get('/{performanceData}/edit', [DataCollectionController::class, 'edit'])->name('sakip.performance-data.edit');
        Route::put('/{performanceData}', [DataCollectionController::class, 'update'])->name('sakip.performance-data.update');
        Route::delete('/{performanceData}', [DataCollectionController::class, 'destroy'])->name('sakip.performance-data.destroy');
        Route::post('/bulk-import', [DataCollectionController::class, 'bulkImport'])->name('sakip.performance-data.bulk-import');
        Route::post('/validate-data', [DataCollectionController::class, 'validateData'])->name('sakip.performance-data.validate');
    });
    
    // Assessments
    Route::prefix('assessments')->group(function () {
        Route::get('/', [AssessmentController::class, 'index'])->name('sakip.assessments.index');
        Route::get('/create', [AssessmentController::class, 'create'])->name('sakip.assessments.create');
        Route::post('/', [AssessmentController::class, 'store'])->name('sakip.assessments.store');
        Route::get('/{assessment}', [AssessmentController::class, 'show'])->name('sakip.assessments.show');
        Route::get('/{assessment}/edit', [AssessmentController::class, 'edit'])->name('sakip.assessments.edit');
        Route::put('/{assessment}', [AssessmentController::class, 'update'])->name('sakip.assessments.update');
        Route::delete('/{assessment}', [AssessmentController::class, 'destroy'])->name('sakip.assessments.destroy');
        Route::post('/{assessment}/approve', [AssessmentController::class, 'approve'])->name('sakip.assessments.approve');
        Route::post('/{assessment}/reject', [AssessmentController::class, 'reject'])->name('sakip.assessments.reject');
        Route::post('/{assessment}/auto-assess', [AssessmentController::class, 'autoAssess'])->name('sakip.assessments.auto-assess');
        Route::post('/batch-assess', [AssessmentController::class, 'batchAssess'])->name('sakip.assessments.batch-assess');
    });
    
    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('sakip.reports.index');
        Route::get('/create', [ReportController::class, 'create'])->name('sakip.reports.create');
        Route::post('/', [ReportController::class, 'store'])->name('sakip.reports.store');
        Route::get('/{report}', [ReportController::class, 'show'])->name('sakip.reports.show');
        Route::get('/{report}/download', [ReportController::class, 'download'])->name('sakip.reports.download');
        Route::get('/{report}/export/{format}', [ReportController::class, 'export'])->name('sakip.reports.export');
        Route::delete('/{report}', [ReportController::class, 'destroy'])->name('sakip.reports.destroy');
        Route::post('/{report}/approve', [ReportController::class, 'approve'])->name('sakip.reports.approve');
        Route::post('/{report}/reject', [ReportController::class, 'reject'])->name('sakip.reports.reject');
        Route::get('/templates/{template}', [ReportController::class, 'template'])->name('sakip.reports.template');
    });
    
    // Audit and Compliance
    Route::prefix('audit')->group(function () {
        Route::get('/', [SakipAuditController::class, 'index'])->name('sakip.audit.index');
        Route::post('/run-compliance-check', [SakipAuditController::class, 'runComplianceCheck'])->name('sakip.audit.run-compliance-check');
        Route::get('/audit-log/{auditLog}', [SakipAuditController::class, 'showAuditLog'])->name('sakip.audit.show-log');
        Route::post('/fix-violation/{violation}', [SakipAuditController::class, 'fixViolation'])->name('sakip.audit.fix-violation');
        Route::get('/export-report', [SakipAuditController::class, 'exportReport'])->name('sakip.audit.export-report');
    });
    
    // API endpoints for AJAX
    Route::prefix('api')->group(function () {
        Route::get('/dashboard-data', [SakipDashboardController::class, 'getDashboardData'])->name('sakip.api.dashboard-data');
        Route::get('/performance-summary', [PerformanceMeasurementController::class, 'getPerformanceSummary'])->name('sakip.api.performance-summary');
        Route::get('/achievement-trends', [PerformanceMeasurementController::class, 'getAchievementTrends'])->name('sakip.api.achievement-trends');
        Route::get('/compliance-status', [SakipAuditController::class, 'getComplianceStatus'])->name('sakip.api.compliance-status');
        Route::get('/indicator-comparison', [PerformanceMeasurementController::class, 'getIndicatorComparison'])->name('sakip.api.indicator-comparison');
        Route::get('/indicators/by-instansi/{instansi}', [PerformanceIndicatorController::class, 'byInstansi'])->name('sakip.api.indicators.by-instansi');
        Route::get('/indicators/{indicator}/targets', [PerformanceIndicatorController::class, 'getTargets'])->name('sakip.api.indicators.targets');
        Route::get('/indicators/{indicator}/performance-data', [PerformanceIndicatorController::class, 'getPerformanceData'])->name('sakip.api.indicators.performance-data');
        Route::get('/assessment-analytics', [AssessmentController::class, 'getAnalytics'])->name('sakip.api.assessment-analytics');
        Route::get('/report-analytics', [ReportController::class, 'getAnalytics'])->name('sakip.api.report-analytics');
        Route::get('/audit-analytics', [SakipAuditController::class, 'getAnalytics'])->name('sakip.api.audit-analytics');
    });
});