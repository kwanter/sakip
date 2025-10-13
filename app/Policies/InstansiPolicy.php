<?php

namespace App\Policies;

use App\Models\Instansi;
use App\Models\User;

class InstansiPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Instansi $instansi): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Instansi $instansi): bool
    {
        return true;
    }

    public function delete(User $user, Instansi $instansi): bool
    {
        return true;
    }
}