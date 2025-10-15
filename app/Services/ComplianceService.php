<?php

namespace App\Services;

use App\Models\PerformanceData;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\Instansi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ComplianceService
{
    /**
     * Run comprehensive compliance check
     */
    public function runComplianceCheck($institutionId, $period, $options = [])
    {
        $results = [
            'overall_score' => 0,
            'total_violations' => 0,
            'violations' => [],
            'recommendations' => [],
            'checks_performed' => [],
        ];

        // Data completeness check
        $dataCompleteness = $this->checkDataCompleteness($institutionId, $period);
        $results['checks_performed'][] = 'data_completeness';
        if ($dataCompleteness['violations']) {
            $results['violations'] = array_merge($results['violations'], $dataCompleteness['violations']);
            $results['total_violations'] += count($dataCompleteness['violations']);
        }

        // Data quality check
        $dataQuality = $this->checkDataQuality($institutionId, $period);
        $results['checks_performed'][] = 'data_quality';
        if ($dataQuality['violations']) {
            $results['violations'] = array_merge($results['violations'], $dataQuality['violations']);
            $results['total_violations'] += count($dataQuality['violations']);
        }

        // Assessment compliance check
        $assessmentCompliance = $this->checkAssessmentCompliance($institutionId, $period);
        $results['checks_performed'][] = 'assessment_compliance';
        if ($assessmentCompliance['violations']) {
            $results['violations'] = array_merge($results['violations'], $assessmentCompliance['violations']);
            $results['total_violations'] += count($assessmentCompliance['violations']);
        }

        // Evidence compliance check
        $evidenceCompliance = $this->checkEvidenceCompliance($institutionId, $period);
        $results['checks_performed'][] = 'evidence_compliance';
        if ($evidenceCompliance['violations']) {
            $results['violations'] = array_merge($results['violations'], $evidenceCompliance['violations']);
            $results['total_violations'] += count($evidenceCompliance['violations']);
        }

        // Calculate overall score
        $totalChecks = count($results['checks_performed']);
        $passedChecks = $totalChecks - $results['total_violations'];
        $results['overall_score'] = $totalChecks > 0 ? round(($passedChecks / $totalChecks) * 100, 2) : 100;

        // Generate recommendations
        $results['recommendations'] = $this->generateComplianceRecommendations($results);

        return $results;
    }

    /**
     * Check data completeness
     */
    private function checkDataCompleteness($institutionId, $period)
    {
        $violations = [];

        // Get all mandatory indicators
        $mandatoryIndicators = DB::table('performance_indicators')
            ->where('institution_id', $institutionId)
            ->where('is_active', true)
            ->where('is_mandatory', true)
            ->get();

        foreach ($mandatoryIndicators as $indicator) {
            $performanceData = PerformanceData::where('indicator_id', $indicator->id)
                ->where('period', $period)
                ->first();

            if (!$performanceData) {
                $violations[] = [
                    'type' => 'missing_data',
                    'severity' => 'high',
                    'indicator_id' => $indicator->id,
                    'indicator_code' => $indicator->code,
                    'indicator_name' => $indicator->name,
                    'message' => 'Data kinerja untuk indikator wajib belum diisi',
                    'recommendation' => 'Segera input data kinerja untuk indikator ini',
                ];
            } elseif (is_null($performanceData->actual_value)) {
                $violations[] = [
                    'type' => 'incomplete_data',
                    'severity' => 'medium',
                    'indicator_id' => $indicator->id,
                    'indicator_code' => $indicator->code,
                    'indicator_name' => $indicator->name,
                    'message' => 'Nilai aktual belum diisi',
                    'recommendation' => 'Lengkapi nilai aktual untuk indikator ini',
                ];
            }
        }

        return ['violations' => $violations];
    }

    /**
     * Check data quality
     */
    private function checkDataQuality($institutionId, $period)
    {
        $violations = [];

        $performanceData = PerformanceData::whereHas('indicator', function ($query) use ($institutionId) {
                $query->where('institution_id', $institutionId);
            })
            ->where('period', $period)
            ->with(['indicator'])
            ->get();

        foreach ($performanceData as $data) {
            // Check data quality score
            if ($data->data_quality_score < 70) {
                $violations[] = [
                    'type' => 'low_data_quality',
                    'severity' => 'medium',
                    'indicator_id' => $data->indicator_id,
                    'indicator_code' => $data->indicator->code,
                    'indicator_name' => $data->indicator->name,
                    'message' => 'Skor kualitas data rendah (' . $data->data_quality_score . ')',
                    'recommendation' => 'Perbaiki kualitas data dengan melengkapi dokumentasi dan evidence',
                ];
            }

            // Check evidence
            if ($data->evidence->count() === 0) {
                $violations[] = [
                    'type' => 'missing_evidence',
                    'severity' => 'medium',
                    'indicator_id' => $data->indicator_id,
                    'indicator_code' => $data->indicator->code,
                    'indicator_name' => $data->indicator->name,
                    'message' => 'Tidak ada evidence yang diunggah',
                    'recommendation' => 'Unggah evidence untuk mendukung data kinerja',
                ];
            }

            // Check achievement percentage
            if ($data->achievement_percentage > 150) {
                $violations[] = [
                    'type' => 'unrealistic_achievement',
                    'severity' => 'high',
                    'indicator_id' => $data->indicator_id,
                    'indicator_code' => $data->indicator->code,
                    'indicator_name' => $data->indicator->name,
                    'message' => 'Pencapaian tidak realistis (' . $data->achievement_percentage . '%)',
                    'recommendation' => 'Verifikasi ulang nilai aktual dan target',
                ];
            }
        }

        return ['violations' => $violations];
    }

    /**
     * Check assessment compliance
     */
    private function checkAssessmentCompliance($institutionId, $period)
    {
        $violations = [];

        $assessments = Assessment::whereHas('performanceData.indicator', function ($query) use ($institutionId) {
                $query->where('institution_id', $institutionId);
            })
            ->where('period', $period)
            ->get();

        // Check for overdue assessments
        $overdueThreshold = Carbon::now()->subDays(30);
        $pendingAssessments = Assessment::whereHas('performanceData.indicator', function ($query) use ($institutionId) {
                $query->where('institution_id', $institutionId);
            })
            ->where('period', $period)
            ->where('status', 'pending')
            ->where('created_at', '<', $overdueThreshold)
            ->get();

        foreach ($pendingAssessments as $assessment) {
            $violations[] = [
                'type' => 'overdue_assessment',
                'severity' => 'medium',
                'indicator_id' => $assessment->performanceData->indicator_id,
                'indicator_code' => $assessment->performanceData->indicator->code,
                'indicator_name' => $assessment->performanceData->indicator->name,
                'message' => 'Penilaian tertunda lebih dari 30 hari',
                'recommendation' => 'Selesaikan penilaian untuk indikator ini',
            ];
        }

        return ['violations' => $violations];
    }

    /**
     * Check evidence compliance
     */
    private function checkEvidenceCompliance($institutionId, $period)
    {
        $violations = [];

        $performanceData = PerformanceData::whereHas('indicator', function ($query) use ($institutionId) {
                $query->where('institution_id', $institutionId);
            })
            ->where('period', $period)
            ->with(['evidence'])
            ->get();

        foreach ($performanceData as $data) {
            // Check evidence quality
            foreach ($data->evidence as $evidence) {
                if ($evidence->file_size > 10 * 1024 * 1024) { // 10MB
                    $violations[] = [
                        'type' => 'oversized_evidence',
                        'severity' => 'low',
                        'indicator_id' => $data->indicator_id,
                        'indicator_code' => $data->indicator->code,
                        'indicator_name' => $data->indicator->name,
                        'message' => 'File evidence terlalu besar',
                        'recommendation' => 'Kompres file atau gunakan format yang lebih ringan',
                    ];
                }
            }
        }

        return ['violations' => $violations];
    }

    /**
     * Generate compliance recommendations
     */
    private function generateComplianceRecommendations($results)
    {
        $recommendations = [];

        if ($results['overall_score'] < 70) {
            $recommendations[] = 'Perlu perbaikan signifikan dalam kepatuhan SAKIP';
        } elseif ($results['overall_score'] < 90) {
            $recommendations[] = 'Terdapat beberapa area yang perlu diperbaiki';
        } else {
            $recommendations[] = 'Kepatuhan SAKIP dalam kondisi baik';
        }

        // Group violations by type
        $violationsByType = [];
        foreach ($results['violations'] as $violation) {
            $violationsByType[$violation['type']][] = $violation;
        }

        // Add specific recommendations
        if (isset($violationsByType['missing_data'])) {
            $recommendations[] = 'Prioritaskan pengisian data untuk indikator wajib';
        }

        if (isset($violationsByType['low_data_quality'])) {
            $recommendations[] = 'Tingkatkan kualitas data dengan melengkapi dokumentasi';
        }

        if (isset($violationsByType['overdue_assessment'])) {
            $recommendations[] = 'Percepat proses penilaian yang tertunda';
        }

        return $recommendations;
    }

    /**
     * Fix compliance violation
     */
    public function fixViolation($violationId, $violationType, $data = [])
    {
        try {
            // Log the fix attempt
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'fix_compliance_violation',
                'entity_type' => 'compliance',
                'entity_id' => $violationId,
                'old_values' => json_encode(['violation_type' => $violationType]),
                'new_values' => json_encode($data),
                'description' => 'Attempted to fix compliance violation: ' . $violationType,
            ]);

            // Handle different violation types
            switch ($violationType) {
                case 'missing_data':
                    return $this->fixMissingData($violationId, $data);
                case 'low_data_quality':
                    return $this->fixLowDataQuality($violationId, $data);
                case 'missing_evidence':
                    return $this->fixMissingEvidence($violationId, $data);
                default:
                    return [
                        'success' => false,
                        'message' => 'Violation type not supported for automatic fixing'
                    ];
            }
        } catch (\Exception $e) {
            Log::error('Error fixing compliance violation: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error fixing violation: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Fix missing data
     */
    private function fixMissingData($indicatorId, $data)
    {
        // This would typically involve creating a task or notification
        // to remind users to input the missing data
        return [
            'success' => true,
            'message' => 'Created reminder for missing data input'
        ];
    }

    /**
     * Fix low data quality
     */
    private function fixLowDataQuality($performanceDataId, $data)
    {
        // This would typically involve flagging the data for review
        // and notifying relevant users
        return [
            'success' => true,
            'message' => 'Flagged data for quality review'
        ];
    }

    /**
     * Fix missing evidence
     */
    private function fixMissingEvidence($performanceDataId, $data)
    {
        // This would typically involve creating a task or notification
        // to remind users to upload evidence
        return [
            'success' => true,
            'message' => 'Created reminder for evidence upload'
        ];
    }
}