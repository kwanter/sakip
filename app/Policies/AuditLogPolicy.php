<?php

namespace App\Policies;

use App\Constants\SystemRoles;
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
     * Ability name for the cross-instansi audit view. Exposed as a constant so
     * call sites do not repeat a literal that the reserved-permission drift
     * guard would otherwise have to treat as a mintable permission name.
     */
    public const ABILITY_VIEW_ALL = 'viewAll';

    /**
     * Determine whether the user can view any models.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        // Super Admins can view all audit logs
        if ($user->hasRole(SystemRoles::SUPER_ADMIN)) {
            return true;
        }

        return $user->hasPermissionTo('view-audit-trails');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, AuditLog $auditLog)
    {
        return $user->hasPermissionTo('view-audit-trails');
    }

    /**
     * Determine whether the user sees audit rows across every institution.
     *
     * SECURITY: this used to be a hardcoded Super Admin role check, so the
     * boundary could not be delegated through the permission model at all.
     * It is now a permission; the role stays as a fallback so an installation
     * that has not re-run the seeder keeps its current behaviour.
     */
    public function viewAll(User $user): bool
    {
        return $user->hasPermission('view-all-audit-logs')
            || $user->hasRole(SystemRoles::SUPER_ADMIN);
    }

    /**
     * Determine whether the user can export audit logs.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function export(User $user)
    {
        return $user->hasPermissionTo('export-audit-data');
    }

    /**
     * Determine whether the user can view audit statistics.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewStatistics(User $user)
    {
        return $user->hasPermissionTo('view-audit-statistics');
    }

    /**
     * Determine whether the user can view compliance information.
     *
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewCompliance(User $user)
    {
        return $user->hasPermissionTo('view-compliance-reports');
    }
}
