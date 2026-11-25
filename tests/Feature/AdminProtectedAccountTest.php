<?php

namespace Tests\Feature;

use App\Constants\SystemRoles;
use App\Models\Instansi;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Services\AdminService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminProtectedAccountTest extends TestCase
{
    protected $seed = false;

    private User $manager;

    private User $superAdmin;

    private Instansi $destination;

    private Instansi $managerInstansi;

    private static string $originalPassword;

    private static string $replacementPassword;

    protected function setUp(): void
    {
        parent::setUp();

        // Generated per-run fixture passwords (no hardcoded credentials).
        self::$originalPassword = Str::password(16);
        self::$replacementPassword = Str::password(16);

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        foreach (['admin.dashboard', 'manage-users'] as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $institution = Instansi::factory()->create();
        $this->destination = Instansi::factory()->create();
        $this->managerInstansi = $institution;
        $this->manager = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $institution->id,
        ]);
        $this->manager->givePermissionTo(['admin.dashboard', 'manage-users']);

        $role = Role::findOrCreate(SystemRoles::SUPER_ADMIN, 'web');
        $this->superAdmin = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $institution->id,
            'password' => self::$originalPassword,
        ]);
        $this->superAdmin->assignRole($role);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue($this->manager->hasVerifiedEmail());
        $this->assertFalse($this->manager->hasRole(SystemRoles::SUPER_ADMIN));
        $this->assertTrue($this->manager->can('admin.dashboard'));
        $this->assertTrue($this->manager->can('manage-users'));
        $this->assertTrue($this->superAdmin->hasRole(SystemRoles::SUPER_ADMIN));
        $this->assertTrue(Hash::check(self::$originalPassword, $this->superAdmin->password));
    }

    private function updatePayload(User $target): array
    {
        return [
            'name' => 'Updated Test Account',
            'email' => 'updated-'.$target->id.'@example.test',
            'password' => self::$replacementPassword,
            'password_confirmation' => self::$replacementPassword,
            'email_verified' => '1',
            // SECURITY: a delegated manager may only place an unplaced account
            // into their own institution. Moving an account into a foreign
            // institution is a cross-tenant takeover and is refused.
            'instansi_id' => $this->managerInstansi->id,
        ];
    }

    /**
     * SECURITY: a delegated manager may not move an account into a foreign
     * institution (cross-tenant takeover) nor edit an account that already
     * belongs to another institution.
     */
    public function test_delegated_manager_cannot_move_an_account_into_a_foreign_instansi(): void
    {
        $target = User::factory()->create(['email_verified_at' => now()]);

        $payload = $this->updatePayload($target);
        $payload['instansi_id'] = $this->destination->id;

        $response = $this->actingAs($this->manager)->put(route('admin.users.update', $target), $payload);

        $response->assertSessionHasErrors('instansi_id');
        $this->assertNotSame($this->destination->id, $target->fresh()->instansi_id);
    }

    public function test_delegated_manager_cannot_update_super_admin(): void
    {
        $response = $this->actingAs($this->manager)->put(
            route('admin.users.update', $this->superAdmin),
            $this->updatePayload($this->superAdmin),
        );

        $response->assertSessionHasNoErrors();
        $response->assertForbidden();
    }

    public function test_delegated_manager_cannot_replace_super_admin_password(): void
    {
        $originalHash = $this->superAdmin->password;

        $response = $this->actingAs($this->manager)->put(
            route('admin.users.update', $this->superAdmin),
            $this->updatePayload($this->superAdmin),
        );

        $response->assertSessionHasNoErrors();
        $this->superAdmin->refresh();
        $this->assertTrue(
            $originalHash === $this->superAdmin->password,
            'A delegated non-Super-Admin manager replaced the persisted Super Admin password hash.',
        );
        $this->assertFalse(Hash::check(self::$replacementPassword, $this->superAdmin->password));
        $this->assertTrue(Hash::check(self::$originalPassword, $this->superAdmin->password));
    }

    public function test_delegated_manager_cannot_replace_super_admin_identity_or_ownership(): void
    {
        $original = $this->superAdmin->only(['name', 'email', 'instansi_id']);
        $originalRoles = $this->superAdmin->roles->modelKeys();
        $originalAuditCount = DB::table('audit_logs')->where('action', 'user.updated')->count();

        $response = $this->actingAs($this->manager)->put(
            route('admin.users.update', $this->superAdmin),
            $this->updatePayload($this->superAdmin),
        );

        $response->assertSessionHasNoErrors();
        $this->superAdmin->refresh();
        $this->assertSame($originalRoles, $this->superAdmin->roles->modelKeys());
        $this->assertSame(
            $original,
            $this->superAdmin->only(['name', 'email', 'instansi_id']),
            'A delegated non-Super-Admin manager changed the persisted Super Admin identity or institution.',
        );
        $this->assertSame($originalAuditCount, DB::table('audit_logs')->where('action', 'user.updated')->count());
    }

    public function test_attacker_credentials_cannot_authenticate_as_super_admin_after_attempted_update(): void
    {
        $payload = $this->updatePayload($this->superAdmin);
        $response = $this->actingAs($this->manager)->put(
            route('admin.users.update', $this->superAdmin),
            $payload,
        );
        $response->assertSessionHasNoErrors();

        Auth::logout();
        $this->app['session']->flush();
        $this->assertGuest();

        $this->post(route('auth.login'), [
            'email' => $payload['email'],
            'password' => self::$replacementPassword,
        ]);

        $this->assertNotEquals(
            $this->superAdmin->id,
            Auth::id(),
            'The replacement credentials authenticated as the protected Super Admin target.',
        );
        $this->assertGuest();
    }

    public function test_delegated_manager_can_update_ordinary_account(): void
    {
        $target = User::factory()->create(['email_verified_at' => now()]);
        $payload = $this->updatePayload($target);

        $response = $this->actingAs($this->manager)->put(route('admin.users.update', $target), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.users.show', $target));
        $target->refresh();
        $this->assertSame($payload['email'], $target->email);
        $this->assertSame($payload['name'], $target->name);
        $this->assertSame($payload['instansi_id'], $target->instansi_id);
        $this->assertTrue(Hash::check(self::$replacementPassword, $target->password));
        $this->assertDatabaseHas('audit_logs', ['user_id' => $this->manager->id, 'action' => 'user.updated']);
    }

    public function test_super_admin_can_update_super_admin_credentials(): void
    {
        $payload = $this->updatePayload($this->superAdmin);

        $response = $this->actingAs($this->superAdmin)->put(
            route('admin.users.update', $this->superAdmin),
            $payload,
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.users.show', $this->superAdmin));
        $this->superAdmin->refresh();
        $this->assertSame($payload['email'], $this->superAdmin->email);
        $this->assertTrue(Hash::check(self::$replacementPassword, $this->superAdmin->password));
        $this->assertTrue($this->superAdmin->hasRole(SystemRoles::SUPER_ADMIN));
        $this->assertDatabaseHas('audit_logs', ['user_id' => $this->superAdmin->id, 'action' => 'user.updated']);
    }

    public function test_delegated_manager_cannot_delete_super_admin(): void
    {
        $this->actingAs($this->manager)->delete(route('admin.users.destroy', $this->superAdmin))
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id]);
        $this->assertTrue($this->superAdmin->fresh()->hasRole(SystemRoles::SUPER_ADMIN));
        $this->assertDatabaseMissing('audit_logs', ['action' => 'user.deleted']);
    }

    public function test_delegated_manager_cannot_remove_super_admin_role_membership(): void
    {
        $this->actingAs($this->manager)->post(
            route('admin.users.roles.update', $this->superAdmin),
            ['roles' => []],
        )->assertForbidden();

        $this->assertTrue($this->superAdmin->fresh()->hasRole(SystemRoles::SUPER_ADMIN));
        $this->assertDatabaseMissing('audit_logs', ['action' => 'user.roles.updated']);
    }

    public function test_delegated_manager_cannot_replace_super_admin_direct_permissions(): void
    {
        $this->superAdmin->givePermissionTo('manage-users');

        $this->actingAs($this->manager)->post(
            route('admin.users.permissions.update', $this->superAdmin),
            ['permissions' => []],
        )->assertForbidden();

        $this->assertTrue($this->superAdmin->fresh()->hasDirectPermission('manage-users'));
        $this->assertDatabaseMissing('audit_logs', ['action' => 'user.permissions.updated']);
    }

    public function test_delegated_manager_can_delete_ordinary_account(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->manager)->delete(route('admin.users.destroy', $target))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $this->manager->id, 'action' => 'user.deleted']);
    }

    public function test_super_admin_can_manage_another_super_admin_membership_and_permissions(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole(SystemRoles::SUPER_ADMIN);
        $role = Role::create(['name' => 'Additional Test Role', 'guard_name' => 'web']);
        $permission = Permission::findByName('manage-users');

        $this->actingAs($actor)->post(route('admin.users.permissions.update', $this->superAdmin), [
            'permissions' => [$permission->id],
        ])->assertRedirect(route('admin.users.show', $this->superAdmin));
        $this->assertTrue($this->superAdmin->fresh()->hasDirectPermission('manage-users'));

        $this->post(route('admin.users.roles.update', $this->superAdmin), [
            'roles' => [$role->id],
        ])->assertRedirect(route('admin.users.show', $this->superAdmin));
        $this->assertTrue($this->superAdmin->fresh()->hasRole($role->name));
        $this->assertFalse($this->superAdmin->fresh()->hasRole(SystemRoles::SUPER_ADMIN));
    }

    public function test_super_admin_can_delete_another_super_admin(): void
    {
        $actor = User::factory()->create();
        $actor->assignRole(SystemRoles::SUPER_ADMIN);

        $this->actingAs($actor)->delete(route('admin.users.destroy', $this->superAdmin))
            ->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $this->superAdmin->id]);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $actor->id, 'action' => 'user.deleted']);
    }

    public function test_authorized_manager_can_assign_access_to_an_ordinary_account(): void
    {
        Permission::findOrCreate('manage-roles', 'web');
        $this->manager->givePermissionTo('manage-roles');
        $role = Role::create(['name' => 'Delegated Ordinary Role', 'guard_name' => 'web']);
        $role->givePermissionTo('manage-users');
        $permission = Permission::findByName('manage-users');
        $target = User::factory()->create();

        $this->actingAs($this->manager)->post(route('admin.users.roles.update', $target), [
            'roles' => [$role->id],
        ])->assertRedirect(route('admin.users.show', $target));
        $this->assertTrue($target->fresh()->hasRole($role->name));

        $this->post(route('admin.users.permissions.update', $target), [
            'permissions' => [$permission->id],
        ])->assertRedirect(route('admin.users.show', $target));
        $this->assertTrue($target->fresh()->hasDirectPermission('manage-users'));
        $this->assertDatabaseHas('audit_logs', ['user_id' => $this->manager->id, 'action' => 'user.roles.updated']);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $this->manager->id, 'action' => 'user.permissions.updated']);
    }

    public function test_protected_update_is_denied_before_input_validation(): void
    {
        $this->actingAs($this->manager)->putJson(route('admin.users.update', $this->superAdmin), [])
            ->assertForbidden();

        $this->assertTrue(Hash::check(self::$originalPassword, $this->superAdmin->fresh()->password));
        $this->assertDatabaseMissing('audit_logs', ['action' => 'user.updated']);
    }

    #[DataProvider('protectedServiceMutations')]
    public function test_direct_service_mutations_cannot_bypass_super_admin_target_protection(string $method): void
    {
        $this->actingAs($this->manager);
        $original = $this->superAdmin->only(['name', 'email', 'password', 'instansi_id']);
        $denied = false;

        try {
            $arguments = match ($method) {
                'updateUser' => [$this->superAdmin, $this->updatePayload($this->superAdmin)],
                'deleteUser' => [$this->superAdmin],
                default => [$this->superAdmin, []],
            };
            app(AdminService::class)->{$method}(...$arguments);
        } catch (AuthorizationException) {
            $denied = true;
        }

        $this->assertTrue($denied, 'The service accepted an unauthorized mutation of a Super Admin target.');
        $this->assertSame($original, $this->superAdmin->fresh()->only(array_keys($original)));
        $this->assertTrue($this->superAdmin->fresh()->hasRole(SystemRoles::SUPER_ADMIN));
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public static function protectedServiceMutations(): array
    {
        return [
            'credentials' => ['updateUser'],
            'deletion' => ['deleteUser'],
            'roles' => ['assignRoles'],
            'permissions' => ['assignPermissions'],
        ];
    }

    public function test_delegated_manager_can_create_and_read_ordinary_accounts(): void
    {
        Permission::findOrCreate('manage-roles', 'web');
        $this->manager->givePermissionTo('manage-roles');
        $role = Role::create(['name' => 'Ordinary Test Role', 'guard_name' => 'web']);
        $payload = [
            'name' => 'Ordinary Managed Account',
            'email' => 'ordinary-managed@example.test',
            'password' => self::$originalPassword,
            'password_confirmation' => self::$originalPassword,
            'instansi_id' => $this->manager->instansi_id,
            'roles' => [$role->id],
        ];

        $this->actingAs($this->manager)->post(route('admin.users.store'), $payload)
            ->assertSessionHasNoErrors();
        $target = User::where('email', $payload['email'])->firstOrFail();
        $this->assertTrue(Hash::check(self::$originalPassword, $target->password));
        $this->assertTrue($target->hasRole($role->name));
        $this->assertFalse($target->hasRole(SystemRoles::SUPER_ADMIN));
        $this->assertDatabaseHas('audit_logs', ['user_id' => $this->manager->id, 'action' => 'user.created']);

        $this->get(route('admin.users.index', ['search' => $payload['email'], 'role' => $role->name]))
            ->assertOk()->assertSee($payload['email']);
        $this->get(route('admin.users.show', $target))->assertOk()->assertSee($payload['name']);
        $this->get(route('admin.users.edit', $target))->assertOk()->assertSee($payload['email']);
        $this->get(route('admin.users.create'))->assertOk()->assertSee($role->name);
    }

    public function test_delegated_manager_cannot_delete_itself(): void
    {
        $this->actingAs($this->manager)->from(route('admin.users.index'))
            ->delete(route('admin.users.destroy', $this->manager))
            ->assertRedirect(route('admin.users.index'))->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $this->manager->id]);
        $this->assertDatabaseMissing('audit_logs', ['action' => 'user.deleted']);
    }

    public function test_ordinary_service_update_without_password_preserves_credentials(): void
    {
        $target = User::factory()->create();
        $originalHash = $target->password;
        $role = Role::create(['name' => 'Ordinary Service Role', 'guard_name' => 'web']);
        $permission = Permission::findByName('manage-users');
        $this->actingAs($this->superAdmin);

        app(AdminService::class)->updateUser($target, [
            'name' => 'Updated Ordinary Account',
            'email' => $target->email,
            'roles' => [$role->id],
            'permissions' => [$permission->id],
        ]);

        $target->refresh();
        $this->assertSame($originalHash, $target->password);
        $this->assertSame('Updated Ordinary Account', $target->name);
        $this->assertTrue($target->hasRole($role->name));
        $this->assertTrue($target->hasDirectPermission('manage-users'));
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.updated']);
    }

    public function test_user_policy_preserves_ordinary_ownership_and_management_contracts(): void
    {
        $policy = new UserPolicy;
        $ordinary = User::factory()->create();
        $other = User::factory()->create();

        $this->assertFalse($policy->viewAny($ordinary));
        $this->assertFalse($policy->create($ordinary));
        $this->assertFalse($policy->view($ordinary, $other));
        $this->assertTrue($policy->view($ordinary, $ordinary));
        $this->assertFalse($policy->update($ordinary, $other));
        $this->assertTrue($policy->update($ordinary, $ordinary));
        $this->assertFalse($policy->delete($ordinary, $other));
        $this->assertFalse($policy->delete($this->manager, $this->manager));
        $this->assertFalse($policy->restore($ordinary, $other));
        $this->assertFalse($policy->forceDelete($ordinary, $other));
        $this->assertFalse($policy->forceDelete($this->manager, $this->manager));

        $this->assertTrue($policy->viewAny($this->manager));
        $this->assertTrue($policy->create($this->manager));
        $this->assertTrue($policy->view($this->manager, $ordinary));
        $this->assertTrue($policy->update($this->manager, $ordinary));
        $this->assertTrue($policy->delete($this->manager, $ordinary));
        $this->assertTrue($policy->restore($this->manager, $ordinary));
        $this->assertTrue($policy->forceDelete($this->manager, $ordinary));
        $this->assertFalse($policy->update($this->manager, $this->superAdmin));
        $this->assertFalse($policy->delete($this->manager, $this->superAdmin));
        $this->assertFalse($policy->manageProtectedAccount($this->manager, $this->superAdmin));
        $this->assertTrue($policy->manageProtectedAccount($this->superAdmin, $this->superAdmin));
    }

    public function test_user_policy_preserves_separate_administrative_capabilities(): void
    {
        $policy = new UserPolicy;
        $ordinary = User::factory()->create();
        $target = User::factory()->create();
        $capabilities = [
            ['assignRoles', 'manage-roles', false],
            ['assignPermissions', 'manage-roles', false],
            ['viewAuditLogs', 'manage-settings', false],
            ['manageSettings', 'admin.settings.manage', false],
            ['accessDashboard', 'admin.dashboard', false],
            ['impersonate', 'admin.users.impersonate', true],
            ['manageSessions', 'admin.users.sessions', true],
            ['export', 'admin.users.export', false],
            ['import', 'admin.users.import', false],
        ];

        foreach ($capabilities as [$ability, $permission, $needsTarget]) {
            Permission::findOrCreate($permission, 'web');
            app(PermissionRegistrar::class)->forgetCachedPermissions();
            $arguments = $needsTarget ? [$ordinary, $target] : [$ordinary];
            $this->assertFalse($policy->{$ability}(...$arguments), $ability.' requires its separate permission.');
            $ordinary->givePermissionTo($permission);
            $this->assertTrue($policy->{$ability}(...$arguments), $ability.' accepts its authorized actor.');
            $ordinary->revokePermissionTo($permission);
        }

        $this->assertFalse($policy->impersonate($ordinary, $ordinary));
        $this->assertFalse($policy->impersonate($ordinary, $this->superAdmin));
        $this->assertTrue($policy->impersonate($this->superAdmin, $target));
    }
}
