<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\Instansi;
use App\Policies\ProgramPolicy;
use App\Policies\KegiatanPolicy;
use App\Policies\InstansiPolicy;

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

        // Gate abilities for admin middleware
        Gate::define('admin.dashboard', function (\App\Models\User $user) {
            return $user->isAdmin() || $user->hasPermission('admin.dashboard');
        });
        Gate::define('admin.settings', function (\App\Models\User $user) {
            return $user->isAdmin() || $user->hasPermission('admin.settings');
        });

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
