<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

// Health check endpoint
Route::get('/health', [HealthController::class, 'health'])->name('api.health');

// CSP violation reports (browser POST; no auth; rate-limited)
// SECURITY: truncate the body before logging — this endpoint is
// unauthenticated, so unbounded writes would allow log flooding/injection.
Route::post('/csp-reports', function (\Illuminate\Http\Request $request) {
    $body = json_encode($request->all());
    \Illuminate\Support\Facades\Log::channel('daily')->info('CSP report', [
        'ip' => $request->ip(),
        'body' => mb_substr($body === false ? '' : $body, 0, 2000),
    ]);

    return response()->noContent();
})->middleware('throttle:30,1')->name('api.csp-reports');

// Additional non-SAKIP API routes can go here
