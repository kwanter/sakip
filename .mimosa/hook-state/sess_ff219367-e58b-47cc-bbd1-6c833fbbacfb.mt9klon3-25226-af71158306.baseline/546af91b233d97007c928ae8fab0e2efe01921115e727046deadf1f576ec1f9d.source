<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Assessment;
use App\Models\PerformanceData;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssessmentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can("view-assessment-reports")
            || $user->can("submit-evaluation-findings");
    }

    public function view(User $user, Assessment $assessment)
    {
        if (! $this->sameTenant($user, $this->tenantId($assessment))) {
            return false;
        }

        if ($user->can("view-assessment-reports")) {
            return true;
        }

        return $user->can("submit-evaluation-findings")
            && $assessment->assessed_by === $user->id;
    }

    public function create(User $user)
    {
        return $user->can("submit-evaluation-findings");
    }

    public function update(User $user, Assessment $assessment)
    {
        if (! $this->sameTenant($user, $this->tenantId($assessment))) {
            return false;
        }

        if ($user->can("submit-evaluation-findings") && $assessment->assessed_by === $user->id) {
            return in_array($assessment->status, ["draft", "needs_revision"], true);
        }

        return false;
    }

    public function delete(User $user, Assessment $assessment)
    {
        return $user->can("manage-high-level-settings")
            && $this->sameTenant($user, $this->tenantId($assessment));
    }

    public function submitForReview(User $user, Assessment $assessment)
    {
        if (! $this->sameTenant($user, $this->tenantId($assessment))) {
            return false;
        }

        return $user->can("submit-evaluation-findings")
            && $assessment->assessed_by === $user->id
            && $assessment->status === "draft";
    }

    public function review(User $user, Assessment $assessment)
    {
        if (! $this->sameTenant($user, $this->tenantId($assessment))) {
            return false;
        }

        return $user->can("review-all-system-data")
            && $assessment->status === "pending_review";
    }

    public function assess(User $user, PerformanceData $performanceData)
    {
        if (! $this->sameTenant($user, $performanceData->instansi_id)) {
            return false;
        }

        return $user->can("submit-evaluation-findings");
    }

    private function tenantId(Assessment $assessment): ?string
    {
        if (isset($assessment->instansi_id) && $assessment->instansi_id) {
            return $assessment->instansi_id;
        }

        $assessment->loadMissing("performanceData");

        return $assessment->performanceData?->instansi_id;
    }

    private function sameTenant(User $user, ?string $instansiId): bool
    {
        if ($user->hasRole("Super Admin")) {
            return true;
        }

        if ($user->instansi_id === null) {
            return $user->hasAnyRole(["Executive", "Auditor"]);
        }

        return $instansiId !== null && $user->instansi_id === $instansiId;
    }
}
