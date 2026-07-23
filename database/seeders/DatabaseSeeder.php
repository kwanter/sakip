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

        // Then seed roles and permissions (idempotent; never truncates in production)
        $this->call(RolesAndPermissionsSeeder::class);

        // Privileged/demo users are NOT seeded by default.
        // Explicitly run only in local/dev:
        //   php artisan db:seed --class=AdminUserSeeder
        //   php artisan db:seed --class=UserSeeder
        if (! app()->environment("production")) {
            $this->call(AdminUserSeeder::class);
            $this->call(UserSeeder::class);
        }
    }
}
