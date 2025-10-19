<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AddSakipPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sakip:add-permissions {--user=} {--role=} {--all-users} {--fix-all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add required SAKIP dashboard permissions to users or roles';

    /**
     * Required SAKIP permissions for dashboard access
     */
    protected $requiredPermissions = [
        'sakip.dashboard.view',
        'sakip.admin',
        'sakip.pimpinan',
        'sakip.data_collector',
        'sakip.assessor',
        'sakip.auditor'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('SAKIP Permission Manager');
        $this->info('------------------------');

        // Create missing permissions if they don't exist
        $this->createMissingPermissions();

        if ($this->option('fix-all')) {
            $this->fixAllPermissions();
            return;
        }

        if ($this->option('all-users')) {
            $this->addPermissionsToAllUsers();
            return;
        }

        if ($userId = $this->option('user')) {
            $this->addPermissionsToUser($userId);
            return;
        }

        if ($roleName = $this->option('role')) {
            $this->addPermissionsToRole($roleName);
            return;
        }

        $this->showPermissionStatus();
    }

    /**
     * Create any missing permissions in the database
     */
    protected function createMissingPermissions()
    {
        $this->info('Checking for missing permissions...');
        
        $permissionsToCreate = [
            ['name' => 'sakip.dashboard.view', 'display_name' => 'View SAKIP Dashboard', 'module' => 'sakip'],
            ['name' => 'sakip.admin', 'display_name' => 'SAKIP Admin Access', 'module' => 'sakip'],
            ['name' => 'sakip.pimpinan', 'display_name' => 'SAKIP Executive Access', 'module' => 'sakip'],
            ['name' => 'sakip.data_collector', 'display_name' => 'SAKIP Data Collector Access', 'module' => 'sakip'],
            ['name' => 'sakip.assessor', 'display_name' => 'SAKIP Assessor Access', 'module' => 'sakip'],
            ['name' => 'sakip.auditor', 'display_name' => 'SAKIP Auditor Access', 'module' => 'sakip'],
            ['name' => 'sakip.dashboard.executive', 'display_name' => 'View Executive Dashboard', 'module' => 'sakip'],
            ['name' => 'sakip.dashboard.collector', 'display_name' => 'View Data Collector Dashboard', 'module' => 'sakip'],
            ['name' => 'sakip.dashboard.assessor', 'display_name' => 'View Assessor Dashboard', 'module' => 'sakip'],
            ['name' => 'sakip.dashboard.audit', 'display_name' => 'View Audit Dashboard', 'module' => 'sakip'],
            ['name' => 'sakip.dashboard.cross_institution', 'display_name' => 'View Cross-Institution Data', 'module' => 'sakip'],
        ];
        
        $created = 0;
        foreach ($permissionsToCreate as $permData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permData['name']],
                $permData
            );
            
            if ($permission->wasRecentlyCreated) {
                $this->info("Created permission: {$permData['name']}");
                $created++;
            }
        }
        
        if ($created > 0) {
            $this->info("Created {$created} new permissions");
        } else {
            $this->info("All required permissions already exist");
        }
    }

    /**
     * Add required permissions to a specific user
     */
    protected function addPermissionsToUser($userId)
    {
        $user = User::find($userId);
        
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return;
        }
        
        $this->info("Adding SAKIP dashboard permissions to user: {$user->name}");
        
        // Get permission IDs
        $permissionIds = Permission::whereIn('name', $this->requiredPermissions)
            ->pluck('id')
            ->toArray();
        
        // Attach permissions that don't already exist for this user
        $user->permissions()->syncWithoutDetaching($permissionIds);
        
        $this->info("Permissions added successfully");
    }

    /**
     * Add required permissions to a specific role
     */
    protected function addPermissionsToRole($roleName)
    {
        $role = Role::where('name', $roleName)->first();
        
        if (!$role) {
            $this->error("Role '{$roleName}' not found");
            return;
        }
        
        $this->info("Adding SAKIP dashboard permissions to role: {$role->name}");
        
        // Get permission IDs
        $permissionIds = Permission::whereIn('name', $this->requiredPermissions)
            ->pluck('id')
            ->toArray();
        
        // Attach permissions that don't already exist for this role
        $role->permissions()->syncWithoutDetaching($permissionIds);
        
        $this->info("Permissions added successfully to role");
    }

    /**
     * Add required permissions to all users
     */
    protected function addPermissionsToAllUsers()
    {
        $this->info("Adding SAKIP dashboard permissions to all users");
        
        $users = User::all();
        $permissionIds = Permission::whereIn('name', $this->requiredPermissions)
            ->pluck('id')
            ->toArray();
        
        foreach ($users as $user) {
            $this->info("Processing user: {$user->name}");
            $user->permissions()->syncWithoutDetaching($permissionIds);
        }
        
        $this->info("Permissions added to all users successfully");
    }

    /**
     * Fix all permission issues by ensuring superadmin role has all permissions
     * and all users have basic dashboard access
     */
    protected function fixAllPermissions()
    {
        $this->info("Fixing all SAKIP permission issues");
        
        // Ensure superadmin role has all permissions
        $superadminRole = Role::where('name', 'superadmin')->first();
        if ($superadminRole) {
            $this->info("Ensuring superadmin role has all permissions");
            $allPermissionIds = Permission::pluck('id')->toArray();
            $superadminRole->permissions()->syncWithoutDetaching($allPermissionIds);
        } else {
            $this->warn("Superadmin role not found");
        }
        
        // Ensure all users have basic dashboard access
        $basicPermission = Permission::where('name', 'sakip.dashboard.view')->first();
        if ($basicPermission) {
            $this->info("Ensuring all users have basic dashboard access");
            $users = User::all();
            foreach ($users as $user) {
                $user->permissions()->syncWithoutDetaching([$basicPermission->id]);
            }
        }
        
        $this->info("Permission fixes applied successfully");
    }

    /**
     * Show current permission status
     */
    protected function showPermissionStatus()
    {
        $this->info("Current SAKIP Permission Status");
        
        // Check if all required permissions exist
        $existingPermissions = Permission::whereIn('name', $this->requiredPermissions)->pluck('name')->toArray();
        $missingPermissions = array_diff($this->requiredPermissions, $existingPermissions);
        
        if (count($missingPermissions) > 0) {
            $this->warn("Missing permissions: " . implode(', ', $missingPermissions));
        } else {
            $this->info("All required permissions exist in the database");
        }
        
        // Show roles with SAKIP permissions
        $this->info("\nRoles with SAKIP permissions:");
        $roles = Role::with('permissions')->get();
        foreach ($roles as $role) {
            $sakipPermissions = $role->permissions->filter(function ($permission) {
                return strpos($permission->name, 'sakip.') === 0;
            })->pluck('name')->toArray();
            
            if (count($sakipPermissions) > 0) {
                $this->line("- {$role->name}: " . implode(', ', $sakipPermissions));
            }
        }
        
        // Show users without any SAKIP permissions
        $this->info("\nUsers without any SAKIP permissions:");
        $users = User::all();
        $usersWithoutPermissions = [];
        
        foreach ($users as $user) {
            $hasAnyPermission = false;
            
            // Check direct permissions
            $directPermissions = $user->permissions->filter(function ($permission) {
                return strpos($permission->name, 'sakip.') === 0;
            });
            
            if ($directPermissions->count() > 0) {
                $hasAnyPermission = true;
            }
            
            // Check permissions via roles
            if (!$hasAnyPermission) {
                $rolePermissions = $user->allPermissions()->filter(function ($permission) {
                    return strpos($permission->name, 'sakip.') === 0;
                });
                
                if ($rolePermissions->count() > 0) {
                    $hasAnyPermission = true;
                }
            }
            
            if (!$hasAnyPermission) {
                $usersWithoutPermissions[] = $user->name . ' (ID: ' . $user->id . ')';
            }
        }
        
        if (count($usersWithoutPermissions) > 0) {
            foreach ($usersWithoutPermissions as $userName) {
                $this->line("- {$userName}");
            }
        } else {
            $this->info("All users have at least one SAKIP permission");
        }
        
        $this->info("\nUse --fix-all option to automatically fix permission issues");
    }
}
