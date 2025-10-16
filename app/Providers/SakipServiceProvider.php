<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class SakipServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register SAKIP services
        $this->app->singleton('sakip', function ($app) {
            return new \App\Services\SakipService();
        });

        // Register SAKIP dashboard service
        $this->app->singleton('sakip.dashboard', function ($app) {
            return new \App\Services\SakipDashboardService();
        });

        // Register SAKIP data table service
        $this->app->singleton('sakip.datatable', function ($app) {
            return new \App\Services\SakipDataTableService();
        });

        // Register SAKIP notification service
        $this->app->singleton('sakip.notification', function ($app) {
            return new \App\Services\SakipNotificationService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register view composers
        $this->registerViewComposers();
        
        // Register Blade directives
        $this->registerBladeDirectives();
        
        // Register components
        $this->registerComponents();
    }

    /**
     * Register view composers
     */
    protected function registerViewComposers(): void
    {
        // Share SAKIP configuration with all views
        View::composer('*', function ($view) {
            $view->with('sakipConfig', config('sakip'));
        });

        // Share current user with SAKIP views
        View::composer(['sakip.*', 'layouts.sakip'], function ($view) {
            $view->with('currentUser', auth()->user());
        });
    }

    /**
     * Register Blade directives
     */
    protected function registerBladeDirectives(): void
    {
        // SAKIP permission directive
        Blade::directive('sakipCan', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->can($expression)): ?>";
        });

        Blade::directive('endsakipCan', function () {
            return "<?php endif; ?>";
        });

        // SAKIP role directive
        Blade::directive('sakipRole', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasRole($expression)): ?>";
        });

        Blade::directive('endsakipRole', function () {
            return "<?php endif; ?>";
        });

        // SAKIP has any role directive
        Blade::directive('sakipHasAnyRole', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasAnyRole($expression)): ?>";
        });

        Blade::directive('endsakipHasAnyRole', function () {
            return "<?php endif; ?>";
        });

        // SAKIP has all roles directive
        Blade::directive('sakipHasAllRoles', function ($expression) {
            return "<?php if(auth()->check() && auth()->user()->hasAllRoles($expression)): ?>";
        });

        Blade::directive('endsakipHasAllRoles', function () {
            return "<?php endif; ?>";
        });

        // SAKIP component directive
        Blade::directive('sakipComponent', function ($expression) {
            return "<?php echo app('sakip')->renderComponent($expression); ?>";
        });

        // SAKIP data table directive
        Blade::directive('sakipDataTable', function ($expression) {
            return "<?php echo app('sakip.datatable')->render($expression); ?>";
        });

        // SAKIP notification directive
        Blade::directive('sakipNotification', function ($expression) {
            return "<?php echo app('sakip.notification')->render($expression); ?>";
        });
    }

    /**
     * Register components
     */
    protected function registerComponents(): void
    {
        // Register SAKIP view components
        $this->loadViewsFrom(resource_path('views/sakip/components'), 'sakip-components');
        
        // Publish SAKIP assets
        $this->publishes([
            __DIR__.'/../../public/sakip' => public_path('sakip'),
        ], 'sakip-assets');
        
        // Publish SAKIP configuration
        $this->publishes([
            __DIR__.'/../../config/sakip.php' => config_path('sakip.php'),
        ], 'sakip-config');
        
        // Publish SAKIP views
        $this->publishes([
            __DIR__.'/../../resources/views/sakip' => resource_path('views/sakip'),
        ], 'sakip-views');
        
        // Publish SAKIP JavaScript
        $this->publishes([
            __DIR__.'/../../resources/js/sakip' => resource_path('js/sakip'),
        ], 'sakip-js');
        
        // Publish SAKIP CSS
        $this->publishes([
            __DIR__.'/../../resources/css/sakip' => resource_path('css/sakip'),
        ], 'sakip-css');
    }
}