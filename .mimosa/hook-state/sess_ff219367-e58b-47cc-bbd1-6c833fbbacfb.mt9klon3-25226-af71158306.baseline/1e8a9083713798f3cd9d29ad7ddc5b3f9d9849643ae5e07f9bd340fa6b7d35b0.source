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
        Schema::table('targets', function (Blueprint $table) {
            $table->dropIndex('idx_targets_indicator_year');
            $table->dropIndex('idx_targets_year');
            $table->dropIndex('idx_targets_approval_status');
            $table->dropIndex('idx_targets_period');
        });
    }
};
