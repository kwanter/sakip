<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * PERFORMANCE: Add indexes to targets table for optimized queries
     */
    public function up(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            // Composite index for performance_indicator + year (common query)
            $table->index(['performance_indicator_id', 'year'], 'idx_targets_indicator_year');

            // Index for year filtering (yearly targets)
            $table->index('year', 'idx_targets_year');

            // Index for approval status (pending approvals)
            $table->index('approval_status', 'idx_targets_approval_status');

            // Index for target_period (quarterly/monthly filtering)
            $table->index('target_period', 'idx_targets_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The approval_status/target_period indexes are dropped by
        // 2026_11_25_000000 (their columns never existed), so their absence
        // on rollback must not abort the rest of this teardown.
        foreach ([
            'idx_targets_indicator_year',
            'idx_targets_year',
            'idx_targets_approval_status',
            'idx_targets_period',
        ] as $index) {
            if (! Schema::hasIndex('targets', $index)) {
                continue;
            }

            Schema::table('targets', function (Blueprint $table) use ($index) {
                $table->dropIndex($index);
            });
        }
    }
};
