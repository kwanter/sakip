<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminService;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
        $this->middleware('can:access-admin-dashboard');
    }

    /**
     * Display admin dashboard with system statistics
     */
    public function index()
    {
        $stats = $this->adminService->getSystemStats();

        $recentLogs = AuditLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        // User growth data for last 30 days
        $userGrowth = \App\Models\User::selectRaw(
            'DATE(created_at) as date, COUNT(*) as count'
        )
            ->where('created_at', '>', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view(
            'admin.dashboard',
            compact('stats', 'recentLogs', 'userGrowth')
        );
    }
}
