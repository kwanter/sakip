<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveTestUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sakip:remove-test-users
                            {--force : Force deletion without confirmation}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove test/seed users from the database (production safety)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Scanning for test/seed users...');
        $this->newLine();

        // Patterns to identify test users
        $testPatterns = [
            '@sakip.local',
            '@example.com',
            '@test.com',
            'test@',
            'demo@',
            'seed@',
        ];

        // Build query to find test users
        $query = User::query();

        foreach ($testPatterns as $pattern) {
            $query->orWhere('email', 'LIKE', "%{$pattern}%");
        }

        // Also check for specific test email addresses
        $specificTestEmails = [
            'superadmin@sakip.local',
            'assessor@sakip.local',
            'datacollector@sakip.local',
            'auditor@sakip.local',
            'executive@sakip.local',
            'collector@sakip.local',
            'official@sakip.local',
            'admin@example.com',
            'test@example.com',
            'user@example.com',
        ];

        $query->orWhereIn('email', $specificTestEmails);

        $testUsers = $query->get();

        if ($testUsers->isEmpty()) {
            $this->info('✅ No test users found in the database.');
            return 0;
        }

        // Display found test users
        $this->warn("⚠️  Found {$testUsers->count()} test user(s):");
        $this->newLine();

        $tableData = $testUsers->map(function ($user) {
            return [
                $user->id,
                $user->name,
                $user->email,
                $user->roles->pluck('name')->join(', ') ?: 'None',
                $user->created_at->format('Y-m-d H:i:s'),
            ];
        })->toArray();

        $this->table(
            ['ID', 'Name', 'Email', 'Roles', 'Created At'],
            $tableData
        );

        // Dry run mode
        if ($this->option('dry-run')) {
            $this->newLine();
            $this->info('🔍 DRY RUN MODE: No users were deleted.');
            $this->info('   Run without --dry-run to actually delete these users.');
            return 0;
        }

        // Confirmation (unless --force is used)
        if (!$this->option('force')) {
            $this->newLine();
            $this->warn('⚠️  WARNING: This action cannot be undone!');

            if (!$this->confirm('Do you want to delete these test users?', false)) {
                $this->info('❌ Operation cancelled.');
                return 0;
            }

            // Double confirmation for production
            if (app()->environment('production')) {
                $this->error('🚨 PRODUCTION ENVIRONMENT DETECTED');
                if (!$this->confirm('Are you ABSOLUTELY SURE you want to delete these users in PRODUCTION?', false)) {
                    $this->info('❌ Operation cancelled.');
                    return 0;
                }
            }
        }

        // Delete users
        $this->info('🗑️  Deleting test users...');
        $this->newLine();

        DB::beginTransaction();

        try {
            $deletedCount = 0;

            foreach ($testUsers as $user) {
                // Log the deletion
                $this->line("  → Deleting: {$user->email} ({$user->name})");

                // Remove role assignments first
                $user->roles()->detach();

                // Remove permission assignments
                $user->permissions()->detach();

                // Delete the user
                $user->delete();

                $deletedCount++;
            }

            DB::commit();

            $this->newLine();
            $this->info("✅ Successfully deleted {$deletedCount} test user(s).");

            // Log to Laravel log
            \Log::info('Test users removed', [
                'count' => $deletedCount,
                'environment' => app()->environment(),
                'executed_by' => 'console',
                'command' => $this->signature,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            $this->error('❌ Error deleting test users: ' . $e->getMessage());
            $this->error('   Transaction rolled back. No users were deleted.');

            \Log::error('Failed to remove test users', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return 1;
        }

        // Security recommendations
        $this->newLine();
        $this->info('🔒 Security Recommendations:');
        $this->line('  • Verify no test users remain: php artisan sakip:remove-test-users --dry-run');
        $this->line('  • Review all user accounts for weak passwords');
        $this->line('  • Ensure APP_ENV=production in .env');
        $this->line('  • Ensure APP_DEBUG=false in .env');

        return 0;
    }
}
