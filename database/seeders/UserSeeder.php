<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Instansi;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates sample users for each role in the SAKIP system:
     * - Super Admin (full system access)
     * - Assessor (evaluation and assessment)
     * - Data Collector (data entry and submission)
     * - Auditor (audit and compliance review)
     * - Executive (high-level reporting and analytics)
     * - Collector (field data collection)
     * - Government Official (official reports and compliance)
     *
     * SECURITY NOTE: In production, this seeder will NOT create default users
     * with weak passwords. You must create users manually or via admin panel.
     */
    public function run(): void
    {
        // CRITICAL: Do not seed test users in production
        if (app()->environment("production")) {
            $this->command->error(
                "❌ SECURITY: User seeding is disabled in production.",
            );
            $this->command->error(
                "   Create users manually via admin panel with strong passwords.",
            );
            return;
        }

        $this->command->warn(
            "⚠️  WARNING: Creating test users with default passwords.",
        );
        $this->command->warn(
            "   This should ONLY be done in development/testing environments.",
        );
        // Get first instansi for assignment, or create a default one
        $instansi = Instansi::first();

        if (!$instansi) {
            $instansi = Instansi::create([
                "kode_instansi" => "INST-001",
                "nama_instansi" => "Instansi Pusat Default",
                "alamat" => "Jakarta Pusat",
                "telepon" => "021-1234567",
                "email" => "admin@instansi.go.id",
                "kepala_instansi" => "Kepala Instansi Default",
                "status" => "aktif",
            ]);
        }

        // Generate secure random passwords for non-production environments
        $securePassword = Str::random(16);

        // Define users for each role
        $users = [
            [
                "role" => "Super Admin",
                "name" => "Super Administrator",
                "email" => "superadmin@sakip.local",
                "password" => $securePassword,
                "instansi_id" => null, // Super admin has access to all instansis
            ],
            [
                "role" => "Assessor",
                "name" => "Asesor SAKIP",
                "email" => "assessor@sakip.local",
                "password" => $securePassword,
                "instansi_id" => $instansi->id,
            ],
            [
                "role" => "Data Collector",
                "name" => "Pengumpul Data",
                "email" => "datacollector@sakip.local",
                "password" => $securePassword,
                "instansi_id" => $instansi->id,
            ],
            [
                "role" => "Auditor",
                "name" => "Auditor Internal",
                "email" => "auditor@sakip.local",
                "password" => $securePassword,
                "instansi_id" => $instansi->id,
            ],
            [
                "role" => "Executive",
                "name" => "Pimpinan Eksekutif",
                "email" => "executive@sakip.local",
                "password" => $securePassword,
                "instansi_id" => $instansi->id,
            ],
            [
                "role" => "Collector",
                "name" => "Kolektor Lapangan",
                "email" => "collector@sakip.local",
                "password" => $securePassword,
                "instansi_id" => $instansi->id,
            ],
            [
                "role" => "Government Official",
                "name" => "Pejabat Pemerintah",
                "email" => "official@sakip.local",
                "password" => $securePassword,
                "instansi_id" => $instansi->id,
            ],
        ];

        foreach ($users as $userData) {
            // Check if user already exists
            $existingUser = User::where("email", $userData["email"])->first();

            if ($existingUser) {
                $this->command->info(
                    "User {$userData["email"]} already exists, skipping...",
                );
                continue;
            }

            // Create user
            $user = User::create([
                "name" => $userData["name"],
                "email" => $userData["email"],
                "password" => Hash::make($userData["password"]),
                "instansi_id" => $userData["instansi_id"],
                "email_verified_at" => now(),
            ]);

            // Assign role
            $role = Role::where("name", $userData["role"])->first();

            if ($role) {
                $user->assignRole($role);
                $this->command->info(
                    "✅ Created {$userData["role"]}: {$userData["email"]} (password: {$userData["password"]})",
                );
            } else {
                $this->command->warn(
                    "⚠️  Role '{$userData["role"]}' not found. Run RolesAndPermissionsSeeder first.",
                );
            }
        }

        $this->command->info("\n📊 User Seeding Summary:");
        $this->command->info("Total users created: " . User::count());
        $this->command->info("\n🔐 Default Login Credentials:");
        $this->command->info(
            "   All users have the same password: {$securePassword}",
        );
        $this->command->warn(
            "\n⚠️  IMPORTANT: Change these passwords immediately!",
        );
        $this->command->table(
            ["Role", "Email"],
            collect($users)
                ->map(fn($u) => [$u["role"], $u["email"]])
                ->toArray(),
        );
    }
}
