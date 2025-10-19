<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Assessment;
use Illuminate\Auth\Access\HandlesAuthorization;

class AssessmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('view-assessment-reports');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Assessment  $assessment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Assessment $assessment)
    {
        if ($user->can('view-assessment-reports')) {
            return true;
        }

        // Assessor can view their own assessments
        if ($user->can('submit-evaluation-findings') && $assessment->assessed_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user)
    {
        return $user->can('submit-evaluation-findings');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Assessment  $assessment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Assessment $assessment)
    {
        // Assessor can update their own assessments if it's in draft or needs revision
        if ($user->can('submit-evaluation-findings') && $assessment->assessed_by === $user->id) {
            return in_array($assessment->status, ['draft', 'needs_revision']);
        }
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Assessment  $assessment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Assessment $assessment)
    {
        return $user->can('manage-high-level-settings');
    }

    /**
     * Determine whether the user can submit the assessment for review.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Assessment  $assessment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function submitForReview(User $user, Assessment $assessment)
    {
        if ($user->can('submit-evaluation-findings') && $assessment->assessed_by === $user->id) {
            return $assessment->status === 'draft';
        }
        return false;
    }

    /**
     * Determine whether the user can review the assessment.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Assessment  $assessment
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function review(User $user, Assessment $assessment)
    {
        // Auditor can review assessments that are pending review
        if ($user->can('review-all-system-data')) {
            return $assessment->status === 'pending_review';
        }
        return false;
    }

    /**
     * Determine whether the user can assess performance data.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PerformanceData  $performanceData
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function assess(User $user, \App\Models\PerformanceData $performanceData)
    {
        return $user->can('submit-evaluation-findings');
    }
}