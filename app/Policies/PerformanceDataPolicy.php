<?php

namespace App\Policies;

use App\Models\PerformanceData;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Carbon\Carbon;

/**
 * PerformanceDataPolicy
 * 
 * Handles authorization for performance data collection, submission, and validation operations.
 * Implements role-based access control with workflow stage permissions and deadline enforcement.
 */
class PerformanceDataPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any performance data.
     * Available to all SAKIP users with appropriate permissions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.data.view',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view the performance data.
     * Users can only view data from their own institution (unless admin).
     */
    public function view(User $user, PerformanceData $data): bool
    {
        // Admin can view any data
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view data from their own institution
        if ($user->instansi_id !== $data->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.data.view',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can create performance data.
     * Restricted to admins, pimpinan, and data collectors from the same institution.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.data.create',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.data_collector'
        ]);
    }

    /**
     * Determine whether the user can update the performance data.
     * Restricted based on workflow stage and user role.
     */
    public function update(User $user, PerformanceData $data): bool
    {
        // Admin can update any data
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only update data from their own institution
        if ($user->instansi_id !== $data->instansi_id) {
            return false;
        }

        // Cannot update if data is locked (validated or audited)
        if ($data->status === 'validated' || $data->status === 'audited') {
            return false;
        }

        // Pimpinan can update any data from their institution
        if ($user->hasPermission('sakip.pimpinan')) {
            return true;
        }

        // Data collectors can only update draft or submitted data they created
        if ($user->hasPermission('sakip.data_collector')) {
            return $data->created_by === $user->id && 
                   in_array($data->status, ['draft', 'submitted']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the performance data.
     * Restricted to admins and only for draft status.
     */
    public function delete(User $user, PerformanceData $data): bool
    {
        // Only admin can delete data
        if (!$user->hasPermission('sakip.admin')) {
            return false;
        }

        // Can only delete draft data
        return $data->status === 'draft';
    }

    /**
     * Determine whether the user can submit performance data for validation.
     * Restricted to data collectors and pimpinan from the same institution.
     */
    public function submit(User $user, PerformanceData $data): bool
    {
        // Admin can submit any data
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only submit data from their own institution
        if ($user->instansi_id !== $data->instansi_id) {
            return false;
        }

        // Can only submit draft data
        if ($data->status !== 'draft') {
            return false;
        }

        // Check deadline restrictions
        if (!$this->checkDeadline($user)) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.data.submit',
            'sakip.pimpinan',
            'sakip.data_collector'
        ]);
    }

    /**
     * Determine whether the user can validate performance data.
     * Restricted to assessors and pimpinan from the same institution.
     */
    public function validate(User $user, PerformanceData $data): bool
    {
        // Admin can validate any data
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only validate data from their own institution
        if ($user->instansi_id !== $data->instansi_id) {
            return false;
        }

        // Can only validate submitted data
        if ($data->status !== 'submitted') {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.data.validate',
            'sakip.pimpinan',
            'sakip.assessor'
        ]);
    }

    /**
     * Determine whether the user can reject performance data.
     * Restricted to assessors and pimpinan from the same institution.
     */
    public function reject(User $user, PerformanceData $data): bool
    {
        // Admin can reject any data
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only reject data from their own institution
        if ($user->instansi_id !== $data->instansi_id) {
            return false;
        }

        // Can only reject submitted data
        if ($data->status !== 'submitted') {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.data.reject',
            'sakip.pimpinan',
            'sakip.assessor'
        ]);
    }

    /**
     * Determine whether the user can audit performance data.
     * Restricted to auditors and pimpinan from the same institution.
     */
    public function audit(User $user, PerformanceData $data): bool
    {
        // Admin can audit any data
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only audit data from their own institution
        if ($user->instansi_id !== $data->instansi_id) {
            return false;
        }

        // Can only audit validated data
        if ($data->status !== 'validated') {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.data.audit',
            'sakip.pimpinan',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view data submission history.
     * Available to users from the same institution with appropriate permissions.
     */
    public function viewSubmissionHistory(User $user, PerformanceData $data): bool
    {
        // Admin can view any submission history
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view submission history from their own institution
        if ($user->instansi_id !== $data->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.data.history',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view data validation history.
     * Available to assessors, pimpinan, and auditors from the same institution.
     */
    public function viewValidationHistory(User $user, PerformanceData $data): bool
    {
        // Admin can view any validation history
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view validation history from their own institution
        if ($user->instansi_id !== $data->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.data.validation_history',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can attach evidence documents to data.
     * Restricted to data collectors and pimpinan from the same institution.
     */
    public function attachEvidence(User $user, PerformanceData $data): bool
    {
        // Admin can attach evidence to any data
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only attach evidence to data from their own institution
        if ($user->instansi_id !== $data->instansi_id) {
            return false;
        }

        // Can only attach evidence to draft or submitted data
        if (!in_array($data->status, ['draft', 'submitted'])) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.data.attach_evidence',
            'sakip.pimpinan',
            'sakip.data_collector'
        ]);
    }

    /**
     * Determine whether the user can detach evidence documents from data.
     * Restricted to data collectors and pimpinan from the same institution.
     */
    public function detachEvidence(User $user, PerformanceData $data): bool
    {
        // Admin can detach evidence from any data
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only detach evidence from data from their own institution
        if ($user->instansi_id !== $data->instansi_id) {
            return false;
        }

        // Can only detach evidence from draft or submitted data
        if (!in_array($data->status, ['draft', 'submitted'])) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.data.detach_evidence',
            'sakip.pimpinan',
            'sakip.data_collector'
        ]);
    }

    /**
     * Determine whether the user can view data analytics.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function viewAnalytics(User $user, PerformanceData $data): bool
    {
        // Admin can view any analytics
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view analytics from their own institution
        if ($user->instansi_id !== $data->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.data.analytics',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can export data.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function exportData(User $user, PerformanceData $data): bool
    {
        // Admin can export any data
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only export data from their own institution
        if ($user->instansi_id !== $data->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.data.export',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can bulk import data.
     * Restricted to admins and data collectors.
     */
    public function bulkImport(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.data.bulk_import',
            'sakip.admin',
            'sakip.data_collector'
        ]);
    }

    /**
     * Determine whether the user can bulk update data.
     * Restricted to admins and pimpinan.
     */
    public function bulkUpdate(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.data.bulk_update',
            'sakip.admin',
            'sakip.pimpinan'
        ]);
    }

    /**
     * Determine whether the user can view data compliance status.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function viewComplianceStatus(User $user, PerformanceData $data): bool
    {
        // Admin can view any compliance status
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view compliance status from their own institution
        if ($user->instansi_id !== $data->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.data.compliance',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Check deadline restrictions for data submission.
     * Returns true if within allowed submission period.
     */
    private function checkDeadline(User $user): bool
    {
        // Admin has no deadline restrictions
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        $now = Carbon::now();
        
        // Example: Allow submission only during specific periods
        // Q1: January 1 - March 31
        // Q2: April 1 - June 30
        // Q3: July 1 - September 30
        // Q4: October 1 - December 31
        
        $currentQuarter = ceil($now->month / 3);
        $quarterStart = Carbon::create($now->year, ($currentQuarter - 1) * 3 + 1, 1);
        $quarterEnd = Carbon::create($now->year, $currentQuarter * 3, 1)->endOfMonth();
        
        // Allow submission up to 15 days after quarter end for late submissions
        $submissionDeadline = $quarterEnd->copy()->addDays(15);
        
        return $now->lte($submissionDeadline);
    }
}