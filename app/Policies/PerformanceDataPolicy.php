<?php

namespace App\Policies;

use App\Models\PerformanceData;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PerformanceDataPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can("view-data-collection-forms")
            || $user->can("view-performance-data");
    }

    public function view(User $user, PerformanceData $performanceData)
    {
        if (! $this->sameTenant($user, $performanceData->instansi_id)) {
            return false;
        }

        if ($user->can("view-data-collection-forms") || $user->can("view-performance-data")) {
            return true;
        }

        return $performanceData->created_by === $user->id
            || $performanceData->submitted_by === $user->id;
    }

    public function create(User $user)
    {
        return $user->can("enter-and-submit-data-records");
    }

    public function update(User $user, PerformanceData $performanceData)
    {
        if (! $this->sameTenant($user, $performanceData->instansi_id)) {
            return false;
        }

        return $user->can("edit-own-data-submissions")
            && ($performanceData->created_by === $user->id
                || $performanceData->submitted_by === $user->id);
    }

    public function delete(User $user, PerformanceData $performanceData)
    {
        return $user->can("manage-high-level-settings")
            && $this->sameTenant($user, $performanceData->instansi_id);
    }

    private function sameTenant(User $user, ?string $instansiId): bool
    {
        if ($user->hasRole("Super Admin")) {
            return true;
        }

        if ($user->instansi_id === null) {
            return $user->hasAnyRole(["Executive", "Auditor"]);
        }

        return $user->instansi_id === $instansiId;
    }
}
