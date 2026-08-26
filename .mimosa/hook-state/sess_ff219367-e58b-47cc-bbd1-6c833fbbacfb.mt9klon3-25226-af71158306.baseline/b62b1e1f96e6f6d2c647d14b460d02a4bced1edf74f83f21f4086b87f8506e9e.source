<?php

namespace App\Policies;

use App\Models\Kegiatan;
use App\Models\User;

class KegiatanPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // any authenticated user
    }

    public function view(User $user, Kegiatan $kegiatan): bool
    {
        return true; // any authenticated user
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'executive']) || $user->isAdmin();
    }

    public function update(User $user, Kegiatan $kegiatan): bool
    {
        return $user->hasAnyRole(['superadmin', 'executive']) || $user->isAdmin();
    }

    public function delete(User $user, Kegiatan $kegiatan): bool
    {
        return $user->hasRole('superadmin') || $user->isAdmin();
    }
}