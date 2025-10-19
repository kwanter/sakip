<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get the test user
$user = App\Models\User::where('email', 'test@sakip.com')->first();

if (!$user) {
    echo "Test user not found!\n";
    exit(1);
}

echo "Testing SAKIP endpoints for user: " . $user->name . "\n";
echo "User roles: " . $user->roles->pluck('name')->implode(', ') . "\n";
echo "Is Admin: " . ($user->isAdmin() ? 'Yes' : 'No') . "\n\n";

// Test authorization for each endpoint
$endpoints = [
    'indicators' => 'App\Models\Sakip\PerformanceIndicator',
    'performance-data' => 'App\Models\Sakip\PerformanceData', 
    'assessments' => 'App\Models\Sakip\Assessment',
    'reports' => 'App\Models\Sakip\Report',
    'audit' => 'App\Models\AuditLog'
];

foreach ($endpoints as $endpoint => $model) {
    try {
        $canViewAny = $user->can('viewAny', $model);
        echo "Endpoint: /sakip/$endpoint - Can viewAny: " . ($canViewAny ? 'Yes' : 'No') . "\n";
    } catch (Exception $e) {
        echo "Endpoint: /sakip/$endpoint - Error: " . $e->getMessage() . "\n";
    }
}
