<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

// Health check endpoint
Route::get('/health', [HealthController::class, 'health'])->name('api.health');

// CSP violation reports (browser POST; no auth; rate-limited)
Route::post('/csp-reports', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Log::channel('daily')->info('CSP report', [
        'ip' => $request->ip(),
        'body' => $request->all(),
    ]);

    return response()->noContent();
})->middleware('throttle:30,1')->name('api.csp-reports');

// Include SAKIP API routes
require __DIR__.'/api_sakip.php';

// Additional non-SAKIP API routes can go here