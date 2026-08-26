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
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewDashboard(User $user)
    {
        return $user->hasPermissionTo('view-sakip-dashboard');
    }

    /**
     * Determine whether the user can view performance indicators.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewPerformanceIndicators(User $user)
    {
        return $user->hasPermissionTo('view-performance-indicators');
    }

    /**
     * Determine whether the user can view performance data.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewPerformanceData(User $user)
    {
        return $user->hasPermissionTo('view-performance-data');
    }

    /**
     * Determine whether the user can view assessments.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAssessments(User $user)
    {
        return $user->hasPermissionTo('view-assessments');
    }

    /**
     * Determine whether the user can view reports.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewReports(User $user)
    {
        return $user->hasPermissionTo('view-reports');
    }

    /**
     * Determine whether the user can export SAKIP data.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function exportData(User $user)
    {
        return $user->hasPermissionTo('export-sakip-data');
    }
}