<?php

namespace Tests\Feature;

use App\Constants\SystemRoles;
use App\Models\Instansi;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminAccessDelegationTest extends TestCase
{
    protected $seed = false;

    private User $manager;

    private User $ordinary;

    private Role $baselineRole;

    private Role $allowedRole;

    private Role $privilegedRole;

    private const INHERITED_PERMISSION = 'admin.users.export';

    private const SENSITIVE_PERMISSION = 'manage-permissions';

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        foreach (['admin.dashboard', 'manage-users', 'manage-roles', self::INHERITED_PERMISSION, self::SENSITIVE_PERMISSION] as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $institution = Instansi::factory()->create();
        $this->baselineRole = Role::create(['name' => 'Slice 2 Baseline Role', 'guard_name' => 'web']);
        $this->baselineRole->givePermissionTo(self::INHERITED_PERMISSION);
        $this->allowedRole = Role::create(['name' => 'Slice 2 Allowed Role', 'guard_name' => 'web']);
        $this->allowedRole->givePermissionTo(['manage-users', self::INHERITED_PERMISSION]);
        $this->privilegedRole = Role::create(['name' => 'Slice 2 Privileged Role', 'guard_name' => 'web']);
        $this->privilegedRole->givePermissionTo(self::SENSITIVE_PERMISSION);

        $this->manager = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $institution->id,
        ]);
        $this->manager->givePermissionTo(['admin.dashboard', 'manage-users']);
        $this->manager->assignRole($this->baselineRole);
        $this->ordinary = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $institution->id,
        ]);
        $this->ordinary->givePermissionTo('admin.dashboard');
        $this->ordinary->assignRole($this->baselineRole);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue($this->manager->hasVerifiedEmail());
        $this->assertTrue($this->ordinary->hasVerifiedEmail());
        $this->assertFalse($this->manager->hasRole(SystemRoles::SUPER_ADMIN));
        $this->assertFalse($this->ordinary->hasRole(SystemRoles::SUPER_ADMIN));
        $this->assertTrue($this->manager->can('admin.dashboard'));
        $this->assertTrue($this->manager->can('manage-users'));
        $this->assertTrue($this->manager->can(self::INHERITED_PERMISSION));
        $this->assertFalse($this->manager->hasDirectPermission(self::INHERITED_PERMISSION));
        $this->assertFalse($this->manager->can('manage-roles'));
        $this->assertFalse($this->manager->can(self::SENSITIVE_PERMISSION));
    }

    private function authorizeAssignmentManager(): void
    {
        $this->manager->givePermissionTo('manage-roles');
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->assertTrue($this->manager->can('assignRoles', User::class));
        $this->assertTrue($this->manager->can('assignPermissions', User::class));
        $this->assertFalse($this->manager->can(self::SENSITIVE_PERMISSION));
    }

    private function accessSnapshot(User $target): array
    {
        $target = $target->fresh();

        return [
            'roles' => $target->roles->pluck('id')->sort()->values()->all(),
            'permissions' => $target->permissions->pluck('id')->sort()->values()->all(),
        ];
    }

    private function probeSensitiveAccess(User $target): int
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $this->actingAs($target->fresh())->get(route('admin.permissions.index'))->status();
    }

    #[DataProvider('assignmentAttempts')]
    public function test_manager_without_assignment_authority_cannot_delegate_access(string $kind, bool $self): void
    {
        $target = $self ? $this->manager : $this->ordinary;
        $this->assertFalse($this->manager->can('assignRoles', User::class));
        $this->assertFalse($this->manager->can('assignPermissions', User::class));
        $this->assertRejectedAssignment($kind, $target);
    }

    #[DataProvider('assignmentAttempts')]
    public function test_assignment_authority_is_required_even_for_grants_within_the_actor_permissions(string $kind, bool $self): void
    {
        $target = $self ? $this->manager : $this->ordinary;
        $this->assertFalse($this->manager->can('assignRoles', User::class));
        $this->assertFalse($this->manager->can('assignPermissions', User::class));
        $before = $this->accessSnapshot($target);
        $auditCount = DB::table('audit_logs')->count();
        $beforeStatus = $this->actingAs($target)->get(route('admin.users.index'))->status();
        $this->assertSame($self ? 200 : 403, $beforeStatus);
        $payload = $kind === 'roles'
            ? ['roles' => [$this->allowedRole->id]]
            : ['permissions' => $this->permissionIds(['admin.dashboard', 'manage-users', self::INHERITED_PERMISSION])];

        $response = $this->actingAs($this->manager)->post(route('admin.users.'.$kind.'.update', $target), $payload);
        $response->assertSessionHasNoErrors();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $afterStatus = $this->actingAs($target->fresh())->get(route('admin.users.index'))->status();

        $this->assertSame([
            'access_unchanged' => true,
            'subsequent_http_status' => $beforeStatus,
            'assignment_http_status' => 403,
            'successful_audit_added' => false,
        ], [
            'access_unchanged' => $before === $this->accessSnapshot($target),
            'subsequent_http_status' => $afterStatus,
            'assignment_http_status' => $response->status(),
            'successful_audit_added' => DB::table('audit_logs')->count() > $auditCount,
        ], 'The user manager assigned access without manage-roles even though the permission ceiling alone was satisfied.');
    }

    #[DataProvider('assignmentAttempts')]
    public function test_limited_assignment_manager_cannot_grant_permissions_it_does_not_hold(string $kind, bool $self): void
    {
        $this->authorizeAssignmentManager();
        $target = $self ? $this->manager : $this->ordinary;
        $this->assertRejectedAssignment($kind, $target);
    }

    private function assertRejectedAssignment(string $kind, User $target): void
    {
        $before = $this->accessSnapshot($target);
        $auditCount = DB::table('audit_logs')->count();
        $this->assertSame(403, $this->probeSensitiveAccess($target));
        $payload = $kind === 'roles'
            ? ['roles' => [$this->allowedRole->id, $this->privilegedRole->id]]
            : ['permissions' => $this->permissionIds(['admin.dashboard', 'manage-users', self::INHERITED_PERMISSION, self::SENSITIVE_PERMISSION])];

        $response = $this->actingAs($this->manager)->post(
            route('admin.users.'.$kind.'.update', $target),
            $payload,
        );
        $response->assertSessionHasNoErrors();

        $after = $this->accessSnapshot($target);
        $probeStatus = $this->probeSensitiveAccess($target);
        $this->assertFalse($target->fresh()->hasRole(SystemRoles::SUPER_ADMIN));
        $this->assertSame([
            'roles_unchanged' => true,
            'direct_permissions_unchanged' => true,
            'sensitive_permission_granted' => false,
            'subsequent_http_status' => 403,
            'assignment_http_status' => 403,
            'successful_audit_added' => false,
        ], [
            'roles_unchanged' => $before['roles'] === $after['roles'],
            'direct_permissions_unchanged' => $before['permissions'] === $after['permissions'],
            'sensitive_permission_granted' => $target->fresh()->can(self::SENSITIVE_PERMISSION),
            'subsequent_http_status' => $probeStatus,
            'assignment_http_status' => $response->status(),
            'successful_audit_added' => DB::table('audit_logs')->count() > $auditCount,
        ], 'Unauthorized access delegation changed persisted grants and enabled a previously forbidden HTTP endpoint.');
    }

    #[DataProvider('memberTargets')]
    public function test_role_manager_cannot_add_absent_permissions_to_a_member_role(bool $self): void
    {
        $this->authorizeAssignmentManager();
        $target = $self ? $this->manager : $this->ordinary;
        $role = Role::create(['name' => 'Slice 2 Mutable Role', 'guard_name' => 'web']);
        $role->givePermissionTo('admin.dashboard');
        $target->assignRole($role);
        $originalPermissions = $role->permissions->pluck('id')->sort()->values()->all();
        $this->assertSame(403, $this->probeSensitiveAccess($target));

        $response = $this->actingAs($this->manager)->post(route('admin.roles.permissions.update', $role), [
            'permissions' => $this->permissionIds(['admin.dashboard', 'manage-users', self::SENSITIVE_PERMISSION]),
        ]);
        $response->assertSessionHasNoErrors();

        $probeStatus = $this->probeSensitiveAccess($target);
        $this->assertSame([
            'role_permissions_unchanged' => true,
            'sensitive_permission_granted' => false,
            'subsequent_http_status' => 403,
            'mutation_http_status' => 403,
        ], [
            'role_permissions_unchanged' => $originalPermissions === $role->fresh()->permissions->pluck('id')->sort()->values()->all(),
            'sensitive_permission_granted' => $target->fresh()->can(self::SENSITIVE_PERMISSION),
            'subsequent_http_status' => $probeStatus,
            'mutation_http_status' => $response->status(),
        ], 'A role edit conveyed absent authority to an existing role member.');
    }

    public function test_role_manager_cannot_create_a_role_conveying_absent_permissions(): void
    {
        $this->authorizeAssignmentManager();
        $name = 'Slice 2 Requested Privileged Role';
        $this->assertDatabaseMissing('roles', ['name' => $name]);

        $response = $this->actingAs($this->manager)->post(route('admin.roles.store'), [
            'name' => $name,
            'guard_name' => 'web',
            'permissions' => $this->permissionIds(['manage-users', self::SENSITIVE_PERMISSION]),
        ]);
        $response->assertSessionHasNoErrors();

        $created = Role::where('name', $name)->first();
        $this->assertSame([
            'role_created' => false,
            'sensitive_permission_stored' => false,
            'creation_http_status' => 403,
        ], [
            'role_created' => $created !== null,
            'sensitive_permission_stored' => $created?->hasPermissionTo(self::SENSITIVE_PERMISSION) ?? false,
            'creation_http_status' => $response->status(),
        ], 'The role creation endpoint persisted a grant outside the actor effective permissions.');
    }

    #[DataProvider('creationAuthorities')]
    public function test_user_creation_cannot_bypass_role_assignment_authority_or_permission_ceiling(bool $assignmentAuthority): void
    {
        if ($assignmentAuthority) {
            $this->authorizeAssignmentManager();
        }
        $email = 'slice2-forbidden-created@example.test';
        $this->assertDatabaseMissing('users', ['email' => $email]);
        $roles = $assignmentAuthority ? [$this->allowedRole->id, $this->privilegedRole->id] : [$this->allowedRole->id];
        $password = Str::password(16);

        $response = $this->actingAs($this->manager)->post(route('admin.users.store'), [
            'name' => 'Slice 2 Forbidden Created Account',
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'instansi_id' => $this->manager->instansi_id,
            'roles' => $roles,
        ]);
        $response->assertSessionHasNoErrors();

        $created = User::where('email', $email)->first();
        $this->assertSame([
            'user_created' => false,
            'role_assigned' => false,
            'sensitive_permission_granted' => false,
            'creation_http_status' => 403,
            'successful_audit_added' => false,
        ], [
            'user_created' => $created !== null,
            'role_assigned' => $created?->hasRole($this->allowedRole->name) ?? false,
            'sensitive_permission_granted' => $created?->can(self::SENSITIVE_PERMISSION) ?? false,
            'creation_http_status' => $response->status(),
            'successful_audit_added' => DB::table('audit_logs')->whereIn('action', ['user.created', 'user.roles.updated'])->exists(),
        ], 'User creation persisted an account and unauthorized role grants instead of rejecting the whole transaction.');
    }

    #[DataProvider('memberTargets')]
    public function test_assignment_manager_can_grant_an_allowed_permission_subset(bool $self): void
    {
        $this->authorizeAssignmentManager();
        $target = $self ? $this->manager : $this->ordinary;
        $ids = $this->permissionIds(['admin.dashboard', 'manage-users', self::INHERITED_PERMISSION]);

        $this->actingAs($this->manager)->post(route('admin.users.permissions.update', $target), [
            'permissions' => $ids,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users.show', $target));

        $this->assertEqualsCanonicalizing($ids, $target->fresh()->permissions->modelKeys());
        $this->assertTrue($target->fresh()->hasDirectPermission(self::INHERITED_PERMISSION));
        $this->assertFalse($target->fresh()->can(self::SENSITIVE_PERMISSION));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($target->fresh())->get(route('admin.users.index'))->assertOk();
        $this->assertDatabaseHas('audit_logs', ['user_id' => $this->manager->id, 'action' => 'user.permissions.updated']);
    }

    #[DataProvider('memberTargets')]
    public function test_assignment_manager_can_grant_an_allowed_role(bool $self): void
    {
        $this->authorizeAssignmentManager();
        $target = $self ? $this->manager : $this->ordinary;

        $this->actingAs($this->manager)->post(route('admin.users.roles.update', $target), [
            'roles' => [$this->allowedRole->id],
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users.show', $target));

        $this->assertSame([$this->allowedRole->id], $target->fresh()->roles->modelKeys());
        $this->assertFalse($target->fresh()->can(self::SENSITIVE_PERMISSION));
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($target->fresh())->get(route('admin.users.index'))->assertOk();
        $this->assertDatabaseHas('audit_logs', ['user_id' => $this->manager->id, 'action' => 'user.roles.updated']);
    }

    public function test_allowed_role_creation_edits_and_empty_permission_removal_succeed(): void
    {
        $this->authorizeAssignmentManager();
        $name = 'Slice 2 Requested Allowed Role';
        $ids = $this->permissionIds([self::INHERITED_PERMISSION]);

        $this->actingAs($this->manager)->post(route('admin.roles.store'), [
            'name' => $name,
            'guard_name' => 'web',
            'permissions' => $ids,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.index'));
        $role = Role::where('name', $name)->firstOrFail();
        $this->assertSame($ids, $role->permissions->modelKeys());
        $this->manager->assignRole($role);
        $this->ordinary->assignRole($role);

        $ids = $this->permissionIds([self::INHERITED_PERMISSION, 'manage-users']);
        $this->post(route('admin.roles.permissions.update', $role), ['permissions' => $ids])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.show', $role));
        $this->assertEqualsCanonicalizing($ids, $role->fresh()->permissions->modelKeys());
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->ordinary->fresh())->get(route('admin.users.index'))->assertOk();

        $this->actingAs($this->manager->fresh())->post(route('admin.roles.permissions.update', $role), ['permissions' => []])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.show', $role));
        $this->assertCount(0, $role->fresh()->permissions);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->actingAs($this->ordinary->fresh())->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_super_admin_can_delegate_permissions_outside_its_explicit_grants(): void
    {
        $superAdmin = User::factory()->create(['email_verified_at' => now()]);
        $superAdminRole = Role::findOrCreate(SystemRoles::SUPER_ADMIN, 'web');
        $superAdminRole->syncPermissions([]);
        $superAdmin->assignRole($superAdminRole);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->assertFalse($superAdmin->getAllPermissions()->contains('name', self::SENSITIVE_PERMISSION));

        $this->actingAs($superAdmin)->post(route('admin.users.permissions.update', $this->ordinary), [
            'permissions' => $this->permissionIds(['admin.dashboard', self::SENSITIVE_PERMISSION]),
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users.show', $this->ordinary));
        $this->assertTrue($this->ordinary->fresh()->hasDirectPermission(self::SENSITIVE_PERMISSION));
        $this->assertSame(200, $this->probeSensitiveAccess($this->ordinary));

        $this->actingAs($superAdmin)->post(route('admin.users.roles.update', $this->ordinary), [
            'roles' => [$this->privilegedRole->id],
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users.show', $this->ordinary));
        $this->assertTrue($this->ordinary->fresh()->hasRole($this->privilegedRole->name));

        $this->post(route('admin.roles.store'), [
            'name' => 'Slice 2 Super Admin Created Role',
            'permissions' => $this->permissionIds([self::SENSITIVE_PERMISSION]),
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.index'));
        $role = Role::where('name', 'Slice 2 Super Admin Created Role')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo(self::SENSITIVE_PERMISSION));
        $this->post(route('admin.roles.permissions.update', $role), [
            'permissions' => $this->permissionIds([self::SENSITIVE_PERMISSION, self::INHERITED_PERMISSION]),
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.show', $role));
        $this->assertTrue($role->fresh()->hasPermissionTo(self::INHERITED_PERMISSION));
    }

    public function test_manager_without_assignment_authority_can_still_update_an_ordinary_identity(): void
    {
        $this->actingAs($this->manager)->put(route('admin.users.update', $this->ordinary), [
            'name' => 'Slice 2 Ordinary Identity Update',
            'email' => $this->ordinary->email,
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users.show', $this->ordinary));

        $this->assertSame('Slice 2 Ordinary Identity Update', $this->ordinary->fresh()->name);
    }

    public function test_manager_without_assignment_authority_can_create_an_ordinary_account_without_roles(): void
    {
        $email = 'slice2-plain-created@example.test';
        $password = Str::password(16);
        $response = $this->actingAs($this->manager)->post(route('admin.users.store'), [
            'name' => 'Slice 2 Plain Created Account',
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
            'instansi_id' => $this->manager->instansi_id,
        ])->assertSessionHasNoErrors();

        $created = User::where('email', $email)->firstOrFail();
        $response->assertRedirect(route('admin.users.show', $created));
        $this->assertCount(0, $created->roles);
        $this->assertCount(0, $created->permissions);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $this->manager->id, 'action' => 'user.created']);
    }

    public function test_role_endpoints_already_deny_an_actor_without_manage_roles(): void
    {
        $permissions = $this->privilegedRole->permissions->modelKeys();
        $this->actingAs($this->manager)->post(route('admin.roles.store'), [
            'name' => 'Slice 2 Role Without Authority',
            'permissions' => $this->permissionIds([self::SENSITIVE_PERMISSION]),
        ])->assertForbidden();
        $this->assertDatabaseMissing('roles', ['name' => 'Slice 2 Role Without Authority']);

        $this->post(route('admin.roles.permissions.update', $this->privilegedRole), ['permissions' => []])
            ->assertForbidden();
        $this->assertSame($permissions, $this->privilegedRole->fresh()->permissions->modelKeys());
    }

    #[DataProvider('serviceDelegationAttempts')]
    public function test_direct_service_calls_cannot_bypass_assignment_authority_or_ceiling(string $method, bool $authority): void
    {
        if ($authority) {
            $this->authorizeAssignmentManager();
        }
        $ids = $method === 'assignRoles'
            ? [$authority ? $this->privilegedRole->id : $this->allowedRole->id]
            : $this->permissionIds([$authority ? self::SENSITIVE_PERMISSION : self::INHERITED_PERMISSION]);
        $before = $this->accessSnapshot($this->ordinary);
        $auditCount = DB::table('audit_logs')->count();
        $this->actingAs($this->manager);
        $denied = false;

        try {
            app(AdminService::class)->{$method}($this->ordinary, $ids);
        } catch (AuthorizationException) {
            $denied = true;
        }

        $this->assertSame([
            'denied' => true,
            'access_unchanged' => true,
            'successful_audit_added' => false,
        ], [
            'denied' => $denied,
            'access_unchanged' => $before === $this->accessSnapshot($this->ordinary),
            'successful_audit_added' => DB::table('audit_logs')->count() > $auditCount,
        ], 'A direct service call bypassed the delegation restriction.');
    }

    #[DataProvider('accessFields')]
    public function test_service_user_update_rolls_back_identity_when_a_mixed_grant_is_denied(string $field): void
    {
        $this->authorizeAssignmentManager();
        $beforeIdentity = $this->ordinary->only(['name', 'email', 'password', 'instansi_id']);
        $beforeAccess = $this->accessSnapshot($this->ordinary);
        $auditCount = DB::table('audit_logs')->count();
        $payload = [
            'name' => 'Identity Must Roll Back',
            'email' => $this->ordinary->email,
            $field => $field === 'roles'
                ? [$this->allowedRole->id, $this->privilegedRole->id]
                : $this->permissionIds([self::INHERITED_PERMISSION, self::SENSITIVE_PERMISSION]),
        ];
        $this->actingAs($this->manager);
        $denied = false;

        try {
            app(AdminService::class)->updateUser($this->ordinary, $payload);
        } catch (AuthorizationException) {
            $denied = true;
        }

        $this->assertSame([
            'denied' => true,
            'identity_unchanged' => true,
            'access_unchanged' => true,
            'successful_audit_added' => false,
        ], [
            'denied' => $denied,
            'identity_unchanged' => $beforeIdentity === $this->ordinary->fresh()->only(array_keys($beforeIdentity)),
            'access_unchanged' => $beforeAccess === $this->accessSnapshot($this->ordinary),
            'successful_audit_added' => DB::table('audit_logs')->count() > $auditCount,
        ], 'The failed delegation request left partial identity or access changes.');
    }

    #[DataProvider('accessFields')]
    public function test_invalid_assignment_ids_return_validation_errors_without_mutation(string $field): void
    {
        $this->authorizeAssignmentManager();
        $before = $this->accessSnapshot($this->ordinary);
        $auditCount = DB::table('audit_logs')->count();

        $this->actingAs($this->manager)->postJson(route('admin.users.'.$field.'.update', $this->ordinary), [
            $field => ['missing-fixture-id'],
        ])->assertUnprocessable()->assertJsonValidationErrors($field.'.0');

        $this->assertSame($before, $this->accessSnapshot($this->ordinary));
        $this->assertSame($auditCount, DB::table('audit_logs')->count());
    }

    #[DataProvider('accessFields')]
    public function test_authorized_empty_user_assignment_removes_only_the_requested_access(string $field): void
    {
        $this->authorizeAssignmentManager();
        $before = $this->accessSnapshot($this->ordinary);

        $this->actingAs($this->manager)->post(route('admin.users.'.$field.'.update', $this->ordinary), [$field => []])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.users.show', $this->ordinary));

        $after = $this->accessSnapshot($this->ordinary);
        $this->assertSame([], $after[$field]);
        $otherField = $field === 'roles' ? 'permissions' : 'roles';
        $this->assertSame($before[$otherField], $after[$otherField]);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $this->manager->id, 'action' => 'user.'.$field.'.updated']);
    }

    #[DataProvider('accessFields')]
    public function test_authorized_assignment_manager_cannot_validate_or_mutate_super_admin_targets(string $field): void
    {
        $this->authorizeAssignmentManager();
        $target = User::factory()->create(['email_verified_at' => now()]);
        $target->assignRole(Role::findOrCreate(SystemRoles::SUPER_ADMIN, 'web'));
        $before = $this->accessSnapshot($target);
        $auditCount = DB::table('audit_logs')->count();

        $this->actingAs($this->manager)->postJson(route('admin.users.'.$field.'.update', $target), [
            $field => ['missing-fixture-id'],
        ])->assertForbidden();

        $this->assertSame($before, $this->accessSnapshot($target));
        $this->assertSame($auditCount, DB::table('audit_logs')->count());
    }

    public function test_authorized_ordinary_role_lifecycle_preserves_existing_responses(): void
    {
        $this->authorizeAssignmentManager();
        $this->actingAs($this->manager)->get(route('admin.roles.index', ['search' => $this->allowedRole->name]))
            ->assertOk()->assertSee($this->allowedRole->name);
        $this->get(route('admin.roles.create'))->assertOk()->assertSee(self::INHERITED_PERMISSION);
        $this->get(route('admin.roles.show', $this->allowedRole))->assertOk()->assertSee($this->allowedRole->name);
        $this->get(route('admin.roles.edit', $this->allowedRole))->assertOk()->assertSee($this->allowedRole->name);

        $this->post(route('admin.roles.store'), ['name' => 'Slice 2 Empty Ordinary Role'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.index'));
        $role = Role::where('name', 'Slice 2 Empty Ordinary Role')->firstOrFail();
        $this->assertCount(0, $role->permissions);

        $this->put(route('admin.roles.update', $role), ['name' => 'Slice 2 Renamed Ordinary Role'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.index'));
        $this->assertSame('Slice 2 Renamed Ordinary Role', $role->fresh()->name);
        $this->assertSame('web', $role->fresh()->guard_name);

        $this->delete(route('admin.roles.destroy', $role))->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);

        $reserved = Role::findOrCreate(SystemRoles::SUPER_ADMIN, 'web');
        $this->from(route('admin.roles.index'))->delete(route('admin.roles.destroy', $reserved))
            ->assertRedirect(route('admin.roles.index'))->assertSessionHas('error');
        $this->assertDatabaseHas('roles', ['id' => $reserved->id, 'name' => SystemRoles::SUPER_ADMIN]);
    }

    private function permissionIds(array $names): array
    {
        return array_map(fn (string $name): string => Permission::findByName($name, 'web')->id, $names);
    }

    public static function assignmentAttempts(): array
    {
        return [
            'direct permissions to self' => ['permissions', true],
            'direct permissions to another user' => ['permissions', false],
            'role to self' => ['roles', true],
            'role to another user' => ['roles', false],
        ];
    }

    public static function memberTargets(): array
    {
        return [
            'self' => [true],
            'another user' => [false],
        ];
    }

    public static function creationAuthorities(): array
    {
        return [
            'without assignment authority and with allowed roles' => [false],
            'with assignment authority and with excessive roles' => [true],
        ];
    }

    public static function serviceDelegationAttempts(): array
    {
        return [
            'roles without assignment authority' => ['assignRoles', false],
            'permissions without assignment authority' => ['assignPermissions', false],
            'roles exceeding the ceiling' => ['assignRoles', true],
            'permissions exceeding the ceiling' => ['assignPermissions', true],
        ];
    }

    public static function accessFields(): array
    {
        return ['roles' => ['roles'], 'permissions' => ['permissions']];
    }
}
