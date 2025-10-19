<?php

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

echo "Testing SAKIP HTTP endpoints with detailed debugging...\n\n";

// Create HTTP client with cookie jar
$cookieJar = new CookieJar();
$client = new Client([
    'base_uri' => 'http://localhost:8000',
    'cookies' => $cookieJar,
    'allow_redirects' => false,
    'timeout' => 30,
]);

try {
    // Step 1: Get login page and CSRF token
    echo "Step 1: Getting login page...\n";
    $response = $client->get('/login');
    $loginPage = $response->getBody()->getContents();
    
    // Extract CSRF token
    preg_match('/<meta name="csrf-token" content="([^"]+)"/', $loginPage, $matches);
    $csrfToken = $matches[1] ?? null;
    
    if (!$csrfToken) {
        preg_match('/<input[^>]*name="_token"[^>]*value="([^"]+)"/', $loginPage, $matches);
        $csrfToken = $matches[1] ?? null;
    }
    
    echo "CSRF Token: " . ($csrfToken ?: 'NOT FOUND') . "\n";
    echo "Login page status: " . $response->getStatusCode() . "\n";
    echo "Cookies after login page: " . count($cookieJar) . "\n\n";
    
    if (!$csrfToken) {
        throw new Exception('Could not extract CSRF token');
    }
    
    // Step 2: Attempt login
    echo "Step 2: Attempting login...\n";
    $loginResponse = $client->post('/login', [
        'form_params' => [
            '_token' => $csrfToken,
            'email' => 'test@sakip.com',
            'password' => 'test123',
        ],
        'headers' => [
            'X-CSRF-TOKEN' => $csrfToken,
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ]
    ]);
    
    echo "Login response status: " . $loginResponse->getStatusCode() . "\n";
    echo "Login response headers:\n";
    foreach ($loginResponse->getHeaders() as $name => $values) {
        echo "  $name: " . implode(', ', $values) . "\n";
    }
    echo "Cookies after login: " . count($cookieJar) . "\n";
    
    // Print all cookies
    echo "Cookie details:\n";
    foreach ($cookieJar as $cookie) {
        echo "  " . $cookie->getName() . " = " . $cookie->getValue() . "\n";
    }
    echo "\n";
    
    // Step 3: Test dashboard access
    echo "Step 3: Testing dashboard access...\n";
    $dashboardResponse = $client->get('/sakip');
    echo "Dashboard status: " . $dashboardResponse->getStatusCode() . "\n";
    
    if ($dashboardResponse->hasHeader('Location')) {
        echo "Dashboard redirect to: " . $dashboardResponse->getHeader('Location')[0] . "\n";
    }
    
    // Step 4: Test specific endpoints
    $endpoints = [
        '/sakip/indicators',
        '/sakip/performance-data',
        '/sakip/assessments',
        '/sakip/reports',
        '/sakip/audit'
    ];
    
    echo "\nStep 4: Testing SAKIP endpoints...\n";
    foreach ($endpoints as $endpoint) {
        try {
            $response = $client->get($endpoint);
            $status = $response->getStatusCode();
            $redirect = $response->hasHeader('Location') ? $response->getHeader('Location')[0] : 'None';
            
            echo "Endpoint: $endpoint - Status: $status - Redirect: $redirect\n";
            
            // If we get a 200, check if it's actually the login page
            if ($status === 200) {
                $content = $response->getBody()->getContents();
                if (strpos($content, 'login') !== false || strpos($content, 'Login') !== false) {
                    echo "  WARNING: Got 200 but content appears to be login page\n";
                }
            }
        } catch (Exception $e) {
            echo "Endpoint: $endpoint - ERROR: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}