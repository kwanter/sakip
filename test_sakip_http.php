<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Start session
session_start();

echo "Testing SAKIP HTTP endpoints...\n\n";

// Step 1: Get CSRF token
$request = Illuminate\Http\Request::create('/login', 'GET');
$response = $kernel->handle($request);
$content = $response->getContent();

// Extract CSRF token
preg_match('/_token[^>]*value="([^"]*)"/', $content, $matches);
$token = $matches[1] ?? null;

if (!$token) {
    echo "Failed to get CSRF token\n";
    exit(1);
}

echo "CSRF Token: $token\n";

// Step 2: Login
$request = Illuminate\Http\Request::create('/login', 'POST', [
    '_token' => $token,
    'email' => 'test@sakip.com',
    'password' => 'password'
]);

// Set session for the request
$request->setLaravelSession($app['session.store']);

$response = $kernel->handle($request);
echo "Login Status: " . $response->getStatusCode() . "\n";

if ($response->getStatusCode() == 302) {
    echo "Login successful, redirect to: " . $response->headers->get('Location') . "\n\n";
    
    // Step 3: Test SAKIP endpoints with authenticated session
    $endpoints = [
        '/sakip/indicators',
        '/sakip/performance-data',
        '/sakip/assessments', 
        '/sakip/reports',
        '/sakip/audit'
    ];
    
    foreach ($endpoints as $endpoint) {
        $request = Illuminate\Http\Request::create($endpoint, 'GET');
        $request->setLaravelSession($app['session.store']);
        
        // Authenticate the user for this request
        $user = App\Models\User::where('email', 'test@sakip.com')->first();
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
        
        $response = $kernel->handle($request);
        echo "Endpoint: $endpoint - Status: " . $response->getStatusCode();
        
        if ($response->getStatusCode() == 302) {
            echo " - Redirect to: " . $response->headers->get('Location');
        }
        echo "\n";
    }
} else {
    echo "Login failed\n";
}

$kernel->terminate($request, $response);
