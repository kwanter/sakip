<?php

namespace Tests\Feature;

use App\Constants\SystemRoles;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SECURITY: Admin must not be able to escalate themselves (or others)
 * to the Super Admin role. Only an actual Super Admin may grant it.
 */
class AdminRoleEscalationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['guard_name' => 'web']);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_cannot_grant_super_admin_to_another_user()
    {
        $admin = $this->makeUserWithRole('Admin');
        $target = User::factory()->create(['email_verified_at' => now()]);
        $superAdminRole = Role::firstOrCreate(['name' => SystemRoles::SUPER_ADMIN], ['guard_name' => 'web']);

        $this->actingAs($admin);

        (new AdminService)->assignRoles($target, [$superAdminRole->id]);

        $this->assertFalse(
            $target->hasRole(SystemRoles::SUPER_ADMIN),
            'Admin granted Super Admin role — privilege escalation not blocked.'
        );
    }

    public function test_super_admin_can_grant_super_admin()
    {
        $superAdmin = $this->makeUserWithRole(SystemRoles::SUPER_ADMIN);
        $target = User::factory()->create(['email_verified_at' => now()]);
        $superAdminRole = Role::firstOrCreate(['name' => SystemRoles::SUPER_ADMIN], ['guard_name' => 'web']);

        $this->actingAs($superAdmin);

        (new AdminService)->assignRoles($target, [$superAdminRole->id]);

        $this->assertTrue(
            $target->hasRole(SystemRoles::SUPER_ADMIN),
            'Super Admin should be able to grant Super Admin.'
        );
    }
}
