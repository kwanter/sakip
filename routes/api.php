<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HealthController;

// Health check endpoint
Route::get('/health', [HealthController::class, 'health'])->name('api.health');

// Include SAKIP API routes
require __DIR__.'/api_sakip.php';

// Additional non-SAKIP API routes can go here