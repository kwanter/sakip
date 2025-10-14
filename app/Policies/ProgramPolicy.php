<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // any authenticated user
    }

    public function view(User $user, Program $program): bool
    {
        return true; // any authenticated user
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'manager']) || $user->isAdmin();
    }

    public function update(User $user, Program $program): bool
    {
        return $user->hasAnyRole(['admin', 'manager']) || $user->isAdmin();
    }

    public function delete(User $user, Program $program): bool
    {
        return $user->hasRole('admin') || $user->isAdmin();
    }
}