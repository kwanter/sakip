<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\PerformanceIndicatorController;
use App\Http\Controllers\Api\V1\DataCollectionController;
use App\Http\Controllers\Api\V1\AssessmentController;
use App\Http\Controllers\Api\V1\ReportController;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Version 1 of the SAKIP API
| Base URL: /api/v1
|
| Authentication: Bearer Token (Sanctum)
| Rate Limiting: Applied per endpoint
|
| Breaking changes require a new API version (v2, v3, etc.)
|
*/

Route::prefix('v1')
    ->middleware(['api', 'throttle:api'])
    ->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Public Routes (No Authentication Required)
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:login')
            ->name('api.v1.auth.login');

        Route::post('register', [AuthController::class, 'register'])
            ->middleware('throttle:api_strict')
            ->name('api.v1.auth.register');

        Route::post('forgot-password', [AuthController::class, 'forgotPassword'])
            ->middleware('throttle:email_verification')
            ->name('api.v1.auth.forgot-password');
    });

    // Health check
    Route::get('health', function () {
        return response()->json([
            'status' => 'healthy',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ]);
    })->name('api.v1.health');

    // API Info
    Route::get('info', function () {
        return response()->json([
            'api_version' => '1.0.0',
            'laravel_version' => app()->version(),
            'documentation_url' => url('/docs/api/v1'),
            'status' => 'stable',
            'deprecated' => false,
            'sunset_date' => null,
        ]);
    })->name('api.v1.info');

    /*
    |--------------------------------------------------------------------------
    | Authenticated Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware(['auth:sanctum', 'verified'])
        ->group(function () {

        // Auth Management
        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout'])
                ->name('api.v1.auth.logout');

            Route::get('user', [AuthController::class, 'user'])
                ->name('api.v1.auth.user');

            Route::put('user', [AuthController::class, 'updateProfile'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.auth.update-profile');

            Route::post('refresh-token', [AuthController::class, 'refreshToken'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.auth.refresh-token');
        });

        // Dashboard
        Route::prefix('dashboard')->group(function () {
            Route::get('/', [DashboardController::class, 'index'])
                ->middleware('throttle:dashboard')
                ->name('api.v1.dashboard.index');

            Route::get('stats', [DashboardController::class, 'stats'])
                ->middleware('throttle:dashboard')
                ->name('api.v1.dashboard.stats');

            Route::get('kpi', [DashboardController::class, 'kpi'])
                ->middleware('throttle:dashboard')
                ->name('api.v1.dashboard.kpi');

            Route::get('charts', [DashboardController::class, 'charts'])
                ->middleware('throttle:dashboard')
                ->name('api.v1.dashboard.charts');

            Route::get('notifications', [DashboardController::class, 'notifications'])
                ->middleware('throttle:dashboard')
                ->name('api.v1.dashboard.notifications');
        });

        // Performance Indicators
        Route::prefix('performance-indicators')->group(function () {
            Route::get('/', [PerformanceIndicatorController::class, 'index'])
                ->name('api.v1.indicators.index');

            Route::get('{id}', [PerformanceIndicatorController::class, 'show'])
                ->name('api.v1.indicators.show');

            Route::post('/', [PerformanceIndicatorController::class, 'store'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.indicators.store');

            Route::put('{id}', [PerformanceIndicatorController::class, 'update'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.indicators.update');

            Route::delete('{id}', [PerformanceIndicatorController::class, 'destroy'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.indicators.destroy');

            Route::get('{id}/targets', [PerformanceIndicatorController::class, 'targets'])
                ->name('api.v1.indicators.targets');

            Route::get('{id}/performance-data', [PerformanceIndicatorController::class, 'performanceData'])
                ->name('api.v1.indicators.performance-data');
        });

        // Data Collection
        Route::prefix('data-collection')->group(function () {
            Route::get('/', [DataCollectionController::class, 'index'])
                ->name('api.v1.data-collection.index');

            Route::get('{id}', [DataCollectionController::class, 'show'])
                ->name('api.v1.data-collection.show');

            Route::post('/', [DataCollectionController::class, 'store'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.data-collection.store');

            Route::put('{id}', [DataCollectionController::class, 'update'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.data-collection.update');

            Route::delete('{id}', [DataCollectionController::class, 'destroy'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.data-collection.destroy');

            Route::post('bulk-import', [DataCollectionController::class, 'bulkImport'])
                ->middleware('throttle:bulk_operations')
                ->name('api.v1.data-collection.bulk-import');

            Route::post('{id}/approve', [DataCollectionController::class, 'approve'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.data-collection.approve');

            Route::post('{id}/reject', [DataCollectionController::class, 'reject'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.data-collection.reject');
        });

        // Assessments
        Route::prefix('assessments')->group(function () {
            Route::get('/', [AssessmentController::class, 'index'])
                ->name('api.v1.assessments.index');

            Route::get('{id}', [AssessmentController::class, 'show'])
                ->name('api.v1.assessments.show');

            Route::post('/', [AssessmentController::class, 'store'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.assessments.store');

            Route::put('{id}', [AssessmentController::class, 'update'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.assessments.update');

            Route::delete('{id}', [AssessmentController::class, 'destroy'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.assessments.destroy');

            Route::post('{id}/submit', [AssessmentController::class, 'submit'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.assessments.submit');

            Route::post('{id}/approve', [AssessmentController::class, 'approve'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.assessments.approve');

            Route::get('{id}/scoring', [AssessmentController::class, 'scoring'])
                ->name('api.v1.assessments.scoring');

            Route::post('batch-assess', [AssessmentController::class, 'batchAssess'])
                ->middleware('throttle:bulk_operations')
                ->name('api.v1.assessments.batch-assess');
        });

        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/', [ReportController::class, 'index'])
                ->name('api.v1.reports.index');

            Route::get('{id}', [ReportController::class, 'show'])
                ->name('api.v1.reports.show');

            Route::post('/', [ReportController::class, 'store'])
                ->middleware('throttle:report_generation')
                ->name('api.v1.reports.store');

            Route::delete('{id}', [ReportController::class, 'destroy'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.reports.destroy');

            Route::get('{id}/download', [ReportController::class, 'download'])
                ->middleware('throttle:export')
                ->name('api.v1.reports.download');

            Route::post('{id}/approve', [ReportController::class, 'approve'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.reports.approve');

            Route::get('templates', [ReportController::class, 'templates'])
                ->name('api.v1.reports.templates');
        });

        // File Uploads
        Route::prefix('uploads')->group(function () {
            Route::post('evidence', [DataCollectionController::class, 'uploadEvidence'])
                ->middleware(['throttle:upload', 'secure.file.upload'])
                ->name('api.v1.uploads.evidence');

            Route::post('document', [DataCollectionController::class, 'uploadDocument'])
                ->middleware(['throttle:upload', 'secure.file.upload'])
                ->name('api.v1.uploads.document');

            Route::delete('{id}', [DataCollectionController::class, 'deleteUpload'])
                ->middleware('throttle:api_strict')
                ->name('api.v1.uploads.delete');
        });

        // Statistics & Analytics
        Route::prefix('statistics')->group(function () {
            Route::get('performance', [DashboardController::class, 'performanceStatistics'])
                ->name('api.v1.statistics.performance');

            Route::get('assessment', [DashboardController::class, 'assessmentStatistics'])
                ->name('api.v1.statistics.assessment');

            Route::get('compliance', [DashboardController::class, 'complianceStatistics'])
                ->name('api.v1.statistics.compliance');
        });

        // Export Endpoints
        Route::prefix('exports')->group(function () {
            Route::post('performance-data', [DataCollectionController::class, 'export'])
                ->middleware('throttle:export')
                ->name('api.v1.exports.performance-data');

            Route::post('assessments', [AssessmentController::class, 'export'])
                ->middleware('throttle:export')
                ->name('api.v1.exports.assessments');

            Route::post('reports', [ReportController::class, 'export'])
                ->middleware('throttle:export')
                ->name('api.v1.exports.reports');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Error Handling
    |--------------------------------------------------------------------------
    */
    Route::fallback(function () {
        return response()->json([
            'success' => false,
            'message' => 'Endpoint not found in API v1',
            'error' => 'NOT_FOUND',
            'api_version' => '1.0.0',
        ], 404);
    });
});
