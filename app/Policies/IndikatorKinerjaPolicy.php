<?php

namespace App\Policies;

use App\Models\User;
use App\Models\IndikatorKinerja;
use Illuminate\Auth\Access\Response;

class IndikatorKinerjaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // any authenticated user
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, IndikatorKinerja $indikatorKinerja): bool
    {
        return true; // any authenticated user
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'executive']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, IndikatorKinerja $indikatorKinerja): bool
    {
        return $user->hasAnyRole(['superadmin', 'executive']);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, IndikatorKinerja $indikatorKinerja): bool
    {
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, IndikatorKinerja $indikatorKinerja): bool
    {
        return $user->hasRole('superadmin');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, IndikatorKinerja $indikatorKinerja): bool
    {
        return $user->hasRole('superadmin');
    }
}