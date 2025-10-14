<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;
    
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('admin.users.view') || $user->isAdmin();
    }
    
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('admin.users.view') || $user->isAdmin() || $user->id === $model->id;
    }
    
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('admin.users.create') || $user->isAdmin();
    }
    
    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('admin.users.update') || $user->isAdmin() || $user->id === $model->id;
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
        
        return $user->hasPermission('admin.users.delete') || $user->isAdmin();
    }
    
    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasPermission('admin.users.restore') || $user->isAdmin();
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
        
        return $user->hasPermission('admin.users.force-delete') || $user->isAdmin();
    }
    
    /**
     * Determine whether the user can assign roles.
     */
    public function assignRoles(User $user): bool
    {
        return $user->hasPermission('admin.users.assign-roles') || $user->isAdmin();
    }
    
    /**
     * Determine whether the user can assign permissions.
     */
    public function assignPermissions(User $user): bool
    {
        return $user->hasPermission('admin.users.assign-permissions') || $user->isAdmin();
    }
    
    /**
     * Determine whether the user can view audit logs.
     */
    public function viewAuditLogs(User $user): bool
    {
        return $user->hasPermission('admin.audit-logs.view') || $user->isAdmin();
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