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
        // Ordinary actors need an instansi binding; Super Admin bypasses via Gate::before.
        return $user->instansi_id !== null
            && $user->hasAnyRole(['Executive', 'Government Official']);
    }

    public function update(User $user, SasaranStrategis $sasaranStrategis): bool
    {
        return $user->hasAnyRole(['Executive', 'Government Official'])
            && $this->sameTenant($user, $sasaranStrategis->instansi_id);
    }

    public function delete(User $user, SasaranStrategis $sasaranStrategis): bool
    {
        return $user->hasRole('Executive')
            && $this->sameTenant($user, $sasaranStrategis->instansi_id);
    }

    private function sameTenant(User $user, ?string $instansiId): bool
    {
        // No tenantless-Executive bypass: default-deny for Instansi Belum
        // Ditetapkan, matching InstansiScope semantics. Super Admin bypasses
        // via Gate::before.
        return $user->instansi_id !== null && $user->instansi_id === $instansiId;
    }
}
