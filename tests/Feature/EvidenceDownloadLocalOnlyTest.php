<?php

namespace Tests\Feature;

use App\Models\EvidenceDocument;
use App\Models\PerformanceData;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (audit run-2, evidence-download.legacy-public-disk-fallback): the
 * routed evidence download serves from the private local disk only. A path
 * missing on the private disk is a 404 — the web-exposed public disk is never
 * a serving fallback. Policy authorization is unchanged.
 */
class EvidenceDownloadLocalOnlyTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = false;

    protected function setUp(): void
    {
        parent::setUp();

        // The download policy probes sakip.* permissions that the default
        // seeder never creates; define them so hasAnyPermission() behaves
        // (they are not granted to any fixture user, so denials still apply).
        foreach (['sakip.admin', 'sakip.evidence.download', 'sakip.pimpinan', 'sakip.data_collector', 'sakip.assessor', 'sakip.auditor'] as $name) {
            Permission::findOrCreate($name, 'web');
        }
    }

    private function makeSuperAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => 'Super Admin'], ['guard_name' => 'web']);
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole($role);

        return $user;
    }

    private function makeDocument(string $path): EvidenceDocument
    {
        return EvidenceDocument::create([
            'performance_data_id' => PerformanceData::factory()->create()->id,
            'file_name' => 'legacy-evidence.txt',
            'file_path' => $path,
            'file_type' => 'text/plain',
            'file_size' => 12,
        ]);
    }

    #[Test]
    public function missing_local_file_is_not_served_from_the_public_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $path = 'evidence_documents/legacy-public.txt';
        // The file exists ONLY on the web-exposed public disk (legacy layout).
        Storage::disk('public')->put($path, 'PUBLIC-DISK-BYTES');

        $document = $this->makeDocument($path);

        $this->actingAs($this->makeSuperAdmin())
            ->get(route('sakip.evidence.download', $document))
            ->assertNotFound();
    }

    #[Test]
    public function local_disk_file_downloads_with_an_audit_row(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $path = 'evidence_documents/local-file.txt';
        Storage::disk('local')->put($path, 'LOCAL-DISK-BYTES');

        $document = $this->makeDocument($path);
        $superAdmin = $this->makeSuperAdmin();

        $response = $this->actingAs($superAdmin)
            ->get(route('sakip.evidence.download', $document));
        $response->assertOk();
        $this->assertStringContainsString(
            'legacy-evidence.txt',
            (string) $response->headers->get('Content-Disposition'),
        );

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $superAdmin->id,
            'action' => 'DOWNLOAD',
        ]);
    }

    #[Test]
    public function instansi_bound_viewer_still_needs_download_authority(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $path = 'evidence_documents/policy-denied.txt';
        Storage::disk('local')->put($path, 'LOCAL-DISK-BYTES');

        $document = $this->makeDocument($path);
        $viewer = User::factory()->create(['email_verified_at' => now()]);

        $this->actingAs($viewer)
            ->get(route('sakip.evidence.download', $document))
            ->assertForbidden();
    }
}
