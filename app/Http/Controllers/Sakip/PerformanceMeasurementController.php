<?php

namespace App\Http\Controllers\Sakip;

use App\Http\Controllers\Controller;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Services\PerformanceCalculationService;
use App\Services\BenchmarkingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

/**
 * Performance Measurement Controller
 *
 * Handles performance measurement, scoring, and benchmark comparison
 * for the SAKIP module with comprehensive calculation and analysis capabilities.
 */
class PerformanceMeasurementController extends Controller
{
    protected PerformanceCalculationService $calculationService;
    protected BenchmarkingService $benchmarkingService;

    /**
     * Constructor with dependency injection
     */
    public function __construct(
        PerformanceCalculationService $calculationService,
        BenchmarkingService $benchmarkingService,
    ) {
        $this->calculationService = $calculationService;
        $this->benchmarkingService = $benchmarkingService;
    }

    /**
     * Display performance measurement dashboard
     */
    public function index(Request $request)
    {
        $this->authorize("viewAny", PerformanceData::class);

        try {
            $user = Auth::user();
            $instansiId = $user->instansi_id;
            $currentYear = Carbon::now()->year;

            // Get filters
            $year = $request->get("year", $currentYear);
            $quarter = $request->get("quarter");
            $category = $request->get("category");
            $comparisonType = $request->get("comparison", "target");

            // Get performance data with calculations
            $query = PerformanceIndicator::where(
                "instansi_id",
                $instansiId,
            )->with([
                "performanceData" => function ($q) use ($year, $quarter) {
                    $q->whereYear("period", $year);
                    if ($quarter) {
                        $q->whereRaw("QUARTER(period) = ?", [$quarter]);
                    }
                    $q->orderBy("period", "desc");
                },
                "targets" => function ($q) use ($year) {
                    $q->where("year", $year);
                },
            ]);

            if ($category) {
                $query->where("category", $category);
            }

            $indicators = $query->orderBy("name")->paginate(15);

            // Calculate overall performance metrics
            $metrics = $this->calculateOverallMetrics(
                $instansiId,
                $year,
                $quarter,
                $category,
            );

            // Get performance trends
            $trends = $this->getPerformanceTrends(
                $instansiId,
                $year,
                $category,
            );

            // Get benchmark comparisons
            $benchmarks = $this->getBenchmarkComparisons(
                $instansiId,
                $year,
                $category,
            );

            return view(
                "sakip.performance-measurement.index",
                compact(
                    "indicators",
                    "metrics",
                    "trends",
                    "benchmarks",
                    "year",
                    "quarter",
                    "category",
                    "comparisonType",
                ),
            );
        } catch (\Exception $e) {
            \Log::error(
                "Performance measurement index error: " . $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat halaman pengukuran kinerja.",
            );
        }
    }

    /**
     * Show detailed performance measurement for a specific indicator
     */
    public function show(PerformanceIndicator $indicator)
    {
        $this->authorize("view", $indicator);

        try {
            $user = Auth::user();
            $currentYear = Carbon::now()->year;

            // Get performance data with calculations
            $performanceData = PerformanceData::where(
                "performance_indicator_id",
                $indicator->id,
            )
                ->whereYear("period", $currentYear)
                ->orderBy("period")
                ->get();

            // Get targets
            $targets = $indicator
                ->targets()
                ->where("year", $currentYear)
                ->orderBy("period")
                ->get();

            // Calculate performance metrics
            $metrics = $this->calculateIndicatorMetrics(
                $indicator,
                $currentYear,
            );

            // Get historical data for trend analysis
            $historicalData = $this->getHistoricalPerformance($indicator, 3); // Last 3 years

            // Get benchmark comparisons
            $benchmarks = $this->getIndicatorBenchmarks($indicator);

            // Calculate scoring
            $scoring = $this->calculatePerformanceScoring(
                $indicator,
                $currentYear,
            );

            return view(
                "sakip.performance-measurement.show",
                compact(
                    "indicator",
                    "performanceData",
                    "targets",
                    "metrics",
                    "historicalData",
                    "benchmarks",
                    "scoring",
                    "currentYear",
                ),
            );
        } catch (\Exception $e) {
            \Log::error(
                "Performance measurement show error: " . $e->getMessage(),
            );
            return back()->with(
                "error",
                "Terjadi kesalahan saat memuat detail pengukuran kinerja.",
            );
        }
    }

    /**
     * Calculate performance measurement
     */
    public function calculate(Request $request, PerformanceIndicator $indicator)
    {
        $this->authorize("calculate", $indicator);

        $validator = Validator::make($request->all(), [
            "year" => "required|integer|min:2020|max:" . Carbon::now()->year,
            "quarter" => "nullable|integer|between:1,4",
            "period" => "nullable|date",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal.",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        try {
            $year = $request->get("year");
            $quarter = $request->get("quarter");
            $period = $request->get("period");

            // Get performance data for calculation
            $query = PerformanceData::where(
                "indicator_id",
                $indicator->id,
            )->whereYear("period", $year);

            if ($quarter) {
                $query->whereRaw("QUARTER(period) = ?", [$quarter]);
            } elseif ($period) {
                $query->where("period", $period);
            }

            $performanceData = $query->get();

            // Calculate performance metrics
            $calculationResult = $this->calculationService->calculateIndicatorPerformance(
                $indicator,
                $performanceData,
                $year,
                $quarter,
                $period,
            );

            // Update performance data with calculated values
            foreach ($performanceData as $data) {
                $data->update([
                    "performance_percentage" =>
                        $calculationResult["individual_scores"][$data->id] ??
                        null,
                    "calculated_at" => Carbon::now(),
                    "updated_by" => Auth::id(),
                ]);
            }

            // Log the activity
            AuditLog::create([
                "user_id" => Auth::id(),
                "instansi_id" => Auth::user()->instansi_id,
                "action" => "CALCULATE",
                "module" => "SAKIP",
                "description" => "Menghitung kinerja untuk indikator: {$indicator->name} (Tahun: {$year})",
                "old_values" => null,
                "new_values" => $calculationResult,
            ]);

            return response()->json([
                "success" => true,
                "message" => "Perhitungan kinerja berhasil dilakukan.",
                "data" => $calculationResult,
            ]);
        } catch (\Exception $e) {
            \Log::error("Calculate performance error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" => "Terjadi kesalahan saat menghitung kinerja.",
                ],
                500,
            );
        }
    }

    /**
     * Compare performance with benchmarks
     */
    public function compareBenchmarks(Request $request)
    {
        $this->authorize("viewAny", PerformanceData::class);

        $validator = Validator::make($request->all(), [
            "indicator_id" => "required|exists:performance_indicators,id",
            "benchmark_type" =>
                "required|in:institution,regional,national,sector",
            "comparison_period" =>
                "required|in:monthly,quarterly,semester,annual",
            "year" => "required|integer|min:2020|max:" . Carbon::now()->year,
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal.",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        try {
            $indicator = PerformanceIndicator::findOrFail(
                $request->get("indicator_id"),
            );
            $benchmarkType = $request->get("benchmark_type");
            $comparisonPeriod = $request->get("comparison_period");
            $year = $request->get("year");

            // Get benchmark data
            $benchmarkData = $this->benchmarkingService->getBenchmarkComparison(
                $indicator,
                $benchmarkType,
                $comparisonPeriod,
                $year,
            );

            // Calculate comparison metrics
            $comparisonResult = $this->benchmarkingService->calculateComparisonMetrics(
                $indicator,
                $benchmarkData,
                $comparisonPeriod,
                $year,
            );

            return response()->json([
                "success" => true,
                "message" => "Perbandingan benchmark berhasil dilakukan.",
                "data" => $comparisonResult,
            ]);
        } catch (\Exception $e) {
            \Log::error("Benchmark comparison error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Terjadi kesalahan saat melakukan perbandingan benchmark.",
                ],
                500,
            );
        }
    }

    /**
     * Generate performance report
     */
    public function generateReport(Request $request)
    {
        $this->authorize("generateReport", PerformanceData::class);

        $validator = Validator::make($request->all(), [
            "report_type" =>
                "required|in:monthly,quarterly,semester,annual,custom",
            "year" => "required|integer|min:2020|max:" . Carbon::now()->year,
            "quarter" => "nullable|integer|between:1,4",
            "month" => "nullable|integer|between:1,12",
            "category" => "nullable|string",
            "format" => "required|in:pdf,excel",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal.",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        try {
            $user = Auth::user();
            $reportType = $request->get("report_type");
            $year = $request->get("year");
            $format = $request->get("format");

            // Get performance data based on report type
            $indicators = PerformanceIndicator::where(
                "instansi_id",
                $user->instansi_id,
            )->with([
                "performanceData" => function ($q) use ($request) {
                    $this->applyPeriodFilter($q, $request);
                },
                "targets" => function ($q) use ($year) {
                    $q->where("year", $year);
                },
            ]);

            if ($request->filled("category")) {
                $indicators->where("category", $request->get("category"));
            }

            $indicators = $indicators->get();

            // Generate report based on format
            if ($format === "pdf") {
                $reportFile = $this->reportService->generatePerformanceReportPDF(
                    $indicators,
                    $reportType,
                    $year,
                    $request->all(),
                );
            } else {
                $reportFile = $this->reportService->generatePerformanceReportExcel(
                    $indicators,
                    $reportType,
                    $year,
                    $request->all(),
                );
            }

            // Log the activity
            AuditLog::create([
                "user_id" => $user->id,
                "instansi_id" => $user->instansi_id,
                "action" => "GENERATE_REPORT",
                "module" => "SAKIP",
                "description" => "Menghasilkan laporan kinerja (Tipe: {$reportType}, Format: {$format}, Tahun: {$year})",
                "old_values" => null,
                "new_values" => [
                    "report_type" => $reportType,
                    "format" => $format,
                    "year" => $year,
                ],
            ]);

            return response()->json([
                "success" => true,
                "message" => "Laporan kinerja berhasil dibuat.",
                "data" => [
                    "file_path" => $reportFile,
                    "download_url" => route(
                        "sakip.performance-measurement.download-report",
                        ["file" => basename($reportFile)],
                    ),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error(
                "Generate performance report error: " . $e->getMessage(),
            );
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Terjadi kesalahan saat membuat laporan kinerja.",
                ],
                500,
            );
        }
    }

    /**
     * Get performance trends
     */
    public function getTrends(Request $request)
    {
        $this->authorize("viewAny", PerformanceData::class);

        $validator = Validator::make($request->all(), [
            "indicator_id" => "nullable|exists:performance_indicators,id",
            "category" => "nullable|string",
            "period" => "required|in:monthly,quarterly,semester,annual",
            "years" => "required|integer|between:1,5",
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    "success" => false,
                    "message" => "Validasi gagal.",
                    "errors" => $validator->errors(),
                ],
                422,
            );
        }

        try {
            $user = Auth::user();
            $indicatorId = $request->get("indicator_id");
            $category = $request->get("category");
            $period = $request->get("period");
            $years = $request->get("years");

            // Get trends data
            $trendsData = $this->calculationService->getPerformanceTrends(
                $user->instansi_id,
                $indicatorId,
                $category,
                $period,
                $years,
            );

            return response()->json([
                "success" => true,
                "message" => "Tren kinerja berhasil diambil.",
                "data" => $trendsData,
            ]);
        } catch (\Exception $e) {
            \Log::error("Get performance trends error: " . $e->getMessage());
            return response()->json(
                [
                    "success" => false,
                    "message" =>
                        "Terjadi kesalahan saat mengambil tren kinerja.",
                ],
                500,
            );
        }
    }

    /**
     * Calculate overall performance metrics
     */
    private function calculateOverallMetrics(
        $instansiId,
        $year,
        $quarter = null,
        $category = null,
    ) {
        $query = PerformanceData::whereHas("indicator", function ($q) use (
            $instansiId,
            $category,
        ) {
            $q->where("instansi_id", $instansiId);
            if ($category) {
                $q->where("category", $category);
            }
        })->whereYear("period", $year);

        if ($quarter) {
            $query->whereRaw("QUARTER(period) = ?", [$quarter]);
        }

        $performanceData = $query->get();

        if ($performanceData->isEmpty()) {
            return [
                "average_performance" => 0,
                "total_indicators" => 0,
                "achieved_indicators" => 0,
                "achievement_rate" => 0,
                "performance_categories" => [
                    "excellent" => 0,
                    "good" => 0,
                    "fair" => 0,
                    "poor" => 0,
                ],
            ];
        }

        $averagePerformance = $performanceData->avg("performance_percentage");
        $totalIndicators = $performanceData->count();
        $achievedIndicators = $performanceData
            ->where("performance_percentage", ">=", 100)
            ->count();
        $achievementRate =
            $totalIndicators > 0
                ? ($achievedIndicators / $totalIndicators) * 100
                : 0;

        // Categorize performance
        $categories = [
            "excellent" => 0,
            "good" => 0,
            "fair" => 0,
            "poor" => 0,
        ];

        foreach ($performanceData as $data) {
            if ($data->performance_percentage >= 120) {
                $categories["excellent"]++;
            } elseif ($data->performance_percentage >= 100) {
                $categories["good"]++;
            } elseif ($data->performance_percentage >= 80) {
                $categories["fair"]++;
            } else {
                $categories["poor"]++;
            }
        }

        return [
            "average_performance" => round($averagePerformance, 2),
            "total_indicators" => $totalIndicators,
            "achieved_indicators" => $achievedIndicators,
            "achievement_rate" => round($achievementRate, 2),
            "performance_categories" => $categories,
        ];
    }

    /**
     * Get performance trends
     */
    private function getPerformanceTrends($instansiId, $year, $category = null)
    {
        $trends = [];
        $months = [];

        // Get monthly trends for the year
        for ($month = 1; $month <= 12; $month++) {
            $query = PerformanceData::whereHas("indicator", function ($q) use (
                $instansiId,
                $category,
            ) {
                $q->where("instansi_id", $instansiId);
                if ($category) {
                    $q->where("category", $category);
                }
            })
                ->whereYear("period", $year)
                ->whereMonth("period", $month);

            $monthData = $query->get();

            $months[] = Carbon::create($year, $month, 1)->format("M");
            $trends[] = $monthData->isNotEmpty()
                ? round($monthData->avg("performance_percentage"), 2)
                : 0;
        }

        return [
            "labels" => $months,
            "data" => $trends,
        ];
    }

    /**
     * Get benchmark comparisons
     */
    private function getBenchmarkComparisons(
        $instansiId,
        $year,
        $category = null,
    ) {
        // Get institution performance
        $institutionQuery = PerformanceData::whereHas("indicator", function (
            $q,
        ) use ($instansiId, $category) {
            $q->where("instansi_id", $instansiId);
            if ($category) {
                $q->where("category", $category);
            }
        })->whereYear("period", $year);

        $institutionPerformance = round(
            $institutionQuery->avg("performance_percentage"),
            2,
        );

        // Get regional average (all institutions in the same region)
        $regionalQuery = PerformanceData::whereHas(
            "indicator.instansi",
            function ($q) use ($instansiId, $category) {
                $q->where("region_id", function ($subQuery) use ($instansiId) {
                    $subQuery
                        ->select("region_id")
                        ->from("instansis")
                        ->where("id", $instansiId);
                });
                if ($category) {
                    $q->where("category", $category);
                }
            },
        )->whereYear("period", $year);

        $regionalPerformance = round(
            $regionalQuery->avg("performance_percentage"),
            2,
        );

        // Get national average (all institutions)
        $nationalQuery = PerformanceData::whereHas("indicator", function (
            $q,
        ) use ($category) {
            if ($category) {
                $q->where("category", $category);
            }
        })->whereYear("period", $year);

        $nationalPerformance = round(
            $nationalQuery->avg("performance_percentage"),
            2,
        );

        return [
            "institution" => $institutionPerformance,
            "regional" => $regionalPerformance,
            "national" => $nationalPerformance,
        ];
    }

    /**
     * Calculate indicator-specific metrics
     */
    private function calculateIndicatorMetrics(
        PerformanceIndicator $indicator,
        $year,
    ) {
        $performanceData = PerformanceData::where(
            "performance_indicator_id",
            $indicator->id,
        )
            ->whereYear("period", $year)
            ->get();

        if ($performanceData->isEmpty()) {
            return [
                "average_performance" => 0,
                "min_performance" => 0,
                "max_performance" => 0,
                "achievement_rate" => 0,
                "consistency_score" => 0,
            ];
        }

        $averagePerformance = $performanceData->avg("performance_percentage");
        $minPerformance = $performanceData->min("performance_percentage");
        $maxPerformance = $performanceData->max("performance_percentage");
        $achievementRate =
            ($performanceData
                ->where("performance_percentage", ">=", 100)
                ->count() /
                $performanceData->count()) *
            100;

        // Calculate consistency score (standard deviation)
        $mean = $averagePerformance;
        $squaredDiffs = $performanceData->map(function ($data) use ($mean) {
            return pow($data->performance_percentage - $mean, 2);
        });
        $variance = $squaredDiffs->avg();
        $standardDeviation = sqrt($variance);
        $consistencyScore = max(0, 100 - $standardDeviation / 10); // Normalize to 0-100

        return [
            "average_performance" => round($averagePerformance, 2),
            "min_performance" => round($minPerformance, 2),
            "max_performance" => round($maxPerformance, 2),
            "achievement_rate" => round($achievementRate, 2),
            "consistency_score" => round($consistencyScore, 2),
        ];
    }

    /**
     * Get historical performance data
     */
    private function getHistoricalPerformance(
        PerformanceIndicator $indicator,
        $years,
    ) {
        $currentYear = Carbon::now()->year;
        $historicalData = [];

        for ($i = 0; $i < $years; $i++) {
            $year = $currentYear - $i;
            $yearData = PerformanceData::where(
                "performance_indicator_id",
                $indicator->id,
            )
                ->whereYear("period", $year)
                ->get();

            if ($yearData->isNotEmpty()) {
                $historicalData[] = [
                    "year" => $year,
                    "average_performance" => round(
                        $yearData->avg("performance_percentage"),
                        2,
                    ),
                    "data_points" => $yearData->count(),
                ];
            }
        }

        return $historicalData;
    }

    /**
     * Get indicator benchmarks
     */
    private function getIndicatorBenchmarks(PerformanceIndicator $indicator)
    {
        // Get sector benchmarks
        $sectorBenchmarks = $this->benchmarkingService->getSectorBenchmarks(
            $indicator,
        );

        // Get regional benchmarks
        $regionalBenchmarks = $this->benchmarkingService->getRegionalBenchmarks(
            $indicator,
        );

        // Get national benchmarks
        $nationalBenchmarks = $this->benchmarkingService->getNationalBenchmarks(
            $indicator,
        );

        return [
            "sector" => $sectorBenchmarks,
            "regional" => $regionalBenchmarks,
            "national" => $nationalBenchmarks,
        ];
    }

    /**
     * Calculate performance scoring
     */
    private function calculatePerformanceScoring(
        PerformanceIndicator $indicator,
        $year,
    ) {
        $performanceData = PerformanceData::where(
            "indicator_id",
            $indicator->id,
        )
            ->whereYear("period", $year)
            ->get();

        if ($performanceData->isEmpty()) {
            return [
                "overall_score" => 0,
                "achievement_score" => 0,
                "consistency_score" => 0,
                "improvement_score" => 0,
                "grade" => "E",
            ];
        }

        // Achievement score (0-40 points)
        $achievementRate =
            $performanceData
                ->where("performance_percentage", ">=", 100)
                ->count() / $performanceData->count();
        $achievementScore = $achievementRate * 40;

        // Consistency score (0-30 points)
        $standardDeviation = $this->calculateStandardDeviation(
            $performanceData->pluck("performance_percentage"),
        );
        $consistencyScore = max(0, 30 - $standardDeviation / 10);

        // Improvement score (0-30 points)
        $improvementScore = $this->calculateImprovementScore($performanceData);

        $overallScore =
            $achievementScore + $consistencyScore + $improvementScore;
        $grade = $this->getPerformanceGrade($overallScore);

        return [
            "overall_score" => round($overallScore, 2),
            "achievement_score" => round($achievementScore, 2),
            "consistency_score" => round($consistencyScore, 2),
            "improvement_score" => round($improvementScore, 2),
            "grade" => $grade,
        ];
    }

    /**
     * Calculate standard deviation
     */
    private function calculateStandardDeviation($values)
    {
        $mean = $values->avg();
        $squaredDiffs = $values->map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        });
        $variance = $squaredDiffs->avg();
        return sqrt($variance);
    }

    /**
     * Calculate improvement score
     */
    private function calculateImprovementScore($performanceData)
    {
        if ($performanceData->count() < 2) {
            return 0;
        }

        $sortedData = $performanceData->sortBy("period");
        $firstHalf = $sortedData->take(ceil($sortedData->count() / 2));
        $secondHalf = $sortedData->slice(ceil($sortedData->count() / 2));

        $firstHalfAvg = $firstHalf->avg("performance_percentage");
        $secondHalfAvg = $secondHalf->avg("performance_percentage");

        $improvement = $secondHalfAvg - $firstHalfAvg;
        return max(0, min(30, ($improvement / 10) * 30)); // Normalize to 0-30
    }

    /**
     * Get performance grade
     */
    private function getPerformanceGrade($score)
    {
        if ($score >= 90) {
            return "A";
        }
        if ($score >= 80) {
            return "B";
        }
        if ($score >= 70) {
            return "C";
        }
        if ($score >= 60) {
            return "D";
        }
        return "E";
    }

    /**
     * Apply period filter to query
     */
    private function applyPeriodFilter($query, Request $request)
    {
        $year = $request->get("year");
        $quarter = $request->get("quarter");
        $month = $request->get("month");

        $query->whereYear("period", $year);

        if ($quarter) {
            $query->whereRaw("QUARTER(period) = ?", [$quarter]);
        } elseif ($month) {
            $query->whereMonth("period", $month);
        }
    }
}
