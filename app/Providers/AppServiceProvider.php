<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\Instansi;
use App\Models\IndikatorKinerja;
use App\Models\LaporanKinerja;
use App\Policies\ProgramPolicy;
use App\Policies\KegiatanPolicy;
use App\Policies\InstansiPolicy;
use App\Policies\IndikatorKinerjaPolicy;
use App\Policies\LaporanKinerjaPolicy;

// SAKIP Models
use App\Models\Sakip\PerformanceIndicator;
use App\Models\Sakip\PerformanceData;
use App\Models\Sakip\Assessment;
use App\Models\Sakip\Report;
use App\Models\Sakip\EvidenceDocument;
use App\Models\Target;
use App\Models\AuditLog;

// SAKIP Policies
use App\Policies\SakipDashboardPolicy;
use App\Policies\PerformanceIndicatorPolicy;
use App\Policies\PerformanceDataPolicy;
use App\Policies\AssessmentPolicy;
use App\Policies\ReportPolicy;
use App\Policies\EvidenceDocumentPolicy;
use App\Policies\TargetPolicy;
use App\Policies\AuditLogPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Program::class, ProgramPolicy::class);
        Gate::policy(Kegiatan::class, KegiatanPolicy::class);
        Gate::policy(Instansi::class, InstansiPolicy::class);
        Gate::policy(
            \App\Models\SasaranStrategis::class,
            \App\Policies\SasaranStrategisPolicy::class,
        );
        Gate::policy(IndikatorKinerja::class, IndikatorKinerjaPolicy::class);
        Gate::policy(LaporanKinerja::class, LaporanKinerjaPolicy::class);

        Gate::policy(
            \App\Models\PerformanceIndicator::class,
            \App\Policies\PerformanceIndicatorPolicy::class,
        );

        // Register SAKIP policies
        Gate::policy(
            PerformanceIndicator::class,
            PerformanceIndicatorPolicy::class,
        );
        Gate::policy(PerformanceData::class, PerformanceDataPolicy::class);
        Gate::policy(Assessment::class, AssessmentPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(EvidenceDocument::class, EvidenceDocumentPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);

        // Explicitly register Target policy to override auto-discovery
        Gate::policy(
            \App\Models\Target::class,
            \App\Policies\TargetPolicy::class,
        );

        // Super Admin bypass: Allow Super Admin to bypass all authorization checks
        Gate::before(function ($user, $ability) {
            return $user->hasRole("Super Admin") ? true : null;
        });

        // Gate abilities for admin middleware
        Gate::define("admin.dashboard", function (\App\Models\User $user) {
            return $user->isAdmin() || $user->hasPermission("admin.dashboard");
        });
        Gate::define("admin.settings", function (\App\Models\User $user) {
            return $user->isAdmin() || $user->hasPermission("admin.settings");
        });

        // SAKIP Dashboard Gates
        Gate::define("sakip.dashboard.view", [
            SakipDashboardPolicy::class,
            "viewDashboard",
        ]);
        Gate::define("sakip.dashboard.executive", [
            SakipDashboardPolicy::class,
            "viewExecutiveDashboard",
        ]);
        Gate::define("sakip.dashboard.data_collector", [
            SakipDashboardPolicy::class,
            "viewDataCollectorDashboard",
        ]);
        Gate::define("sakip.dashboard.assessor", [
            SakipDashboardPolicy::class,
            "viewAssessorDashboard",
        ]);
        Gate::define("sakip.dashboard.audit", [
            SakipDashboardPolicy::class,
            "viewAuditDashboard",
        ]);

        // SAKIP Main Gates
        Gate::define("sakip.view.dashboard", [
            \App\Policies\SakipPolicy::class,
            "viewDashboard",
        ]);
        Gate::define("sakip.view.performance-indicators", [
            \App\Policies\SakipPolicy::class,
            "viewPerformanceIndicators",
        ]);
        Gate::define("sakip.view.performance-data", [
            \App\Policies\SakipPolicy::class,
            "viewPerformanceData",
        ]);
        Gate::define("sakip.view.assessments", [
            \App\Policies\SakipPolicy::class,
            "viewAssessments",
        ]);
        Gate::define("sakip.view.reports", [
            \App\Policies\SakipPolicy::class,
            "viewReports",
        ]);
        Gate::define("sakip.export.data", [
            \App\Policies\SakipPolicy::class,
            "exportData",
        ]);

        Gate::define("isSuperAdmin", function ($user) {
            return $user->hasRole("Super Admin");
        });

        // Blade directives for roles and permissions
        Blade::if("role", function ($role) {
            $user = auth()->user();
            return $user && $user->hasRole($role);
        });

        Blade::if("anyrole", function (...$roles) {
            $user = auth()->user();
            return $user && $user->hasAnyRole($roles);
        });

        Blade::if("permission", function ($permission) {
            $user = auth()->user();
            return $user && $user->hasPermission($permission);
        });

        // Slow query logging (threshold: 150ms)
        DB::listen(function ($query) {
            $thresholdMs = 150;
            // $query->time is in milliseconds for Laravel 10+; guard if null
            $duration = method_exists($query, "time") ? $query->time ?? 0 : 0;
            if ($duration >= $thresholdMs) {
                Log::channel("daily")->warning("Slow query detected", [
                    "sql" => $query->sql,
                    "bindings" => $query->bindings,
                    "time_ms" => $duration,
                    "connection" => property_exists($query, "connectionName")
                        ? $query->connectionName
                        : null,
                ]);
            }
        });
    }
}
