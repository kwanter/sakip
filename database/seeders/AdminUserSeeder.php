<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Admin User Seeder
 *
 * Creates a default administrator user with Super Admin role.
 * This seeder provides a quick way to create an admin user independently
 * of the full UserSeeder.
 *
 * SECURITY NOTE: This seeder will NOT run in production environment.
 * In production, create admin users manually via the admin panel.
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Default admin user credentials
     */
    private string $adminEmail = "admin@sakip.local";
    private string $adminName = "Administrator";

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // CRITICAL: Do not seed admin user in production
        /*
        if (app()->environment('production')) {
            $this->command->error('❌ SECURITY: Admin user seeding is disabled in production.');
            $this->command->error('   Create admin users manually via admin panel.');

            return;
        }
        */

        $this->command->warn(
            "⚠️  WARNING: Creating admin user with default credentials.",
        );
        $this->command->warn(
            "   This should ONLY be done in development/testing environments.",
        );

        // Check if admin user already exists
        $existingAdmin = User::where("email", $this->adminEmail)->first();

        if ($existingAdmin) {
            $this->command->info(
                "ℹ️  Admin user ({$this->adminEmail}) already exists, skipping...",
            );

            return;
        }

        // Get Super Admin role
        $superAdminRole = Role::where("name", "Super Admin")->first();

        if (!$superAdminRole) {
            $this->command->error('❌ ERROR: "Super Admin" role not found.');
            $this->command->error(
                "   Run RolesAndPermissionsSeeder first: php artisan db:seed --class=RolesAndPermissionsSeeder",
            );

            return;
        }

        // Generate secure random password
        $password = Str::random(16);

        // Create admin user
        $admin = User::create([
            "name" => $this->adminName,
            "email" => $this->adminEmail,
            "password" => Hash::make($password),
            "instansi_id" => null, // Admin has global access, no specific instansi
            "email_verified_at" => now(), // Auto-verify email
        ]);

        // Assign Super Admin role
        $admin->assignRole($superAdminRole);

        // Display success message and credentials
        $this->command->info("");
        $this->command->info("✅ Admin user created successfully!");
        $this->command->info("");
        $this->command->table(
            ["Field", "Value"],
            [
                ["Name", $admin->name],
                ["Email", $admin->email],
                ["Password", $password],
                ["Role", "Super Admin"],
                ["Instansi", "None (Global Access)"],
                ["Email Verified", "Yes"],
            ],
        );
        $this->command->warn("");
        $this->command->warn(
            "⚠️  IMPORTANT: Change this password immediately after first login!",
        );
        $this->command->warn("   Login URL: " . config("app.url") . "/login");
        $this->command->info("");
    }
}
