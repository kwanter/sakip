<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminService
{
    public function createUser(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);
            
            if (isset($data['roles'])) {
                $this->assignRoles($user, $data['roles']);
            }
            
            $this->logAction('user.created', [
                'user_id' => $user->id,
                'email' => $user->email,
                'name' => $user->name
            ]);
            
            return $user;
        });
    }
    
    public function updateUser(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $oldData = $user->toArray();
            
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            
            $user->update($data);
            
            if (isset($data['roles'])) {
                $this->assignRoles($user, $data['roles']);
            }
            
            $this->logAction('user.updated', [
                'user_id' => $user->id,
                'old_data' => $oldData,
                'new_data' => $data
            ]);
            
            return $user;
        });
    }
    
    public function assignRoles(User $user, array $roleNames): void
    {
        $roles = Role::whereIn('name', $roleNames)->get();
        $user->roles()->sync($roles);
        
        $this->logAction('user.roles.updated', [
            'user_id' => $user->id,
            'roles' => $roleNames
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
                    'user_data' => $userData
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
            'active_sessions' => DB::table('sessions')->where('last_activity', '>', now()->subMinutes(30)->timestamp)->count(),
            'recent_logins' => AuditLog::where('action', 'login')->where('created_at', '>', now()->subDays(7))->count(),
            'system_load' => sys_getloadavg()[0] ?? 0,
        ];
    }
}