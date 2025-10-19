<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;

class SakipDashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPermissions(array $permissionNames = [], bool $verified = true): User
    {
        $user = User::factory()->create([
            'email_verified_at' => $verified ? now() : null,
        ]);

        // Create permissions and assign directly to user
        foreach ($permissionNames as $name) {
            $perm = Permission::firstOrCreate(['name' => $name], ['display_name' => $name]);
            $user->permissions()->attach($perm);
        }

        $user->refresh();
        return $user;
    }

    public function test_guest_redirects_to_login_for_sakip_dashboard()
    {
        $response = $this->get(route('sakip.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_unverified_user_redirects_to_verification_notice_for_sakip_dashboard()
    {
        $user = $this->createUserWithPermissions(['sakip.dashboard.view'], verified: false);
        $response = $this->actingAs($user)->get(route('sakip.dashboard'));
        $response->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_with_permission_can_access_sakip_dashboard()
    {
        $user = $this->createUserWithPermissions(['sakip.dashboard.view'], verified: true);
        $response = $this->actingAs($user)->get(route('sakip.dashboard'));
        $response->assertStatus(200);
    }

    public function test_verified_superadmin_can_access_sakip_dashboard_without_explicit_permission()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $role = Role::firstOrCreate(['name' => 'superadmin'], ['display_name' => 'Super Admin']);
        $user->roles()->attach($role);
        $user->refresh();

        $response = $this->actingAs($user)->get(route('sakip.dashboard'));
        $response->assertStatus(200);
    }

    public function test_hasAnyPermission_logic_allows_access_with_any_sakip_role_or_permission()
    {
        // user with assessor role should access dashboard via policy's hasAnyPermission list
        $user = User::factory()->create(['email_verified_at' => now()]);
        $assessorRole = Role::firstOrCreate(['name' => 'assessor'], ['display_name' => 'Assessor']);
        $user->roles()->attach($assessorRole);
        $user->refresh();

        $response = $this->actingAs($user)->get(route('sakip.dashboard'));
        $response->assertStatus(200);
    }

    public function test_redirect_flow_root_to_dashboard_for_verified_user()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $response = $this->actingAs($user)->get('/');
        $response->assertRedirect(route('sakip.dashboard'));
    }
}