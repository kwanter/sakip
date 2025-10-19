<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RedirectFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_from_root()
    {
        $response = $this->get('/');
        $response->assertRedirect(route('login'));
    }

    public function test_unverified_user_is_redirected_to_verification_notice_from_root()
    {
        $user = User::factory()->unverified()->create();
        $response = $this->actingAs($user)->get('/');
        $response->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_is_redirected_to_sakip_dashboard_from_root()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/');
        $response->assertRedirect(route('sakip.dashboard'));
    }

    public function test_login_redirects_to_dashboard_when_verified()
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $response = $this->post(route('auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response->assertRedirect(route('sakip.dashboard'));
    }

    public function test_login_redirects_to_verification_notice_when_unverified()
    {
        $user = User::factory()->unverified()->create(['password' => bcrypt('password')]);
        $response = $this->post(route('auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response->assertRedirect(route('verification.notice'));
    }
}