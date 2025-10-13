<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
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
    }
}
