<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()['cache']->forget('spatie.permission.cache');

        // Truncate tables to start from scratch
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        Role::truncate();
        DB::table('role_has_permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Create permissions
        $permissions = [
            // Assessor
            'view-assessment-reports',
            'submit-evaluation-findings',
            'comment-on-assessment-results',
            'access-assessment-tools',

            // Data Collector
            'enter-and-submit-data-records',
            'edit-own-data-submissions',
            'view-data-collection-forms',
            'access-basic-reporting-features',

            // Auditor
            'review-all-system-data',
            'generate-audit-reports',
            'flag-data-inconsistencies',
            'access-historical-records',
            'export-audit-findings',

            // Collector
            'gather-and-input-field-data',
            'update-collection-records',
            'view-collection-schedules',
            'submit-completed-work-reports',

            // Executive
            'view-all-reports-and-dashboards',
            'access-analytics-tools',
            'approve-system-changes',
            'manage-high-level-settings',
            'export-comprehensive-reports',

            // Government Official
            'view-verified-public-data',
            'access-official-reports',
            'submit-formal-requests',
            'review-compliance-documents',
            'access-restricted-government-portals',
        ];

        foreach ($permissions as $permission) {
            $newPermission = new Permission();
            $newPermission->name = $permission;
            $newPermission->save();
        }

        // Create roles and assign existing permissions

        // Assessor
        $assessorRole = new Role();
        $assessorRole->name = 'Assessor';
        $assessorRole->save();
        $assessorRole->givePermissionTo([
            'view-assessment-reports',
            'submit-evaluation-findings',
            'comment-on-assessment-results',
            'access-assessment-tools',
        ]);

        // Data Collector
        $dataCollectorRole = new Role();
        $dataCollectorRole->name = 'Data Collector';
        $dataCollectorRole->save();
        $dataCollectorRole->givePermissionTo([
            'enter-and-submit-data-records',
            'edit-own-data-submissions',
            'view-data-collection-forms',
            'access-basic-reporting-features',
        ]);

        // Auditor
        $auditorRole = new Role();
        $auditorRole->name = 'Auditor';
        $auditorRole->save();
        $auditorRole->givePermissionTo([
            'review-all-system-data',
            'generate-audit-reports',
            'flag-data-inconsistencies',
            'access-historical-records',
            'export-audit-findings',
        ]);

        // Collector
        $collectorRole = new Role();
        $collectorRole->name = 'Collector';
        $collectorRole->save();
        $collectorRole->givePermissionTo([
            'gather-and-input-field-data',
            'update-collection-records',
            'view-collection-schedules',
            'submit-completed-work-reports',
        ]);

        // Executive
        $executiveRole = new Role();
        $executiveRole->name = 'Executive';
        $executiveRole->save();
        $executiveRole->givePermissionTo([
            'view-all-reports-and-dashboards',
            'access-analytics-tools',
            'approve-system-changes',
            'manage-high-level-settings',
            'export-comprehensive-reports',
        ]);

        // Government Official
        $governmentOfficialRole = new Role();
        $governmentOfficialRole->name = 'Government Official';
        $governmentOfficialRole->save();
        $governmentOfficialRole->givePermissionTo([
            'view-verified-public-data',
            'access-official-reports',
            'submit-formal-requests',
            'review-compliance-documents',
            'access-restricted-government-portals',
        ]);

        // Super Admin
        $superAdminRole = new Role();
        $superAdminRole->name = 'Super Admin';
        $superAdminRole->save();
        $superAdminRole->givePermissionTo(Permission::all());
    }
}
