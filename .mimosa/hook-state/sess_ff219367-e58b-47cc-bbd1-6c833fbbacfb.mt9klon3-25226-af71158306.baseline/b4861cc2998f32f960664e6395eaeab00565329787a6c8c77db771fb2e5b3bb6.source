<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class RateLimitServiceProvider extends ServiceProvider
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
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Default API rate limit: 60 requests per minute
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        // Strict API rate limit for sensitive operations: 20 requests per minute
        RateLimiter::for('api_strict', function (Request $request) {
            return Limit::perMinute(20)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Too many requests. Please try again later.',
                            'retry_after' => $headers['Retry-After'] ?? 60,
                        ],
                        429,
                        $headers
                    );
                });
        });

        // Login rate limit: 5 attempts per minute
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');
            $throttleKey = strtolower($email) . '|' . $request->ip();

            return [
                // 5 attempts per minute per email+IP combination
                Limit::perMinute(5)
                    ->by($throttleKey)
                    ->response(function (Request $request, array $headers) {
                        return response()->json(
                            [
                                'success' => false,
                                'message' => 'Too many login attempts. Please try again in ' .
                                    ($headers['Retry-After'] ?? 60) . ' seconds.',
                                'retry_after' => $headers['Retry-After'] ?? 60,
                            ],
                            429,
                            $headers
                        );
                    }),
                // 20 attempts per hour per IP (prevents distributed attacks)
                Limit::perHour(20)
                    ->by($request->ip())
                    ->response(function (Request $request, array $headers) {
                        return response()->json(
                            [
                                'success' => false,
                                'message' => 'Account temporarily locked. Please try again later.',
                                'retry_after' => $headers['Retry-After'] ?? 3600,
                            ],
                            429,
                            $headers
                        );
                    }),
            ];
        });

        // File upload rate limit: 10 uploads per minute
        RateLimiter::for('upload', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Too many upload requests. Please wait before uploading more files.',
                            'retry_after' => $headers['Retry-After'] ?? 60,
                        ],
                        429,
                        $headers
                    );
                });
        });

        // Report generation rate limit: 5 reports per minute (resource intensive)
        RateLimiter::for('report_generation', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Report generation limit reached. Please wait before generating more reports.',
                            'retry_after' => $headers['Retry-After'] ?? 60,
                        ],
                        429,
                        $headers
                    );
                });
        });

        // Data export rate limit: 3 exports per minute
        RateLimiter::for('export', function (Request $request) {
            return Limit::perMinute(3)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Export limit reached. Please wait before exporting more data.',
                            'retry_after' => $headers['Retry-After'] ?? 60,
                        ],
                        429,
                        $headers
                    );
                });
        });

        // Bulk operations rate limit: 2 operations per minute
        RateLimiter::for('bulk_operations', function (Request $request) {
            return Limit::perMinute(2)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Bulk operation limit reached. Please wait before performing more bulk operations.',
                            'retry_after' => $headers['Retry-After'] ?? 60,
                        ],
                        429,
                        $headers
                    );
                });
        });

        // Dashboard/Read operations: Higher limit (120 per minute)
        RateLimiter::for('dashboard', function (Request $request) {
            return Limit::perMinute(120)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        // Email verification resend: Very strict (3 per hour)
        RateLimiter::for('email_verification', function (Request $request) {
            return Limit::perHour(3)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Too many verification emails sent. Please check your inbox or try again later.',
                            'retry_after' => $headers['Retry-After'] ?? 3600,
                        ],
                        429,
                        $headers
                    );
                });
        });

        // Global rate limit for unauthenticated users: 30 per minute
        RateLimiter::for('guest', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        // Admin operations: Higher limit (100 per minute)
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(100)->by(
                $request->user()?->id ?: $request->ip()
            );
        });
    }
}
