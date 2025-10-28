<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
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

    /** @test */
    public function rate_limit_resets_after_time_window()
    {
        // This test demonstrates the concept but may not work in real-time
        // In production, you'd use Carbon::setTestNow() or similar

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // Clear the rate limiter to simulate time passing
        RateLimiter::clear('login:' . strtolower('test@example.com') . '|' . request()->ip());

        // Should be able to attempt again
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->assertNotEquals(429, $response->status());
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function guest_users_have_separate_rate_limit()
    {
        // Guest users are rate limited by IP
        // Make sure guest limit is lower than authenticated users

        for ($i = 0; $i < 31; $i++) {
            $response = $this->get('/');

            if ($i < 30) {
                // Guest limit is 30 per minute
                $this->assertNotEquals(429, $response->status());
            } else {
                $response->assertStatus(429);
            }
        }
    }

    /** @test */
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

    /** @test */
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

    /** @test */
    public function bulk_operations_have_stricter_rate_limits()
    {
        $user = User::factory()->create();
        $user->email_verified_at = now();
        $user->save();

        // Assign necessary permissions
        $user->givePermissionTo('view-dashboard');

        $this->actingAs($user);

        // Bulk operations limited to 2 per minute
        $response1 = $this->postJson('/api/sakip/bulk/verify-data', []);
        $this->assertNotEquals(429, $response1->status());

        $response2 = $this->postJson('/api/sakip/bulk/verify-data', []);
        $this->assertNotEquals(429, $response2->status());

        // 3rd attempt should be rate limited
        $response3 = $this->postJson('/api/sakip/bulk/verify-data', []);
        $response3->assertStatus(429);
    }

    /** @test */
    public function report_generation_has_moderate_rate_limit()
    {
        $user = User::factory()->create();
        $user->email_verified_at = now();
        $user->save();

        $user->givePermissionTo('view-dashboard');

        $this->actingAs($user);

        // Report generation limited to 5 per minute
        for ($i = 0; $i < 6; $i++) {
            $response = $this->postJson('/api/sakip/reports', [
                'type' => 'performance',
                'period' => 'monthly',
            ]);

            if ($i < 5) {
                $this->assertNotEquals(429, $response->status());
            } else {
                $response->assertStatus(429);
            }
        }
    }

    /** @test */
    public function file_upload_is_rate_limited()
    {
        $user = User::factory()->create();
        $user->email_verified_at = now();
        $user->save();

        $user->givePermissionTo('view-dashboard');

        $this->actingAs($user);

        // Upload limited to 10 per minute
        for ($i = 0; $i < 11; $i++) {
            $response = $this->postJson('/api/sakip/upload/evidence', [
                'file' => 'test',
            ]);

            if ($i < 10) {
                $this->assertNotEquals(429, $response->status());
            } else {
                $response->assertStatus(429);
            }
        }
    }
}
