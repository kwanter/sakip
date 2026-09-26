<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear rate limiter between tests
        RateLimiter::clear('login');
        RateLimiter::clear('api');
        RateLimiter::clear('api_strict');
    }

    #[Test]
    public function login_is_rate_limited_after_five_attempts()
    {
        // First 5 attempts should go through (even if credentials are wrong)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);

            // Should get validation error or redirect, but not rate limited
            $this->assertNotEquals(429, $response->status());
        }

        // 6th attempt should be rate limited
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }

    #[Test]
    public function login_rate_limit_includes_retry_after_header()
    {
        // Exceed rate limit
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
        $this->assertNotNull($response->headers->get('Retry-After'));
    }

    #[Test]
    public function api_requests_are_rate_limited()
    {
        $user = User::factory()->create();
        $user->email_verified_at = now();
        $user->save();

        $this->actingAs($user);

        // Make 61 requests (limit is 60 per minute)
        for ($i = 0; $i < 61; $i++) {
            $response = $this->getJson('/api/health');

            if ($i < 60) {
                // First 60 should succeed
                $this->assertNotEquals(429, $response->status());
            } else {
                // 61st should be rate limited
                $response->assertStatus(429);
            }
        }
    }

    #[Test]
    public function different_users_have_separate_rate_limits()
    {
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $user2 = User::factory()->create(['email' => 'user2@example.com']);

        // User 1 exhausts their rate limit
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'user1@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // User 2 should still be able to attempt login
        $response = $this->post('/login', [
            'email' => 'user2@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertNotEquals(429, $response->status());
    }

    #[Test]
    public function rate_limit_resets_after_time_window()
    {
        // F1 closed (retro 2026-09-26). The previous version cleared the key `login:email|ip`, but
        // ThrottleRequests namespaces and hashes named-limiter keys (`md5($limiterName.$limit->key)`,
        // vendor/laravel/framework/src/Illuminate/Routing/Middleware/ThrottleRequests.php:134), so
        // that call never matched the live key and the window never reset — the case was skipped as
        // "flaky" rather than wrong. Travelling past the decay is key-agnostic and tests the stated
        // property directly: the limiter must release the account once its window has elapsed.
        // (The clears in setUp() above are no-ops for the same reason; they are left untouched as
        // pre-existing code outside this fix.)
        $payload = ['email' => 'test@example.com', 'password' => 'wrongpassword'];

        // Exhaust the window: 5 attempts per minute per email + IP.
        for ($i = 0; $i < 5; $i++) {
            $this->assertNotEquals(429, $this->post('/login', $payload)->status());
        }

        // While the window is open, the sixth attempt is refused.
        $this->assertSame(429, $this->post('/login', $payload)->status());

        // Once the minute has elapsed the same account may attempt again.
        $this->travel(2)->minutes();

        $this->assertNotEquals(
            429,
            $this->post('/login', $payload)->status(),
            'the limiter must release the account after its window elapses',
        );
    }

    #[Test]
    public function email_verification_resend_is_strictly_rate_limited()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        // First 3 attempts should succeed (limit is 3 per hour)
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post('/email/resend');
            $this->assertNotEquals(429, $response->status());
        }

        // 4th attempt should be rate limited
        $response = $this->post('/email/resend');
        $response->assertStatus(429);
    }

    #[Test]
    public function rate_limit_response_contains_helpful_message()
    {
        // Exceed login rate limit
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        $response = $this->postJson('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
        $response->assertJsonStructure([
            'success',
            'message',
            'retry_after',
        ]);

        $this->assertFalse($response->json('success'));
        $this->assertStringContainsString('Too many', $response->json('message'));
    }

    #[Test]
    public function guest_requests_to_the_root_route_are_redirected_and_never_throttled()
    {
        // Characterisation pin (F1 closed, retro 2026-09-26). The case's original premise — "guest
        // limit is 30 per minute" — described the `guest` limiter declared in
        // App\Providers\RateLimitServiceProvider, but no route attaches it: routes/web.php carries
        // `throttle:login`, `throttle:email_verification` and `throttle:60,1` only. Guests therefore
        // reach `/` unthrottled and are redirected to the login page. This pin states that reality, so
        // wiring `throttle:guest` (a product decision) deliberately breaks it and forces an update
        // here instead of shipping silently. Follow-up: retro action A10.
        for ($i = 1; $i <= 31; $i++) {
            $response = $this->get('/');

            $this->assertSame(
                302,
                $response->status(),
                "guest request {$i} must stay a redirect to login, never 429",
            );
        }
    }

    #[Test]
    public function authenticated_user_can_bypass_guest_rate_limit()
    {
        $user = User::factory()->create();
        $user->email_verified_at = now();
        $user->save();

        $this->actingAs($user);

        // Authenticated users should have higher limits
        for ($i = 0; $i < 40; $i++) {
            $response = $this->get('/sakip');
            $this->assertNotEquals(429, $response->status());
        }
    }

    #[Test]
    public function rate_limiter_works_with_different_ip_addresses()
    {
        // Simulate different IP addresses
        $ip1 = '192.168.1.1';
        $ip2 = '192.168.1.2';

        // First IP exhausts rate limit
        for ($i = 0; $i < 6; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ], ['REMOTE_ADDR' => $ip1]);
        }

        // Second IP should still be able to attempt
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ], ['REMOTE_ADDR' => $ip2]);

        $this->assertNotEquals(429, $response->status());
    }
}
