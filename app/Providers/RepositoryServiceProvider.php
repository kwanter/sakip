<?php

namespace App\Providers;

use App\Repositories\Contracts\KegiatanRepositoryInterface;
use App\Repositories\Contracts\ProgramRepositoryInterface;
use App\Repositories\KegiatanRepository;
use App\Repositories\ProgramRepository;
use App\Services\DropdownCacheService;
use Illuminate\Support\ServiceProvider;

/**
 * Repository Service Provider
 *
 * Registers repository interfaces with their concrete implementations.
 * This enables dependency injection and makes the codebase more testable.
 *
 * Usage in controllers/services:
 *   constructor(KegiatanRepositoryInterface $kegiatanRepo)
 *   {
 *       $this->kegiatanRepo = $kegiatanRepo;
 *   }
 *
 * Benefits:
 * - Loose coupling: Controllers depend on interfaces, not concrete classes
 * - Testability: Easy to mock repositories for unit tests
 * - Flexibility: Can swap implementations (e.g., for caching or logging)
 *
 * @package App\Providers
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // Bind Kegiatan repository
        $this->app->bind(
            KegiatanRepositoryInterface::class,
            KegiatanRepository::class
        );

        // Bind Program repository
        $this->app->bind(
            ProgramRepositoryInterface::class,
            ProgramRepository::class
        );

        // Register Dropdown Cache Service as singleton
        $this->app->singleton(DropdownCacheService::class);

        // Add more repository bindings here as needed:
        // $this->app->bind(PerformanceIndicatorRepositoryInterface::class, PerformanceIndicatorRepository::class);
        // $this->app->bind(PerformanceDataRepositoryInterface::class, PerformanceDataRepository::class);
        // $this->app->bind(AssessmentRepositoryInterface::class, AssessmentRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
