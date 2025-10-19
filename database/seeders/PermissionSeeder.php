<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $superadmin = Role::firstOrCreate([
            'name' => 'superadmin',
            'guard_name' => 'web'
        ]);

        $permissions = [
            'view-sakip-dashboard',
            'view-executive-dashboard',
            'view-performance-indicators',
            'view-performance-data',
            'view-assessments',
            'view-reports',
            'export-sakip-data',
        ];

        foreach ($permissions as $permissionName) {
            $permission = Permission::firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
            $superadmin->givePermissionTo($permission);
        }
    }
}
