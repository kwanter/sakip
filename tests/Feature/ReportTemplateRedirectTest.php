<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Permission;
use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The sakip.reports.template route pointed at a controller method that
 * never existed (500 on hit). Intended behaviour: clicking a template
 * sends the user to the create form with that template preselected.
 */
class ReportTemplateRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['sakip.reports.create', 'sakip.admin'] as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
    }

    private function makeUser(): User
    {
        $instansi = Instansi::factory()->create(['kode_instansi' => str()->random(8)]);
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $instansi->id,
        ]);
        $user->givePermissionTo('sakip.reports.create');

        return $user;
    }

    #[Test]
    public function active_template_redirects_to_create_form_with_preselection()
    {
        $user = $this->makeUser();
        $template = ReportTemplate::create([
            'name' => 'Template Triwulanan',
            'module' => 'sakip',
            'type' => 'general',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('sakip.reports.template', $template));

        $response->assertRedirect(route('sakip.reports.create', ['template_id' => $template->id]));
    }

    #[Test]
    public function inactive_template_is_not_found()
    {
        $user = $this->makeUser();
        $template = ReportTemplate::create([
            'name' => 'Template Lama',
            'module' => 'sakip',
            'type' => 'general',
            'is_active' => false,
        ]);

        $response = $this->actingAs($user)->get(route('sakip.reports.template', $template));

        $response->assertNotFound();
    }
}
