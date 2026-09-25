<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:access-admin-dashboard');
    }

    /**
     * Display the admin triage dashboard: verification queue, pipeline health,
     * and recent activity. Counts feed both the stat cards and the layout's
     * sidebar pending-data badge (pendingDataCount).
     */
    public function index()
    {
        $pendingDataCount = PerformanceData::where('status', 'submitted')->count();
        $currentPeriod = now()->format('Y-m');
        $validatedCount = PerformanceData::where('status', 'validated')
            ->where('period', $currentPeriod)
            ->count();
        $indicatorCount = PerformanceIndicator::count();
        $currentPeriodLabel = now()->locale('id')->translatedFormat('M Y');

        $recentLogins = AuditLog::where('action', 'login')
            ->where('created_at', '>', now()->subDays(7))
            ->count();

        $recentLogs = AuditLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'pendingDataCount',
            'validatedCount',
            'indicatorCount',
            'recentLogins',
            'recentLogs',
            'currentPeriodLabel',
        ));
    }
}
