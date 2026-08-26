<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SakipPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the SAKIP dashboard.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewDashboard(User $user)
    {
        return $user->hasPermissionTo('view-sakip-dashboard');
    }

    /**
     * Determine whether the user can view performance indicators.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewPerformanceIndicators(User $user)
    {
        return $user->hasPermissionTo('view-performance-indicators');
    }

    /**
     * Determine whether the user can view performance data.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewPerformanceData(User $user)
    {
        return $user->hasPermissionTo('view-performance-data');
    }

    /**
     * Determine whether the user can view assessments.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAssessments(User $user)
    {
        return $user->hasPermissionTo('view-assessments');
    }

    /**
     * Determine whether the user can view reports.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewReports(User $user)
    {
        return $user->hasPermissionTo('view-reports');
    }

    /**
     * Determine whether the user can export SAKIP data.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function exportData(User $user)
    {
        return $user->hasPermissionTo('export-sakip-data');
    }
}
