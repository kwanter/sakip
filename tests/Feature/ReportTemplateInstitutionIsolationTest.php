<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\PerformanceIndicator;
use App\Models\Permission;
use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * SECURITY (audit run-2, report-template.catalog-missing-instansi-filter):
 * report template catalogs expose only active global templates or templates
 * within the viewer's Cakupan Instansi, and the store path must reject a
 * foreign template_id. Global (null-instansi) templates stay available.
 */
class ReportTemplateInstitutionIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = false;

    private Instansi $instansiA;

    private Instansi $instansiB;

    private User $viewerA;

    private ReportTemplate $globalTemplate;

    private ReportTemplate $ownTemplate;

    private ReportTemplate $foreignTemplate;

    private ReportTemplate $inactiveTemplate;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['sakip.reports.view', 'sakip.reports.create'] as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $this->instansiA = Instansi::factory()->create(['nama_instansi' => 'Instansi A']);
        $this->instansiB = Instansi::factory()->create(['nama_instansi' => 'Instansi B']);

        $this->viewerA = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $this->instansiA->id,
        ]);
        $this->viewerA->givePermissionTo(['sakip.reports.view', 'sakip.reports.create']);

        $this->globalTemplate = ReportTemplate::create([
            'name' => 'TPL-GLOBAL-ACTIVE',
            'description' => 'Global active template',
            'module' => 'sakip',
            'is_active' => true,
        ]);
        $this->ownTemplate = ReportTemplate::create([
            'name' => 'TPL-OWN-ACTIVE',
            'description' => 'Own active template',
            'module' => 'sakip',
            'is_active' => true,
            'instansi_id' => $this->instansiA->id,
        ]);
        $this->foreignTemplate = ReportTemplate::create([
            'name' => 'TPL-FOREIGN-ACTIVE',
            'description' => 'Foreign active template',
            'module' => 'sakip',
            'is_active' => true,
            'instansi_id' => $this->instansiB->id,
        ]);
        $this->inactiveTemplate = ReportTemplate::create([
            'name' => 'TPL-OWN-INACTIVE',
            'description' => 'Inactive template',
            'module' => 'sakip',
            'is_active' => false,
            'instansi_id' => $this->instansiA->id,
        ]);
    }

    private function storePayload(ReportTemplate $template): array
    {
        $indicator = PerformanceIndicator::create([
            'instansi_id' => $this->instansiA->id,
            'code' => 'PI-'.str()->random(8),
            'name' => 'Indikator uji',
            'measurement_unit' => 'unit',
            'frequency' => 'annual',
            'category' => 'output',
            'created_by' => null,
            'updated_by' => null,
        ]);

        return [
            'report_type' => 'quarterly',
            'period' => '2026-01-01',
            'category' => 'performance',
            'title' => 'Laporan Uji Template',
            'template_id' => $template->id,
            'indicators' => [$indicator->id],
            'format' => 'pdf',
        ];
    }

    #[Test]
    public function report_index_lists_only_global_and_own_instansi_templates(): void
    {
        $this->actingAs($this->viewerA)
            ->get(route('sakip.reports.index'))
            ->assertOk()
            ->assertSee('TPL-GLOBAL-ACTIVE', false)
            ->assertSee('TPL-OWN-ACTIVE', false)
            ->assertDontSee('TPL-FOREIGN-ACTIVE', false)
            ->assertDontSee('TPL-OWN-INACTIVE', false);
    }

    #[Test]
    public function store_rejects_a_foreign_instansi_template(): void
    {
        $this->actingAs($this->viewerA)
            ->postJson(route('sakip.reports.store'), $this->storePayload($this->foreignTemplate))
            ->assertJsonValidationErrors('template_id');

        $this->assertDatabaseMissing('reports', ['title' => 'Laporan Uji Template']);
    }

    #[Test]
    public function store_accepts_an_own_instansi_template(): void
    {
        $this->actingAs($this->viewerA)
            ->postJson(route('sakip.reports.store'), $this->storePayload($this->ownTemplate))
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('reports', ['title' => 'Laporan Uji Template']);
    }

    #[Test]
    public function store_accepts_a_global_template(): void
    {
        $this->actingAs($this->viewerA)
            ->postJson(route('sakip.reports.store'), $this->storePayload($this->globalTemplate))
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('reports', ['title' => 'Laporan Uji Template']);
    }
}
