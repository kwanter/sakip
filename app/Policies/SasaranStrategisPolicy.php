<?php

namespace App\Policies;

use App\Models\SasaranStrategis;
use App\Models\User;

class SasaranStrategisPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // any authenticated user
    }

    public function view(User $user, SasaranStrategis $sasaranStrategis): bool
    {
        return true; // any authenticated user
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Executive', 'Government Official', 'Assessor']);
    }

    public function update(User $user, SasaranStrategis $sasaranStrategis): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Executive', 'Government Official', 'Assessor']);
    }

    public function delete(User $user, SasaranStrategis $sasaranStrategis): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Executive']);
    }
}
