<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Report;
use App\Models\Instansi;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReportIndexRendersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function report_index_page_does_not_500()
    {
        Permission::firstOrCreate(['name' => 'sakip.reports.view']);

        $instansi = Instansi::factory()->create(['kode_instansi' => str()->random(8), 'nama_instansi' => 'Test Instansi']);

        $user = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $instansi->id,
        ]);
        $user->givePermissionTo('sakip.reports.view');

        $report = Report::forceCreate([
            'id' => str()->uuid(),
            'instansi_id' => $instansi->id,
            'generated_by' => $user->id,
            'report_type' => 'quarterly_report',
            'period' => '2024-Q1',
            'status' => 'completed',
            'generated_at' => now(),
        ]);

        $this->actingAs($user);
        $response = $this->get('/sakip/reports');

        $response->assertOk();
    }
}