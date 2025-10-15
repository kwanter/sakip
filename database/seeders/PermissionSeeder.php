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
        // Permission untuk Indikator Kinerja
        $indikatorPermissions = [
            'indikator-kinerja.viewAny',
            'indikator-kinerja.view',
            'indikator-kinerja.create',
            'indikator-kinerja.update',
            'indikator-kinerja.delete',
            'indikator-kinerja.restore',
            'indikator-kinerja.forceDelete',
        ];

        // Permission untuk Laporan Kinerja
        $laporanPermissions = [
            'laporan-kinerja.viewAny',
            'laporan-kinerja.view',
            'laporan-kinerja.create',
            'laporan-kinerja.update',
            'laporan-kinerja.delete',
            'laporan-kinerja.restore',
            'laporan-kinerja.forceDelete',
        ];

        // Buat permission untuk Indikator Kinerja
        foreach ($indikatorPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['display_name' => ucwords(str_replace('-', ' ', str_replace('.', ' ', $permission)))]
            );
        }

        // Buat permission untuk Laporan Kinerja
        foreach ($laporanPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['display_name' => ucwords(str_replace('-', ' ', str_replace('.', ' ', $permission)))]
            );
        }

        // Assign semua permission ke role admin
        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $allPermissions = array_merge($indikatorPermissions, $laporanPermissions);
            $permissionIds = Permission::whereIn('name', $allPermissions)->pluck('id');
            $adminRole->permissions()->syncWithoutDetaching($permissionIds);
        }

        // Assign permission view ke role manager
        $managerRole = Role::where('name', 'manager')->first();
        if ($managerRole) {
            $viewPermissions = [
                'indikator-kinerja.viewAny',
                'indikator-kinerja.view',
                'laporan-kinerja.viewAny',
                'laporan-kinerja.view',
            ];
            $permissionIds = Permission::whereIn('name', $viewPermissions)->pluck('id');
            $managerRole->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
