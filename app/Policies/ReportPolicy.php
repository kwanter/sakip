<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Carbon\Carbon;

/**
 * ReportPolicy
 * 
 * Handles authorization for report generation and access operations.
 * Implements role-based access control with report type permissions and access restrictions.
 */
class ReportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any reports.
     * Available to all SAKIP users with appropriate permissions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.reports.view',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view the report.
     * Users can only view reports from their own institution (unless admin).
     */
    public function view(User $user, Report $report): bool
    {
        // Admin can view any report
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view reports from their own institution
        if ($user->instansi_id !== $report->instansi_id) {
            return false;
        }

        // Check report type permissions
        if (!$this->hasReportTypePermission($user, $report->type)) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.reports.view',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can create reports.
     * Restricted to admins, pimpinan, and assessors from the same institution.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.reports.create',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.assessor'
        ]);
    }

    /**
     * Determine whether the user can update the report.
     * Restricted to admins and pimpinan from the same institution.
     */
    public function update(User $user, Report $report): bool
    {
        // Admin can update any report
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only update reports from their own institution
        if ($user->instansi_id !== $report->instansi_id) {
            return false;
        }

        // Pimpinan can update any report from their institution
        if ($user->hasPermission('sakip.pimpinan')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the report.
     * Restricted to admins and only for draft status.
     */
    public function delete(User $user, Report $report): bool
    {
        // Only admin can delete reports
        if (!$user->hasPermission('sakip.admin')) {
            return false;
        }

        // Can only delete draft reports
        return $report->status === 'draft';
    }

    /**
     * Determine whether the user can generate reports.
     * Restricted to admins, pimpinan, and assessors from the same institution.
     */
    public function generate(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.reports.generate',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.assessor'
        ]);
    }

    /**
     * Determine whether the user can generate specific report types.
     * Restricted based on user role and report type.
     */
    public function generateType(User $user, string $reportType): bool
    {
        // Admin can generate any report type
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Check specific report type permissions
        switch ($reportType) {
            case 'performance':
                return $user->hasAnyPermission([
                    'sakip.reports.performance',
                    'sakip.pimpinan',
                    'sakip.assessor'
                ]);
            
            case 'compliance':
                return $user->hasAnyPermission([
                    'sakip.reports.compliance',
                    'sakip.pimpinan',
                    'sakip.auditor'
                ]);
            
            case 'audit':
                return $user->hasAnyPermission([
                    'sakip.reports.audit',
                    'sakip.pimpinan',
                    'sakip.auditor'
                ]);
            
            case 'executive':
                return $user->hasAnyPermission([
                    'sakip.reports.executive',
                    'sakip.pimpinan'
                ]);
            
            case 'operational':
                return $user->hasAnyPermission([
                    'sakip.reports.operational',
                    'sakip.pimpinan',
                    'sakip.assessor',
                    'sakip.data_collector'
                ]);
            
            case 'trend':
                return $user->hasAnyPermission([
                    'sakip.reports.trend',
                    'sakip.pimpinan',
                    'sakip.assessor',
                    'sakip.auditor'
                ]);
            
            default:
                return false;
        }
    }

    /**
     * Determine whether the user can export reports.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function export(User $user, Report $report): bool
    {
        // Admin can export any report
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only export reports from their own institution
        if ($user->instansi_id !== $report->instansi_id) {
            return false;
        }

        // Check report type permissions
        if (!$this->hasReportTypePermission($user, $report->type)) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.reports.export',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can schedule automated reports.
     * Restricted to admins and pimpinan.
     */
    public function schedule(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.reports.schedule',
            'sakip.admin',
            'sakip.pimpinan'
        ]);
    }

    /**
     * Determine whether the user can share reports.
     * Restricted to pimpinan from the same institution.
     */
    public function share(User $user, Report $report): bool
    {
        // Admin can share any report
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only share reports from their own institution
        if ($user->instansi_id !== $report->instansi_id) {
            return false;
        }

        // Only pimpinan can share reports
        return $user->hasPermission('sakip.pimpinan');
    }

    /**
     * Determine whether the user can publish reports.
     * Restricted to pimpinan from the same institution.
     */
    public function publish(User $user, Report $report): bool
    {
        // Admin can publish any report
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only publish reports from their own institution
        if ($user->instansi_id !== $report->instansi_id) {
            return false;
        }

        // Only pimpinan can publish reports
        return $user->hasPermission('sakip.pimpinan');
    }

    /**
     * Determine whether the user can archive reports.
     * Restricted to admins and pimpinan from the same institution.
     */
    public function archive(User $user, Report $report): bool
    {
        // Admin can archive any report
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only archive reports from their own institution
        if ($user->instansi_id !== $report->instansi_id) {
            return false;
        }

        // Pimpinan can archive reports from their institution
        return $user->hasPermission('sakip.pimpinan');
    }

    /**
     * Determine whether the user can view report history.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function viewHistory(User $user, Report $report): bool
    {
        // Admin can view any report history
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view report history from their own institution
        if ($user->instansi_id !== $report->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.reports.history',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view report analytics.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function viewAnalytics(User $user, Report $report): bool
    {
        // Admin can view any report analytics
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view report analytics from their own institution
        if ($user->instansi_id !== $report->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.reports.analytics',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can customize report templates.
     * Restricted to admins and pimpinan.
     */
    public function customizeTemplate(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.reports.customize_template',
            'sakip.admin',
            'sakip.pimpinan'
        ]);
    }

    /**
     * Determine whether the user can view report compliance status.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function viewComplianceStatus(User $user, Report $report): bool
    {
        // Admin can view any compliance status
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view compliance status from their own institution
        if ($user->instansi_id !== $report->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.reports.compliance',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can bulk generate reports.
     * Restricted to admins and pimpinan.
     */
    public function bulkGenerate(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.reports.bulk_generate',
            'sakip.admin',
            'sakip.pimpinan'
        ]);
    }

    /**
     * Determine whether the user can bulk export reports.
     * Restricted to admins and pimpinan.
     */
    public function bulkExport(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.reports.bulk_export',
            'sakip.admin',
            'sakip.pimpinan'
        ]);
    }

    /**
     * Check if user has permission for specific report type.
     * Helper method for report type permission checking.
     */
    private function hasReportTypePermission(User $user, string $reportType): bool
    {
        // Admin has access to all report types
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Check specific report type permissions
        switch ($reportType) {
            case 'performance':
                return $user->hasAnyPermission(['sakip.reports.performance', 'sakip.pimpinan', 'sakip.assessor']);
            
            case 'compliance':
                return $user->hasAnyPermission(['sakip.reports.compliance', 'sakip.pimpinan', 'sakip.auditor']);
            
            case 'audit':
                return $user->hasAnyPermission(['sakip.reports.audit', 'sakip.pimpinan', 'sakip.auditor']);
            
            case 'executive':
                return $user->hasPermission('sakip.pimpinan');
            
            case 'operational':
                return $user->hasAnyPermission(['sakip.reports.operational', 'sakip.pimpinan', 'sakip.assessor', 'sakip.data_collector']);
            
            case 'trend':
                return $user->hasAnyPermission(['sakip.reports.trend', 'sakip.pimpinan', 'sakip.assessor', 'sakip.auditor']);
            
            default:
                return false;
        }
    }
}