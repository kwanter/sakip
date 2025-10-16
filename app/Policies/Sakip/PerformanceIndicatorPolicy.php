<?php

namespace App\Policies\Sakip;

use App\Models\User;
use App\Models\PerformanceIndicator;
use Illuminate\Auth\Access\HandlesAuthorization;

class PerformanceIndicatorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PerformanceIndicator $performanceIndicator): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PerformanceIndicator $performanceIndicator): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PerformanceIndicator $performanceIndicator): bool
    {
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PerformanceIndicator $performanceIndicator): bool
    {
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PerformanceIndicator $performanceIndicator): bool
    {
        return $user->hasRole(['admin']);
    }
}