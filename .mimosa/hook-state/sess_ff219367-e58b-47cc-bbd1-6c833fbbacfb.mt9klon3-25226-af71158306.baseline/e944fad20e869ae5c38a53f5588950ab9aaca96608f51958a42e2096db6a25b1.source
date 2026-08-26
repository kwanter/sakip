<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckMissingClasses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sakip:check-missing-classes
                            {--fix : Automatically fix namespace issues}
                            {--path= : Specific path to check (e.g., app/Http/Controllers)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for missing class imports and namespace issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking for missing class imports...');
        $this->newLine();

        $path = $this->option('path') ?: 'app';
        $basePath = base_path($path);

        if (!File::exists($basePath)) {
            $this->error("Path does not exist: {$path}");
            return 1;
        }

        $issues = $this->scanDirectory($basePath);

        if (empty($issues)) {
            $this->info('✅ No missing class imports found!');
            return 0;
        }

        $this->warn("⚠️  Found " . count($issues) . " potential issue(s):");
        $this->newLine();

        foreach ($issues as $issue) {
            $this->displayIssue($issue);
        }

        if ($this->option('fix')) {
            $this->newLine();
            $this->info('🔧 Attempting to fix issues...');
            $fixed = $this->fixIssues($issues);
            $this->info("✅ Fixed {$fixed} issue(s)");
        } else {
            $this->newLine();
            $this->info('💡 Tip: Run with --fix option to automatically fix namespace issues');
        }

        return count($issues) > 0 ? 1 : 0;
    }

    /**
     * Scan directory for PHP files and check imports.
     *
     * @param string $directory
     * @return array
     */
    protected function scanDirectory(string $directory): array
    {
        $issues = [];
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $fileIssues = $this->checkFile($file->getPathname());
            if (!empty($fileIssues)) {
                $issues = array_merge($issues, $fileIssues);
            }
        }

        return $issues;
    }

    /**
     * Check a single file for missing imports.
     *
     * @param string $filepath
     * @return array
     */
    protected function checkFile(string $filepath): array
    {
        $issues = [];
        $content = File::get($filepath);
        $lines = explode("\n", $content);

        // Extract namespace
        $namespace = $this->extractNamespace($content);

        // Extract use statements
        $useStatements = $this->extractUseStatements($content);

        // Look for common patterns that might indicate missing imports
        foreach ($lines as $lineNumber => $line) {
            // Check for Request classes
            if (preg_match('/use\s+App\\\\Http\\\\Requests\\\\(\w+Request)/', $line, $matches)) {
                $className = $matches[1];
                $expectedPath = base_path("app/Http/Requests/{$className}.php");
                $sakipPath = base_path("app/Http/Requests/Sakip/{$className}.php");

                if (!File::exists($expectedPath) && File::exists($sakipPath)) {
                    $issues[] = [
                        'type' => 'wrong_namespace',
                        'file' => $filepath,
                        'line' => $lineNumber + 1,
                        'class' => $className,
                        'current' => "App\\Http\\Requests\\{$className}",
                        'suggested' => "App\\Http\\Requests\\Sakip\\{$className}",
                        'content' => trim($line),
                    ];
                }
            }

            // Check for class usage without import
            if (preg_match('/new\s+(\w+)\(/', $line, $matches) ||
                preg_match('/(\w+)::(class|[\w]+)\(/', $line, $matches)) {
                $className = $matches[1];

                // Skip common PHP classes and primitives
                if (in_array($className, ['self', 'static', 'parent', 'array', 'string', 'int', 'bool'])) {
                    continue;
                }

                // Check if class is imported
                $isImported = false;
                foreach ($useStatements as $use) {
                    if (str_ends_with($use, "\\{$className}") || $use === $className) {
                        $isImported = true;
                        break;
                    }
                }

                if (!$isImported && !$this->isBuiltInClass($className)) {
                    // Try to find the class
                    $possibleLocations = $this->findClassLocations($className);

                    if (!empty($possibleLocations)) {
                        $issues[] = [
                            'type' => 'missing_import',
                            'file' => $filepath,
                            'line' => $lineNumber + 1,
                            'class' => $className,
                            'suggestions' => $possibleLocations,
                            'content' => trim($line),
                        ];
                    }
                }
            }
        }

        return $issues;
    }

    /**
     * Extract namespace from file content.
     *
     * @param string $content
     * @return string|null
     */
    protected function extractNamespace(string $content): ?string
    {
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Extract use statements from file content.
     *
     * @param string $content
     * @return array
     */
    protected function extractUseStatements(string $content): array
    {
        $uses = [];
        if (preg_match_all('/use\s+([^;]+);/', $content, $matches)) {
            foreach ($matches[1] as $use) {
                // Handle aliased imports
                if (strpos($use, ' as ') !== false) {
                    $parts = explode(' as ', $use);
                    $uses[] = trim($parts[0]);
                } else {
                    $uses[] = trim($use);
                }
            }
        }
        return $uses;
    }

    /**
     * Check if class is a built-in PHP class.
     *
     * @param string $className
     * @return bool
     */
    protected function isBuiltInClass(string $className): bool
    {
        $builtIn = [
            'Exception', 'Throwable', 'DateTime', 'DateTimeImmutable',
            'stdClass', 'Closure', 'Generator', 'PDO', 'PDOStatement',
            'DOMDocument', 'SimpleXMLElement', 'ArrayObject', 'SplFileInfo',
        ];

        return in_array($className, $builtIn);
    }

    /**
     * Find possible locations for a class.
     *
     * @param string $className
     * @return array
     */
    protected function findClassLocations(string $className): array
    {
        $locations = [];
        $searchPaths = [
            'app/Models',
            'app/Http/Controllers',
            'app/Http/Requests',
            'app/Http/Requests/Sakip',
            'app/Services',
            'app/Providers',
        ];

        foreach ($searchPaths as $searchPath) {
            $fullPath = base_path($searchPath);
            if (!File::exists($fullPath)) {
                continue;
            }

            $files = File::allFiles($fullPath);
            foreach ($files as $file) {
                if ($file->getFilenameWithoutExtension() === $className) {
                    $relativePath = str_replace(base_path() . '/', '', $file->getPathname());
                    $namespace = $this->pathToNamespace($relativePath);
                    $locations[] = $namespace;
                }
            }
        }

        return $locations;
    }

    /**
     * Convert file path to namespace.
     *
     * @param string $path
     * @return string
     */
    protected function pathToNamespace(string $path): string
    {
        // Remove .php extension
        $path = str_replace('.php', '', $path);

        // Convert path separators to namespace separators
        $namespace = str_replace('/', '\\', $path);

        // Capitalize first letter after 'app'
        $namespace = preg_replace('/^app\\\\/', 'App\\', $namespace);

        return $namespace;
    }

    /**
     * Display an issue.
     *
     * @param array $issue
     * @return void
     */
    protected function displayIssue(array $issue): void
    {
        $relativePath = str_replace(base_path() . '/', '', $issue['file']);

        if ($issue['type'] === 'wrong_namespace') {
            $this->line("<fg=yellow>• Wrong Namespace</>: <fg=cyan>{$relativePath}:{$issue['line']}</>");
            $this->line("  Class: <fg=red>{$issue['class']}</>");
            $this->line("  Current:   {$issue['current']}");
            $this->line("  Suggested: <fg=green>{$issue['suggested']}</>");
            $this->line("  Line: <fg=gray>{$issue['content']}</>");
        } elseif ($issue['type'] === 'missing_import') {
            $this->line("<fg=yellow>• Missing Import</>: <fg=cyan>{$relativePath}:{$issue['line']}</>");
            $this->line("  Class: <fg=red>{$issue['class']}</>");
            if (!empty($issue['suggestions'])) {
                $this->line("  Suggestions:");
                foreach ($issue['suggestions'] as $suggestion) {
                    $this->line("    - <fg=green>{$suggestion}</>");
                }
            }
            $this->line("  Line: <fg=gray>{$issue['content']}</>");
        }

        $this->newLine();
    }

    /**
     * Attempt to fix issues automatically.
     *
     * @param array $issues
     * @return int
     */
    protected function fixIssues(array $issues): int
    {
        $fixed = 0;

        foreach ($issues as $issue) {
            if ($issue['type'] === 'wrong_namespace') {
                if ($this->fixWrongNamespace($issue)) {
                    $fixed++;
                    $relativePath = str_replace(base_path() . '/', '', $issue['file']);
                    $this->line("✓ Fixed: {$relativePath}");
                }
            }
        }

        return $fixed;
    }

    /**
     * Fix wrong namespace issue.
     *
     * @param array $issue
     * @return bool
     */
    protected function fixWrongNamespace(array $issue): bool
    {
        try {
            $content = File::get($issue['file']);

            // Replace the wrong import with the correct one
            $content = str_replace(
                $issue['current'],
                $issue['suggested'],
                $content
            );

            File::put($issue['file'], $content);

            return true;
        } catch (\Exception $e) {
            $this->error("Failed to fix {$issue['file']}: {$e->getMessage()}");
            return false;
        }
    }
}
