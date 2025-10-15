<?php

namespace App\Policies;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Carbon\Carbon;

/**
 * AssessmentPolicy
 * 
 * Handles authorization for assessment workflow and evaluation operations.
 * Implements role-based access control with workflow stage permissions and evaluation restrictions.
 */
class AssessmentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any assessments.
     * Available to all SAKIP users with appropriate permissions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.assessments.view',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view the assessment.
     * Users can only view assessments from their own institution (unless admin).
     */
    public function view(User $user, Assessment $assessment): bool
    {
        // Admin can view any assessment
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view assessments from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.assessments.view',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can create assessments.
     * Restricted to admins, pimpinan, and assessors from the same institution.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.assessments.create',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.assessor'
        ]);
    }

    /**
     * Determine whether the user can update the assessment.
     * Restricted based on workflow stage and user role.
     */
    public function update(User $user, Assessment $assessment): bool
    {
        // Admin can update any assessment
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only update assessments from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        // Cannot update if assessment is completed or audited
        if (in_array($assessment->status, ['completed', 'audited'])) {
            return false;
        }

        // Pimpinan can update any assessment from their institution
        if ($user->hasPermission('sakip.pimpinan')) {
            return true;
        }

        // Assessors can only update assessments they created and in draft/submitted status
        if ($user->hasPermission('sakip.assessor')) {
            return $assessment->assessor_id === $user->id && 
                   in_array($assessment->status, ['draft', 'submitted']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the assessment.
     * Restricted to admins and only for draft status.
     */
    public function delete(User $user, Assessment $assessment): bool
    {
        // Only admin can delete assessments
        if (!$user->hasPermission('sakip.admin')) {
            return false;
        }

        // Can only delete draft assessments
        return $assessment->status === 'draft';
    }

    /**
     * Determine whether the user can submit assessment for review.
     * Restricted to assessors and pimpinan from the same institution.
     */
    public function submit(User $user, Assessment $assessment): bool
    {
        // Admin can submit any assessment
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only submit assessments from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        // Can only submit draft assessments
        if ($assessment->status !== 'draft') {
            return false;
        }

        // Check if all required criteria have been evaluated
        if (!$assessment->criteria->every(fn($criterion) => $criterion->pivot->score !== null)) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.assessments.submit',
            'sakip.pimpinan',
            'sakip.assessor'
        ]);
    }

    /**
     * Determine whether the user can review assessment.
     * Restricted to pimpinan from the same institution.
     */
    public function review(User $user, Assessment $assessment): bool
    {
        // Admin can review any assessment
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only review assessments from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        // Can only review submitted assessments
        if ($assessment->status !== 'submitted') {
            return false;
        }

        return $user->hasPermission('sakip.pimpinan');
    }

    /**
     * Determine whether the user can approve assessment.
     * Restricted to pimpinan from the same institution.
     */
    public function approve(User $user, Assessment $assessment): bool
    {
        // Admin can approve any assessment
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only approve assessments from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        // Can only approve submitted assessments
        if ($assessment->status !== 'submitted') {
            return false;
        }

        return $user->hasPermission('sakip.pimpinan');
    }

    /**
     * Determine whether the user can reject assessment.
     * Restricted to pimpinan from the same institution.
     */
    public function reject(User $user, Assessment $assessment): bool
    {
        // Admin can reject any assessment
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only reject assessments from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        // Can only reject submitted assessments
        if ($assessment->status !== 'submitted') {
            return false;
        }

        return $user->hasPermission('sakip.pimpinan');
    }

    /**
     * Determine whether the user can audit assessment.
     * Restricted to auditors and pimpinan from the same institution.
     */
    public function audit(User $user, Assessment $assessment): bool
    {
        // Admin can audit any assessment
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only audit assessments from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        // Can only audit approved assessments
        if ($assessment->status !== 'approved') {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.assessments.audit',
            'sakip.pimpinan',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can evaluate assessment criteria.
     * Restricted to assessors and pimpinan from the same institution.
     */
    public function evaluateCriteria(User $user, Assessment $assessment): bool
    {
        // Admin can evaluate any criteria
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only evaluate criteria for assessments from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        // Can only evaluate criteria for draft assessments
        if ($assessment->status !== 'draft') {
            return false;
        }

        // Assessors can only evaluate criteria for assessments they are assigned to
        if ($user->hasPermission('sakip.assessor')) {
            return $assessment->assessor_id === $user->id;
        }

        // Pimpinan can evaluate any criteria from their institution
        return $user->hasPermission('sakip.pimpinan');
    }

    /**
     * Determine whether the user can view assessment criteria details.
     * Available to users from the same institution with appropriate permissions.
     */
    public function viewCriteria(User $user, Assessment $assessment): bool
    {
        // Admin can view any criteria
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view criteria for assessments from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.assessments.criteria.view',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can add assessment criteria.
     * Restricted to admins and pimpinan from the same institution.
     */
    public function addCriteria(User $user, Assessment $assessment): bool
    {
        // Admin can add criteria to any assessment
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only add criteria to assessments from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        // Can only add criteria to draft assessments
        if ($assessment->status !== 'draft') {
            return false;
        }

        return $user->hasPermission('sakip.pimpinan');
    }

    /**
     * Determine whether the user can remove assessment criteria.
     * Restricted to admins and pimpinan from the same institution.
     */
    public function removeCriteria(User $user, Assessment $assessment): bool
    {
        // Admin can remove criteria from any assessment
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only remove criteria from assessments from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        // Can only remove criteria from draft assessments
        if ($assessment->status !== 'draft') {
            return false;
        }

        return $user->hasPermission('sakip.pimpinan');
    }

    /**
     * Determine whether the user can view assessment history.
     * Available to users from the same institution with appropriate permissions.
     */
    public function viewHistory(User $user, Assessment $assessment): bool
    {
        // Admin can view any assessment history
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view assessment history from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.assessments.history',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view assessment analytics.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function viewAnalytics(User $user, Assessment $assessment): bool
    {
        // Admin can view any assessment analytics
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view assessment analytics from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.assessments.analytics',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can export assessment data.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function exportData(User $user, Assessment $assessment): bool
    {
        // Admin can export any assessment data
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only export assessment data from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.assessments.export',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can bulk create assessments.
     * Restricted to admins and pimpinan.
     */
    public function bulkCreate(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.assessments.bulk_create',
            'sakip.admin',
            'sakip.pimpinan'
        ]);
    }

    /**
     * Determine whether the user can bulk update assessments.
     * Restricted to admins and pimpinan.
     */
    public function bulkUpdate(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.assessments.bulk_update',
            'sakip.admin',
            'sakip.pimpinan'
        ]);
    }

    /**
     * Determine whether the user can view assessment compliance status.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function viewComplianceStatus(User $user, Assessment $assessment): bool
    {
        // Admin can view any compliance status
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view compliance status from their own institution
        if ($user->instansi_id !== $assessment->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.assessments.compliance',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Check if assessment is within evaluation period.
     * Returns true if within allowed evaluation timeframe.
     */
    public function evaluateDuringPeriod(User $user, Assessment $assessment): bool
    {
        // Admin has no period restrictions
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        $now = Carbon::now();
        
        // Check if assessment period is active
        if ($assessment->period_start && $assessment->period_end) {
            return $now->between($assessment->period_start, $assessment->period_end);
        }

        // Default quarterly evaluation periods
        $currentQuarter = ceil($now->month / 3);
        $quarterStart = Carbon::create($now->year, ($currentQuarter - 1) * 3 + 1, 1);
        $quarterEnd = Carbon::create($now->year, $currentQuarter * 3, 1)->endOfMonth();
        
        return $now->between($quarterStart, $quarterEnd);
    }
}