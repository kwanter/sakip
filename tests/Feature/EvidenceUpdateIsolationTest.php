<?php

namespace Tests\Feature;

use App\Models\EvidenceDocument;
use App\Models\Instansi;
use App\Models\PerformanceData;
use App\Models\PerformanceIndicator;
use App\Models\User;
use App\Services\EvidenceDocumentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * SECURITY: EvidenceDocument updates must only persist the whitelisted fields
 * (description/document_type), never server-pinned file_path/file_name/instansi.
 */
class EvidenceUpdateIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_ignores_server_pinned_fields()
    {
        $instansi = Instansi::create(['kode_instansi' => str()->random(8), 'nama_instansi' => 'A']);
        $indicator = PerformanceIndicator::create([
            'instansi_id' => $instansi->id,
            'code' => str()->random(10),
            'name' => 'Ind',
            'measurement_unit' => 'unit',
            'frequency' => 'tahunan',
            'category' => 'output',
        ]);
        $user = User::factory()->create(['email_verified_at' => now(), 'instansi_id' => $instansi->id]);
        $performanceData = PerformanceData::forceCreate([
            'performance_indicator_id' => $indicator->id,
            'instansi_id' => $instansi->id,
            'period' => '2026-01',
            'submitted_by' => $user->id,
        ]);
        $evidence = EvidenceDocument::create([
            'performance_data_id' => $performanceData->id,
            'instansi_id' => $instansi->id,
            'file_name' => 'original.pdf',
            'file_path' => 'evidence-documents/legit.pdf',
            'file_type' => 'application/pdf',
            'file_size' => 100,
        ]);

        $this->actingAs($user);

        (new EvidenceDocumentService)->updateEvidence($evidence, [
            'description' => 'ok',
            'file_path' => 'evidence-documents/attacker.pdf',
            'file_name' => 'evil.pdf',
        ]);

        $evidence->refresh();
        $this->assertSame('ok', $evidence->description);
        $this->assertSame('evidence-documents/legit.pdf', $evidence->file_path, 'file_path must be server-pinned');
        $this->assertSame('original.pdf', $evidence->file_name, 'file_name must be server-pinned');
    }
}
