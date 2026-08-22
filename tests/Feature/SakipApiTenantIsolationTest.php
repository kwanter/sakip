<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Instansi;
use App\Http\Controllers\Api\Sakip\SakipApiController;

/**
 * SECURITY: sakip api must never honor a client-supplied foreign instansi_id.
 * Non-HQ users are pinned to their own institution regardless of input.
 */
class SakipApiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUserForInstansi(string $name): array
    {
        $instansi = Instansi::create(['kode_instansi' => str()->random(8), 'nama_instansi' => $name]);
        $user = User::factory()->create(['email_verified_at' => now(), 'instansi_id' => $instansi->id]);
        return [$user, $instansi];
    }

    private function guard(): \ReflectionMethod
    {
        // Controller constructor needs services; bypass with reflection.
        return new \ReflectionMethod(SakipApiController::class, 'resolveInstansiId');
    }

    private function invoke(mixed $requested): mixed
    {
        $m = $this->guard();
        $m->setAccessible(true);
        $ctrl = app()->makeWith(SakipApiController::class, []);
        return $m->invoke($ctrl, $requested);
    }

    public function test_user_is_pinned_to_own_instansi_despite_foreign_input()
    {
        [$userA, $instA] = $this->makeUserForInstansi('Instansi A');
        [, $instB] = $this->makeUserForInstansi('Instansi B');

        $this->actingAs($userA);

        $this->assertSame($instA->id, $this->invoke((string) $instB->id));
        $this->assertSame($instA->id, $this->invoke(null));
    }

    public function test_hq_user_without_instansi_may_request_any()
    {
        $hq = User::factory()->create(['email_verified_at' => now(), 'instansi_id' => null]);
        [, $instB] = $this->makeUserForInstansi('Instansi B');

        $this->actingAs($hq);

        $this->assertSame($instB->id, $this->invoke((string) $instB->id));
        $this->assertNull($this->invoke(null));
    }
}
