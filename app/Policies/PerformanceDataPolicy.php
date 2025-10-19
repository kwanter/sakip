<?php

namespace App\Policies;

use App\Models\PerformanceData;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Carbon\Carbon;

/**
 * PerformanceDataPolicy
 * 
 * Handles authorization for performance data collection, submission, and validation operations.
 * Implements role-based access control with workflow stage permissions and deadline enforcement.
 */
class PerformanceDataPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('view-data-collection-forms');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('enter-and-submit-data-records');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PerformanceData  $performanceData
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, PerformanceData $performanceData)
    {
        if ($user->can('edit-own-data-submissions') && $performanceData->created_by === $user->id) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PerformanceData  $performanceData
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, PerformanceData $performanceData)
    {
        return $user->can('manage-high-level-settings');
    }
}