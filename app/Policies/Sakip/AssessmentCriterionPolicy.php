<?php

namespace App\Policies\Sakip;

use App\Models\User;
use App\Models\AssessmentCriterion;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssessmentCriterionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, AssessmentCriterion $assessmentCriterion): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'assessor']);
    }

    public function update(User $user, AssessmentCriterion $assessmentCriterion): bool
    {
        return $user->hasAnyRole(['superadmin', 'assessor']);
    }

    public function delete(User $user, AssessmentCriterion $assessmentCriterion): bool
    {
        return $user->hasRole('superadmin');
    }

    public function restore(User $user, AssessmentCriterion $assessmentCriterion): bool
    {
        return $user->hasRole('superadmin');
    }

    public function forceDelete(User $user, AssessmentCriterion $assessmentCriterion): bool
    {
        return $user->hasRole('superadmin');
    }
}