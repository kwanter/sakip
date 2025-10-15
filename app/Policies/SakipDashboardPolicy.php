<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

/**
 * SakipDashboardPolicy
 * 
 * Handles authorization for SAKIP dashboard access and viewing permissions.
 * Implements role-based access control with institution-based restrictions.
 */
class SakipDashboardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the main SAKIP dashboard.
     * All authenticated users can access the dashboard, but content varies by role.
     */
    public function viewDashboard(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.view',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view executive dashboard.
     * Restricted to pimpinan and admin roles.
     */
    public function viewExecutiveDashboard(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.executive',
            'sakip.admin',
            'sakip.pimpinan'
        ]);
    }

    /**
     * Determine whether the user can view data collector dashboard.
     * Available to data collectors, admins, and auditors.
     */
    public function viewDataCollectorDashboard(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.collector',
            'sakip.admin',
            'sakip.data_collector',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view assessor dashboard.
     * Available to assessors, admins, and pimpinan.
     */
    public function viewAssessorDashboard(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.assessor',
            'sakip.admin',
            'sakip.assessor',
            'sakip.pimpinan'
        ]);
    }

    /**
     * Determine whether the user can view audit dashboard.
     * Restricted to auditors and admins.
     */
    public function viewAuditDashboard(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.audit',
            'sakip.admin',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view KPI metrics and statistics.
     * Available to all SAKIP users based on their institution access.
     */
    public function viewKpiMetrics(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.kpi',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view alerts and notifications.
     * Available to all SAKIP users.
     */
    public function viewAlerts(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.alerts',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can perform quick actions from dashboard.
     * Based on user's primary role and permissions.
     */
    public function performQuickActions(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.quick_actions',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor'
        ]);
    }

    /**
     * Determine whether the user can view institution-specific data.
     * Ensures users only see data from their assigned institution.
     */
    public function viewInstitutionData(User $user, ?int $instansiId = null): bool
    {
        // Admin can view all institution data
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view their own institution's data
        if ($instansiId !== null) {
            return $user->instansi_id === $instansiId;
        }

        return true; // No specific institution requested
    }

    /**
     * Determine whether the user can export dashboard data.
     * Available to pimpinan, admins, and auditors.
     */
    public function exportDashboardData(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.export',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can customize dashboard widgets.
     * Available to admins and pimpinan.
     */
    public function customizeDashboard(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.customize',
            'sakip.admin',
            'sakip.pimpinan'
        ]);
    }

    /**
     * Determine whether the user can view real-time updates.
     * Available to all SAKIP users.
     */
    public function viewRealTimeUpdates(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.realtime',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can access dashboard during maintenance.
     * Restricted to admins only.
     */
    public function accessDuringMaintenance(User $user): bool
    {
        return $user->hasPermission('sakip.admin');
    }

    /**
     * Determine whether the user can view sensitive data on dashboard.
     * Restricted to pimpinan, admins, and authorized personnel.
     */
    public function viewSensitiveData(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.sensitive',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can drill down into detailed metrics.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function drillDownMetrics(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.drill_down',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can access cross-institution comparison.
     * Restricted to pimpinan and admins.
     */
    public function viewCrossInstitutionComparison(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.dashboard.cross_institution',
            'sakip.admin',
            'sakip.pimpinan'
        ]);
    }
}