<?php

namespace Tests\Unit;

use App\Http\Middleware\SecurityHeadersMiddleware;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    protected SecurityHeadersMiddleware $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new SecurityHeadersMiddleware();
    }

    /** @test */
    public function it_adds_x_content_type_options_header()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Test');
        });

        $this->assertEquals('nosniff', $response->headers->get('X-Content-Type-Options'));
    }

    /** @test */
    public function it_adds_x_frame_options_header()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Test');
        });

        $this->assertEquals('SAMEORIGIN', $response->headers->get('X-Frame-Options'));
    }

    /** @test */
    public function it_adds_x_xss_protection_header()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Test');
        });

        $this->assertEquals('1; mode=block', $response->headers->get('X-XSS-Protection'));
    }

    /** @test */
    public function it_adds_referrer_policy_header()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Test');
        });

        $this->assertEquals('strict-origin-when-cross-origin', $response->headers->get('Referrer-Policy'));
    }

    /** @test */
    public function it_adds_permissions_policy_header()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Test');
        });

        $this->assertStringContainsString('geolocation=()', $response->headers->get('Permissions-Policy'));
        $this->assertStringContainsString('microphone=()', $response->headers->get('Permissions-Policy'));
    }

    /** @test */
    public function it_adds_content_security_policy_header()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Test');
        });

        $csp = $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
    }

    /** @test */
    public function it_adds_hsts_header_in_production()
    {
        $this->app->detectEnvironment(function () {
            return 'production';
        });

        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Test');
        });

        $hsts = $response->headers->get('Strict-Transport-Security');
        $this->assertNotNull($hsts);
        $this->assertStringContainsString('max-age=31536000', $hsts);
        $this->assertStringContainsString('includeSubDomains', $hsts);
    }

    /** @test */
    public function it_does_not_add_hsts_header_in_development()
    {
        $this->app->detectEnvironment(function () {
            return 'local';
        });

        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Test');
        });

        $this->assertNull($response->headers->get('Strict-Transport-Security'));
    }

    /** @test */
    public function it_removes_sensitive_headers()
    {
        $request = Request::create('/test', 'GET');

        $response = new Response('Test');
        $response->headers->set('X-Powered-By', 'PHP/8.2');
        $response->headers->set('Server', 'Apache/2.4');

        $response = $this->middleware->handle($request, function ($req) use ($response) {
            return $response;
        });

        $this->assertNull($response->headers->get('X-Powered-By'));
        $this->assertNull($response->headers->get('Server'));
    }

    /** @test */
    public function it_includes_development_csp_rules_in_local_environment()
    {
        $this->app->detectEnvironment(function () {
            return 'local';
        });

        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Test');
        });

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('localhost', $csp);
        $this->assertStringContainsString('127.0.0.1', $csp);
    }

    /** @test */
    public function it_does_not_include_development_csp_rules_in_production()
    {
        $this->app->detectEnvironment(function () {
            return 'production';
        });

        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Test');
        });

        $csp = $response->headers->get('Content-Security-Policy');
        $this->assertStringNotContainsString('localhost', $csp);
    }

    /** @test */
    public function it_allows_all_headers_to_coexist()
    {
        $request = Request::create('/test', 'GET');

        $response = $this->middleware->handle($request, function ($req) {
            return new Response('Test');
        });

        // Verify all critical headers are present
        $this->assertNotNull($response->headers->get('X-Content-Type-Options'));
        $this->assertNotNull($response->headers->get('X-Frame-Options'));
        $this->assertNotNull($response->headers->get('X-XSS-Protection'));
        $this->assertNotNull($response->headers->get('Referrer-Policy'));
        $this->assertNotNull($response->headers->get('Permissions-Policy'));
        $this->assertNotNull($response->headers->get('Content-Security-Policy'));
    }
}
