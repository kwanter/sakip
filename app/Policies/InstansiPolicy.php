<?php

namespace App\Policies;

use App\Models\Instansi;
use App\Models\User;

class InstansiPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // any authenticated user
    }

    public function view(User $user, Instansi $instansi): bool
    {
        return true; // any authenticated user
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            "Super Admin",
            "Executive",
            "Government Official",
            "Assessor",
        ]);
    }

    public function update(User $user, Instansi $instansi): bool
    {
        return $user->hasAnyRole([
            "Super Admin",
            "Executive",
            "Government Official",
            "Assessor",
        ]);
    }

    public function delete(User $user, Instansi $instansi): bool
    {
        return $user->hasAnyRole(["Super Admin", "Executive"]);
    }
}
