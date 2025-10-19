<?php

namespace App\Policies\Sakip;

use App\Models\User;
use App\Models\EvidenceDocument;
use Illuminate\Auth\Access\HandlesAuthorization;

class EvidenceDocumentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, EvidenceDocument $evidenceDocument): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['superadmin', 'data_collector']);
    }

    public function update(User $user, EvidenceDocument $evidenceDocument): bool
    {
        return $user->hasAnyRole(['superadmin', 'data_collector']) || $user->id === $evidenceDocument->uploaded_by;
    }

    public function delete(User $user, EvidenceDocument $evidenceDocument): bool
    {
        return $user->hasAnyRole(['superadmin', 'data_collector']) || $user->id === $evidenceDocument->uploaded_by;
    }

    public function restore(User $user, EvidenceDocument $evidenceDocument): bool
    {
        return $user->hasRole('superadmin');
    }

    public function forceDelete(User $user, EvidenceDocument $evidenceDocument): bool
    {
        return $user->hasRole('superadmin');
    }
}