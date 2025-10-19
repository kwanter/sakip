<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\Target;
use App\Models\Assessment;
use App\Models\Report;
use App\Services\Sakip\SakipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SakipController extends Controller
{
    protected SakipService $sakipService;

    public function __construct(SakipService $sakipService)
    {
        $this->sakipService = $sakipService;
    }

    /**
     * Display the SAKIP dashboard
     */
    public function index(Request $request)
    {
        $this->authorize('sakip.view.dashboard');

        $instansiId = Auth::user()->instansi_id;
        $period = $request->get('period', date('Y'));

        $dashboardData = $this->sakipService->getDashboardData($instansiId, $period);

        return Inertia::render('Sakip/Dashboard', [
            'dashboardData' => $dashboardData,
            'period' => $period,
        ]);
    }

    /**
     * Display performance indicators management
     */
    public function indicators(Request $request)
    {
        $this->authorize('sakip.view.performance-indicators');

        $query = PerformanceIndicator::with(['instansi', 'targets', 'performanceData'])
            ->when(Auth::user()->instansi_id, function ($q, $instansiId) {
                return $q->where('instansi_id', $instansiId);
            });

        $indicators = $query->paginate(20);

        return Inertia::render('Sakip/Indicators/Index', [
            'indicators' => $indicators,
        ]);
    }

    /**
     * Display performance data management
     */
    public function performanceData(Request $request)
    {
        $this->authorize('sakip.view.performance-data');

        $query = PerformanceData::with([
            'performanceIndicator',
            'instansi',
            'submitter',
            'validator',
            'evidenceDocuments',
            'assessment'
        ])
        ->when(Auth::user()->instansi_id, function ($q, $instansiId) {
            return $q->where('instansi_id', $instansiId);
        });

        $performanceData = $query->paginate(20);

        return Inertia::render('Sakip/PerformanceData/Index', [
            'performanceData' => $performanceData,
        ]);
    }

    /**
     * Display assessments management
     */
    public function assessments(Request $request)
    {
        $this->authorize('view assessments');

        $query = Assessment::with([
            'performanceData.performanceIndicator',
            'performanceData.instansi',
            'assessor',
            'criteria'
        ])
        ->when(Auth::user()->instansi_id, function ($q, $instansiId) {
            return $q->whereHas('performanceData', function ($subQuery) use ($instansiId) {
                $subQuery->where('instansi_id', $instansiId);
            });
        });

        $assessments = $query->paginate(20);

        return Inertia::render('Sakip/Assessments/Index', [
            'assessments' => $assessments,
        ]);
    }

    /**
     * Display reports management
     */
    public function reports(Request $request)
    {
        $this->authorize('view reports');

        $query = Report::with(['instansi', 'generator'])
            ->when(Auth::user()->instansi_id, function ($q, $instansiId) {
                return $q->where('instansi_id', $instansiId);
            });

        $reports = $query->paginate(20);

        return Inertia::render('Sakip/Reports/Index', [
            'reports' => $reports,
        ]);
    }

    /**
     * Get performance summary data
     */
    public function getPerformanceSummary(Request $request)
    {
        $this->authorize('view sakip dashboard');

        $instansiId = Auth::user()->instansi_id;
        $period = $request->get('period', date('Y'));

        $summary = $this->sakipService->getPerformanceSummary($instansiId, $period);

        return response()->json($summary);
    }

    /**
     * Get achievement trends
     */
    public function getAchievementTrends(Request $request)
    {
        $this->authorize('view sakip dashboard');

        $instansiId = Auth::user()->instansi_id;
        $indicatorId = $request->get('indicator_id');
        $periods = $request->get('periods', 12);

        $trends = $this->sakipService->getAchievementTrends($instansiId, $indicatorId, $periods);

        return response()->json($trends);
    }

    /**
     * Get compliance status
     */
    public function getComplianceStatus(Request $request)
    {
        $this->authorize('view sakip dashboard');

        $instansiId = Auth::user()->instansi_id;
        $period = $request->get('period', date('Y'));

        $compliance = $this->sakipService->getComplianceStatus($instansiId, $period);

        return response()->json($compliance);
    }

    /**
     * Export SAKIP data
     */
    public function exportData(Request $request)
    {
        $this->authorize('export sakip data');

        $type = $request->get('type', 'performance_summary');
        $format = $request->get('format', 'excel');
        $period = $request->get('period', date('Y'));

        $instansiId = Auth::user()->instansi_id;

        return $this->sakipService->exportData($instansiId, $type, $format, $period);
    }

    /**
     * Get indicator comparison data
     */
    public function getIndicatorComparison(Request $request)
    {
        $this->authorize('view sakip dashboard');

        $instansiId = Auth::user()->instansi_id;
        $period = $request->get('period', date('Y'));
        $category = $request->get('category');

        $comparison = $this->sakipService->getIndicatorComparison($instansiId, $period, $category);

        return response()->json($comparison);
    }
}