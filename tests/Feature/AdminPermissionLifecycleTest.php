<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * SECURITY (audit run-2, reserved-permission-name-unprotected): the
 * permission lifecycle must not let a delegated permission manager convert
 * held permissions into reserved authority (Spatie resolves gates by NAME)
 * or cascade-revoke reserved/in-use permissions. Reserved names come from
 * RolesAndPermissionsSeeder plus the route gate names.
 */
class AdminPermissionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = false;

    private User $manager;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin.dashboard', 'manage-permissions', 'manage-users', 'view-sakip-dashboard'] as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $institution = Instansi::factory()->create();
        $this->manager = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $institution->id,
        ]);
        $this->manager->givePermissionTo(['admin.dashboard', 'manage-permissions']);

        $role = Role::findOrCreate('Super Admin', 'web');
        $this->superAdmin = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $institution->id,
        ]);
        $this->superAdmin->assignRole($role);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // A held, non-reserved permission row owned by the manager.
        Permission::findOrCreate('custom.held-row', 'web');
        $this->manager->givePermissionTo('custom.held-row');

        // A free non-reserved row the manager does NOT hold (ceiling target).
        Permission::findOrCreate('custom.free-row', 'web');

        // A non-reserved row attached to a role (in-use integrity target).
        $attached = Permission::findOrCreate('custom.attached-row', 'web');
        $someRole = Role::findOrCreate('Auditor', 'web');
        $someRole->givePermissionTo($attached);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        // The shared :memory: connection persists rows across suites; remove
        // this suite's rows so later files never observe them (review
        // R2-STD-05).
        Permission::where('name', 'like', 'custom.%')->delete();
        Permission::where('name', 'view-executive-dashboard')->delete();
        Role::where('name', 'Auditor')->whereDoesntHave('users')->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        parent::tearDown();
    }

    #[Test]
    public function manager_cannot_rename_into_a_reserved_permission_name(): void
    {
        $this->actingAs($this->manager)
            ->put(route('admin.permissions.update', Permission::where('name', 'custom.held-row')->first()), [
                'name' => 'view-audit-logs',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('permissions', ['name' => 'custom.held-row']);
        $this->assertDatabaseMissing('permissions', ['name' => 'view-audit-logs']);
    }

    #[Test]
    public function renaming_away_from_a_reserved_name_is_denied_even_for_super_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('admin.permissions.update', Permission::where('name', 'manage-users')->first()), [
                'name' => 'renamed-users-row',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('permissions', ['name' => 'manage-users']);
    }

    #[Test]
    public function manager_cannot_delete_a_reserved_permission(): void
    {
        $this->actingAs($this->manager)
            ->delete(route('admin.permissions.destroy', Permission::where('name', 'manage-users')->first()))
            ->assertForbidden();

        $this->assertDatabaseHas('permissions', ['name' => 'manage-users']);
    }

    #[Test]
    public function deleting_an_in_use_permission_is_denied(): void
    {
        $this->actingAs($this->manager)
            ->delete(route('admin.permissions.destroy', Permission::where('name', 'custom.attached-row')->first()))
            ->assertForbidden();

        $this->assertDatabaseHas('permissions', ['name' => 'custom.attached-row']);
    }

    #[Test]
    public function manager_cannot_create_a_reserved_permission(): void
    {
        $this->actingAs($this->manager)
            ->post(route('admin.permissions.store'), [
                'name' => 'view-audit-logs',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('permissions', ['name' => 'view-audit-logs']);
    }

    /**
     * SECURITY (review STD-01/SEC-101): policies consume authority-bearing
     * permission names that RolesAndPermissionsSeeder never seeds (dashboard
     * branch names, the sakip.* policy family, the space-name indicator
     * family). A delegated manager must not be able to mint any of them via
     * the unguarded store endpoint.
     */
    public static function authorityBearingPermissionNameProvider(): array
    {
        return [
            'dashboard executive branch' => ['view-executive-dashboard'],
            'dashboard data-entry branch' => ['view-data-entry-dashboard'],
            'dashboard assessor branch' => ['view-assessor-dashboard'],
            'dashboard audit branch' => ['view-audit-dashboard'],
            'evidence admin family' => ['sakip.admin'],
            'evidence download family' => ['sakip.evidence.download'],
            'indicator view family' => ['view performance indicators'],
            'indicator create family' => ['create performance indicators'],
        ];
    }

    #[Test]
    #[DataProvider('authorityBearingPermissionNameProvider')]
    public function manager_cannot_mint_an_authority_bearing_permission_name(string $name): void
    {
        $this->actingAs($this->manager)
            ->post(route('admin.permissions.store'), [
                'name' => $name,
            ])
            ->assertForbidden();

        // No assertDatabaseMissing here: other suites legitimately create
        // these names in their own fixtures and the shared in-memory
        // connection persists across files, so row presence is not evidence
        // of a successful mint. The denial itself is the contract.
    }

    #[Test]
    public function super_admin_can_create_an_authority_bearing_permission_name(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.permissions.store'), [
                'name' => 'view-executive-dashboard',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('permissions', ['name' => 'view-executive-dashboard']);
    }

    #[Test]
    public function manager_cannot_rename_a_permission_it_does_not_hold(): void
    {
        $this->actingAs($this->manager)
            ->put(route('admin.permissions.update', Permission::where('name', 'custom.free-row')->first()), [
                'name' => 'custom.free-row-renamed',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('permissions', ['name' => 'custom.free-row']);
    }

    #[Test]
    public function manager_can_rename_a_held_non_reserved_permission(): void
    {
        $this->actingAs($this->manager)
            ->put(route('admin.permissions.update', Permission::where('name', 'custom.held-row')->first()), [
                'name' => 'custom.held-row-renamed',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('permissions', ['name' => 'custom.held-row-renamed']);
    }

    #[Test]
    public function super_admin_can_create_and_delete_a_non_reserved_permission(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.permissions.store'), [
                'name' => 'custom.superadmin-created',
            ])
            ->assertRedirect();

        $created = Permission::where('name', 'custom.superadmin-created')->first();
        $this->assertNotNull($created);

        $this->actingAs($this->superAdmin)
            ->delete(route('admin.permissions.destroy', $created))
            ->assertRedirect();

        $this->assertDatabaseMissing('permissions', ['name' => 'custom.superadmin-created']);
    }

    /**
     * SECURITY (review STD-10): guard_name is forced server-side to 'web' —
     * the only guard the application configures. A free-text guard would
     * hide a row from every web-guard check while it still exists.
     */
    #[Test]
    public function store_forces_the_web_guard(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('admin.permissions.store'), [
                'name' => 'custom.guard-forced-create',
                'guard_name' => 'api',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('permissions', [
            'name' => 'custom.guard-forced-create',
            'guard_name' => 'web',
        ]);
    }

    #[Test]
    public function update_forces_the_web_guard(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('admin.permissions.update', Permission::where('name', 'custom.free-row')->first()), [
                'name' => 'custom.free-row',
                'guard_name' => 'api',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('permissions', [
            'name' => 'custom.free-row',
            'guard_name' => 'web',
        ]);
    }
}
