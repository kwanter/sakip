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
        return $user->can('view-data-collection-forms')
            || $user->can('view-performance-data');
    }

    public function view(User $user, PerformanceData $performanceData)
    {
        if (! $this->sameTenant($user, $performanceData->instansi_id)) {
            return false;
        }

        if ($user->can('view-data-collection-forms') || $user->can('view-performance-data')) {
            return true;
        }

        return $performanceData->created_by === $user->id
            || $performanceData->submitted_by === $user->id;
    }

    public function create(User $user)
    {
        return $user->can('enter-and-submit-data-records');
    }

    public function update(User $user, PerformanceData $performanceData)
    {
        if (! $this->sameTenant($user, $performanceData->instansi_id)) {
            return false;
        }

        return $user->can('edit-own-data-submissions')
            && ($performanceData->created_by === $user->id
                || $performanceData->submitted_by === $user->id);
    }

    public function delete(User $user, PerformanceData $performanceData)
    {
        return $user->can('manage-high-level-settings')
            && $this->sameTenant($user, $performanceData->instansi_id);
    }

    /**
     * Assessors/pimpinan may move a submitted record to validated or
     * rejected. Same tenant, and never the collector who submitted it (the
     * workflow's own separation of duties).
     */
    public function validate(User $user, PerformanceData $performanceData)
    {
        return $this->isReviewer($user, $performanceData);
    }

    public function reject(User $user, PerformanceData $performanceData)
    {
        return $this->isReviewer($user, $performanceData);
    }

    private function isReviewer(User $user, PerformanceData $performanceData): bool
    {
        if (! $this->sameTenant($user, $performanceData->instansi_id)) {
            return false;
        }

        if ($user->id === $performanceData->submitted_by) {
            return false;
        }

        return $user->can('submit-evaluation-findings');
    }

    private function sameTenant(User $user, ?string $instansiId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        if ($user->instansi_id === null) {
            return $user->hasAnyRole(['Executive', 'Auditor']);
        }

        return $user->instansi_id === $instansiId;
    }
}
