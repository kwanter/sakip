<?php

namespace App\Policies;

use App\Constants\SystemRoles;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * SECURITY (audit run-3, admin-cross-tenant): 'manage-users' alone let a
     * delegated administrator read and edit accounts of every institution.
     * A delegated manager now only reaches their own institution; Super Admin
     * keeps explicit cross-instansi authority.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('manage-users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can('manage-users') && $this->managesTenant($user, $model);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('manage-users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) {
            return $this->manageProtectedAccount($user, $model);
        }

        return $this->manageProtectedAccount($user, $model)
            && $user->can('manage-users')
            && $this->managesTenant($user, $model);
    }

    /**
     * Tenant boundary for delegated (non-Super-Admin) administrators.
     *
     * Super Admin keeps cross-instansi authority. A delegated manager may
     * reach accounts of their own institution, plus accounts that are not
     * attached to any institution yet (those are unplaced and can be
     * adopted). An account already owned by another institution is off
     * limits.
     */
    public function managesTenant(User $user, User $model): bool
    {
        if ($user->hasRole(SystemRoles::SUPER_ADMIN)) {
            return true;
        }

        if ($user->instansi_id === null) {
            return false;
        }

        // Unplaced accounts may be adopted by any manager.
        if ($model->instansi_id === null) {
            return true;
        }

        return $user->instansi_id === $model->instansi_id;
    }

    /**
     * Only Super Admin actors may mutate an existing Super Admin account.
     */
    public function manageProtectedAccount(User $user, User $model): bool
    {
        return ! $model->hasRole(SystemRoles::SUPER_ADMIN)
            || $user->hasRole(SystemRoles::SUPER_ADMIN);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Users cannot delete themselves
        if ($user->id === $model->id) {
            return false;
        }

        return $this->manageProtectedAccount($user, $model)
            && $user->can('manage-users')
            && $this->managesTenant($user, $model);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can('manage-users') && $this->managesTenant($user, $model);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Users cannot permanently delete themselves
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can('manage-users') && $this->managesTenant($user, $model);
    }

    /**
     * Determine whether the user can assign roles.
     */
    public function assignRoles(User $user): bool
    {
        return $user->can('manage-roles');
    }

    /**
     * Determine whether the user can assign permissions.
     */
    public function assignPermissions(User $user): bool
    {
        return $user->can('manage-roles');
    }

    /**
     * Delegated grants cannot exceed the actor's effective permissions.
     */
    public function grantPermissions(User $user, array $permissionIds): bool
    {
        return $this->assignPermissions($user)
            && collect($permissionIds)->diff($user->getAllPermissions()->modelKeys())->isEmpty();
    }

    /**
     * Determine whether the user can view audit logs.
     */
    public function viewAuditLogs(User $user): bool
    {
        return $user->can('manage-settings');
    }

    /**
     * Determine whether the user can manage system settings.
     */
    public function manageSettings(User $user): bool
    {
        return $user->hasPermission('admin.settings.manage') || $user->isAdmin();
    }

    /**
     * Determine whether the user can access the admin dashboard.
     */
    public function accessDashboard(User $user): bool
    {
        return $user->hasPermission('admin.dashboard') || $user->isAdmin();
    }

    /**
     * Determine whether the user can impersonate other users.
     */
    public function impersonate(User $user, User $model): bool
    {
        // Users cannot impersonate themselves or other admins
        if ($user->id === $model->id || $model->isAdmin()) {
            return false;
        }

        return $user->hasPermission('admin.users.impersonate') || $user->isAdmin();
    }

    /**
     * Determine whether the user can manage user sessions.
     */
    public function manageSessions(User $user, User $model): bool
    {
        return $user->hasPermission('admin.users.sessions') || $user->isAdmin();
    }

    /**
     * Determine whether the user can export user data.
     */
    public function export(User $user): bool
    {
        return $user->hasPermission('admin.users.export') || $user->isAdmin();
    }

    /**
     * Determine whether the user can import user data.
     */
    public function import(User $user): bool
    {
        return $user->hasPermission('admin.users.import') || $user->isAdmin();
    }
}
