<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sakip\SakipDashboardController;
use App\Http\Controllers\Sakip\PerformanceIndicatorController;
use App\Http\Controllers\Sakip\DataCollectionController;
use App\Http\Controllers\Sakip\PerformanceMeasurementController;
use App\Http\Controllers\Sakip\AssessmentController;
use App\Http\Controllers\Sakip\ReportController;
use App\Http\Controllers\Sakip\SakipAuditController;

/*
|--------------------------------------------------------------------------
| SAKIP API Routes
|--------------------------------------------------------------------------
|
| API routes for SAKIP (Sistem Akuntabilitas Kinerja Instansi Pemerintah)
| All routes are prefixed with /sakip/api and use API middleware
|
*/

Route::prefix('sakip/api')->middleware(['auth', 'api'])->group(function () {
    
    // Dashboard Routes
    Route::get('/dashboard-data', [SakipDashboardController::class, 'getDashboardData'])
        ->name('sakip.api.dashboard.data');
    
    Route::get('/dashboard-charts', [SakipDashboardController::class, 'getDashboardCharts'])
        ->name('sakip.api.dashboard.charts');
    
    Route::get('/dashboard-kpi', [SakipDashboardController::class, 'getDashboardKPI'])
        ->name('sakip.api.dashboard.kpi');
    
    Route::get('/dashboard-notifications', [SakipDashboardController::class, 'getDashboardNotifications'])
        ->name('sakip.api.dashboard.notifications');
    
    // Performance Indicators Routes
    Route::get('/performance-indicators', [PerformanceIndicatorController::class, 'getIndicators'])
        ->name('sakip.api.performance-indicators.list');
    
    Route::get('/performance-indicators/{id}', [PerformanceIndicatorController::class, 'getIndicator'])
        ->name('sakip.api.performance-indicators.detail');
    
    Route::post('/performance-indicators', [PerformanceIndicatorController::class, 'storeIndicator'])
        ->name('sakip.api.performance-indicators.store');
    
    Route::put('/performance-indicators/{id}', [PerformanceIndicatorController::class, 'updateIndicator'])
        ->name('sakip.api.performance-indicators.update');
    
    Route::delete('/performance-indicators/{id}', [PerformanceIndicatorController::class, 'deleteIndicator'])
        ->name('sakip.api.performance-indicators.delete');
    
    Route::get('/performance-indicators/{id}/targets', [PerformanceIndicatorController::class, 'getIndicatorTargets'])
        ->name('sakip.api.performance-indicators.targets');
    
    // Data Collection Routes
    Route::get('/data-collection', [DataCollectionController::class, 'getDataCollection'])
        ->name('sakip.api.data-collection.list');
    
    Route::get('/data-collection/{id}', [DataCollectionController::class, 'getDataCollectionDetail'])
        ->name('sakip.api.data-collection.detail');
    
    Route::post('/data-collection', [DataCollectionController::class, 'storeDataCollection'])
        ->name('sakip.api.data-collection.store');
    
    Route::put('/data-collection/{id}', [DataCollectionController::class, 'updateDataCollection'])
        ->name('sakip.api.data-collection.update');
    
    Route::delete('/data-collection/{id}', [DataCollectionController::class, 'deleteDataCollection'])
        ->name('sakip.api.data-collection.delete');
    
    Route::post('/data-collection/bulk-import', [DataCollectionController::class, 'bulkImport'])
        ->name('sakip.api.data-collection.bulk-import');
    
    Route::get('/data-collection/templates/download', [DataCollectionController::class, 'downloadTemplate'])
        ->name('sakip.api.data-collection.template.download');
    
    // Performance Measurement Routes
    Route::get('/performance-measurement', [PerformanceMeasurementController::class, 'getMeasurements'])
        ->name('sakip.api.performance-measurement.list');
    
    Route::get('/performance-measurement/{id}', [PerformanceMeasurementController::class, 'getMeasurement'])
        ->name('sakip.api.performance-measurement.detail');
    
    Route::post('/performance-measurement', [PerformanceMeasurementController::class, 'storeMeasurement'])
        ->name('sakip.api.performance-measurement.store');
    
    Route::put('/performance-measurement/{id}', [PerformanceMeasurementController::class, 'updateMeasurement'])
        ->name('sakip.api.performance-measurement.update');
    
    Route::delete('/performance-measurement/{id}', [PerformanceMeasurementController::class, 'deleteMeasurement'])
        ->name('sakip.api.performance-measurement.delete');
    
    Route::get('/performance-measurement/{id}/trend', [PerformanceMeasurementController::class, 'getMeasurementTrend'])
        ->name('sakip.api.performance-measurement.trend');
    
    Route::get('/performance-measurement/{id}/comparison', [PerformanceMeasurementController::class, 'getMeasurementComparison'])
        ->name('sakip.api.performance-measurement.comparison');
    
    // Assessment Routes
    Route::get('/assessments', [AssessmentController::class, 'getAssessments'])
        ->name('sakip.api.assessments.list');
    
    Route::get('/assessments/{id}', [AssessmentController::class, 'getAssessment'])
        ->name('sakip.api.assessments.detail');
    
    Route::post('/assessments', [AssessmentController::class, 'storeAssessment'])
        ->name('sakip.api.assessments.store');
    
    Route::put('/assessments/{id}', [AssessmentController::class, 'updateAssessment'])
        ->name('sakip.api.assessments.update');
    
    Route::delete('/assessments/{id}', [AssessmentController::class, 'deleteAssessment'])
        ->name('sakip.api.assessments.delete');
    
    Route::post('/assessments/{id}/submit', [AssessmentController::class, 'submitAssessment'])
        ->name('sakip.api.assessments.submit');
    
    Route::post('/assessments/{id}/verify', [AssessmentController::class, 'verifyAssessment'])
        ->name('sakip.api.assessments.verify');
    
    Route::post('/assessments/{id}/approve', [AssessmentController::class, 'approveAssessment'])
        ->name('sakip.api.assessments.approve');
    
    Route::get('/assessments/{id}/scoring', [AssessmentController::class, 'getAssessmentScoring'])
        ->name('sakip.api.assessments.scoring');
    
    Route::post('/assessments/{id}/scoring', [AssessmentController::class, 'updateAssessmentScoring'])
        ->name('sakip.api.assessments.scoring.update');
    
    Route::get('/assessments/{id}/criteria', [AssessmentController::class, 'getAssessmentCriteria'])
        ->name('sakip.api.assessments.criteria');
    
    Route::post('/assessments/batch-assess', [AssessmentController::class, 'batchAssess'])
        ->name('sakip.api.assessments.batch-assess');
    
    // Report Routes
    Route::get('/reports', [ReportController::class, 'getReports'])
        ->name('sakip.api.reports.list');
    
    Route::get('/reports/{id}', [ReportController::class, 'getReport'])
        ->name('sakip.api.reports.detail');
    
    Route::post('/reports', [ReportController::class, 'generateReport'])
        ->name('sakip.api.reports.generate');
    
    Route::delete('/reports/{id}', [ReportController::class, 'deleteReport'])
        ->name('sakip.api.reports.delete');
    
    Route::get('/reports/{id}/download', [ReportController::class, 'downloadReport'])
        ->name('sakip.api.reports.download');
    
    Route::get('/reports/templates', [ReportController::class, 'getReportTemplates'])
        ->name('sakip.api.reports.templates');
    
    Route::get('/reports/chart-data', [ReportController::class, 'getChartData'])
        ->name('sakip.api.reports.chart-data');
    
    // Audit Trail Routes
    Route::get('/audit-trail', [SakipAuditController::class, 'getAuditTrail'])
        ->name('sakip.api.audit-trail.list');
    
    Route::get('/audit-trail/{id}', [SakipAuditController::class, 'getAuditEntry'])
        ->name('sakip.api.audit-trail.detail');
    
    Route::get('/audit-trail/export', [SakipAuditController::class, 'exportAuditTrail'])
        ->name('sakip.api.audit-trail.export');
    
    Route::get('/audit-trail/statistics', [SakipAuditController::class, 'getAuditStatistics'])
        ->name('sakip.api.audit-trail.statistics');
    
    // Compliance Routes
    Route::get('/compliance/status', [SakipDashboardController::class, 'getComplianceStatus'])
        ->name('sakip.api.compliance.status');
    
    Route::get('/compliance/issues', [SakipDashboardController::class, 'getComplianceIssues'])
        ->name('sakip.api.compliance.issues');
    
    Route::post('/compliance/issues', [SakipDashboardController::class, 'storeComplianceIssue'])
        ->name('sakip.api.compliance.issues.store');
    
    Route::put('/compliance/issues/{id}', [SakipDashboardController::class, 'updateComplianceIssue'])
        ->name('sakip.api.compliance.issues.update');
    
    Route::delete('/compliance/issues/{id}', [SakipDashboardController::class, 'deleteComplianceIssue'])
        ->name('sakip.api.compliance.issues.delete');
    
    // Notification Routes
    Route::get('/notifications', [SakipDashboardController::class, 'getNotifications'])
        ->name('sakip.api.notifications.list');
    
    Route::get('/notifications/unread', [SakipDashboardController::class, 'getUnreadNotifications'])
        ->name('sakip.api.notifications.unread');
    
    Route::put('/notifications/{id}/read', [SakipDashboardController::class, 'markNotificationRead'])
        ->name('sakip.api.notifications.read');
    
    Route::put('/notifications/read-all', [SakipDashboardController::class, 'markAllNotificationsRead'])
        ->name('sakip.api.notifications.read-all');
    
    Route::delete('/notifications/{id}', [SakipDashboardController::class, 'deleteNotification'])
        ->name('sakip.api.notifications.delete');
    
    // File Upload Routes
    Route::post('/upload/evidence', [DataCollectionController::class, 'uploadEvidence'])
        ->name('sakip.api.upload.evidence');
    
    Route::post('/upload/report', [ReportController::class, 'uploadReportFile'])
        ->name('sakip.api.upload.report');
    
    Route::delete('/upload/{id}', [DataCollectionController::class, 'deleteUpload'])
        ->name('sakip.api.upload.delete');
    
    // Statistics and Analytics Routes
    Route::get('/statistics/performance', [SakipDashboardController::class, 'getPerformanceStatistics'])
        ->name('sakip.api.statistics.performance');
    
    Route::get('/statistics/assessment', [SakipDashboardController::class, 'getAssessmentStatistics'])
        ->name('sakip.api.statistics.assessment');
    
    Route::get('/statistics/compliance', [SakipDashboardController::class, 'getComplianceStatistics'])
        ->name('sakip.api.statistics.compliance');
    
    Route::get('/analytics/trends', [SakipDashboardController::class, 'getTrendsAnalytics'])
        ->name('sakip.api.analytics.trends');
    
    Route::get('/analytics/comparison', [SakipDashboardController::class, 'getComparisonAnalytics'])
        ->name('sakip.api.analytics.comparison');
    
    // Export Routes
    Route::get('/export/performance-data', [DataCollectionController::class, 'exportPerformanceData'])
        ->name('sakip.api.export.performance-data');
    
    Route::get('/export/assessment-results', [AssessmentController::class, 'exportAssessmentResults'])
        ->name('sakip.api.export.assessment-results');
    
    Route::get('/export/compliance-report', [SakipDashboardController::class, 'exportComplianceReport'])
        ->name('sakip.api.export.compliance-report');
    
    // Bulk Operations Routes
    Route::post('/bulk/update-targets', [PerformanceIndicatorController::class, 'bulkUpdateTargets'])
        ->name('sakip.api.bulk.update-targets');
    
    Route::post('/bulk/verify-data', [DataCollectionController::class, 'bulkVerifyData'])
        ->name('sakip.api.bulk.verify-data');
    
    Route::post('/bulk/export-reports', [ReportController::class, 'bulkExportReports'])
        ->name('sakip.api.bulk.export-reports');
    
});