<?php

namespace Tests\Feature;

use App\Constants\SystemRoles;
use App\Models\Instansi;
use App\Models\PerformanceIndicator;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AdminReservedRoleTest extends TestCase
{
    protected $seed = false;

    private User $manager;

    private Role $reservedRole;

    private Role $ownedRole;

    private PerformanceIndicator $ownIndicator;

    private PerformanceIndicator $foreignIndicator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        foreach (['admin.dashboard', 'manage-users', 'manage-roles', 'manage-permissions', 'view performance indicators'] as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $ownInstitution = Instansi::factory()->create();
        $foreignInstitution = Instansi::factory()->create();
        $this->reservedRole = Role::findOrCreate(SystemRoles::SUPER_ADMIN, 'web');
        $this->reservedRole->syncPermissions(['manage-users']);
        $this->ownedRole = Role::create(['name' => 'Slice 3 Owned Role', 'guard_name' => 'web']);
        $this->manager = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $ownInstitution->id,
        ]);
        $this->manager->givePermissionTo(['admin.dashboard', 'manage-users', 'manage-roles', 'view performance indicators']);
        $this->manager->assignRole($this->ownedRole);
        $this->ownIndicator = $this->makeIndicator($ownInstitution, 'S3-OWN', 'Slice 3 Own Indicator');
        $this->foreignIndicator = $this->makeIndicator($foreignInstitution, 'S3-FOREIGN', 'Slice 3 Foreign Sentinel');
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->assertTrue($this->manager->hasVerifiedEmail());
        $this->assertTrue($this->manager->can('admin.dashboard'));
        $this->assertTrue($this->manager->can('manage-roles'));
        $this->assertFalse($this->manager->hasRole(SystemRoles::SUPER_ADMIN));
        $this->assertFalse($this->manager->hasPermissionTo('manage-permissions'));
        $this->assertFalse($this->manager->can('manage-permissions'));
        $this->indicatorResponse($this->manager, $this->ownIndicator)
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('indicator.id', $this->ownIndicator->id);
        $this->indicatorResponse($this->manager, $this->foreignIndicator)->assertNotFound();
        $this->actingAs($this->manager->fresh())->get(route('admin.permissions.index'))->assertForbidden();
    }

    private function makeIndicator(Instansi $institution, string $code, string $name): PerformanceIndicator
    {
        return PerformanceIndicator::create([
            'instansi_id' => $institution->id,
            'code' => $code,
            'name' => $name,
            'measurement_unit' => 'unit',
            'frequency' => 'tahunan',
            'category' => 'output',
            'calculation_formula' => [],
        ]);
    }

    private function indicatorResponse(User $actor, PerformanceIndicator $indicator): TestResponse
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $this->actingAs($actor->fresh())->getJson(route('sakip.api.indicators.performance-data', [
            'indicator' => $indicator->id,
            'year' => 2026,
        ]));
    }

    private function superAdminActor(): User
    {
        $actor = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->manager->instansi_id,
        ]);
        $actor->assignRole($this->reservedRole);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->assertTrue($actor->hasRole(SystemRoles::SUPER_ADMIN));

        return $actor;
    }

    public function test_manager_cannot_release_reserved_name_and_reclaim_it_on_its_own_role(): void
    {
        $originalOwnedName = $this->ownedRole->name;
        $originalRoleIds = $this->manager->roles->modelKeys();
        $originalPermissions = $this->manager->getAllPermissions()->pluck('id')->sort()->values()->all();

        $release = $this->actingAs($this->manager->fresh())->put(route('admin.roles.update', $this->reservedRole), [
            'name' => 'Slice 3 Released Reserved Name',
            'guard_name' => 'web',
        ]);
        $release->assertSessionHasNoErrors();
        $claim = $this->actingAs($this->manager->fresh())->put(route('admin.roles.update', $this->ownedRole), [
            'name' => SystemRoles::SUPER_ADMIN,
            'guard_name' => 'web',
        ]);
        $claim->assertSessionHasNoErrors();

        $actor = $this->manager->fresh();
        $foreign = $this->indicatorResponse($actor, $this->foreignIndicator);
        $gateProbe = $this->actingAs($actor->fresh())->get(route('admin.permissions.index'));
        $this->assertSame($originalRoleIds, $actor->roles->modelKeys());
        $this->assertSame($originalPermissions, $actor->getAllPermissions()->pluck('id')->sort()->values()->all());
        $this->assertFalse($actor->hasPermissionTo('manage-permissions'));

        $this->assertSame([
            'reserved_name_unchanged' => true,
            'owned_name_unchanged' => true,
            'actor_became_super_admin' => false,
            'release_http_status' => 403,
            'claim_http_status' => 403,
            'sensitive_http_status' => 403,
            'foreign_http_status' => 404,
            'foreign_indicator_disclosed' => false,
            'foreign_name_disclosed' => false,
        ], [
            'reserved_name_unchanged' => $this->reservedRole->fresh()->name === SystemRoles::SUPER_ADMIN,
            'owned_name_unchanged' => $this->ownedRole->fresh()->name === $originalOwnedName,
            'actor_became_super_admin' => $actor->hasRole(SystemRoles::SUPER_ADMIN),
            'release_http_status' => $release->status(),
            'claim_http_status' => $claim->status(),
            'sensitive_http_status' => $gateProbe->status(),
            'foreign_http_status' => $foreign->status(),
            'foreign_indicator_disclosed' => $foreign->json('indicator.id') === $this->foreignIndicator->id,
            'foreign_name_disclosed' => $foreign->json('indicator.name') === $this->foreignIndicator->name,
        ], 'Two role renames changed the reserved identity and granted Gate/tenant bypass without changing permission grants.');
    }

    public function test_manager_cannot_rename_its_role_into_an_available_reserved_name(): void
    {
        $this->reservedRole->update(['name' => 'Slice 3 Placeholder Role']);
        $this->assertDatabaseMissing('roles', ['name' => SystemRoles::SUPER_ADMIN]);
        $originalName = $this->ownedRole->name;

        $response = $this->actingAs($this->manager)->put(route('admin.roles.update', $this->ownedRole), [
            'name' => SystemRoles::SUPER_ADMIN,
            'guard_name' => 'web',
        ]);
        $response->assertSessionHasNoErrors();
        $actor = $this->manager->fresh();
        $foreign = $this->indicatorResponse($actor, $this->foreignIndicator);

        $this->assertSame([
            'owned_name_unchanged' => true,
            'actor_became_super_admin' => false,
            'mutation_http_status' => 403,
            'foreign_http_status' => 404,
            'foreign_indicator_disclosed' => false,
            'foreign_name_disclosed' => false,
        ], [
            'owned_name_unchanged' => $this->ownedRole->fresh()->name === $originalName,
            'actor_became_super_admin' => $actor->hasRole(SystemRoles::SUPER_ADMIN),
            'mutation_http_status' => $response->status(),
            'foreign_http_status' => $foreign->status(),
            'foreign_indicator_disclosed' => $foreign->json('indicator.id') === $this->foreignIndicator->id,
            'foreign_name_disclosed' => $foreign->json('indicator.name') === $this->foreignIndicator->name,
        ], 'Renaming an existing membership into the reserved name bypassed assignment and permission-ceiling controls.');
    }

    public function test_manager_cannot_create_a_role_with_an_available_reserved_name(): void
    {
        $this->reservedRole->update(['name' => 'Slice 3 Placeholder Role']);
        $this->assertDatabaseMissing('roles', ['name' => SystemRoles::SUPER_ADMIN]);

        $response = $this->actingAs($this->manager)->post(route('admin.roles.store'), [
            'name' => SystemRoles::SUPER_ADMIN,
            'guard_name' => 'web',
            'permissions' => [],
        ]);
        $response->assertSessionHasNoErrors();

        $this->assertSame([
            'reserved_role_created' => false,
            'creation_http_status' => 403,
        ], [
            'reserved_role_created' => Role::where('name', SystemRoles::SUPER_ADMIN)->exists(),
            'creation_http_status' => $response->status(),
        ], 'A non-Super-Admin role manager created the reserved role even with an empty permission payload.');
    }

    #[DataProvider('actorKinds')]
    public function test_reserved_role_cannot_be_renamed_away_even_by_super_admin(bool $super): void
    {
        $actor = $super ? $this->superAdminActor() : $this->manager;
        $response = $this->actingAs($actor)->put(route('admin.roles.update', $this->reservedRole), [
            'name' => 'Slice 3 Renamed Reserved Role',
            'guard_name' => 'web',
        ]);
        $response->assertSessionHasNoErrors();

        $this->assertSame([
            'reserved_name_unchanged' => true,
            'mutation_http_status' => 403,
            'actor_super_admin_status' => $super,
        ], [
            'reserved_name_unchanged' => $this->reservedRole->fresh()->name === SystemRoles::SUPER_ADMIN,
            'mutation_http_status' => $response->status(),
            'actor_super_admin_status' => $actor->fresh()->hasRole(SystemRoles::SUPER_ADMIN),
        ], 'The reserved identity was renamed, breaking the name-based authorization invariant.');
    }

    public function test_manager_cannot_change_the_reserved_role_guard(): void
    {
        $before = $this->reservedRole->only(['name', 'guard_name']);
        $response = $this->actingAs($this->manager)->put(route('admin.roles.update', $this->reservedRole), [
            'name' => SystemRoles::SUPER_ADMIN,
            'guard_name' => 'api',
        ]);
        $response->assertSessionHasNoErrors();

        $this->assertSame([
            'reserved_identity_unchanged' => true,
            'mutation_http_status' => 403,
        ], [
            'reserved_identity_unchanged' => $before === $this->reservedRole->fresh()->only(array_keys($before)),
            'mutation_http_status' => $response->status(),
        ], 'A non-Super-Admin actor changed the reserved role guard namespace.');
    }

    #[DataProvider('permissionMutations')]
    public function test_manager_cannot_mutate_reserved_role_permissions_within_its_ceiling(bool $clear): void
    {
        $before = $this->reservedRole->permissions->pluck('id')->sort()->values()->all();
        $ids = $clear ? [] : $this->permissionIds(['manage-users', 'admin.dashboard']);
        $this->assertTrue($this->manager->can('grantPermissions', [User::class, $ids]));
        $response = $this->actingAs($this->manager)->post(route('admin.roles.permissions.update', $this->reservedRole), [
            'permissions' => $ids,
        ]);
        $response->assertSessionHasNoErrors();

        $this->assertSame([
            'permissions_unchanged' => true,
            'mutation_http_status' => 403,
        ], [
            'permissions_unchanged' => $before === $this->reservedRole->fresh()->permissions->pluck('id')->sort()->values()->all(),
            'mutation_http_status' => $response->status(),
        ], 'A role manager mutated the reserved role despite satisfying the separate permission-ceiling check.');
    }

    public function test_manager_can_rename_an_ordinary_role_without_changing_its_authority(): void
    {
        $this->actingAs($this->manager)->put(route('admin.roles.update', $this->ownedRole), [
            'name' => 'Slice 3 Renamed Ordinary Role',
            'guard_name' => 'web',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.index'));

        $this->assertSame('Slice 3 Renamed Ordinary Role', $this->ownedRole->fresh()->name);
        $this->assertFalse($this->manager->fresh()->hasRole(SystemRoles::SUPER_ADMIN));
        $this->indicatorResponse($this->manager, $this->foreignIndicator)->assertNotFound();
    }

    public function test_manager_can_create_edit_and_delete_an_ordinary_role(): void
    {
        $this->actingAs($this->manager)->post(route('admin.roles.store'), [
            'name' => 'Slice 3 Ordinary Lifecycle Role',
            'guard_name' => 'web',
            'permissions' => $this->permissionIds(['manage-users']),
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.index'));
        $role = Role::where('name', 'Slice 3 Ordinary Lifecycle Role')->firstOrFail();
        $this->assertTrue($role->hasPermissionTo('manage-users'));

        $this->post(route('admin.roles.permissions.update', $role), ['permissions' => []])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.show', $role));
        $this->assertCount(0, $role->fresh()->permissions);
        $this->delete(route('admin.roles.destroy', $role))->assertRedirect(route('admin.roles.index'));
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    #[DataProvider('actorKinds')]
    public function test_existing_reserved_role_deletion_prohibition_preserves_role_and_members(bool $super): void
    {
        $actor = $super ? $this->superAdminActor() : $this->manager;
        $beforeMembers = $this->reservedRole->users->pluck('id')->sort()->values()->all();
        $beforePermissions = $this->reservedRole->permissions->pluck('id')->sort()->values()->all();
        $this->actingAs($actor)->from(route('admin.roles.index'))->delete(route('admin.roles.destroy', $this->reservedRole))
            ->assertRedirect(route('admin.roles.index'))->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['id' => $this->reservedRole->id, 'name' => SystemRoles::SUPER_ADMIN]);
        $this->assertSame($beforeMembers, $this->reservedRole->fresh()->users->pluck('id')->sort()->values()->all());
        $this->assertSame($beforePermissions, $this->reservedRole->fresh()->permissions->pluck('id')->sort()->values()->all());
    }

    #[DataProvider('permissionMutations')]
    public function test_super_admin_can_perform_authorized_reserved_role_permission_edits(bool $clear): void
    {
        $actor = $this->superAdminActor();
        $ids = $clear ? [] : $this->permissionIds(['admin.dashboard']);
        $this->actingAs($actor)->post(route('admin.roles.permissions.update', $this->reservedRole), ['permissions' => $ids])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.show', $this->reservedRole));

        $this->assertSame($ids, $this->reservedRole->fresh()->permissions->modelKeys());
        $this->assertSame(SystemRoles::SUPER_ADMIN, $this->reservedRole->fresh()->name);
        $this->assertTrue($actor->fresh()->hasRole(SystemRoles::SUPER_ADMIN));
        $this->indicatorResponse($actor, $this->foreignIndicator)
            ->assertOk()->assertJsonPath('indicator.id', $this->foreignIndicator->id);
    }

    public function test_legitimate_super_admin_can_read_the_foreign_indicator(): void
    {
        $this->indicatorResponse($this->superAdminActor(), $this->foreignIndicator)
            ->assertOk()->assertJsonPath('success', true)
            ->assertJsonPath('indicator.id', $this->foreignIndicator->id)
            ->assertJsonPath('indicator.name', $this->foreignIndicator->name);
    }

    #[DataProvider('protectedValidationAttempts')]
    public function test_non_super_admin_reserved_mutations_are_denied_before_validation(string $action): void
    {
        $before = $this->reservedRole->only(['name', 'guard_name']);
        $permissions = $this->reservedRole->permissions->modelKeys();
        $this->actingAs($this->manager);

        $response = match ($action) {
            'create' => $this->postJson(route('admin.roles.store'), ['name' => SystemRoles::SUPER_ADMIN]),
            'claim' => $this->putJson(route('admin.roles.update', $this->ownedRole), ['name' => SystemRoles::SUPER_ADMIN]),
            'update' => $this->putJson(route('admin.roles.update', $this->reservedRole), []),
            'permissions' => $this->postJson(route('admin.roles.permissions.update', $this->reservedRole), [
                'permissions' => ['missing-fixture-id'],
            ]),
        };

        $response->assertForbidden();
        $this->assertSame($before, $this->reservedRole->fresh()->only(array_keys($before)));
        $this->assertSame($permissions, $this->reservedRole->fresh()->permissions->modelKeys());
        $this->assertFalse($this->manager->fresh()->hasRole(SystemRoles::SUPER_ADMIN));
    }

    public function test_super_admin_can_update_reserved_identity_without_renaming_it(): void
    {
        $actor = $this->superAdminActor();
        $before = $this->reservedRole->only(['id', 'name', 'guard_name']);

        $this->actingAs($actor)->put(route('admin.roles.update', $this->reservedRole), [
            'name' => SystemRoles::SUPER_ADMIN,
            'guard_name' => 'web',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.roles.index'));

        $this->assertSame($before, $this->reservedRole->fresh()->only(array_keys($before)));
        $this->assertTrue($actor->fresh()->hasRole(SystemRoles::SUPER_ADMIN));
        $this->indicatorResponse($actor, $this->foreignIndicator)->assertOk()
            ->assertJsonPath('indicator.id', $this->foreignIndicator->id);
    }

    public function test_authorized_super_admin_reserved_inputs_keep_validation_errors(): void
    {
        $actor = $this->superAdminActor();
        $before = $this->reservedRole->only(['name', 'guard_name']);
        $this->actingAs($actor)->putJson(route('admin.roles.update', $this->reservedRole), [])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->postJson(route('admin.roles.store'), ['name' => SystemRoles::SUPER_ADMIN])
            ->assertUnprocessable()->assertJsonValidationErrors('name');

        $this->assertSame($before, $this->reservedRole->fresh()->only(array_keys($before)));
    }

    public function test_invalid_ordinary_role_inputs_still_return_validation_errors(): void
    {
        $before = $this->ownedRole->name;
        $this->actingAs($this->manager)->postJson(route('admin.roles.store'), [])
            ->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->putJson(route('admin.roles.update', $this->ownedRole), [])
            ->assertUnprocessable()->assertJsonValidationErrors('name');

        $this->assertSame($before, $this->ownedRole->fresh()->name);
    }

    private function permissionIds(array $names): array
    {
        return array_map(fn (string $name): string => Permission::findByName($name, 'web')->id, $names);
    }

    public static function actorKinds(): array
    {
        return ['non-Super-Admin manager' => [false], 'Super Admin actor' => [true]];
    }

    public static function permissionMutations(): array
    {
        return ['clear grants' => [true], 'set grants' => [false]];
    }

    public static function protectedValidationAttempts(): array
    {
        return [
            'create duplicate reserved name' => ['create'],
            'claim duplicate reserved name' => ['claim'],
            'update reserved role with missing name' => ['update'],
            'reserved permission input with missing ID' => ['permissions'],
        ];
    }
}
