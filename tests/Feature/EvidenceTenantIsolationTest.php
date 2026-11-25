<?php

namespace Tests\Feature;

use App\Models\EvidenceDocument;
use App\Models\Instansi;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (audit run-3, evidence.instansi_id-missing): evidence_documents
 * carried no instansi_id, so every policy comparison against the tenant was
 * made against null and every non-Super-Admin download was a 403. The column
 * now exists, is filled on upload, and drives the download policy.
 */
class EvidenceTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = false;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['sakip.admin', 'sakip.evidence.download', 'sakip.evidence.view', 'sakip.pimpinan', 'sakip.data_collector', 'sakip.assessor', 'sakip.auditor'] as $name) {
            Permission::findOrCreate($name, 'web');
        }
    }

    private function makeUser(Instansi $instansi): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $instansi->id,
        ]);
        $user->givePermissionTo('sakip.evidence.download');

        return $user;
    }

    private function makeDocument(Instansi $instansi): EvidenceDocument
    {
        Storage::fake('local');

        // The indicator owns the tenant; PerformanceData::booted() derives
        // instansi_id from it, so build the chain explicitly.
        $indicator = PerformanceIndicator::factory()->create([
            'instansi_id' => $instansi->id,
        ]);

        $performanceData = PerformanceData::factory()->create([
            'performance_indicator_id' => $indicator->id,
        ]);

        Storage::disk('local')->put('evidence_documents/tenant.txt', 'BYTES');

        return EvidenceDocument::create([
            'performance_data_id' => $performanceData->id,
            'file_name' => 'tenant-evidence.txt',
            'file_path' => 'evidence_documents/tenant.txt',
            'file_type' => 'text/plain',
            'file_size' => 5,
        ]);
    }

    #[Test]
    public function creation_inherits_the_instansi_of_its_performance_data(): void
    {
        $instansi = Instansi::factory()->create();

        $document = $this->makeDocument($instansi);

        $this->assertSame($instansi->id, $document->instansi_id);
        $this->assertSame('draft', $document->status);
    }

    #[Test]
    public function caller_supplied_tenant_and_status_are_overridden(): void
    {
        $instansi = Instansi::factory()->create();
        $foreign = Instansi::factory()->create();

        $indicator = PerformanceIndicator::factory()->create([
            'instansi_id' => $instansi->id,
        ]);
        $performanceData = PerformanceData::factory()->create([
            'performance_indicator_id' => $indicator->id,
        ]);

        // A request payload shaped like EvidenceDocumentService::uploadEvidence()
        // carries both fields; the owner row must win and the state must be draft.
        $document = EvidenceDocument::create([
            'performance_data_id' => $performanceData->id,
            'instansi_id' => $foreign->id,
            'status' => 'validated',
            'file_name' => 'forged.txt',
            'file_path' => 'evidence_documents/forged.txt',
        ]);

        $this->assertSame($instansi->id, $document->fresh()->instansi_id);
        $this->assertSame('draft', $document->fresh()->status);
    }

    #[Test]
    public function same_instansi_viewer_can_download(): void
    {
        $instansi = Instansi::factory()->create();
        $document = $this->makeDocument($instansi);

        $this->actingAs($this->makeUser($instansi))
            ->get(route('sakip.evidence.download', $document))
            ->assertOk();
    }

    #[Test]
    public function foreign_instansi_viewer_is_denied(): void
    {
        $document = $this->makeDocument(Instansi::factory()->create());

        $this->actingAs($this->makeUser(Instansi::factory()->create()))
            ->get(route('sakip.evidence.download', $document))
            ->assertForbidden();
    }
}
