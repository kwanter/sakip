<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Completely disable Telescope in production
        if ($this->app->environment("production")) {
            Telescope::$stopRecording = true;
            return;
        }

        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment("local");

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
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
        if ($this->app->environment("local")) {
            return;
        }

        // Hide sensitive request parameters
        Telescope::hideRequestParameters([
            "_token",
            "password",
            "password_confirmation",
            "api_key",
            "secret",
            "token",
        ]);

        // Hide sensitive headers
        Telescope::hideRequestHeaders([
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
}
