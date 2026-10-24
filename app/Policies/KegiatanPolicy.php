<?php

namespace App\Policies;

use App\Models\Kegiatan;
use App\Models\User;

class KegiatanPolicy
{
    public function viewAny(User $user): bool
    {
        if ($user->isAdmin()) {
            return true; // Super Admin keeps cross-instansi access
        }

        // Tenant-bound viewers only; a viewer without an instansi is default-denied.
        return $user->instansi_id !== null;
    }

    public function view(User $user, Kegiatan $kegiatan): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        // Kegiatan has no instansi_id of its own: bind through the program relation.
        return $user->instansi_id !== null
            && $kegiatan->program !== null
            && $kegiatan->program->instansi_id === $user->instansi_id;
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
