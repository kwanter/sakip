<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AdminTriageService;
use App\Support\ReportingPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class AdminDashboardController extends Controller
{
    public function __construct(private readonly AdminTriageService $triageService) {}

    /**
     * The HQ triage landing: three queue figures for the selected period and Cakupan Instansi.
     *
     * Authorization is owned by the route group (`can:admin.dashboard`, routes/web.php:201): this
     * controller must not add a second gate (REQ-013, finding C1).
     */
    public function index(Request $request): View
    {
        /** @var User $viewer */
        $viewer = $request->user();

        $rawPeriod = $request->query('period'); // untrusted: validated by the whitelist below (SEC-003)
        $period = ReportingPeriod::fromKey(is_string($rawPeriod) ? $rawPeriod : null);

        $summary = $this->triageService->summaryFor($viewer, $period);

        $recentLogs = AuditLog::with('user')
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('summary', 'recentLogs'));
    }
}
