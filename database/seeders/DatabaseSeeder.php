<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create default roles
        $admin = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $manager = Role::firstOrCreate(['name' => 'manager'], ['display_name' => 'Manager']);
        $userRole = Role::firstOrCreate(['name' => 'user'], ['display_name' => 'User']);

        // Create comprehensive admin permissions
        $adminPermissions = [
            // System Management
            ['name' => 'manage_system', 'display_name' => 'Manage System', 'module' => 'system'],
            ['name' => 'manage_users', 'display_name' => 'Manage Users', 'module' => 'users'],
            ['name' => 'manage_roles', 'display_name' => 'Manage Roles', 'module' => 'roles'],
            ['name' => 'manage_permissions', 'display_name' => 'Manage Permissions', 'module' => 'permissions'],
            ['name' => 'view_system_logs', 'display_name' => 'View System Logs', 'module' => 'system'],
            ['name' => 'manage_database', 'display_name' => 'Manage Database', 'module' => 'system'],
            
            // Module Access
            ['name' => 'view_all_dashboard', 'display_name' => 'View All Dashboard Data', 'module' => 'dashboard'],
            ['name' => 'manage_all_instansis', 'display_name' => 'Manage All Institutions', 'module' => 'instansi'],
            ['name' => 'manage_all_programs', 'display_name' => 'Manage All Programs', 'module' => 'program'],
            ['name' => 'manage_all_kegiatans', 'display_name' => 'Manage All Activities', 'module' => 'kegiatan'],
            ['name' => 'manage_all_indikators', 'display_name' => 'Manage All Indicators', 'module' => 'indikator'],
            ['name' => 'manage_all_laporans', 'display_name' => 'Manage All Reports', 'module' => 'laporan'],
            
            // Administrative
            ['name' => 'override_permissions', 'display_name' => 'Override Permissions', 'module' => 'admin'],
            ['name' => 'impersonate_users', 'display_name' => 'Impersonate Users', 'module' => 'admin'],
            ['name' => 'manage_api_access', 'display_name' => 'Manage API Access', 'module' => 'admin'],
            ['name' => 'configure_system_settings', 'display_name' => 'Configure System Settings', 'module' => 'system'],
        ];

        // Create permissions
        $permissionIds = [];
        foreach ($adminPermissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
            $permissionIds[] = $permission->id;
        }

        // Create standard permissions
        $standardPermissions = [
            ['name' => 'manage_settings', 'display_name' => 'Manage Settings', 'module' => 'settings'],
            ['name' => 'manage_programs', 'display_name' => 'Manage Programs', 'module' => 'program'],
            ['name' => 'manage_kegiatans', 'display_name' => 'Manage Activities', 'module' => 'kegiatan'],
            ['name' => 'manage_instansis', 'display_name' => 'Manage Institutions', 'module' => 'instansi'],
        ];

        foreach ($standardPermissions as $permissionData) {
            Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        // Attach all permissions to admin role
        $admin->permissions()->sync($permissionIds);
        
        // Attach standard permissions to manager role
        $manager->permissions()->syncWithoutDetaching([
            Permission::where('name', 'manage_programs')->first()->id,
            Permission::where('name', 'manage_kegiatans')->first()->id,
            Permission::where('name', 'manage_instansis')->first()->id,
        ]);

        // Seed admin user
        $user = User::firstOrCreate(
            ['email' => 'admin@sakip.com'],
            [
                'name' => 'System Administrator',
                'password' => bcrypt('admin123')
            ]
        );
        $user->roles()->sync([$admin->id]);

        // Seed test manager user
        $managerUser = User::firstOrCreate(
            ['email' => 'manager@sakip.com'],
            [
                'name' => 'Manager User',
                'password' => bcrypt('manager123')
            ]
        );
        $managerUser->roles()->sync([$manager->id]);

        // Seed test regular user
        $regularUser = User::firstOrCreate(
            ['email' => 'user@sakip.com'],
            [
                'name' => 'Regular User',
                'password' => bcrypt('user123')
            ]
        );
        $regularUser->roles()->sync([$userRole->id]);

        // Create SAKIP permissions
        $sakipPermissions = [
            // Performance Indicators
            ['name' => 'manage_sakip_indicators', 'display_name' => 'Manage SAKIP Indicators', 'module' => 'sakip'],
            ['name' => 'view_sakip_indicators', 'display_name' => 'View SAKIP Indicators', 'module' => 'sakip'],
            ['name' => 'create_sakip_indicators', 'display_name' => 'Create SAKIP Indicators', 'module' => 'sakip'],
            ['name' => 'edit_sakip_indicators', 'display_name' => 'Edit SAKIP Indicators', 'module' => 'sakip'],
            ['name' => 'delete_sakip_indicators', 'display_name' => 'Delete SAKIP Indicators', 'module' => 'sakip'],
            
            // Targets
            ['name' => 'manage_sakip_targets', 'display_name' => 'Manage SAKIP Targets', 'module' => 'sakip'],
            ['name' => 'view_sakip_targets', 'display_name' => 'View SAKIP Targets', 'module' => 'sakip'],
            ['name' => 'create_sakip_targets', 'display_name' => 'Create SAKIP Targets', 'module' => 'sakip'],
            ['name' => 'edit_sakip_targets', 'display_name' => 'Edit SAKIP Targets', 'module' => 'sakip'],
            ['name' => 'approve_sakip_targets', 'display_name' => 'Approve SAKIP Targets', 'module' => 'sakip'],
            
            // Performance Data
            ['name' => 'manage_sakip_data', 'display_name' => 'Manage SAKIP Performance Data', 'module' => 'sakip'],
            ['name' => 'view_sakip_data', 'display_name' => 'View SAKIP Performance Data', 'module' => 'sakip'],
            ['name' => 'submit_sakip_data', 'display_name' => 'Submit SAKIP Performance Data', 'module' => 'sakip'],
            ['name' => 'validate_sakip_data', 'display_name' => 'Validate SAKIP Performance Data', 'module' => 'sakip'],
            ['name' => 'edit_sakip_data', 'display_name' => 'Edit SAKIP Performance Data', 'module' => 'sakip'],
            
            // Assessments
            ['name' => 'manage_sakip_assessments', 'display_name' => 'Manage SAKIP Assessments', 'module' => 'sakip'],
            ['name' => 'view_sakip_assessments', 'display_name' => 'View SAKIP Assessments', 'module' => 'sakip'],
            ['name' => 'create_sakip_assessments', 'display_name' => 'Create SAKIP Assessments', 'module' => 'sakip'],
            ['name' => 'edit_sakip_assessments', 'display_name' => 'Edit SAKIP Assessments', 'module' => 'sakip'],
            ['name' => 'approve_sakip_assessments', 'display_name' => 'Approve SAKIP Assessments', 'module' => 'sakip'],
            
            // Reports
            ['name' => 'manage_sakip_reports', 'display_name' => 'Manage SAKIP Reports', 'module' => 'sakip'],
            ['name' => 'view_sakip_reports', 'display_name' => 'View SAKIP Reports', 'module' => 'sakip'],
            ['name' => 'generate_sakip_reports', 'display_name' => 'Generate SAKIP Reports', 'module' => 'sakip'],
            ['name' => 'submit_sakip_reports', 'display_name' => 'Submit SAKIP Reports', 'module' => 'sakip'],
            ['name' => 'download_sakip_reports', 'display_name' => 'Download SAKIP Reports', 'module' => 'sakip'],
            
            // Evidence Documents
            ['name' => 'manage_sakip_evidence', 'display_name' => 'Manage SAKIP Evidence', 'module' => 'sakip'],
            ['name' => 'view_sakip_evidence', 'display_name' => 'View SAKIP Evidence', 'module' => 'sakip'],
            ['name' => 'upload_sakip_evidence', 'display_name' => 'Upload SAKIP Evidence', 'module' => 'sakip'],
            ['name' => 'delete_sakip_evidence', 'display_name' => 'Delete SAKIP Evidence', 'module' => 'sakip'],
            
            // SAKIP Dashboard
            ['name' => 'access_sakip_dashboard', 'display_name' => 'Access SAKIP Dashboard', 'module' => 'sakip'],
            ['name' => 'view_sakip_analytics', 'display_name' => 'View SAKIP Analytics', 'module' => 'sakip'],
            ['name' => 'export_sakip_data', 'display_name' => 'Export SAKIP Data', 'module' => 'sakip'],
            
            // SAKIP Administration
            ['name' => 'manage_sakip_settings', 'display_name' => 'Manage SAKIP Settings', 'module' => 'sakip'],
            ['name' => 'view_sakip_audit_logs', 'display_name' => 'View SAKIP Audit Logs', 'module' => 'sakip'],
            ['name' => 'manage_sakip_users', 'display_name' => 'Manage SAKIP Users', 'module' => 'sakip'],
        ];

        // Create SAKIP permissions
        $sakipPermissionIds = [];
        foreach ($sakipPermissions as $permissionData) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
            $sakipPermissionIds[] = $permission->id;
        }

        // Attach SAKIP permissions to admin role
        $admin->permissions()->syncWithoutDetaching($sakipPermissionIds);
        
        // Attach basic SAKIP permissions to manager role
        $manager->permissions()->syncWithoutDetaching([
            Permission::where('name', 'view_sakip_indicators')->first()->id,
            Permission::where('name', 'view_sakip_targets')->first()->id,
            Permission::where('name', 'view_sakip_data')->first()->id,
            Permission::where('name', 'submit_sakip_data')->first()->id,
            Permission::where('name', 'view_sakip_assessments')->first()->id,
            Permission::where('name', 'view_sakip_reports')->first()->id,
            Permission::where('name', 'generate_sakip_reports')->first()->id,
            Permission::where('name', 'download_sakip_reports')->first()->id,
            Permission::where('name', 'view_sakip_evidence')->first()->id,
            Permission::where('name', 'upload_sakip_evidence')->first()->id,
            Permission::where('name', 'access_sakip_dashboard')->first()->id,
            Permission::where('name', 'view_sakip_analytics')->first()->id,
            Permission::where('name', 'export_sakip_data')->first()->id,
        ]);

        // Jalankan seeder Permission tambahan untuk indikator/laporan dan SAKIP
        $this->call([
            PermissionSeeder::class,
        ]);
    }
}
