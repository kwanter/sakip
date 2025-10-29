<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Sakip\SakipDashboardController;
use App\Http\Controllers\Sakip\PerformanceIndicatorController;
use App\Http\Controllers\Sakip\DataCollectionController;
use App\Http\Controllers\Sakip\PerformanceMeasurementController;
use App\Http\Controllers\Sakip\AssessmentController;
use App\Http\Controllers\Sakip\ReportController;
use App\Http\Controllers\Sakip\SakipAuditController;
use App\Http\Controllers\Sakip\InstansiController;
use App\Http\Controllers\Sakip\SasaranStrategisController;
use App\Http\Controllers\Sakip\ProgramController;
use App\Http\Controllers\Sakip\KegiatanController;

// SAKIP Routes
Route::prefix("sakip")
    ->middleware(["auth", "verified"])
    ->group(function () {
        // Dashboard
        Route::get("/", [SakipDashboardController::class, "index"])->name(
            "sakip.dashboard",
        );

        // Master Data - Instansi
        Route::resource("instansi", InstansiController::class)->names([
            "index" => "sakip.instansi.index",
            "create" => "sakip.instansi.create",
            "store" => "sakip.instansi.store",
            "show" => "sakip.instansi.show",
            "edit" => "sakip.instansi.edit",
            "update" => "sakip.instansi.update",
            "destroy" => "sakip.instansi.destroy",
        ]);

        // Master Data - Sasaran Strategis
        Route::resource("sasaran-strategis", SasaranStrategisController::class)
            ->parameters(["sasaran-strategis" => "sasaranStrategis"])
            ->names([
                "index" => "sakip.sasaran-strategis.index",
                "create" => "sakip.sasaran-strategis.create",
                "store" => "sakip.sasaran-strategis.store",
                "show" => "sakip.sasaran-strategis.show",
                "edit" => "sakip.sasaran-strategis.edit",
                "update" => "sakip.sasaran-strategis.update",
                "destroy" => "sakip.sasaran-strategis.destroy",
            ]);

        // Master Data - Program
        Route::resource("program", ProgramController::class)->names([
            "index" => "sakip.program.index",
            "create" => "sakip.program.create",
            "store" => "sakip.program.store",
            "show" => "sakip.program.show",
            "edit" => "sakip.program.edit",
            "update" => "sakip.program.update",
            "destroy" => "sakip.program.destroy",
        ]);

        // Master Data - Kegiatan (Activities under Program)
        Route::resource("kegiatan", KegiatanController::class)->names([
            "index" => "sakip.kegiatan.index",
            "create" => "sakip.kegiatan.create",
            "store" => "sakip.kegiatan.store",
            "show" => "sakip.kegiatan.show",
            "edit" => "sakip.kegiatan.edit",
            "update" => "sakip.kegiatan.update",
            "destroy" => "sakip.kegiatan.destroy",
        ]);

        // Performance Indicators
        Route::resource(
            "indicators",
            PerformanceIndicatorController::class,
        )->names([
            "index" => "sakip.indicators.index",
            "create" => "sakip.indicators.create",
            "store" => "sakip.indicators.store",
            "show" => "sakip.indicators.show",
            "edit" => "sakip.indicators.edit",
            "update" => "sakip.indicators.update",
            "destroy" => "sakip.indicators.destroy",
        ]);

        // Target Management (nested under indicators)
        Route::prefix("indicators/{indicator}/targets")->group(function () {
            Route::get("/", [
                \App\Http\Controllers\Sakip\TargetController::class,
                "index",
            ])->name("sakip.targets.index");
            Route::get("/create", [
                \App\Http\Controllers\Sakip\TargetController::class,
                "create",
            ])->name("sakip.targets.create");
            Route::post("/", [
                \App\Http\Controllers\Sakip\TargetController::class,
                "store",
            ])->name("sakip.targets.store");
            Route::get("/{target}/edit", [
                \App\Http\Controllers\Sakip\TargetController::class,
                "edit",
            ])->name("sakip.targets.edit");
            Route::put("/{target}", [
                \App\Http\Controllers\Sakip\TargetController::class,
                "update",
            ])->name("sakip.targets.update");
            Route::delete("/{target}", [
                \App\Http\Controllers\Sakip\TargetController::class,
                "destroy",
            ])->name("sakip.targets.destroy");

            // Approval workflow
            Route::post("/{target}/approve", [
                \App\Http\Controllers\Sakip\TargetController::class,
                "approve",
            ])->name("sakip.targets.approve");
            Route::post("/{target}/reject", [
                \App\Http\Controllers\Sakip\TargetController::class,
                "reject",
            ])->name("sakip.targets.reject");
            Route::post("/{target}/revise", [
                \App\Http\Controllers\Sakip\TargetController::class,
                "revise",
            ])->name("sakip.targets.revise");
        });

        // Performance Data
        Route::prefix("performance-data")->group(function () {
            Route::get("/", [DataCollectionController::class, "index"])->name(
                "sakip.performance-data.index",
            );
            Route::get("/create", [
                DataCollectionController::class,
                "create",
            ])->name("sakip.performance-data.create");
            Route::post("/", [DataCollectionController::class, "store"])->name(
                "sakip.performance-data.store",
            );
            Route::get("/{performanceData}", [
                DataCollectionController::class,
                "show",
            ])->name("sakip.performance-data.show");
            Route::get("/{performanceData}/edit", [
                DataCollectionController::class,
                "edit",
            ])->name("sakip.performance-data.edit");
            Route::put("/{performanceData}", [
                DataCollectionController::class,
                "update",
            ])->name("sakip.performance-data.update");
            Route::delete("/{performanceData}", [
                DataCollectionController::class,
                "destroy",
            ])->name("sakip.performance-data.destroy");
            Route::post("/bulk-import", [
                DataCollectionController::class,
                "bulkImport",
            ])->name("sakip.performance-data.bulk-import");
            Route::post("/validate-data", [
                DataCollectionController::class,
                "validateData",
            ])->name("sakip.performance-data.validate");
        });

        // Data Collection (alias for Performance Data with different route names)
        Route::prefix("data-collection")->group(function () {
            Route::get("/", [DataCollectionController::class, "index"])->name(
                "sakip.data-collection.index",
            );
            Route::get("/create", [
                DataCollectionController::class,
                "create",
            ])->name("sakip.data-collection.create");
            Route::post("/", [DataCollectionController::class, "store"])->name(
                "sakip.data-collection.store",
            );
            Route::get("/{performanceData}", [
                DataCollectionController::class,
                "show",
            ])->name("sakip.data-collection.show");
            Route::get("/{performanceData}/edit", [
                DataCollectionController::class,
                "edit",
            ])->name("sakip.data-collection.edit");
            Route::put("/{performanceData}", [
                DataCollectionController::class,
                "update",
            ])->name("sakip.data-collection.update");
            Route::delete("/{performanceData}", [
                DataCollectionController::class,
                "destroy",
            ])->name("sakip.data-collection.destroy");
            Route::post("/bulk-import", [
                DataCollectionController::class,
                "bulkImport",
            ])->name("sakip.data-collection.bulk-import");
            Route::post("/validate-data", [
                DataCollectionController::class,
                "validateData",
            ])->name("sakip.data-collection.validate");
            Route::post("/{performanceData}/reject", [
                DataCollectionController::class,
                "reject",
            ])->name("sakip.data-collection.reject");
            Route::get("/template", [
                DataCollectionController::class,
                "downloadTemplate",
            ])->name("sakip.data-collection.template");
            Route::get("/sample", [
                DataCollectionController::class,
                "downloadSample",
            ])->name("sakip.data-collection.sample");
            Route::post("/import", [
                DataCollectionController::class,
                "import",
            ])->name("sakip.data-collection.import");
            Route::post("/import/confirm", [
                DataCollectionController::class,
                "confirmImport",
            ])->name("sakip.data-collection.import.confirm");
            Route::get("/evidence-upload", [
                DataCollectionController::class,
                "evidenceUpload",
            ])->name("sakip.data-collection.evidence-upload");
            Route::get("/evidence", [
                DataCollectionController::class,
                "evidence",
            ])->name("sakip.data-collection.evidence");
        });

        // Assessments
        Route::prefix("assessments")->group(function () {
            Route::get("/", [AssessmentController::class, "index"])->name(
                "sakip.assessments.index",
            );
            Route::get("/create", [
                AssessmentController::class,
                "create",
            ])->name("sakip.assessments.create");
            Route::post("/", [AssessmentController::class, "store"])->name(
                "sakip.assessments.store",
            );
            Route::get("/{assessment}", [
                AssessmentController::class,
                "show",
            ])->name("sakip.assessments.show");
            Route::get("/{assessment}/edit", [
                AssessmentController::class,
                "edit",
            ])->name("sakip.assessments.edit");
            Route::put("/{assessment}", [
                AssessmentController::class,
                "update",
            ])->name("sakip.assessments.update");
            Route::delete("/{assessment}", [
                AssessmentController::class,
                "destroy",
            ])->name("sakip.assessments.destroy");
            Route::post("/{assessment}/approve", [
                AssessmentController::class,
                "approve",
            ])->name("sakip.assessments.approve");
            Route::post("/{assessment}/reject", [
                AssessmentController::class,
                "reject",
            ])->name("sakip.assessments.reject");
            Route::post("/{assessment}/auto-assess", [
                AssessmentController::class,
                "autoAssess",
            ])->name("sakip.assessments.auto-assess");
            Route::post("/batch-assess", [
                AssessmentController::class,
                "batchAssess",
            ])->name("sakip.assessments.batch-assess");
        });

        // Reports
        Route::prefix("reports")->group(function () {
            Route::get("/", [ReportController::class, "index"])->name(
                "sakip.reports.index",
            );
            Route::get("/create", [ReportController::class, "create"])->name(
                "sakip.reports.create",
            );
            Route::post("/", [ReportController::class, "store"])->name(
                "sakip.reports.store",
            );
            Route::get("/{report}", [ReportController::class, "show"])->name(
                "sakip.reports.show",
            );
            Route::get("/{report}/download", [
                ReportController::class,
                "download",
            ])->name("sakip.reports.download");
            Route::get("/{report}/export/{format}", [
                ReportController::class,
                "export",
            ])->name("sakip.reports.export");
            Route::delete("/{report}", [
                ReportController::class,
                "destroy",
            ])->name("sakip.reports.destroy");
            Route::post("/{report}/approve", [
                ReportController::class,
                "approve",
            ])->name("sakip.reports.approve");
            Route::post("/{report}/reject", [
                ReportController::class,
                "reject",
            ])->name("sakip.reports.reject");
            Route::get("/templates/{template}", [
                ReportController::class,
                "template",
            ])->name("sakip.reports.template");
        });

        // Audit and Compliance
        Route::prefix("audit")->group(function () {
            Route::get("/", [SakipAuditController::class, "index"])->name(
                "sakip.audit.index",
            );
            Route::post("/run-compliance-check", [
                SakipAuditController::class,
                "runComplianceCheck",
            ])->name("sakip.audit.run-compliance-check");
            Route::get("/audit-log/{auditLog}", [
                SakipAuditController::class,
                "showAuditLog",
            ])->name("sakip.audit.show-log");
            Route::post("/fix-violation/{violation}", [
                SakipAuditController::class,
                "fixViolation",
            ])->name("sakip.audit.fix-violation");
            Route::get("/export-report", [
                SakipAuditController::class,
                "exportReport",
            ])->name("sakip.audit.export-report");
        });

        // API endpoints for AJAX
        Route::prefix("api")->group(function () {
            Route::get("/dashboard-data", [
                SakipDashboardController::class,
                "getDashboardData",
            ])->name("sakip.api.dashboard-data");
            Route::get("/performance-summary", [
                PerformanceMeasurementController::class,
                "getPerformanceSummary",
            ])->name("sakip.api.performance-summary");
            Route::get("/achievement-trends", [
                PerformanceMeasurementController::class,
                "getAchievementTrends",
            ])->name("sakip.api.achievement-trends");
            Route::get("/compliance-status", [
                SakipAuditController::class,
                "getComplianceStatus",
            ])->name("sakip.api.compliance-status");
            Route::get("/indicator-comparison", [
                PerformanceMeasurementController::class,
                "getIndicatorComparison",
            ])->name("sakip.api.indicator-comparison");
            Route::get("/indicators/by-instansi/{instansi}", [
                PerformanceIndicatorController::class,
                "byInstansi",
            ])->name("sakip.api.indicators.by-instansi");
            Route::get("/indicators/{indicator}/targets", [
                PerformanceIndicatorController::class,
                "getTargets",
            ])->name("sakip.api.indicators.targets");
            Route::get("/indicators/{indicator}/performance-data", [
                PerformanceIndicatorController::class,
                "getPerformanceData",
            ])->name("sakip.api.indicators.performance-data");
            Route::get("/assessment-analytics", [
                AssessmentController::class,
                "getAnalytics",
            ])->name("sakip.api.assessment-analytics");
            Route::get("/report-analytics", [
                ReportController::class,
                "getAnalytics",
            ])->name("sakip.api.report-analytics");
            Route::get("/audit-analytics", [
                SakipAuditController::class,
                "getAnalytics",
            ])->name("sakip.api.audit-analytics");

            // Master Data API endpoints for cascade dropdowns
            Route::get("/sasaran-strategis/by-instansi/{instansi}", [
                SasaranStrategisController::class,
                "byInstansi",
            ])->name("sakip.api.sasaran-strategis.by-instansi");
            Route::get("/program/by-sasaran-strategis/{sasaranStrategis}", [
                ProgramController::class,
                "bySasaranStrategis",
            ])->name("sakip.api.program.by-sasaran-strategis");
            Route::get("/kegiatan/by-program/{program}", [
                KegiatanController::class,
                "byProgram",
            ])->name("sakip.api.kegiatan.by-program");
        });
    });
