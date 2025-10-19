<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a simple test request to check if the application is working
$testRequest = Request::create('/', 'GET');
$testResponse = $kernel->handle($testRequest);

echo "Application Status: " . $testResponse->getStatusCode() . "\n";

// Test SAKIP endpoints directly without authentication for now
$endpoints = [
    '/sakip/indicators',
    '/sakip/performance-data', 
    '/sakip/assessments',
    '/sakip/reports',
    '/sakip/audit'
];

foreach ($endpoints as $endpoint) {
    $request = Request::create($endpoint, 'GET');
    $response = $kernel->handle($request);
    echo "Endpoint: $endpoint - Status: " . $response->getStatusCode() . "\n";
    
    // Check for database errors in response content
    $content = $response->getContent();
    if (strpos($content, 'SQLSTATE') !== false || strpos($content, 'Column not found') !== false) {
        echo "  ⚠️  Database error detected in response\n";
    } elseif ($response->getStatusCode() == 200) {
        echo "  ✅ Endpoint working correctly\n";
    } elseif ($response->getStatusCode() == 302) {
        echo "  🔄 Redirected (likely to login)\n";
    }
}

$kernel->terminate($testRequest, $testResponse);
