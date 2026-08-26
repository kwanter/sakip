<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SakipDashboardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the SAKIP dashboard.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewDashboard(User $user)
    {
        // Super Admin can access everything
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->hasPermissionTo('view-sakip-dashboard');
    }

    /**
     * Determine whether the user can view the executive dashboard.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewExecutiveDashboard(User $user)
    {
        return $user->hasPermissionTo('view-executive-dashboard');
    }

    /**
     * Determine whether the user can view the data collector dashboard.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewDataCollectorDashboard(User $user)
    {
        return $user->hasPermissionTo('view-data-entry-dashboard');
    }

    /**
     * Determine whether the user can view the assessor dashboard.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAssessorDashboard(User $user)
    {
        return $user->hasPermissionTo('view-assessor-dashboard');
    }

    /**
     * Determine whether the user can view the audit dashboard.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAuditDashboard(User $user)
    {
        return $user->hasPermissionTo('view-audit-dashboard');
    }
}
