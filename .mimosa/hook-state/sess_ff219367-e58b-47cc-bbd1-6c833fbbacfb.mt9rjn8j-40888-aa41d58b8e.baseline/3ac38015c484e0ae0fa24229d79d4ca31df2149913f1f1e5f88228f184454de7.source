<?php

namespace App\Services;

use App\Constants\SystemRoles;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'instansi_id' => $data['instansi_id'] ?? null,
            ]);

            if (isset($data['roles'])) {
                $this->assignRoles($user, $data['roles']);
            }

            $this->logAction('user.created', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'instansi_id' => $user->instansi_id,
            ]);

            return $user;
        });
    }

    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $oldData = $user->toArray();

            // Build update payload without password by default
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            // Handle instansi_id (can be null to remove assignment)
            if (array_key_exists('instansi_id', $data)) {
                $updateData['instansi_id'] = $data['instansi_id'];
            }

            // Only set password if provided and not empty (controller already validated 'confirmed')
            if (
                array_key_exists('password', $data) &&
                ! empty($data['password'])
            ) {
                $updateData['password'] = Hash::make($data['password']);
            }

            // Handle email verification checkbox from admin edit form
            // If checkbox is present (checked), set to now(); if absent, set to null
            if (array_key_exists('email_verified', $data)) {
                $updateData['email_verified_at'] = now();
            } else {
                $updateData['email_verified_at'] = null;
            }

            // Perform update (Eloquent sets updated_at automatically)
            $user->update($updateData);

            if (isset($data['roles'])) {
                $this->assignRoles($user, $data['roles']);
            }

            if (isset($data['permissions'])) {
                $this->assignPermissions($user, $data['permissions']);
            }

            $this->logAction('user.updated', [
                'user_id' => $user->id,
                'old_data' => $oldData,
                'new_data' => $updateData,
            ]);

            return $user;
        });
    }

    public function assignRoles(User $user, array $roleIds): void
    {
        // SECURITY: Only a Super Admin may grant the Super Admin role.
        // A caller with can:manage-users (e.g. Admin) must never escalate
        // themselves or others to full-system privileges.
        $actor = auth()->user();
        $isSuperAdmin = (bool) $actor?->hasRole(SystemRoles::SUPER_ADMIN);

        if (! $isSuperAdmin) {
            $superAdminRoleId = Role::query()
                ->where('name', SystemRoles::SUPER_ADMIN)
                ->value('id');

            // Strip Super Admin from the requested set; keep it out of sync
            // and out of the audit log as an attempted escalation.
            if ($superAdminRoleId !== null) {
                $roleIds = array_diff($roleIds, [$superAdminRoleId]);
            }
        }

        $roles = Role::whereIn('id', $roleIds)->get();
        $user->roles()->sync($roles);

        $this->logAction('user.roles.updated', [
            'user_id' => $user->id,
            'roles' => $roleIds,
        ]);
    }

    public function assignPermissions(User $user, array $permissionIds): void
    {
        $permissions = Permission::whereIn('id', $permissionIds)->get();
        $user->permissions()->sync($permissions);

        $this->logAction('user.permissions.updated', [
            'user_id' => $user->id,
            'permissions' => $permissionIds,
        ]);
    }

    public function deleteUser(User $user): bool
    {
        return DB::transaction(function () use ($user) {
            $userId = $user->id;
            $userData = $user->toArray();

            $result = $user->delete();

            if ($result) {
                $this->logAction('user.deleted', [
                    'user_id' => $userId,
                    'user_data' => $userData,
                ]);
            }

            return $result;
        });
    }

    public function logAction(string $action, array $details = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function getSystemStats(): array
    {
        return [
            'total_users' => User::count(),
            'total_roles' => Role::count(),
            'total_permissions' => Permission::count(),
            'active_sessions' => DB::table('sessions')
                ->where('last_activity', '>', now()->subMinutes(30)->timestamp)
                ->count(),
            'recent_logins' => AuditLog::where('action', 'login')
                ->where('created_at', '>', now()->subDays(7))
                ->count(),
            'system_load' => sys_getloadavg()[0] ?? 0,
        ];
    }
}
