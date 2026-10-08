<?php

namespace Tests\Feature;

use App\Models\Instansi;
use App\Models\Permission;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * The download route authorizes with 'download', but ReportPolicy never
 * defined that method, so every download was a 403. These tests pin the
 * intended behaviour: same-instansi users with sakip.reports.download may
 * download existing files; foreign instansi users may not.
 */
class ReportDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['sakip.reports.view', 'sakip.reports.download', 'sakip.admin'] as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }
    }

    private function makeUserWithPermissions(Instansi $instansi): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'instansi_id' => $instansi->id,
        ]);
        $user->givePermissionTo(['sakip.reports.view', 'sakip.reports.download']);

        return $user;
    }

    private function makeReport(Instansi $instansi, User $user, ?string $filePath): Report
    {
        return Report::forceCreate([
            'id' => str()->uuid(),
            'instansi_id' => $instansi->id,
            'generated_by' => $user->id,
            'report_type' => 'quarterly_report',
            'period' => '2024-Q1',
            'status' => 'completed',
            'file_path' => $filePath,
            'generated_at' => now(),
        ]);
    }

    #[Test]
    public function same_instansi_user_with_download_permission_receives_the_file()
    {
        Storage::fake('local');

        $instansi = Instansi::factory()->create(['kode_instansi' => str()->random(8)]);
        $user = $this->makeUserWithPermissions($instansi);

        $path = 'reports/'.$instansi->id.'/laporan-q1.pdf';
        Storage::disk('local')->put($path, 'PDF-CONTENT');
        $report = $this->makeReport($instansi, $user, $path);

        $response = $this->actingAs($user)->get(route('sakip.reports.download', $report));

        $response->assertOk();
        $this->assertSame('PDF-CONTENT', $response->streamedContent());
    }

    #[Test]
    public function user_from_another_instansi_cannot_reach_the_report()
    {
        Storage::fake('local');

        $owner = Instansi::factory()->create(['kode_instansi' => str()->random(8)]);
        $outsiderInstansi = Instansi::factory()->create(['kode_instansi' => str()->random(8)]);
        $outsider = $this->makeUserWithPermissions($outsiderInstansi);

        $path = 'reports/'.$owner->id.'/laporan-q1.pdf';
        Storage::disk('local')->put($path, 'PDF-CONTENT');
        $report = $this->makeReport($owner, $outsider, $path);

        $response = $this->actingAs($outsider)->get(route('sakip.reports.download', $report));

        // InstansiScope hides the record entirely, so route model binding
        // yields 404 for foreign-instansi reports (tenant isolation).
        $response->assertNotFound();
    }

    #[Test]
    public function missing_file_returns_not_found_json()
    {
        Storage::fake('local');

        $instansi = Instansi::factory()->create(['kode_instansi' => str()->random(8)]);
        $user = $this->makeUserWithPermissions($instansi);
        $report = $this->makeReport($instansi, $user, null);

        $response = $this->actingAs($user)->get(route('sakip.reports.download', $report));

        $response->assertNotFound();
        $response->assertJson(['success' => false]);
    }
}
