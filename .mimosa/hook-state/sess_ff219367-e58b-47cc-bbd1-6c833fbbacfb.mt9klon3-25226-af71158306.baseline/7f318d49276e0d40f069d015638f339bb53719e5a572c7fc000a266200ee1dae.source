<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Idempotent: never truncates role/permission tables (production-safe).
     */
    public function run()
    {
        app()["cache"]->forget("spatie.permission.cache");

        $permissions = [
            "view-dashboard",
            "view-sakip-dashboard",
            "view-performance-indicators",
            "view-performance-data",
            "view-assessments",
            "view-reports",
            "export-sakip-data",
            "manage-instansi",
            "manage-sasaran-strategis",
            "manage-program",
            "admin.dashboard",
            "admin.settings",
            "manage-users",
            "manage-roles",
            "manage-permissions",
            "view-assessment-reports",
            "submit-evaluation-findings",
            "comment-on-assessment-results",
            "access-assessment-tools",
            "enter-and-submit-data-records",
            "edit-own-data-submissions",
            "view-data-collection-forms",
            "access-basic-reporting-features",
            "review-all-system-data",
            "generate-audit-reports",
            "flag-data-inconsistencies",
            "access-historical-records",
            "export-audit-findings",
            "gather-and-input-field-data",
            "update-collection-records",
            "view-collection-schedules",
            "submit-completed-work-reports",
            "view-all-reports-and-dashboards",
            "access-analytics-tools",
            "approve-system-changes",
            "manage-high-level-settings",
            "export-comprehensive-reports",
            "view-verified-public-data",
            "access-official-reports",
            "submit-formal-requests",
            "review-compliance-documents",
            "access-restricted-government-portals",
            "approve-targets",
            "view-audit-trails",
            "export-audit-data",
            "view-audit-statistics",
            "view-compliance-reports",
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(["name" => $permission, "guard_name" => "web"]);
        }

        $rolePermissions = [
            "Assessor" => [
                "view-dashboard",
                "view-sakip-dashboard",
                "view-assessments",
                "view-assessment-reports",
                "submit-evaluation-findings",
                "comment-on-assessment-results",
                "access-assessment-tools",
            ],
            "Data Collector" => [
                "view-dashboard",
                "view-sakip-dashboard",
                "view-performance-data",
                "enter-and-submit-data-records",
                "edit-own-data-submissions",
                "view-data-collection-forms",
                "access-basic-reporting-features",
            ],
            "Auditor" => [
                "view-dashboard",
                "view-sakip-dashboard",
                "view-performance-data",
                "view-reports",
                "export-sakip-data",
                "review-all-system-data",
                "generate-audit-reports",
                "flag-data-inconsistencies",
                "access-historical-records",
                "export-audit-findings",
                "view-audit-trails",
                "export-audit-data",
                "view-audit-statistics",
                "view-compliance-reports",
            ],
            "Collector" => [
                "view-dashboard",
                "view-sakip-dashboard",
                "view-performance-data",
                "gather-and-input-field-data",
                "update-collection-records",
                "view-collection-schedules",
                "submit-completed-work-reports",
            ],
            "Executive" => [
                "view-dashboard",
                "view-sakip-dashboard",
                "view-performance-indicators",
                "view-performance-data",
                "view-assessments",
                "view-reports",
                "export-sakip-data",
                "manage-instansi",
                "manage-sasaran-strategis",
                "manage-program",
                "view-all-reports-and-dashboards",
                "access-analytics-tools",
                "approve-system-changes",
                "manage-high-level-settings",
                "export-comprehensive-reports",
                "approve-targets",
            ],
            "Government Official" => [
                "view-dashboard",
                "view-sakip-dashboard",
                "view-reports",
                "view-verified-public-data",
                "access-official-reports",
                "submit-formal-requests",
                "review-compliance-documents",
                "access-restricted-government-portals",
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(["name" => $roleName, "guard_name" => "web"]);
            $role->syncPermissions($perms);
        }

        $superAdminRole = Role::firstOrCreate([
            "name" => "Super Admin",
            "guard_name" => "web",
        ]);
        $superAdminRole->syncPermissions(Permission::all());
    }
}
