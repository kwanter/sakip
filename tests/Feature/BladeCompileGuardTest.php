<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * A malformed Blade echo (e.g. a ternary badge missing its closing `}}`)
 * still "looks fine" in review but produces a fatal PHP parse error on
 * render, taking the whole page down with a 500. Compiling every view in
 * the suite catches that class of bug before it ships.
 */
class BladeCompileGuardTest extends TestCase
{
    #[Test]
    public function all_blade_views_compile()
    {
        $this->artisan('view:cache')->assertExitCode(0);

        $this->artisan('view:clear')->assertExitCode(0);

        $this->assertTrue(true);
    }
}
