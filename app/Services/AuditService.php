<?php

namespace App\Services;

use App\Models\SakipAuditLog;
use App\Models\PerformanceData;
use App\Models\Assessment;
use App\Models\Report;
use App\Models\EvidenceDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Audit Service
 * 
 * Handles audit trails, compliance monitoring, and audit logging for SAKIP module
 */
class AuditService
{
    /**
     * Log audit event
     * 
     * @param string $eventType
     * @param string $eventDescription
     * @param string $tableName
     * @param int $recordId
     * @param int $userId
     * @param array $oldValues
     * @param array $newValues
     * @param array $metadata
     * @return SakipAuditLog|null
     */
    public function logAuditEvent(
        string $eventType,
        string $eventDescription,
        string $tableName,
        int $recordId,
        int $userId,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = []
    ): ?SakipAuditLog {
        try {
            return SakipAuditLog::create([
                'event_type' => $eventType,
                'event_description' => $eventDescription,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'user_id' => $userId,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'metadata' => $metadata,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'occurred_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging audit event: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Log data modification
     * 
     * @param string $tableName
     * @param int $recordId
     * @param int $userId
     * @param string $operation
     * @param array $oldValues
     * @param array $newValues
     * @return SakipAuditLog|null
     */
    public function logDataModification(
        string $tableName,
        int $recordId,
        int $userId,
        string $operation,
        array $oldValues = [],
        array $newValues = []
    ): ?SakipAuditLog {
        $eventDescription = $this->getModificationDescription($tableName, $operation, $oldValues, $newValues);
        
        return $this->logAuditEvent(
            'data_modification',
            $eventDescription,
            $tableName,
            $recordId,
            $userId,
            $oldValues,
            $newValues,
            ['operation' => $operation]
        );
    }

    /**
     * Log user activity
     * 
     * @param int $userId
     * @param string $activity
     * @param array $details
     * @return SakipAuditLog|null
     */
    public function logUserActivity(int $userId, string $activity, array $details = []): ?SakipAuditLog
    {
        return $this->logAuditEvent(
            'user_activity',
            $activity,
            'users',
            $userId,
            $userId,
            [],
            [],
            $details
        );
    }

    /**
     * Log system event
     * 
     * @param string $event
     * @param array $details
     * @return SakipAuditLog|null
     */
    public function logSystemEvent(string $event, array $details = []): ?SakipAuditLog
    {
        return $this->logAuditEvent(
            'system_event',
            $event,
            'system',
            0,
            0,
            [],
            [],
            $details
        );
    }

    /**
     * Get audit trail for specific record
     * 
     * @param string $tableName
     * @param int $recordId
     * @param array $options
     * @return Collection
     */
    public function getAuditTrail(string $tableName, int $recordId, array $options = [])
    {
        $query = SakipAuditLog::where('table_name', $tableName)
            ->where('record_id', $recordId)
            ->with(['user'])
            ->orderBy('occurred_at', 'desc');

        // Filter by event type
        if (!empty($options['event_types'])) {
            $query->whereIn('event_type', $options['event_types']);
        }

        // Filter by date range
        if (!empty($options['start_date'])) {
            $query->where('occurred_at', '>=', $options['start_date']);
        }

        if (!empty($options['end_date'])) {
            $query->where('occurred_at', '<=', $options['end_date']);
        }

        // Filter by user
        if (!empty($options['user_id'])) {
            $query->where('user_id', $options['user_id']);
        }

        return $query->get();
    }

    /**
     * Get audit analytics
     * 
     * @param array $filters
     * @return array
     */
    public function getAuditAnalytics(array $filters = []): array
    {
        $query = SakipAuditLog::query();

        // Apply filters
        if (!empty($filters['start_date'])) {
            $query->where('occurred_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->where('occurred_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['table_name'])) {
            $query->where('table_name', $filters['table_name']);
        }

        if (!empty($filters['event_types'])) {
            $query->whereIn('event_type', $filters['event_types']);
        }

        $totalEvents = $query->count();
        
        if ($totalEvents === 0) {
            return [
                'total_events' => 0,
                'by_event_type' => [],
                'by_table' => [],
                'by_user' => [],
                'by_day' => [],
                'by_hour' => [],
                'recent_events' => [],
            ];
        }

        return [
            'total_events' => $totalEvents,
            'by_event_type' => $query->selectRaw('event_type, COUNT(*) as count')
                ->groupBy('event_type')
                ->pluck('count', 'event_type')
                ->toArray(),
            'by_table' => $query->selectRaw('table_name, COUNT(*) as count')
                ->groupBy('table_name')
                ->pluck('count', 'table_name')
                ->toArray(),
            'by_user' => $query->selectRaw('user_id, COUNT(*) as count')
                ->groupBy('user_id')
                ->pluck('count', 'user_id')
                ->toArray(),
            'by_day' => $query->selectRaw('DATE(occurred_at) as date, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(30)
                ->pluck('count', 'date')
                ->toArray(),
            'by_hour' => $query->selectRaw('HOUR(occurred_at) as hour, COUNT(*) as count')
                ->groupBy('hour')
                ->pluck('count', 'hour')
                ->toArray(),
            'recent_events' => $query->orderBy('occurred_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($event) {
                    return [
                        'id' => $event->id,
                        'event_type' => $event->event_type,
                        'event_description' => $event->event_description,
                        'table_name' => $event->table_name,
                        'user_id' => $event->user_id,
                        'occurred_at' => $event->occurred_at,
                    ];
                })
                ->toArray(),
        ];
    }

    /**
     * Run compliance checks
     * 
     * @param int $institutionId
     * @param string $period
     * @return array
     */
    public function runComplianceChecks(int $institutionId, string $period): array
    {
        $complianceResults = [
            'instansi_id' => $institutionId,
            'period' => $period,
            'checks' => [],
            'violations' => [],
            'compliance_score' => 0,
            'status' => 'pending',
        ];

        try {
            // Check data completeness
            $dataCompleteness = $this->checkDataCompleteness($institutionId, $period);
            $complianceResults['checks']['data_completeness'] = $dataCompleteness;

            // Check data timeliness
            $dataTimeliness = $this->checkDataTimeliness($institutionId, $period);
            $complianceResults['checks']['data_timeliness'] = $dataTimeliness;

            // Check assessment completeness
            $assessmentCompleteness = $this->checkAssessmentCompleteness($institutionId, $period);
            $complianceResults['checks']['assessment_completeness'] = $assessmentCompleteness;

            // Check report submission
            $reportSubmission = $this->checkReportSubmission($institutionId, $period);
            $complianceResults['checks']['report_submission'] = $reportSubmission;

            // Check evidence requirements
            $evidenceRequirements = $this->checkEvidenceRequirements($institutionId, $period);
            $complianceResults['checks']['evidence_requirements'] = $evidenceRequirements;

            // Check audit trail completeness
            $auditTrailCompleteness = $this->checkAuditTrailCompleteness($institutionId, $period);
            $complianceResults['checks']['audit_trail_completeness'] = $auditTrailCompleteness;

            // Collect violations
            $violations = $this->collectViolations($complianceResults['checks']);
            $complianceResults['violations'] = $violations;

            // Calculate compliance score
            $complianceResults['compliance_score'] = $this->calculateComplianceScore($complianceResults['checks']);

            // Determine status
            $complianceResults['status'] = $this->determineComplianceStatus($complianceResults['compliance_score']);

        } catch (\Exception $e) {
            Log::error('Error running compliance checks: ' . $e->getMessage());
            $complianceResults['error'] = 'System error during compliance check';
        }

        return $complianceResults;
    }

    /**
     * Check data completeness compliance
     */
    private function checkDataCompleteness(int $institutionId, string $period): array
    {
        $indicators = PerformanceIndicator::where('instansi_id', $institutionId)
            ->where('is_active', true)
            ->count();

        $performanceData = PerformanceData::where('instansi_id', $institutionId)
            ->where('period', $period)
            ->count();

        $completionRate = $indicators > 0 ? ($performanceData / $indicators) * 100 : 0;

        return [
            'required_indicators' => $indicators,
            'submitted_indicators' => $performanceData,
            'completion_rate' => round($completionRate, 2),
            'status' => $completionRate >= 90 ? 'compliant' : ($completionRate >= 70 ? 'partial' : 'non_compliant'),
            'issues' => [],
        ];
    }

    /**
     * Check data timeliness compliance
     */
    private function checkDataTimeliness(int $institutionId, string $period): array
    {
        $periodEnd = Carbon::parse($period)->endOfMonth();
        $deadline = $periodEnd->copy()->addDays(30); // 30 days after period end
        $now = now();

        $lateSubmissions = PerformanceData::where('instansi_id', $institutionId)
            ->where('period', $period)
            ->where('collected_at', '>', $deadline)
            ->count();

        $totalSubmissions = PerformanceData::where('instansi_id', $institutionId)
            ->where('period', $period)
            ->count();

        $timelinessRate = $totalSubmissions > 0 ? (($totalSubmissions - $lateSubmissions) / $totalSubmissions) * 100 : 0;

        return [
            'deadline' => $deadline->toDateString(),
            'late_submissions' => $lateSubmissions,
            'total_submissions' => $totalSubmissions,
            'timeliness_rate' => round($timelinessRate, 2),
            'status' => $timelinessRate >= 95 ? 'compliant' : ($timelinessRate >= 80 ? 'partial' : 'non_compliant'),
            'issues' => [],
        ];
    }

    /**
     * Check assessment completeness compliance
     */
    private function checkAssessmentCompleteness(int $institutionId, string $period): array
    {
        $performanceData = PerformanceData::where('instansi_id', $institutionId)
            ->where('period', $period)
            ->count();

        $assessments = Assessment::whereHas('performanceData', function ($query) use ($institutionId, $period) {
                $query->where('instansi_id', $institutionId)->where('period', $period);
            })
            ->where('status', 'completed')
            ->count();

        $assessmentRate = $performanceData > 0 ? ($assessments / $performanceData) * 100 : 0;

        return [
            'required_assessments' => $performanceData,
            'completed_assessments' => $assessments,
            'assessment_rate' => round($assessmentRate, 2),
            'status' => $assessmentRate >= 95 ? 'compliant' : ($assessmentRate >= 80 ? 'partial' : 'non_compliant'),
            'issues' => [],
        ];
    }

    /**
     * Check report submission compliance
     */
    private function checkReportSubmission(int $institutionId, string $period): array
    {
        $reports = Report::where('instansi_id', $institutionId)
            ->where('period', $period)
            ->get();

        $submittedReports = $reports->where('status', 'submitted')->count();
        $approvedReports = $reports->where('status', 'approved')->count();

        return [
            'total_reports' => $reports->count(),
            'submitted_reports' => $submittedReports,
            'approved_reports' => $approvedReports,
            'submission_rate' => $reports->count() > 0 ? ($submittedReports / $reports->count()) * 100 : 0,
            'approval_rate' => $submittedReports > 0 ? ($approvedReports / $submittedReports) * 100 : 0,
            'status' => $approvedReports > 0 ? 'compliant' : ($submittedReports > 0 ? 'partial' : 'non_compliant'),
            'issues' => [],
        ];
    }

    /**
     * Check evidence requirements compliance
     */
    private function checkEvidenceRequirements(int $institutionId, string $period): array
    {
        $performanceData = PerformanceData::where('instansi_id', $institutionId)
            ->where('period', $period)
            ->with('evidenceDocuments')
            ->get();

        $totalRecords = $performanceData->count();
        $recordsWithEvidence = $performanceData->filter(function ($data) {
            return $data->evidenceDocuments->count() > 0;
        })->count();

        $evidenceCoverage = $totalRecords > 0 ? ($recordsWithEvidence / $totalRecords) * 100 : 0;

        return [
            'total_performance_data' => $totalRecords,
            'records_with_evidence' => $recordsWithEvidence,
            'evidence_coverage' => round($evidenceCoverage, 2),
            'status' => $evidenceCoverage >= 90 ? 'compliant' : ($evidenceCoverage >= 70 ? 'partial' : 'non_compliant'),
            'issues' => [],
        ];
    }

    /**
     * Check audit trail completeness
     */
    private function checkAuditTrailCompleteness(int $institutionId, string $period): array
    {
        $startDate = Carbon::parse($period)->startOfMonth();
        $endDate = Carbon::parse($period)->endOfMonth();

        $auditEvents = SakipAuditLog::where('occurred_at', '>=', $startDate)
            ->where('occurred_at', '<=', $endDate)
            ->where('table_name', '!=', 'system')
            ->count();

        $expectedEvents = $this->estimateExpectedAuditEvents($institutionId, $period);

        $auditCoverage = $expectedEvents > 0 ? ($auditEvents / $expectedEvents) * 100 : 0;

        return [
            'actual_audit_events' => $auditEvents,
            'expected_audit_events' => $expectedEvents,
            'audit_coverage' => round($auditCoverage, 2),
            'status' => $auditCoverage >= 95 ? 'compliant' : ($auditCoverage >= 80 ? 'partial' : 'non_compliant'),
            'issues' => [],
        ];
    }

    /**
     * Estimate expected audit events
     */
    private function estimateExpectedAuditEvents(int $institutionId, string $period): int
    {
        // Estimate based on data volume
        $performanceData = PerformanceData::where('instansi_id', $institutionId)
            ->where('period', $period)
            ->count();

        $assessments = Assessment::whereHas('performanceData', function ($query) use ($institutionId, $period) {
                $query->where('instansi_id', $institutionId)->where('period', $period);
            })
            ->count();

        $reports = Report::where('instansi_id', $institutionId)
            ->where('period', $period)
            ->count();

        // Rough estimate: 2-3 audit events per data record
        return ($performanceData * 2.5) + ($assessments * 3) + ($reports * 5);
    }

    /**
     * Collect violations from compliance checks
     */
    private function collectViolations(array $checks): array
    {
        $violations = [];

        foreach ($checks as $checkType => $checkResult) {
            if ($checkResult['status'] === 'non_compliant') {
                $violations[] = [
                    'type' => $checkType,
                    'severity' => 'high',
                    'description' => $this->getViolationDescription($checkType, $checkResult),
                    'recommendation' => $this->getViolationRecommendation($checkType, $checkResult),
                ];
            } elseif ($checkResult['status'] === 'partial') {
                $violations[] = [
                    'type' => $checkType,
                    'severity' => 'medium',
                    'description' => $this->getViolationDescription($checkType, $checkResult),
                    'recommendation' => $this->getViolationRecommendation($checkType, $checkResult),
                ];
            }
        }

        return $violations;
    }

    /**
     * Get violation description
     */
    private function getViolationDescription(string $checkType, array $checkResult): string
    {
        switch ($checkType) {
            case 'data_completeness':
                return "Data completeness is {$checkResult['completion_rate']}% (minimum 90% required)";
            case 'data_timeliness':
                return "Data timeliness is {$checkResult['timeliness_rate']}% (minimum 95% required)";
            case 'assessment_completeness':
                return "Assessment completion is {$checkResult['assessment_rate']}% (minimum 95% required)";
            case 'report_submission':
                return "Report submission is incomplete";
            case 'evidence_requirements':
                return "Evidence coverage is {$checkResult['evidence_coverage']}% (minimum 90% required)";
            case 'audit_trail_completeness':
                return "Audit trail coverage is {$checkResult['audit_coverage']}% (minimum 95% required)";
            default:
                return "Non-compliance detected in {$checkType}";
        }
    }

    /**
     * Get violation recommendation
     */
    private function getViolationRecommendation(string $checkType, array $checkResult): string
    {
        switch ($checkType) {
            case 'data_completeness':
                return "Complete missing indicator data submissions";
            case 'data_timeliness':
                return "Submit data within the specified deadlines";
            case 'assessment_completeness':
                return "Complete assessments for all performance data";
            case 'report_submission':
                return "Submit required reports and obtain approval";
            case 'evidence_requirements':
                return "Upload supporting evidence documents";
            case 'audit_trail_completeness':
                return "Ensure all activities are properly logged";
            default:
                return "Address the identified compliance issue";
        }
    }

    /**
     * Calculate compliance score
     */
    private function calculateComplianceScore(array $checks): float
    {
        $totalScore = 0;
        $totalWeight = 0;

        $weights = [
            'data_completeness' => 0.25,
            'data_timeliness' => 0.20,
            'assessment_completeness' => 0.20,
            'report_submission' => 0.15,
            'evidence_requirements' => 0.10,
            'audit_trail_completeness' => 0.10,
        ];

        foreach ($checks as $checkType => $checkResult) {
            $weight = $weights[$checkType] ?? 0.1;
            $totalWeight += $weight;

            switch ($checkResult['status']) {
                case 'compliant':
                    $totalScore += 100 * $weight;
                    break;
                case 'partial':
                    $totalScore += 70 * $weight;
                    break;
                case 'non_compliant':
                    $totalScore += 30 * $weight;
                    break;
            }
        }

        return $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : 0;
    }

    /**
     * Determine compliance status
     */
    private function determineComplianceStatus(float $score): string
    {
        if ($score >= 90) {
            return 'compliant';
        } elseif ($score >= 70) {
            return 'partially_compliant';
        } else {
            return 'non_compliant';
        }
    }

    /**
     * Get modification description
     */
    private function getModificationDescription(string $tableName, string $operation, array $oldValues, array $newValues): string
    {
        $tableDescriptions = [
            'performance_indicators' => 'Performance Indicator',
            'performance_data' => 'Performance Data',
            'assessments' => 'Assessment',
            'targets' => 'Target',
            'evidence_documents' => 'Evidence Document',
            'reports' => 'Report',
        ];

        $tableDescription = $tableDescriptions[$tableName] ?? $tableName;

        switch ($operation) {
            case 'create':
                return "Created new {$tableDescription}";
            case 'update':
                $changedFields = array_keys(array_diff_assoc($oldValues, $newValues));
                $fieldCount = count($changedFields);
                return "Updated {$tableDescription} ({$fieldCount} fields changed)";
            case 'delete':
                return "Deleted {$tableDescription}";
            default:
                return "Modified {$tableDescription}";
        }
    }
}