<?php

namespace App\Policies;

use App\Models\AssessmentCriterion;
use App\Models\User;

/**
 * AssessmentCriterionPolicy
 *
 * Criteria inherit their tenant from the parent assessment. Without this
 * policy, auto-discovery finds nothing (manual Gate registration bypasses
 * it) and criterion CRUD is unpoliced.
 */
class AssessmentCriterionPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // listing is always tenant-scoped at query level
    }

    public function view(User $user, AssessmentCriterion $criterion): bool
    {
        return $this->sameTenant($user, $this->tenantId($criterion));
    }

    public function create(User $user): bool
    {
        return $user->hasAnyPermission(['sakip.assessor', 'sakip.pimpinan', 'sakip.admin'])
            || $user->hasRole('Super Admin');
    }

    public function update(User $user, AssessmentCriterion $criterion): bool
    {
        if (! $user->hasAnyPermission(['sakip.assessor', 'sakip.pimpinan', 'sakip.admin'])
            && ! $user->hasRole('Super Admin')) {
            return false;
        }

        return $this->sameTenant($user, $this->tenantId($criterion));
    }

    public function delete(User $user, AssessmentCriterion $criterion): bool
    {
        if (! $user->hasAnyPermission(['sakip.admin']) && ! $user->hasRole('Super Admin')) {
            return false;
        }

        return $this->sameTenant($user, $this->tenantId($criterion));
    }

    private function tenantId(AssessmentCriterion $criterion): ?string
    {
        if ($criterion->relationLoaded('assessment') || $criterion->assessment) {
            return $criterion->assessment?->instansi_id
                ?? $criterion->assessment?->performanceData?->instansi_id;
        }

        return null;
    }

    private function sameTenant(User $user, ?string $instansiId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
        if ($user->instansi_id === null) {
            return $user->hasAnyRole(['Executive', 'Auditor']);
        }

        return $instansiId !== null && $user->instansi_id === $instansiId;
    }
}
