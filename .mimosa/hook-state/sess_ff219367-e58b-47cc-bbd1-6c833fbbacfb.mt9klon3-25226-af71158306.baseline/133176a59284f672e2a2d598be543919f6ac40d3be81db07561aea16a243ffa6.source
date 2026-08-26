<?php

namespace App\Policies;

use App\Models\Instansi;
use App\Models\User;

class InstansiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, Instansi $instansi): bool
    {
        // Super Admin bypass is handled by Gate::before
        return $user->instansi_id === $instansi->id
            || $user->hasAnyRole(["Executive", "Government Official", "Assessor"]);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(["Executive"]);
    }

    public function update(User $user, Instansi $instansi): bool
    {
        if ($user->hasRole("Executive") && $user->instansi_id === null) {
            return true;
        }

        return $user->hasAnyRole(["Executive"])
            && $user->instansi_id === $instansi->id;
    }

    public function delete(User $user, Instansi $instansi): bool
    {
        return $user->hasRole("Executive") && $user->instansi_id === null;
    }
}
