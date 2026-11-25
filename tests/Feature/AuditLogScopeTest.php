<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Instansi;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (audit run-3, item 13): the cross-tenant audit view was gated on a
 * hardcoded Super Admin role, so the boundary could not be delegated and the
 * permission model was bypassed. It is now a permission with the role kept as a
 * fallback.
 *
 * Tested at the policy seam on purpose: the audit index Blade view still reads
 * event_type/auditable_type, columns the audit_logs table does not have, so
 * asserting through the page would test a broken view rather than the boundary.
 */
class AuditLogScopeTest extends TestCase
{
    use RefreshDatabase;

    private Instansi $own;

    private User $auditor;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['view-audit-trails', 'view-all-audit-logs'] as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        $this->own = Instansi::factory()->create();

        $this->auditor = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->own->id,
        ]);
        $this->auditor->givePermissionTo('view-audit-trails');
    }

    #[Test]
    public function an_auditor_without_the_permission_is_limited_to_their_instansi(): void
    {
        $this->assertTrue($this->auditor->can('viewAny', AuditLog::class));
        $this->assertFalse($this->auditor->can('viewAll', AuditLog::class));
    }

    #[Test]
    public function the_permission_grants_the_system_wide_view(): void
    {
        $this->auditor->givePermissionTo('view-all-audit-logs');

        $this->assertTrue($this->auditor->fresh()->can('viewAll', AuditLog::class));
    }

    #[Test]
    public function super_admin_keeps_the_system_wide_view_without_the_new_permission(): void
    {
        $superAdmin = User::factory()->create(['email_verified_at' => now()]);
        $superAdmin->assignRole(Role::findOrCreate('Super Admin', 'web'));

        $this->assertTrue($superAdmin->fresh()->can('viewAll', AuditLog::class));
    }
}
