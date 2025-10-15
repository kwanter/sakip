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
use App\Models\Sakip\Target;

// SAKIP Policies
use App\Policies\Sakip\SakipDashboardPolicy;
use App\Policies\Sakip\PerformanceIndicatorPolicy;
use App\Policies\Sakip\PerformanceDataPolicy;
use App\Policies\Sakip\AssessmentPolicy;
use App\Policies\Sakip\ReportPolicy;
use App\Policies\Sakip\EvidenceDocumentPolicy;
use App\Policies\Sakip\TargetPolicy;

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
        Gate::policy(IndikatorKinerja::class, IndikatorKinerjaPolicy::class);
        Gate::policy(LaporanKinerja::class, LaporanKinerjaPolicy::class);

        // Register SAKIP policies
        Gate::policy(PerformanceIndicator::class, PerformanceIndicatorPolicy::class);
        Gate::policy(PerformanceData::class, PerformanceDataPolicy::class);
        Gate::policy(Assessment::class, AssessmentPolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(EvidenceDocument::class, EvidenceDocumentPolicy::class);
        Gate::policy(Target::class, TargetPolicy::class);

        // Gate abilities for admin middleware
        Gate::define('admin.dashboard', function (\App\Models\User $user) {
            return $user->isAdmin() || $user->hasPermission('admin.dashboard');
        });
        Gate::define('admin.settings', function (\App\Models\User $user) {
            return $user->isAdmin() || $user->hasPermission('admin.settings');
        });

        // SAKIP Dashboard Gates
        Gate::define('sakip.dashboard.view', [SakipDashboardPolicy::class, 'viewDashboard']);
        Gate::define('sakip.dashboard.executive', [SakipDashboardPolicy::class, 'viewExecutiveDashboard']);
        Gate::define('sakip.dashboard.data_collector', [SakipDashboardPolicy::class, 'viewDataCollectorDashboard']);
        Gate::define('sakip.dashboard.assessor', [SakipDashboardPolicy::class, 'viewAssessorDashboard']);
        Gate::define('sakip.dashboard.audit', [SakipDashboardPolicy::class, 'viewAuditDashboard']);

        // Blade directives for roles and permissions
        Blade::if('role', function ($role) {
            $user = auth()->user();
            return $user && $user->hasRole($role);
        });

        Blade::if('anyrole', function (...$roles) {
            $user = auth()->user();
            return $user && $user->hasAnyRole($roles);
        });

        Blade::if('permission', function ($permission) {
            $user = auth()->user();
            return $user && $user->hasPermission($permission);
        });
    }
}
