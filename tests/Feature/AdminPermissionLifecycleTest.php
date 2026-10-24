<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
