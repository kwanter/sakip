<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Route;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;
use Tests\TestCase;

/**
 * Regression guards for the architecture remediation plan.
 *
 * These prevent recurrence of the duplicate-class-name and missing-route-name
 * problems that were cleaned up in P0-P2.
 */
class ArchitectureGuardTest extends TestCase
{
    /** @test */
    public function no_duplicate_short_class_names_in_app()
    {
        $classes = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(app_path(), FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }
            $short = $file->getBasename('.php');
            $path = $file->getPathname();

            if (isset($classes[$short])) {
                $this->fail(
                    "Duplicate class name '{$short}' found in:\n".
                    "  {$classes[$short]}\n  {$path}",
                );
            }
            $classes[$short] = $path;
        }

        $this->assertNotEmpty($classes);
    }

    /** @test */
    public function every_blade_route_name_exists()
    {
        $routeNames = collect(Route::getRoutes()->getRoutesByName())->keys()->all();

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(resource_path('views'), FilesystemIterator::SKIP_DOTS),
        );

        $missing = [];

        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());

            preg_match_all(
                "/route\(['\"]([a-zA-Z0-9_.-]+)['\"]/",
                $content,
                $matches,
            );

            foreach ($matches[1] as $name) {
                // Skip request()->route() which accesses route parameters
                $pattern = "/request\(\)\s*->\s*route\(\s*['\"]{$name}['\"]/";
                if (preg_match($pattern, $content)) {
                    continue;
                }

                if (! in_array($name, $routeNames, true)) {
                    $relative = str_replace(resource_path(), '', $file->getPathname());
                    $missing[] = "{$name} in {$relative}";
                }
            }
        }

        if (! empty($missing)) {
            $this->fail(
                "Missing route names referenced in Blade views:\n  ".
                implode("\n  ", $missing).
                "\n\nAdd the missing routes or remove the references.",
            );
        }

        $this->assertNotEmpty($routeNames);
    }
}