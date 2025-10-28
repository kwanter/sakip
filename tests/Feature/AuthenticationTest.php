<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_page_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/sakip');
    }

    /** @test */
    public function users_cannot_authenticate_with_invalid_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function users_cannot_authenticate_with_nonexistent_email()
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function email_is_required_for_login()
    {
        $response = $this->post('/login', [
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function password_is_required_for_login()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function email_must_be_valid_format()
    {
        $response = $this->post('/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function authenticated_users_are_redirected_from_login_page()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/login');

        $response->assertRedirect('/sakip');
    }

    /** @test */
    public function users_can_logout()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }

    /** @test */
    public function guests_cannot_access_protected_routes()
    {
        $response = $this->get('/sakip');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function unverified_users_are_redirected_to_verification_notice()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/sakip');

        $response->assertRedirect('/email/verify');
    }

    /** @test */
    public function verified_users_can_access_protected_routes()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/sakip');

        $response->assertStatus(200);
    }

    /** @test */
    public function remember_me_functionality_works()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
            'remember' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/sakip');
        $this->assertNotNull(auth()->user()->getRememberToken());
    }

    /** @test */
    public function session_is_regenerated_on_successful_login()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);

        $oldSessionId = session()->getId();

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $newSessionId = session()->getId();

        $this->assertNotEquals($oldSessionId, $newSessionId);
    }

    /** @test */
    public function login_attempts_are_throttled()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Make 5 failed login attempts
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // 6th attempt should be throttled
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }

    /** @test */
    public function email_verification_notice_is_shown_to_unverified_users()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/email/verify');

        $response->assertStatus(200);
        $response->assertViewIs('auth.verify-email');
    }

    /** @test */
    public function verified_users_are_redirected_from_verification_notice()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/email/verify');

        $response->assertRedirect('/sakip');
    }

    /** @test */
    public function email_can_be_verified_with_valid_link()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = \URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user);

        $response = $this->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect('/sakip');
    }

    /** @test */
    public function email_cannot_be_verified_with_invalid_hash()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $verificationUrl = \URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => 'invalid-hash']
        );

        $this->actingAs($user);

        $response = $this->get($verificationUrl);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
        $response->assertStatus(403);
    }

    /** @test */
    public function email_verification_resend_is_throttled()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        // First 3 attempts should succeed
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post('/email/resend');
            $this->assertNotEquals(429, $response->status());
        }

        // 4th attempt should be throttled
        $response = $this->post('/email/resend');
        $response->assertStatus(429);
    }

    /** @test */
    public function password_field_is_hidden_in_failed_login_response()
    {
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Password should not be in the old input
        $this->assertArrayNotHasKey('password', session()->getOldInput());
    }

    /** @test */
    public function no_hardcoded_credentials_in_login_view()
    {
        $response = $this->get('/login');

        $content = $response->getContent();

        // Check that common test credentials are NOT present
        $this->assertStringNotContainsString('test@example.com', $content);
        $this->assertStringNotContainsString('password', $content);
        $this->assertStringNotContainsString('test@sakip', $content);
    }

    /** @test */
    public function dashboard_redirects_based_on_user_role()
    {
        $adminUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $adminUser->assignRole('Super Admin');

        $this->actingAs($adminUser);

        $response = $this->get('/dashboard');

        $response->assertRedirect('/admin');
    }

    /** @test */
    public function regular_user_redirects_to_sakip_dashboard()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertRedirect('/sakip');
    }

    /** @test */
    public function home_route_redirects_unauthenticated_users_to_login()
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function home_route_redirects_authenticated_users_to_sakip()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get('/');

        $response->assertRedirect('/sakip');
    }
}
