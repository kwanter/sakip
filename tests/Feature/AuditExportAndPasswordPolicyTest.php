<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (audit run-3, item 11): the admin user forms accepted an 8
 * character password while the dedicated FormRequests demanded 12 with
 * complexity, so the weak rule was the one that ran.
 */
class AuditExportAndPasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    private Instansi $instansi;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin.dashboard', 'manage-users', 'export-audit-data', 'view-audit-trails'] as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $this->instansi = Instansi::factory()->create();

        $this->admin = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansi->id,
        ]);
        $this->admin->givePermissionTo([
            'admin.dashboard',
            'manage-users',
            'export-audit-data',
            'view-audit-trails',
        ]);
    }

    #[Test]
    public function creating_a_user_rejects_a_password_below_the_policy(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Weak Password',
                'email' => 'weak-'.str()->random(6).'@example.test',
                'password' => 'Ab1!xyzw',
                'password_confirmation' => 'Ab1!xyzw',
                'instansi_id' => $this->instansi->id,
            ])
            ->assertSessionHasErrors('password');
    }

    #[Test]
    public function updating_a_user_rejects_a_password_below_the_policy(): void
    {
        $target = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansi->id,
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'password' => 'Ab1!xyzw',
                'password_confirmation' => 'Ab1!xyzw',
            ])
            ->assertSessionHasErrors('password');
    }

    #[Test]
    public function updating_a_user_still_accepts_a_compliant_password(): void
    {
        $target = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansi->id,
        ]);

        $this->actingAs($this->admin)
            ->put(route('admin.users.update', $target), [
                'name' => $target->name,
                'email' => $target->email,
                'password' => 'Str0ng-Passw0rd!',
                'password_confirmation' => 'Str0ng-Passw0rd!',
            ])
            ->assertSessionHasNoErrors();
    }
}
