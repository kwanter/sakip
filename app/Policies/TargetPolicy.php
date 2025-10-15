<?php

namespace App\Policies;

use App\Models\Target;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Carbon\Carbon;

/**
 * TargetPolicy
 * 
 * Handles authorization for target setting and management operations.
 * Implements role-based access control with period-based restrictions and approval workflows.
 */
class TargetPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any targets.
     * Available to all SAKIP users with appropriate permissions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.targets.view',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view the target.
     * Users can only view targets from their own institution (unless admin).
     */
    public function view(User $user, Target $target): bool
    {
        // Admin can view any target
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view targets from their own institution
        if ($user->instansi_id !== $target->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.targets.view',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can create targets.
     * Restricted to admins and pimpinan from the same institution.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.targets.create',
            'sakip.admin',
            'sakip.pimpinan'
        ]);
    }

    /**
     * Determine whether the user can update the target.
     * Restricted based on target status and user role.
     */
    public function update(User $user, Target $target): bool
    {
        // Admin can update any target
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only update targets from their own institution
        if ($user->instansi_id !== $target->instansi_id) {
            return false;
        }

        // Cannot update if target is locked (approved or audited)
        if (in_array($target->status, ['approved', 'audited'])) {
            return false;
        }

        // Pimpinan can update any target from their institution
        if ($user->hasPermission('sakip.pimpinan')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the target.
     * Restricted to admins and only for draft status.
     */
    public function delete(User $user, Target $target): bool
    {
        // Only admin can delete targets
        if (!$user->hasPermission('sakip.admin')) {
            return false;
        }

        // Can only delete draft targets
        return $target->status === 'draft';
    }

    /**
     * Determine whether the user can set targets for performance indicators.
     * Restricted to admins and pimpinan from the same institution.
     */
    public function setTarget(User $user, Target $target): bool
    {
        // Admin can set any target
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only set targets for their own institution
        if ($user->instansi_id !== $target->instansi_id) {
            return false;
        }

        // Can only set targets for draft targets
        if ($target->status !== 'draft') {
            return false;
        }

        return $user->hasPermission('sakip.pimpinan');
    }

    /**
     * Determine whether the user can approve targets.
     * Restricted to pimpinan from the same institution.
     */
    public function approve(User $user, Target $target): bool
    {
        // Admin can approve any target
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only approve targets from their own institution
        if ($user->instansi_id !== $target->instansi_id) {
            return false;
        }

        // Can only approve submitted targets
        if ($target->status !== 'submitted') {
            return false;
        }

        return $user->hasPermission('sakip.pimpinan');
    }

    /**
     * Determine whether the user can reject targets.
     * Restricted to pimpinan from the same institution.
     */
    public function reject(User $user, Target $target): bool
    {
        // Admin can reject any target
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only reject targets from their own institution
        if ($user->instansi_id !== $target->instansi_id) {
            return false;
        }

        // Can only reject submitted targets
        if ($target->status !== 'submitted') {
            return false;
        }

        return $user->hasPermission('sakip.pimpinan');
    }

    /**
     * Determine whether the user can submit targets for approval.
     * Restricted to pimpinan from the same institution.
     */
    public function submit(User $user, Target $target): bool
    {
        // Admin can submit any target
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only submit targets from their own institution
        if ($user->instansi_id !== $target->instansi_id) {
            return false;
        }

        // Can only submit draft targets
        if ($target->status !== 'draft') {
            return false;
        }

        // Check deadline restrictions
        if (!$this->checkDeadline($user)) {
            return false;
        }

        return $user->hasPermission('sakip.pimpinan');
    }

    /**
     * Determine whether the user can audit targets.
     * Restricted to auditors and pimpinan from the same institution.
     */
    public function audit(User $user, Target $target): bool
    {
        // Admin can audit any target
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only audit targets from their own institution
        if ($user->instansi_id !== $target->instansi_id) {
            return false;
        }

        // Can only audit approved targets
        if ($target->status !== 'approved') {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.targets.audit',
            'sakip.pimpinan',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view target history.
     * Available to users from the same institution with appropriate permissions.
     */
    public function viewHistory(User $user, Target $target): bool
    {
        // Admin can view any target history
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view target history from their own institution
        if ($user->instansi_id !== $target->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.targets.history',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view target analytics.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function viewAnalytics(User $user, Target $target): bool
    {
        // Admin can view any target analytics
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view target analytics from their own institution
        if ($user->instansi_id !== $target->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.targets.analytics',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can export target data.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function exportData(User $user, Target $target): bool
    {
        // Admin can export any target data
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only export target data from their own institution
        if ($user->instansi_id !== $target->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.targets.export',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can bulk set targets.
     * Restricted to admins and pimpinan.
     */
    public function bulkSet(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.targets.bulk_set',
            'sakip.admin',
            'sakip.pimpinan'
        ]);
    }

    /**
     * Determine whether the user can bulk approve targets.
     * Restricted to admins and pimpinan.
     */
    public function bulkApprove(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.targets.bulk_approve',
            'sakip.admin',
            'sakip.pimpinan'
        ]);
    }

    /**
     * Determine whether the user can view target compliance status.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function viewComplianceStatus(User $user, Target $target): bool
    {
        // Admin can view any compliance status
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view compliance status from their own institution
        if ($user->instansi_id !== $target->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.targets.compliance',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Check deadline restrictions for target setting.
     * Returns true if within allowed submission period.
     */
    private function checkDeadline(User $user): bool
    {
        // Admin has no deadline restrictions
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        $now = Carbon::now();
        
        // Example: Allow target setting only during specific periods
        // Annual targets: November 1 - December 31 (for next year)
        // Quarterly targets: 15 days before quarter start
        
        $currentMonth = $now->month;
        
        // Allow annual target setting in November and December
        if ($currentMonth >= 11 && $currentMonth <= 12) {
            return true;
        }
        
        // Allow quarterly target setting up to 15 days before quarter start
        $nextQuarterStart = Carbon::create($now->year, (ceil($now->month / 3) * 3) + 1, 1);
        $deadline = $nextQuarterStart->copy()->subDays(15);
        
        return $now->lte($deadline);
    }

    /**
     * Determine whether the user can compare targets with actual performance.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function compareWithPerformance(User $user, Target $target): bool
    {
        // Admin can compare any target
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only compare targets from their own institution
        if ($user->instansi_id !== $target->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.targets.compare',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can adjust targets after approval.
     * Restricted to admins and pimpinan with special justification.
     */
    public function adjustAfterApproval(User $user, Target $target): bool
    {
        // Admin can adjust any approved target
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only adjust targets from their own institution
        if ($user->instansi_id !== $target->instansi_id) {
            return false;
        }

        // Only pimpinan can adjust approved targets with justification
        return $user->hasPermission('sakip.pimpinan') && $target->status === 'approved';
    }
}