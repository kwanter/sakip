<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed instansi data first
        $this->call(InstansiSeeder::class);

        // Then seed roles and permissions
        $this->call(RolesAndPermissionsSeeder::class);

        // Finally seed users with assigned roles
        $this->call(UserSeeder::class);
    }
}
