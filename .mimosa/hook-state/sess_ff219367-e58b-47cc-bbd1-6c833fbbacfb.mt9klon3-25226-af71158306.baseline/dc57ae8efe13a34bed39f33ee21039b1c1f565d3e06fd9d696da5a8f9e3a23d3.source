<?php

namespace App\Policies;

use App\Models\SasaranStrategis;
use App\Models\User;

class SasaranStrategisPolicy
{
    public function viewAny(User $user): bool
    {
        return $user !== null;
    }

    public function view(User $user, SasaranStrategis $sasaranStrategis): bool
    {
        return $this->sameTenant($user, $sasaranStrategis->instansi_id);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(["Executive", "Government Official"]);
    }

    public function update(User $user, SasaranStrategis $sasaranStrategis): bool
    {
        return $user->hasAnyRole(["Executive", "Government Official"])
            && $this->sameTenant($user, $sasaranStrategis->instansi_id);
    }

    public function delete(User $user, SasaranStrategis $sasaranStrategis): bool
    {
        return $user->hasRole("Executive")
            && $this->sameTenant($user, $sasaranStrategis->instansi_id);
    }

    private function sameTenant(User $user, ?string $instansiId): bool
    {
        if ($user->instansi_id === null && $user->hasRole("Executive")) {
            return true;
        }

        return $user->instansi_id !== null && $user->instansi_id === $instansiId;
    }
}
