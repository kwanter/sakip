<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class TelescopeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (! class_exists(\Laravel\Telescope\Telescope::class)) {
            return;
        }

        // Completely disable Telescope in production
        if ($this->app->environment("production")) {
            \Laravel\Telescope\Telescope::stopRecording();
            return;
        }

        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment("local");

        \Laravel\Telescope\Telescope::filter(function (\Laravel\Telescope\IncomingEntry $entry) use ($isLocal) {
            return $isLocal ||
                $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if (! class_exists(\Laravel\Telescope\Telescope::class)) {
            return;
        }

        if ($this->app->environment("local")) {
            return;
        }

        // Hide sensitive request parameters
        \Laravel\Telescope\Telescope::hideRequestParameters([
            "_token",
            "password",
            "password_confirmation",
            "api_key",
            "secret",
            "token",
        ]);

        // Hide sensitive headers
        \Laravel\Telescope\Telescope::hideRequestHeaders([
            "cookie",
            "x-csrf-token",
            "x-xsrf-token",
            "authorization",
            "php-auth-pw",
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define("viewTelescope", function ($user) {
            // Only allow Super Admin role to access Telescope
            // Add specific admin emails as a secondary check
            return $user->hasRole("Super Admin") ||
                in_array($user->email, [
                    // Add specific admin emails here in non-production environments
                    // DO NOT commit actual admin emails to version control
                ]);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (class_exists(\Laravel\Telescope\TelescopeApplicationServiceProvider::class)) {
            $this->gate();
        }
    }
}
