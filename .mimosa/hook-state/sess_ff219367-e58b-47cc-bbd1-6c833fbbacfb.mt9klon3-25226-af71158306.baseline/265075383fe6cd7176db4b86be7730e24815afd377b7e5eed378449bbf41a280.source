<?php

namespace App\Services;

use App\Models\Benchmark;
use App\Models\PerformanceIndicator;
use App\Models\Institution;
use App\Models\PerformanceMeasurement;
use Illuminate\Support\Facades\Log;

/**
 * Benchmarking Service
 * 
 * Handles benchmark management, comparison calculations, and performance
 * benchmarking against sector, regional, and national standards.
 */
class BenchmarkingService
{
    /**
     * Get benchmark comparison for an indicator
     */
    public function getBenchmarkComparison($indicatorId, $achievement)
    {
        try {
            $indicator = PerformanceIndicator::find($indicatorId);
            
            if (!$indicator) {
                throw new \Exception('Performance indicator not found');
            }

            $benchmarks = Benchmark::where('indicator_id', $indicatorId)
                ->where('is_active', true)
                ->get();

            if ($benchmarks->isEmpty()) {
                return null;
            }

            $comparisons = [];

            foreach ($benchmarks as $benchmark) {
                $comparison = $this->calculateBenchmarkComparison($achievement, $benchmark);
                $comparisons[] = $comparison;
            }

            return $comparisons;

        } catch (\Exception $e) {
            Log::error('Failed to get benchmark comparison: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate benchmark comparison metrics
     */
    public function calculateComparisonMetrics($indicatorId, $achievement, $benchmarkType = 'all')
    {
        try {
            $indicator = PerformanceIndicator::find($indicatorId);
            
            if (!$indicator) {
                throw new \Exception('Performance indicator not found');
            }

            $metrics = [];

            if ($benchmarkType === 'all' || $benchmarkType === 'sector') {
                $sectorMetrics = $this->getSectorComparisonMetrics($indicator, $achievement);
                if ($sectorMetrics) {
                    $metrics['sector'] = $sectorMetrics;
                }
            }

            if ($benchmarkType === 'all' || $benchmarkType === 'regional') {
                $regionalMetrics = $this->getRegionalComparisonMetrics($indicator, $achievement);
                if ($regionalMetrics) {
                    $metrics['regional'] = $regionalMetrics;
                }
            }

            if ($benchmarkType === 'all' || $benchmarkType === 'national') {
                $nationalMetrics = $this->getNationalComparisonMetrics($indicator, $achievement);
                if ($nationalMetrics) {
                    $metrics['national'] = $nationalMetrics;
                }
            }

            return $metrics;

        } catch (\Exception $e) {
            Log::error('Failed to calculate comparison metrics: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get sector benchmarks
     */
    public function getSectorBenchmarks($indicator)
    {
        try {
            return Benchmark::where('indicator_id', $indicator->id)
                ->where('benchmark_type', 'sector')
                ->where('is_active', true)
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to get sector benchmarks: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get regional benchmarks
     */
    public function getRegionalBenchmarks($indicator)
    {
        try {
            return Benchmark::where('indicator_id', $indicator->id)
                ->where('benchmark_type', 'regional')
                ->where('is_active', true)
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to get regional benchmarks: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get national benchmarks
     */
    public function getNationalBenchmarks($indicator)
    {
        try {
            return Benchmark::where('indicator_id', $indicator->id)
                ->where('benchmark_type', 'national')
                ->where('is_active', true)
                ->get();
        } catch (\Exception $e) {
            Log::error('Failed to get national benchmarks: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Calculate benchmark comparison
     */
    private function calculateBenchmarkComparison($achievement, $benchmark)
    {
        $benchmarkValue = $benchmark->benchmark_value;
        $difference = $achievement - $benchmarkValue;
        $percentageDifference = $benchmarkValue > 0 ? ($difference / $benchmarkValue) * 100 : 0;

        return [
            'benchmark_id' => $benchmark->id,
            'benchmark_name' => $benchmark->name,
            'benchmark_type' => $benchmark->benchmark_type,
            'benchmark_value' => $benchmarkValue,
            'actual_value' => $achievement,
            'difference' => $difference,
            'percentage_difference' => round($percentageDifference, 2),
            'performance_level' => $this->getPerformanceLevel($percentageDifference),
            'description' => $benchmark->description,
        ];
    }

    /**
     * Get performance level based on benchmark difference
     */
    private function getPerformanceLevel($percentageDifference)
    {
        if ($percentageDifference >= 10) {
            return 'excellent';
        } elseif ($percentageDifference >= 0) {
            return 'good';
        } elseif ($percentageDifference >= -10) {
            return 'average';
        } else {
            return 'poor';
        }
    }

    /**
     * Get sector comparison metrics
     */
    private function getSectorComparisonMetrics($indicator, $achievement)
    {
        try {
            // Get sector benchmarks for this indicator type
            $sectorBenchmarks = $this->getSectorBenchmarks($indicator);
            
            if ($sectorBenchmarks->isEmpty()) {
                return null;
            }

            $institution = Institution::find($indicator->instansi_id);
            
            if (!$institution) {
                return null;
            }

            // Calculate sector average
            $sectorAverage = $this->calculateSectorAverage($indicator, $institution->sector);
            
            return [
                'benchmark_type' => 'sector',
                'benchmark_name' => 'Rata-rata Sektor ' . $institution->sector,
                'benchmark_value' => $sectorAverage,
                'actual_value' => $achievement,
                'difference' => $achievement - $sectorAverage,
                'percentage_difference' => $sectorAverage > 0 ? round((($achievement - $sectorAverage) / $sectorAverage) * 100, 2) : 0,
                'performance_level' => $this->getPerformanceLevel($achievement - $sectorAverage),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get sector comparison metrics: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get regional comparison metrics
     */
    private function getRegionalComparisonMetrics($indicator, $achievement)
    {
        try {
            // Get regional benchmarks for this indicator
            $regionalBenchmarks = $this->getRegionalBenchmarks($indicator);
            
            if ($regionalBenchmarks->isEmpty()) {
                return null;
            }

            $institution = Institution::find($indicator->instansi_id);
            
            if (!$institution) {
                return null;
            }

            // Calculate regional average
            $regionalAverage = $this->calculateRegionalAverage($indicator, $institution->region);
            
            return [
                'benchmark_type' => 'regional',
                'benchmark_name' => 'Rata-rata Regional ' . $institution->region,
                'benchmark_value' => $regionalAverage,
                'actual_value' => $achievement,
                'difference' => $achievement - $regionalAverage,
                'percentage_difference' => $regionalAverage > 0 ? round((($achievement - $regionalAverage) / $regionalAverage) * 100, 2) : 0,
                'performance_level' => $this->getPerformanceLevel($achievement - $regionalAverage),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get regional comparison metrics: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get national comparison metrics
     */
    private function getNationalComparisonMetrics($indicator, $achievement)
    {
        try {
            // Get national benchmarks for this indicator
            $nationalBenchmarks = $this->getNationalBenchmarks($indicator);
            
            if ($nationalBenchmarks->isEmpty()) {
                return null;
            }

            // Calculate national average
            $nationalAverage = $this->calculateNationalAverage($indicator);
            
            return [
                'benchmark_type' => 'national',
                'benchmark_name' => 'Rata-rata Nasional',
                'benchmark_value' => $nationalAverage,
                'actual_value' => $achievement,
                'difference' => $achievement - $nationalAverage,
                'percentage_difference' => $nationalAverage > 0 ? round((($achievement - $nationalAverage) / $nationalAverage) * 100, 2) : 0,
                'performance_level' => $this->getPerformanceLevel($achievement - $nationalAverage),
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get national comparison metrics: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate sector average
     */
    private function calculateSectorAverage($indicator, $sector)
    {
        try {
            // Get all institutions in the same sector
            $sectorInstitutions = Institution::where('sector', $sector)->pluck('id');
            
            // Get performance measurements for these institutions
            $measurements = PerformanceMeasurement::where('indicator_id', $indicator->id)
                ->whereIn('instansi_id', $sectorInstitutions)
                ->where('period', $indicator->period)
                ->where('year', $indicator->year)
                ->get();

            if ($measurements->isEmpty()) {
                return 0;
            }

            return round($measurements->avg('achievement'), 2);

        } catch (\Exception $e) {
            Log::error('Failed to calculate sector average: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate regional average
     */
    private function calculateRegionalAverage($indicator, $region)
    {
        try {
            // Get all institutions in the same region
            $regionalInstitutions = Institution::where('region', $region)->pluck('id');
            
            // Get performance measurements for these institutions
            $measurements = PerformanceMeasurement::where('indicator_id', $indicator->id)
                ->whereIn('instansi_id', $regionalInstitutions)
                ->where('period', $indicator->period)
                ->where('year', $indicator->year)
                ->get();

            if ($measurements->isEmpty()) {
                return 0;
            }

            return round($measurements->avg('achievement'), 2);

        } catch (\Exception $e) {
            Log::error('Failed to calculate regional average: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calculate national average
     */
    private function calculateNationalAverage($indicator)
    {
        try {
            // Get all performance measurements for this indicator
            $measurements = PerformanceMeasurement::where('indicator_id', $indicator->id)
                ->where('period', $indicator->period)
                ->where('year', $indicator->year)
                ->get();

            if ($measurements->isEmpty()) {
                return 0;
            }

            return round($measurements->avg('achievement'), 2);

        } catch (\Exception $e) {
            Log::error('Failed to calculate national average: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Create benchmark
     */
    public function createBenchmark($data)
    {
        try {
            return Benchmark::create([
                'indicator_id' => $data['indicator_id'],
                'name' => $data['name'],
                'benchmark_type' => $data['benchmark_type'],
                'benchmark_value' => $data['benchmark_value'],
                'description' => $data['description'] ?? null,
                'source' => $data['source'] ?? null,
                'year' => $data['year'] ?? now()->year,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create benchmark: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update benchmark
     */
    public function updateBenchmark($benchmarkId, $data)
    {
        try {
            $benchmark = Benchmark::find($benchmarkId);
            
            if (!$benchmark) {
                return null;
            }

            $benchmark->update([
                'name' => $data['name'] ?? $benchmark->name,
                'benchmark_value' => $data['benchmark_value'] ?? $benchmark->benchmark_value,
                'description' => $data['description'] ?? $benchmark->description,
                'source' => $data['source'] ?? $benchmark->source,
                'year' => $data['year'] ?? $benchmark->year,
                'is_active' => $data['is_active'] ?? $benchmark->is_active,
                'updated_by' => auth()->id(),
            ]);

            return $benchmark;
        } catch (\Exception $e) {
            Log::error('Failed to update benchmark: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete benchmark
     */
    public function deleteBenchmark($benchmarkId)
    {
        try {
            $benchmark = Benchmark::find($benchmarkId);
            
            if (!$benchmark) {
                return false;
            }

            return $benchmark->delete();
        } catch (\Exception $e) {
            Log::error('Failed to delete benchmark: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get benchmark statistics
     */
    public function getBenchmarkStatistics($indicatorId = null)
    {
        try {
            $query = Benchmark::where('is_active', true);

            if ($indicatorId) {
                $query->where('indicator_id', $indicatorId);
            }

            $totalBenchmarks = $query->count();
            
            $byType = $query->select('benchmark_type', \DB::raw('count(*) as count'))
                ->groupBy('benchmark_type')
                ->pluck('count', 'benchmark_type')
                ->toArray();

            return [
                'total_benchmarks' => $totalBenchmarks,
                'by_type' => $byType,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get benchmark statistics: ' . $e->getMessage());
            return [];
        }
    }
}