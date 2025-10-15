<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LaporanKinerja;
use Illuminate\Auth\Access\Response;

class LaporanKinerjaPolicy
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
    public function view(User $user, LaporanKinerja $laporanKinerja): bool
    {
        return true; // any authenticated user
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager']) || $user->isAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LaporanKinerja $laporanKinerja): bool
    {
        return $user->hasAnyRole(['admin', 'manager']) || $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LaporanKinerja $laporanKinerja): bool
    {
        return $user->hasRole('admin') || $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, LaporanKinerja $laporanKinerja): bool
    {
        return $user->hasRole('admin') || $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, LaporanKinerja $laporanKinerja): bool
    {
        return $user->hasRole('admin') || $user->isAdmin();
    }
}