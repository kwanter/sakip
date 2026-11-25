<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (audit run-3, admin-cross-tenant): UserPolicy gated user
 * management on the 'manage-users' permission alone, with no tenant check.
 * A delegated (non-Super-Admin) administrator could therefore list every
 * account in the system, read another institution's user, reset that user's
 * password and move the account into their own institution. Super Admin
 * keeps explicit cross-instansi authority.
 */
class AdminTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Instansi $own;

    private Instansi $foreign;

    private User $manager;

    private User $foreignUser;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin.dashboard', 'manage-users'] as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $this->own = Instansi::factory()->create();
        $this->foreign = Instansi::factory()->create();

        $this->manager = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->own->id,
        ]);
        $this->manager->givePermissionTo(['admin.dashboard', 'manage-users']);

        $this->foreignUser = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->foreign->id,
        ]);

        $this->superAdmin = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => null,
        ]);
        // App\Models\Role (not Spatie's): the local model adds HasUuids, and
        // the pivot columns are uuid-typed.
        $superAdminRole = \App\Models\Role::findOrCreate('Super Admin', 'web');
        $this->superAdmin->assignRole($superAdminRole);
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }

    #[Test]
    public function index_only_lists_users_of_the_callers_instansi(): void
    {
        $response = $this->actingAs($this->manager)
            ->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee($this->manager->email);
        $response->assertDontSee($this->foreignUser->email);
    }

    #[Test]
    public function a_foreign_user_cannot_be_read(): void
    {
        $this->actingAs($this->manager)
            ->get(route('admin.users.show', $this->foreignUser))
            ->assertForbidden();
    }

    #[Test]
    public function a_foreign_user_cannot_be_updated(): void
    {
        $this->actingAs($this->manager)
            ->put(route('admin.users.update', $this->foreignUser), [
                'name' => 'Hijacked',
                'email' => $this->foreignUser->email,
            ])
            ->assertForbidden();

        $this->assertNotSame('Hijacked', $this->foreignUser->fresh()->name);
    }

    #[Test]
    public function a_user_cannot_be_moved_into_another_instansi(): void
    {
        $ownUser = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->own->id,
        ]);

        $this->actingAs($this->manager)
            ->put(route('admin.users.update', $ownUser), [
                'name' => 'Moved',
                'email' => $ownUser->email,
                'instansi_id' => $this->foreign->id,
            ])
            ->assertSessionHasErrors('instansi_id');

        $this->assertSame($this->own->id, $ownUser->fresh()->instansi_id);
    }

    #[Test]
    public function a_delegated_manager_cannot_create_an_account_in_another_instansi(): void
    {
        $this->actingAs($this->manager)
            ->post(route('admin.users.store'), [
                'name' => 'Injected Account',
                'email' => 'injected-'.str()->random(6).'@example.test',
                'password' => 'Str0ng-Passw0rd!',
                'password_confirmation' => 'Str0ng-Passw0rd!',
                'instansi_id' => $this->foreign->id,
            ])
            ->assertSessionHasErrors('instansi_id');

        $this->assertDatabaseMissing('users', [
            'name' => 'Injected Account',
            'instansi_id' => $this->foreign->id,
        ]);
    }

    #[Test]
    public function the_create_form_only_offers_the_callers_instansi(): void
    {
        $this->actingAs($this->manager)
            ->get(route('admin.users.create'))
            ->assertOk()
            ->assertSee($this->own->nama_instansi)
            ->assertDontSee($this->foreign->nama_instansi);
    }

    #[Test]
    public function super_admin_keeps_cross_instansi_authority(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.users.show', $this->foreignUser))
            ->assertOk();

        $this->actingAs($this->superAdmin)
            ->put(route('admin.users.update', $this->foreignUser), [
                'name' => 'Moved By Super Admin',
                'email' => $this->foreignUser->email,
                'instansi_id' => $this->own->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($this->own->id, $this->foreignUser->fresh()->instansi_id);
    }
}
