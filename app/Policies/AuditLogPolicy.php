<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * AuditLogPolicy
 * 
 * Handles authorization for audit log viewing and management operations.
 * Implements role-based access control for audit trail functionality.
 */
class AuditLogPolicy
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
        return $user->hasPermissionTo('view-audit-trails');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AuditLog  $auditLog
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, AuditLog $auditLog)
    {
        return $user->hasPermissionTo('view-audit-trails');
    }

    /**
     * Determine whether the user can export audit logs.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function export(User $user)
    {
        return $user->hasPermissionTo('export-audit-data');
    }

    /**
     * Determine whether the user can view audit statistics.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewStatistics(User $user)
    {
        return $user->hasPermissionTo('view-audit-statistics');
    }

    /**
     * Determine whether the user can view compliance information.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewCompliance(User $user)
    {
        return $user->hasPermissionTo('view-compliance-reports');
    }
}