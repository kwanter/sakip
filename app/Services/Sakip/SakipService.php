<?php

namespace App\Services\Sakip;

use App\Models\PerformanceIndicator;
use App\Models\PerformanceData;
use App\Models\Target;
use App\Models\Assessment;
use App\Models\Report;
use App\Models\Instansi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Pdf;

class SakipService
{
    /**
     * Get dashboard data for SAKIP module
     */
    public function getDashboardData(?int $instansiId = null, string $period = null): array
    {
        $period = $period ?? date('Y');
        
        $data = [
            'total_indicators' => $this->getTotalIndicators($instansiId),
            'active_targets' => $this->getActiveTargets($instansiId, $period),
            'submitted_data' => $this->getSubmittedData($instansiId, $period),
            'completed_assessments' => $this->getCompletedAssessments($instansiId, $period),
            'overall_achievement' => $this->getOverallAchievement($instansiId, $period),
            'compliance_rate' => $this->getComplianceRate($instansiId, $period),
            'recent_activities' => $this->getRecentActivities($instansiId, 10),
            'top_performers' => $this->getTopPerformers($instansiId, $period, 5),
            'underperforming_indicators' => $this->getUnderperformingIndicators($instansiId, $period, 5),
        ];

        return $data;
    }

    /**
     * Get performance summary data
     */
    public function getPerformanceSummary(?int $instansiId = null, string $period = null): array
    {
        $period = $period ?? date('Y');
        
        $query = PerformanceData::with(['performanceIndicator', 'instansi'])
            ->where('period', 'like', $period . '%')
            ->when($instansiId, function ($q) use ($instansiId) {
                return $q->where('instansi_id', $instansiId);
            });

        $performanceData = $query->get();

        $summary = [
            'total_indicators' => $performanceData->count(),
            'achieved_indicators' => 0,
            'partially_achieved' => 0,
            'not_achieved' => 0,
            'average_achievement' => 0,
            'category_breakdown' => [],
            'instansi_breakdown' => [],
        ];

        foreach ($performanceData as $data) {
            $achievement = $this->calculateAchievementPercentage($data);
            
            if ($achievement >= 100) {
                $summary['achieved_indicators']++;
            } elseif ($achievement >= 70) {
                $summary['partially_achieved']++;
            } else {
                $summary['not_achieved']++;
            }

            // Category breakdown
            $category = $data->performanceIndicator->category;
            if (!isset($summary['category_breakdown'][$category])) {
                $summary['category_breakdown'][$category] = [
                    'total' => 0,
                    'achieved' => 0,
                    'average_achievement' => 0,
                ];
            }
            $summary['category_breakdown'][$category]['total']++;
            $summary['category_breakdown'][$category]['achieved'] += $achievement;

            // Instansi breakdown
            $instansiName = $data->instansi->name;
            if (!isset($summary['instansi_breakdown'][$instansiName])) {
                $summary['instansi_breakdown'][$instansiName] = [
                    'total' => 0,
                    'achieved' => 0,
                    'average_achievement' => 0,
                ];
            }
            $summary['instansi_breakdown'][$instansiName]['total']++;
            $summary['instansi_breakdown'][$instansiName]['achieved'] += $achievement;
        }

        // Calculate averages
        if ($summary['total_indicators'] > 0) {
            $summary['average_achievement'] = round(
                ($summary['achieved_indicators'] * 100 + $summary['partially_achieved'] * 70) / $summary['total_indicators'],
                2
            );
        }

        // Calculate category averages
        foreach ($summary['category_breakdown'] as &$category) {
            if ($category['total'] > 0) {
                $category['average_achievement'] = round($category['achieved'] / $category['total'], 2);
            }
        }

        // Calculate instansi averages
        foreach ($summary['instansi_breakdown'] as &$instansi) {
            if ($instansi['total'] > 0) {
                $instansi['average_achievement'] = round($instansi['achieved'] / $instansi['total'], 2);
            }
        }

        return $summary;
    }

    /**
     * Get achievement trends over time
     */
    public function getAchievementTrends(?int $instansiId = null, ?int $indicatorId = null, int $periods = 12): array
    {
        $query = PerformanceData::with(['performanceIndicator', 'target'])
            ->when($instansiId, function ($q) use ($instansiId) {
                return $q->where('instansi_id', $instansiId);
            })
            ->when($indicatorId, function ($q) use ($indicatorId) {
                return $q->where('performance_indicator_id', $indicatorId);
            })
            ->where('status', 'validated')
            ->orderBy('period', 'desc')
            ->limit($periods);

        $data = $query->get();

        $trends = [];
        foreach ($data as $item) {
            $achievement = $this->calculateAchievementPercentage($item);
            $trends[] = [
                'period' => $item->period,
                'indicator_name' => $item->performanceIndicator->name,
                'actual_value' => $item->actual_value,
                'target_value' => $item->target?->target_value ?? 0,
                'achievement_percentage' => $achievement,
                'status' => $this->getPerformanceStatus($achievement),
            ];
        }

        return array_reverse($trends);
    }

    /**
     * Get compliance status
     */
    public function getComplianceStatus(?int $instansiId = null, string $period = null): array
    {
        $period = $period ?? date('Y');
        
        $query = PerformanceData::with(['performanceIndicator'])
            ->where('period', 'like', $period . '%')
            ->when($instansiId, function ($q) use ($instansiId) {
                return $q->where('instansi_id', $instansiId);
            });

        $totalIndicators = PerformanceIndicator::when($instansiId, function ($q) use ($instansiId) {
            return $q->where('instansi_id', $instansiId);
        })->count();

        $submittedData = $query->where('status', 'submitted')->count();
        $validatedData = $query->where('status', 'validated')->count();
        $mandatoryData = $query->whereHas('performanceIndicator', function ($q) {
            $q->where('is_mandatory', true);
        })->count();

        return [
            'total_indicators' => $totalIndicators,
            'submitted_data' => $submittedData,
            'validated_data' => $validatedData,
            'submission_rate' => $totalIndicators > 0 ? round(($submittedData / $totalIndicators) * 100, 2) : 0,
            'validation_rate' => $submittedData > 0 ? round(($validatedData / $submittedData) * 100, 2) : 0,
            'mandatory_compliance' => $this->getMandatoryCompliance($instansiId, $period),
        ];
    }

    /**
     * Get indicator comparison data
     */
    public function getIndicatorComparison(?int $instansiId = null, string $period = null, ?string $category = null): array
    {
        $period = $period ?? date('Y');
        
        $query = PerformanceIndicator::with(['performanceData' => function ($q) use ($period) {
            $q->where('period', 'like', $period . '%')
              ->where('status', 'validated');
        }])
        ->when($instansiId, function ($q) use ($instansiId) {
            return $q->where('instansi_id', $instansiId);
        })
        ->when($category, function ($q) use ($category) {
            return $q->where('category', $category);
        });

        $indicators = $query->get();

        $comparison = [];
        foreach ($indicators as $indicator) {
            if ($indicator->performanceData->isNotEmpty()) {
                $latestData = $indicator->performanceData->first();
                $achievement = $this->calculateAchievementPercentage($latestData);
                
                $comparison[] = [
                    'indicator_id' => $indicator->id,
                    'indicator_name' => $indicator->name,
                    'category' => $indicator->category,
                    'target_value' => $indicator->targets->where('year', $period)->first()?->target_value ?? 0,
                    'actual_value' => $latestData->actual_value,
                    'achievement_percentage' => $achievement,
                    'status' => $this->getPerformanceStatus($achievement),
                    'weight' => $indicator->weight,
                ];
            }
        }

        // Sort by achievement percentage
        usort($comparison, function ($a, $b) {
            return $b['achievement_percentage'] <=> $a['achievement_percentage'];
        });

        return $comparison;
    }

    /**
     * Export SAKIP data
     */
    public function exportData(?int $instansiId = null, string $type = 'performance_summary', string $format = 'excel', string $period = null)
    {
        $period = $period ?? date('Y');
        
        $data = match ($type) {
            'performance_summary' => $this->getPerformanceSummary($instansiId, $period),
            'achievement_trends' => $this->getAchievementTrends($instansiId, null, 12),
            'compliance_status' => $this->getComplianceStatus($instansiId, $period),
            'indicator_comparison' => $this->getIndicatorComparison($instansiId, $period),
            default => $this->getPerformanceSummary($instansiId, $period),
        };

        $filename = 'sakip_' . $type . '_' . $period . '_' . date('Y-m-d');

        return match ($format) {
            'excel' => $this->exportToExcel($data, $type, $filename),
            'pdf' => $this->exportToPdf($data, $type, $filename),
            'csv' => $this->exportToCsv($data, $type, $filename),
            default => $this->exportToExcel($data, $type, $filename),
        };
    }

    /**
     * Helper method to calculate achievement percentage
     */
    private function calculateAchievementPercentage($performanceData): float
    {
        $target = $performanceData->performanceIndicator->targets
            ->where('year', substr($performanceData->period, 0, 4))
            ->first();

        if (!$target || $target->target_value == 0) {
            return 0;
        }

        return round(($performanceData->actual_value / $target->target_value) * 100, 2);
    }

    /**
     * Helper method to get performance status
     */
    private function getPerformanceStatus(float $achievement): string
    {
        if ($achievement >= 100) {
            return 'excellent';
        } elseif ($achievement >= 80) {
            return 'good';
        } elseif ($achievement >= 60) {
            return 'satisfactory';
        } else {
            return 'needs_improvement';
        }
    }

    /**
     * Helper methods for dashboard data
     */
    private function getTotalIndicators(?int $instansiId = null): int
    {
        return PerformanceIndicator::when($instansiId, function ($q) use ($instansiId) {
            return $q->where('instansi_id', $instansiId);
        })->count();
    }

    private function getActiveTargets(?int $instansiId = null, string $period = null): int
    {
        return Target::when($instansiId, function ($q) use ($instansiId) {
            return $q->whereHas('performanceIndicator', function ($subQuery) use ($instansiId) {
                $subQuery->where('instansi_id', $instansiId);
            });
        })
        ->where('year', $period)
        ->where('status', 'approved')
        ->count();
    }

    private function getSubmittedData(?int $instansiId = null, string $period = null): int
    {
        return PerformanceData::when($instansiId, function ($q) use ($instansiId) {
            return $q->where('instansi_id', $instansiId);
        })
        ->where('period', 'like', $period . '%')
        ->where('status', 'submitted')
        ->count();
    }

    private function getCompletedAssessments(?int $instansiId = null, string $period = null): int
    {
        return Assessment::when($instansiId, function ($q) use ($instansiId) {
            return $q->whereHas('performanceData', function ($subQuery) use ($instansiId) {
                $subQuery->where('instansi_id', $instansiId);
            });
        })
        ->where('status', 'approved')
        ->whereHas('performanceData', function ($q) use ($period) {
            $q->where('period', 'like', $period . '%');
        })
        ->count();
    }

    private function getOverallAchievement(?int $instansiId = null, string $period = null): float
    {
        $performanceData = PerformanceData::with(['performanceIndicator.targets'])
            ->when($instansiId, function ($q) use ($instansiId) {
                return $q->where('instansi_id', $instansiId);
            })
            ->where('period', 'like', $period . '%')
            ->where('status', 'validated')
            ->get();

        if ($performanceData->isEmpty()) {
            return 0;
        }

        $totalAchievement = 0;
        foreach ($performanceData as $data) {
            $totalAchievement += $this->calculateAchievementPercentage($data);
        }

        return round($totalAchievement / $performanceData->count(), 2);
    }

    private function getComplianceRate(?int $instansiId = null, string $period = null): float
    {
        $totalIndicators = $this->getTotalIndicators($instansiId);
        $submittedData = $this->getSubmittedData($instansiId, $period);

        return $totalIndicators > 0 ? round(($submittedData / $totalIndicators) * 100, 2) : 0;
    }

    private function getMandatoryCompliance(?int $instansiId = null, string $period = null): array
    {
        $mandatoryIndicators = PerformanceIndicator::where('is_mandatory', true)
            ->when($instansiId, function ($q) use ($instansiId) {
                return $q->where('instansi_id', $instansiId);
            })
            ->count();

        $compliantData = PerformanceData::whereHas('performanceIndicator', function ($q) {
            $q->where('is_mandatory', true);
        })
        ->when($instansiId, function ($q) use ($instansiId) {
            return $q->where('instansi_id', $instansiId);
        })
        ->where('period', 'like', $period . '%')
        ->where('status', 'validated')
        ->count();

        return [
            'mandatory_indicators' => $mandatoryIndicators,
            'compliant_data' => $compliantData,
            'compliance_rate' => $mandatoryIndicators > 0 ? round(($compliantData / $mandatoryIndicators) * 100, 2) : 0,
        ];
    }

    private function getRecentActivities(?int $instansiId = null, int $limit = 10): array
    {
        // This would typically come from audit logs or activity logs
        // For now, return recent performance data submissions
        return PerformanceData::with(['performanceIndicator', 'submitter'])
            ->when($instansiId, function ($q) use ($instansiId) {
                return $q->where('instansi_id', $instansiId);
            })
            ->orderBy('submitted_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($data) {
                return [
                    'type' => 'data_submission',
                    'description' => 'Performance data submitted for ' . $data->performanceIndicator->name,
                    'user' => $data->submitter->name,
                    'timestamp' => $data->submitted_at,
                ];
            })
            ->toArray();
    }

    private function getTopPerformers(?int $instansiId = null, string $period = null, int $limit = 5): array
    {
        $performanceData = PerformanceData::with(['performanceIndicator'])
            ->when($instansiId, function ($q) use ($instansiId) {
                return $q->where('instansi_id', $instansiId);
            })
            ->where('period', 'like', $period . '%')
            ->where('status', 'validated')
            ->get();

        $achievements = [];
        foreach ($performanceData as $data) {
            $achievement = $this->calculateAchievementPercentage($data);
            $achievements[] = [
                'indicator_name' => $data->performanceIndicator->name,
                'achievement_percentage' => $achievement,
                'actual_value' => $data->actual_value,
                'target_value' => $data->target?->target_value ?? 0,
            ];
        }

        // Sort by achievement and take top performers
        usort($achievements, function ($a, $b) {
            return $b['achievement_percentage'] <=> $a['achievement_percentage'];
        });

        return array_slice($achievements, 0, $limit);
    }

    private function getUnderperformingIndicators(?int $instansiId = null, string $period = null, int $limit = 5): array
    {
        $performanceData = PerformanceData::with(['performanceIndicator'])
            ->when($instansiId, function ($q) use ($instansiId) {
                return $q->where('instansi_id', $instansiId);
            })
            ->where('period', 'like', $period . '%')
            ->where('status', 'validated')
            ->get();

        $achievements = [];
        foreach ($performanceData as $data) {
            $achievement = $this->calculateAchievementPercentage($data);
            if ($achievement < 60) { // Underperforming indicators
                $achievements[] = [
                    'indicator_name' => $data->performanceIndicator->name,
                    'achievement_percentage' => $achievement,
                    'actual_value' => $data->actual_value,
                    'target_value' => $data->target?->target_value ?? 0,
                ];
            }
        }

        // Sort by achievement (lowest first)
        usort($achievements, function ($a, $b) {
            return $a['achievement_percentage'] <=> $b['achievement_percentage'];
        });

        return array_slice($achievements, 0, $limit);
    }

    /**
     * Export methods
     */
    private function exportToExcel($data, string $type, string $filename)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers and data based on type
        $this->setExcelHeaders($sheet, $type);
        $this->setExcelData($sheet, $data, $type);

        $writer = new Xlsx($spreadsheet);
        $filePath = storage_path('app/temp/' . $filename . '.xlsx');
        $writer->save($filePath);

        return response()->download($filePath, $filename . '.xlsx')->deleteFileAfterSend();
    }

    private function exportToPdf($data, string $type, string $filename)
    {
        // Implementation for PDF export
        // This would typically use a PDF library like DomPDF or similar
        return response()->json(['message' => 'PDF export not implemented yet']);
    }

    private function exportToCsv($data, string $type, string $filename)
    {
        // Implementation for CSV export
        return response()->json(['message' => 'CSV export not implemented yet']);
    }

    private function setExcelHeaders($sheet, string $type): void
    {
        $headers = match ($type) {
            'performance_summary' => [
                'A1' => 'Indicator Name',
                'B1' => 'Category',
                'C1' => 'Target Value',
                'D1' => 'Actual Value',
                'E1' => 'Achievement %',
                'F1' => 'Status',
            ],
            'indicator_comparison' => [
                'A1' => 'Indicator Name',
                'B1' => 'Category',
                'C1' => 'Target Value',
                'D1' => 'Actual Value',
                'E1' => 'Achievement %',
                'F1' => 'Weight',
            ],
            default => [
                'A1' => 'Data',
                'B1' => 'Value',
            ]
        };

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
    }

    private function setExcelData($sheet, $data, string $type): void
    {
        $row = 2;
        
        if ($type === 'performance_summary' && isset($data['category_breakdown'])) {
            foreach ($data['category_breakdown'] as $category => $values) {
                $sheet->setCellValue('A' . $row, $category);
                $sheet->setCellValue('B' . $row, $category);
                $sheet->setCellValue('C' . $row, $values['total']);
                $sheet->setCellValue('D' . $row, $values['achieved']);
                $sheet->setCellValue('E' . $row, $values['average_achievement']);
                $sheet->setCellValue('F' . $row, $this->getPerformanceStatus($values['average_achievement']));
                $row++;
            }
        }
    }
}