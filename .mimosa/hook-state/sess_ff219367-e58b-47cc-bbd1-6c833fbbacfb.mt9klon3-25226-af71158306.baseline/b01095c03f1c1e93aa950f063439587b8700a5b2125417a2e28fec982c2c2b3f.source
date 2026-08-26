<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;

class ProgramPolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, Program $program): bool
    {
        return $this->sameTenant($user, $program->instansi_id);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(["Executive", "Government Official"]);
    }

    public function update(User $user, Program $program): bool
    {
        return $user->hasAnyRole(["Executive", "Government Official"])
            && $this->sameTenant($user, $program->instansi_id);
    }

    public function delete(User $user, Program $program): bool
    {
        return $user->hasRole("Executive")
            && $this->sameTenant($user, $program->instansi_id);
    }

    private function sameTenant(User $user, ?string $instansiId): bool
    {
        if ($user->instansi_id === null && $user->hasRole("Executive")) {
            return true;
        }

        return $user->instansi_id !== null && $user->instansi_id === $instansiId;
    }
}
