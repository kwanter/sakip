<?php

namespace App\Policies;

use App\Models\EvidenceDocument;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Carbon\Carbon;

/**
 * EvidenceDocumentPolicy
 * 
 * Handles authorization for file upload and evidence management operations.
 * Implements role-based access control with file type restrictions and storage limits.
 */
class EvidenceDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any evidence documents.
     * Available to all SAKIP users with appropriate permissions.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.evidence.view',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view the evidence document.
     * Users can only view documents from their own institution (unless admin).
     */
    public function view(User $user, EvidenceDocument $document): bool
    {
        // Admin can view any document
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view documents from their own institution
        if ($user->instansi_id !== $document->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.evidence.view',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can create evidence documents.
     * Restricted to admins, pimpinan, and data collectors from the same institution.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.evidence.create',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.data_collector'
        ]);
    }

    /**
     * Determine whether the user can update the evidence document.
     * Restricted based on document status and user role.
     */
    public function update(User $user, EvidenceDocument $document): bool
    {
        // Admin can update any document
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only update documents from their own institution
        if ($user->instansi_id !== $document->instansi_id) {
            return false;
        }

        // Cannot update if document is locked (validated or audited)
        if ($document->status === 'validated' || $document->status === 'audited') {
            return false;
        }

        // Pimpinan can update any document from their institution
        if ($user->hasPermission('sakip.pimpinan')) {
            return true;
        }

        // Data collectors can only update documents they uploaded and in draft status
        if ($user->hasPermission('sakip.data_collector')) {
            return $document->uploaded_by === $user->id && $document->status === 'draft';
        }

        return false;
    }

    /**
     * Determine whether the user can delete the evidence document.
     * Restricted to admins and only for draft status.
     */
    public function delete(User $user, EvidenceDocument $document): bool
    {
        // Only admin can delete documents
        if (!$user->hasPermission('sakip.admin')) {
            return false;
        }

        // Can only delete draft documents
        return $document->status === 'draft';
    }

    /**
     * Determine whether the user can upload evidence documents.
     * Restricted to data collectors and pimpinan from the same institution.
     */
    public function upload(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.evidence.upload',
            'sakip.admin',
            'sakip.pimpinan',
            'sakip.data_collector'
        ]);
    }

    /**
     * Determine whether the user can download evidence documents.
     * Available to users from the same institution with appropriate permissions.
     */
    public function download(User $user, EvidenceDocument $document): bool
    {
        // Admin can download any document
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only download documents from their own institution
        if ($user->instansi_id !== $document->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.evidence.download',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can validate evidence documents.
     * Restricted to assessors and pimpinan from the same institution.
     */
    public function validate(User $user, EvidenceDocument $document): bool
    {
        // Admin can validate any document
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only validate documents from their own institution
        if ($user->instansi_id !== $document->instansi_id) {
            return false;
        }

        // Can only validate submitted documents
        if ($document->status !== 'submitted') {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.evidence.validate',
            'sakip.pimpinan',
            'sakip.assessor'
        ]);
    }

    /**
     * Determine whether the user can reject evidence documents.
     * Restricted to assessors and pimpinan from the same institution.
     */
    public function reject(User $user, EvidenceDocument $document): bool
    {
        // Admin can reject any document
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only reject documents from their own institution
        if ($user->instansi_id !== $document->instansi_id) {
            return false;
        }

        // Can only reject submitted documents
        if ($document->status !== 'submitted') {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.evidence.reject',
            'sakip.pimpinan',
            'sakip.assessor'
        ]);
    }

    /**
     * Determine whether the user can audit evidence documents.
     * Restricted to auditors and pimpinan from the same institution.
     */
    public function audit(User $user, EvidenceDocument $document): bool
    {
        // Admin can audit any document
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only audit documents from their own institution
        if ($user->instansi_id !== $document->instansi_id) {
            return false;
        }

        // Can only audit validated documents
        if ($document->status !== 'validated') {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.evidence.audit',
            'sakip.pimpinan',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can attach documents to performance data.
     * Restricted to data collectors and pimpinan from the same institution.
     */
    public function attachToPerformanceData(User $user, EvidenceDocument $document): bool
    {
        // Admin can attach any document
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only attach documents from their own institution
        if ($user->instansi_id !== $document->instansi_id) {
            return false;
        }

        // Can only attach draft documents
        if ($document->status !== 'draft') {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.evidence.attach',
            'sakip.pimpinan',
            'sakip.data_collector'
        ]);
    }

    /**
     * Determine whether the user can attach documents to assessments.
     * Restricted to assessors and pimpinan from the same institution.
     */
    public function attachToAssessment(User $user, EvidenceDocument $document): bool
    {
        // Admin can attach any document
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only attach documents from their own institution
        if ($user->instansi_id !== $document->instansi_id) {
            return false;
        }

        // Can only attach validated documents
        if ($document->status !== 'validated') {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.evidence.attach',
            'sakip.pimpinan',
            'sakip.assessor'
        ]);
    }

    /**
     * Determine whether the user can view document metadata.
     * Available to users from the same institution with appropriate permissions.
     */
    public function viewMetadata(User $user, EvidenceDocument $document): bool
    {
        // Admin can view any document metadata
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view document metadata from their own institution
        if ($user->instansi_id !== $document->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.evidence.metadata',
            'sakip.pimpinan',
            'sakip.data_collector',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view document validation history.
     * Available to assessors, pimpinan, and auditors from the same institution.
     */
    public function viewValidationHistory(User $user, EvidenceDocument $document): bool
    {
        // Admin can view any validation history
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view validation history from their own institution
        if ($user->instansi_id !== $document->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.evidence.validation_history',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can view document audit trail.
     * Available to auditors, pimpinan, and admins.
     */
    public function viewAuditTrail(User $user, EvidenceDocument $document): bool
    {
        // Admin can view any audit trail
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view audit trail from their own institution
        if ($user->instansi_id !== $document->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.evidence.audit_trail',
            'sakip.pimpinan',
            'sakip.auditor'
        ]);
    }

    /**
     * Determine whether the user can bulk upload documents.
     * Restricted to admins and data collectors.
     */
    public function bulkUpload(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.evidence.bulk_upload',
            'sakip.admin',
            'sakip.data_collector'
        ]);
    }

    /**
     * Determine whether the user can bulk validate documents.
     * Restricted to admins and assessors.
     */
    public function bulkValidate(User $user): bool
    {
        return $user->hasAnyPermission([
            'sakip.evidence.bulk_validate',
            'sakip.admin',
            'sakip.assessor'
        ]);
    }

    /**
     * Check file size and type restrictions for upload.
     * Returns true if file meets system requirements.
     */
    public function checkFileRestrictions(User $user, array $fileInfo): bool
    {
        // Admin has no file restrictions
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Check file size (max 10MB for regular users, 50MB for pimpinan)
        $maxSize = $user->hasPermission('sakip.pimpinan') ? 50 * 1024 * 1024 : 10 * 1024 * 1024;
        if ($fileInfo['size'] > $maxSize) {
            return false;
        }

        // Check allowed file types
        $allowedTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
            'image/jpg',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        return in_array($fileInfo['type'], $allowedTypes);
    }

    /**
     * Check storage quota for user.
     * Returns true if user has not exceeded storage limit.
     */
    public function checkStorageQuota(User $user): bool
    {
        // Admin has unlimited storage
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Get user's current storage usage
        $currentUsage = EvidenceDocument::where('uploaded_by', $user->id)
            ->where('instansi_id', $user->instansi_id)
            ->sum('file_size');

        // Storage quota based on role (in bytes)
        $quota = $user->hasPermission('sakip.pimpinan') ? 5 * 1024 * 1024 * 1024 : 1 * 1024 * 1024 * 1024; // 5GB for pimpinan, 1GB for others

        return $currentUsage < $quota;
    }

    /**
     * Determine whether the user can view document compliance status.
     * Available to pimpinan, admins, assessors, and auditors.
     */
    public function viewComplianceStatus(User $user, EvidenceDocument $document): bool
    {
        // Admin can view any compliance status
        if ($user->hasPermission('sakip.admin')) {
            return true;
        }

        // Users can only view compliance status from their own institution
        if ($user->instansi_id !== $document->instansi_id) {
            return false;
        }

        return $user->hasAnyPermission([
            'sakip.evidence.compliance',
            'sakip.pimpinan',
            'sakip.assessor',
            'sakip.auditor'
        ]);
    }
}