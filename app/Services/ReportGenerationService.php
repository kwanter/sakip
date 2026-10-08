<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Instansi;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Report;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

class ReportGenerationService
{
    /**
     * Output path for a generated report file. Deterministic per report so
     * regeneration overwrites instead of leaking orphan files.
     */
    public function reportFilePath(Report $report, string $extension): string
    {
        return 'reports/'.$report->instansi_id.'/'.$report->id.'.'.$extension;
    }

    /**
     * Render the report as a PDF via Dompdf and store it on the default disk.
     *
     * @return string Stored file path
     */
    public function generatePDFFile(Report $report, array $reportData): string
    {
        $html = view('sakip.reports.export.pdf', [
            'report' => $report,
            'data' => $reportData,
        ])->render();

        $dompdf = new Dompdf(['isRemoteEnabled' => false]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $path = $this->reportFilePath($report, 'pdf');
        Storage::put($path, $dompdf->output());

        return $path;
    }

    /**
     * Render the report as an XLSX workbook and store it on the default disk.
     *
     * @return string Stored file path
     */
    public function generateExcelFile(Report $report, array $reportData): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan');

        $summary = $reportData['summary'] ?? [];

        $sheet->setCellValue('A1', $report->title ?: 'Laporan Kinerja');
        $sheet->setCellValue('A2', 'Periode: '.$report->period);
        $sheet->setCellValue('A3', 'Dibuat: '.Carbon::now()->translatedFormat('d F Y H:i'));

        // Summary block
        $sheet->setCellValue('A5', 'Ringkasan');
        $sheet->setCellValue('A6', 'Total Indikator');
        $sheet->setCellValue('B6', $summary['total_indicators'] ?? 0);
        $sheet->setCellValue('A7', 'Indikator dengan Data');
        $sheet->setCellValue('B7', $summary['indicators_with_data'] ?? 0);
        $sheet->setCellValue('A8', 'Rata-rata Capaian (%)');
        $sheet->setCellValue('B8', $summary['average_performance'] ?? 0);
        $sheet->setCellValue('A9', 'Capaian Tercapai');
        $sheet->setCellValue('B9', $summary['achieved_indicators'] ?? 0);

        // Indicator table
        $headerRow = 11;
        $columns = ['Kode', 'Indikator', 'Satuan', 'Target', 'Realisasi', 'Capaian (%)'];
        foreach ($columns as $index => $label) {
            $sheet->setCellValue(chr(65 + $index).$headerRow, $label);
        }

        $year = Carbon::parse($report->period)->year;
        $row = $headerRow + 1;

        foreach ($report->indicators as $indicator) {
            $target = $indicator->targets->firstWhere('year', $year);
            $performance = $indicator->performanceData
                ->filter(fn ($data) => str_starts_with((string) $data->period, (string) $year))
                ->sortByDesc('period')
                ->first();

            $sheet->setCellValue('A'.$row, $indicator->code);
            $sheet->setCellValue('B'.$row, $indicator->name);
            $sheet->setCellValue('C'.$row, $indicator->unit);
            $sheet->setCellValue('D'.$row, $target->target_value ?? null);
            $sheet->setCellValue('E'.$row, $performance->actual_value ?? null);
            $sheet->setCellValue('F'.$row, $performance?->calculateAchievement());
            $row++;
        }

        foreach (range('A', 'F') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $temporaryFile = tempnam(sys_get_temp_dir(), 'report_xlsx_');
        (new XlsxWriter($spreadsheet))->save($temporaryFile);

        $path = $this->reportFilePath($report, 'xlsx');
        Storage::put($path, file_get_contents($temporaryFile));
        unlink($temporaryFile);
        $spreadsheet->disconnectWorksheets();

        return $path;
    }

    /**
     * Render the report as a Word-compatible HTML document (.doc) and store
     * it on the default disk.
     *
     * No PHPWord dependency is installed; Word opens HTML files with the
     * mso application header natively, which keeps the export dependency-free.
     *
     * @return string Stored file path
     */
    public function generateWordFile(Report $report, array $reportData): string
    {
        $html = view('sakip.reports.export.word', [
            'report' => $report,
            'data' => $reportData,
        ])->render();

        $path = $this->reportFilePath($report, 'doc');
        Storage::put($path, $html);

        return $path;
    }

    /**
     * Build the report content payload stored on the report record.
     *
     * The controller has always called this method on store(); it did not
     * exist, so creating a report from the form died with a 500.
     *
     * @param  \Illuminate\Support\Collection  $indicators
     * @param  \Illuminate\Support\Collection|null  $assessments
     * @return array Structured content snapshot
     */
    public function generateReportContent(
        Report $report,
        $indicators,
        $assessments,
        array $options = [],
    ): array {
        return [
            'generated_at' => Carbon::now()->toIso8601String(),
            'report' => [
                'title' => $report->title,
                'report_type' => $report->report_type,
                'period' => $report->period,
            ],
            'options' => [
                'include_assessments' => (bool) ($options['include_assessments'] ?? false),
                'include_benchmarks' => (bool) ($options['include_benchmarks'] ?? false),
                'include_recommendations' => (bool) ($options['include_recommendations'] ?? false),
            ],
            'indicators' => collect($indicators)
                ->map(fn ($indicator) => [
                    'id' => $indicator->id,
                    'code' => $indicator->code,
                    'name' => $indicator->name,
                    'unit' => $indicator->unit,
                ])
                ->values()
                ->all(),
            'assessments' => collect($assessments ?? [])
                ->map(fn ($assessment) => [
                    'id' => $assessment->id,
                    'score' => $assessment->assessment_score,
                    'status' => $assessment->status,
                ])
                ->values()
                ->all(),
        ];
    }

    public function generateReportData(
        $institutionId,
        $reportType,
        $period,
        $dataSources,
        $options = [],
    ) {
        $reportData = [
            'metadata' => [
                'instansi_id' => $institutionId,
                'report_type' => $reportType,
                'period' => $period,
                'generated_at' => Carbon::now()->toDateTimeString(),
                'data_sources' => $dataSources,
                'options' => $options,
            ],
            'summary' => [],
            'indicators' => [],
            'performance_data' => [],
            'assessments' => [],
            'trends' => [],
            'benchmarks' => [],
            'recommendations' => [],
        ];

        if (in_array('indicators', $dataSources)) {
            $reportData['indicators'] = $this->generateIndicatorsData(
                $institutionId,
                $period,
            );
        }

        if (in_array('performance_data', $dataSources)) {
            $reportData['performance_data'] = $this->generatePerformanceData(
                $institutionId,
                $period,
            );
        }

        if (in_array('assessments', $dataSources)) {
            $reportData['assessments'] = $this->generateAssessmentsData(
                $institutionId,
                $period,
            );
        }

        if ($options['include_trends'] ?? false) {
            $reportData['trends'] = $this->generateTrendAnalysis(
                $institutionId,
                $period,
            );
        }

        if ($options['include_benchmarks'] ?? false) {
            $reportData['benchmarks'] = $this->generateBenchmarkAnalysis(
                $institutionId,
                $period,
            );
        }

        $reportData['summary'] = $this->generateSummary($reportData);

        return $reportData;
    }

    private function generateIndicatorsData($institutionId, $period)
    {
        return PerformanceIndicator::where('instansi_id', $institutionId)
            ->where('is_active', true)
            ->with(['category', 'measurementType'])
            ->orderBy('code')
            ->get()
            ->map(function ($indicator) {
                return [
                    'id' => $indicator->id,
                    'code' => $indicator->code,
                    'name' => $indicator->name,
                    'description' => $indicator->description,
                    'category' => $indicator->category->name ?? null,
                    'measurement_type' => $indicator->measurementType->name ?? null,
                    'unit' => $indicator->unit,
                    'polarity' => $indicator->polarity,
                    'baseline_value' => $indicator->baseline_value,
                    'baseline_year' => $indicator->baseline_year,
                    'data_source' => $indicator->data_source,
                    'frequency' => $indicator->frequency,
                    'is_mandatory' => $indicator->is_mandatory,
                    'is_active' => $indicator->is_active,
                ];
            })
            ->toArray();
    }

    private function generatePerformanceData($institutionId, $period)
    {
        return PerformanceData::whereHas('indicator', function ($query) use (
            $institutionId,
        ) {
            $query->where('instansi_id', $institutionId);
        })
            ->where('period', $period)
            ->with(['indicator', 'evidence'])
            ->orderBy('indicator_id')
            ->get()
            ->map(function ($data) {
                return [
                    'id' => $data->id,
                    'indicator_id' => $data->indicator_id,
                    'indicator_code' => $data->indicator->code,
                    'indicator_name' => $data->indicator->name,
                    'actual_value' => $data->actual_value,
                    'target_value' => $data->target_value,
                    'achievement_percentage' => $data->achievement_percentage,
                    'status' => $data->status,
                    'data_quality_score' => $data->data_quality_score,
                    'evidence_count' => $data->evidence->count(),
                    'data_source' => $data->data_source,
                    'collection_date' => $data->collection_date,
                    'notes' => $data->notes,
                ];
            })
            ->toArray();
    }

    private function generateAssessmentsData($institutionId, $period)
    {
        return Assessment::whereHas('performanceData.indicator', function (
            $query,
        ) use ($institutionId) {
            $query->where('instansi_id', $institutionId);
        })
            ->where('period', $period)
            ->with(['performanceData.indicator', 'assessor'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($assessment) {
                return [
                    'id' => $assessment->id,
                    'indicator_id' => $assessment->performanceData->indicator->id,
                    'indicator_code' => $assessment->performanceData->indicator->code,
                    'indicator_name' => $assessment->performanceData->indicator->name,
                    'assessment_score' => $assessment->assessment_score,
                    'achievement_level' => $assessment->achievement_level,
                    'assessor_name' => $assessment->assessor->name ?? null,
                    'assessed_at' => $assessment->assessed_at,
                    'status' => $assessment->status,
                ];
            })
            ->toArray();
    }

    /**
     * Resolve the start date of a report period string.
     *
     * reports.period stores several formats in practice: 'YYYY', 'YYYY-MM',
     * 'YYYY-MM-DD' and 'YYYY-Qn'. createFromFormat('Y-m', ...) threw for
     * most of them.
     */
    private function periodStart(string $period): Carbon
    {
        if (preg_match('/^(\d{4})-Q([1-4])$/', $period, $match)) {
            return Carbon::create((int) $match[1], (((int) $match[2]) - 1) * 3 + 1, 1)->startOfDay();
        }

        if (preg_match('/^(\d{4})$/', $period, $match)) {
            return Carbon::create((int) $match[1], 1, 1)->startOfDay();
        }

        try {
            return Carbon::parse($period)->startOfMonth();
        } catch (\Exception $e) {
            // Unknown format: fall back to the embedded year, else now.
            if (preg_match('/(\d{4})/', $period, $match)) {
                return Carbon::create((int) $match[1], 1, 1)->startOfDay();
            }

            return Carbon::now()->startOfMonth();
        }
    }

    private function generateTrendAnalysis($institutionId, $currentPeriod)
    {
        $currentDate = $this->periodStart($currentPeriod);
        $previousPeriods = [];

        for ($i = 1; $i <= 5; $i++) {
            $previousPeriods[] = $currentDate
                ->copy()
                ->subMonths($i)
                ->format('Y-m');
        }

        $allPeriods = array_merge(array_reverse($previousPeriods), [
            $currentPeriod,
        ]);

        return PerformanceIndicator::where('instansi_id', $institutionId)
            ->where('is_active', true)
            ->get()
            ->map(function ($indicator) use ($allPeriods) {
                $indicatorTrends = [];

                foreach ($allPeriods as $period) {
                    $performanceData = PerformanceData::where(
                        'performance_indicator_id',
                        $indicator->id,
                    )
                        ->where('period', $period)
                        ->first();

                    $indicatorTrends[] = [
                        'period' => $period,
                        'actual_value' => $performanceData
                            ? $performanceData->actual_value
                            : null,
                        'achievement_percentage' => $performanceData
                            ? $performanceData->achievement_percentage
                            : null,
                    ];
                }

                $achievementValues = array_filter(
                    array_column($indicatorTrends, 'achievement_percentage'),
                );

                $trendDirection = 'stable';
                if (count($achievementValues) >= 2) {
                    $firstValue = reset($achievementValues);
                    $lastValue = end($achievementValues);
                    $change = $lastValue - $firstValue;

                    if ($change > 5) {
                        $trendDirection = 'improving';
                    } elseif ($change < -5) {
                        $trendDirection = 'declining';
                    }
                }

                return [
                    'indicator_id' => $indicator->id,
                    'indicator_code' => $indicator->code,
                    'indicator_name' => $indicator->name,
                    'trend_data' => $indicatorTrends,
                    'trend_direction' => $trendDirection,
                    'average_achievement' => count($achievementValues) > 0
                            ? array_sum($achievementValues) /
                                count($achievementValues)
                            : 0,
                ];
            })
            ->toArray();
    }

    private function generateBenchmarkAnalysis($institutionId, $period)
    {
        $currentInstitution = Instansi::find($institutionId);

        if (! $currentInstitution) {
            return [];
        }

        // instansis has no 'type' column; benchmark against all other
        // institutions instead of filtering by a non-existent peer group.
        $peerInstitutions = Instansi::where('id', '!=', $institutionId)->pluck('id');

        return PerformanceIndicator::where('instansi_id', $institutionId)
            ->where('is_active', true)
            ->get()
            ->map(function ($indicator) use ($period, $peerInstitutions) {
                $currentPerformance = PerformanceData::where(
                    'indicator_id',
                    $indicator->id,
                )
                    ->where('period', $period)
                    ->first();

                if (! $currentPerformance) {
                    return null;
                }

                $peerPerformances = PerformanceData::whereHas(
                    'indicator',
                    function ($query) use ($peerInstitutions) {
                        $query->whereIn('instansi_id', $peerInstitutions);
                    },
                )
                    ->where('performance_indicator_id', $indicator->id)
                    ->where('period', $period)
                    ->get();

                if ($peerPerformances->isEmpty()) {
                    return null;
                }

                $peerAchievements = $peerPerformances
                    ->pluck('achievement_percentage')
                    ->filter()
                    ->toArray();

                if (empty($peerAchievements)) {
                    return null;
                }

                $averageAchievement =
                    array_sum($peerAchievements) / count($peerAchievements);
                $maxAchievement = max($peerAchievements);
                $minAchievement = min($peerAchievements);

                return [
                    'indicator_id' => $indicator->id,
                    'indicator_code' => $indicator->code,
                    'indicator_name' => $indicator->name,
                    'current_achievement' => $currentPerformance->achievement_percentage,
                    'peer_average' => $averageAchievement,
                    'peer_max' => $maxAchievement,
                    'peer_min' => $minAchievement,
                    'benchmark_status' => $this->getBenchmarkStatus(
                        $currentPerformance->achievement_percentage,
                        $averageAchievement,
                    ),
                ];
            })
            ->filter()
            ->toArray();
    }

    private function generateSummary($reportData)
    {
        $summary = [
            'total_indicators' => count($reportData['indicators'] ?? []),
            'total_performance_data' => count(
                $reportData['performance_data'] ?? [],
            ),
            'total_assessments' => count($reportData['assessments'] ?? []),
            'average_achievement' => 0,
            'achievement_distribution' => [
                'excellent' => 0,
                'good' => 0,
                'fair' => 0,
                'poor' => 0,
            ],
        ];

        if (! empty($reportData['performance_data'])) {
            $achievements = array_filter(
                array_column(
                    $reportData['performance_data'],
                    'achievement_percentage',
                ),
            );

            if (count($achievements) > 0) {
                $summary['average_achievement'] =
                    array_sum($achievements) / count($achievements);
            }

            foreach ($achievements as $achievement) {
                if ($achievement >= 100) {
                    $summary['achievement_distribution']['excellent']++;
                } elseif ($achievement >= 80) {
                    $summary['achievement_distribution']['good']++;
                } elseif ($achievement >= 60) {
                    $summary['achievement_distribution']['fair']++;
                } else {
                    $summary['achievement_distribution']['poor']++;
                }
            }
        }

        return $summary;
    }

    private function getBenchmarkStatus($current, $average)
    {
        $difference = $current - $average;

        if ($difference >= 10) {
            return 'above_average';
        } elseif ($difference <= -10) {
            return 'below_average';
        } else {
            return 'average';
        }
    }
}
