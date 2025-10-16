<?php

namespace App\Policies\Sakip;

use App\Models\User;
use App\Models\PerformanceData;
use Illuminate\Auth\Access\HandlesAuthorization;

class PerformanceDataPolicy
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
    public function view(User $user, PerformanceData $performanceData): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'manager', 'user']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PerformanceData $performanceData): bool
    {
        return $user->hasRole(['admin', 'manager']) || $user->id === $performanceData->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PerformanceData $performanceData): bool
    {
        return $user->hasRole(['admin', 'manager']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PerformanceData $performanceData): bool
    {
        return $user->hasRole(['admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PerformanceData $performanceData): bool
    {
        return $user->hasRole(['admin']);
    }
}