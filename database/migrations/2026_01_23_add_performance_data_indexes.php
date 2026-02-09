<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * PERFORMANCE: Add indexes to performance_data table for optimized queries
     */
    public function up(): void
    {
        Schema::table('performance_data', function (Blueprint $table) {
            // Composite index for institution + period queries (dashboard filters)
            $table->index(['instansi_id', 'period'], 'idx_perf_data_instansi_period');
            
            // Composite index for indicator + period queries (trends analysis)
            $table->index(['performance_indicator_id', 'period'], 'idx_perf_data_indicator_period');
            
            // Index for status filtering
            $table->index('status', 'idx_perf_data_status');
            
            // Index for submission date ordering
            $table->index('submitted_at', 'idx_perf_data_submitted_at');
            
            // Index for validation date ordering
            $table->index('validated_at', 'idx_perf_data_validated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_data', function (Blueprint $table) {
            $table->dropIndex('idx_perf_data_instansi_period');
            $table->dropIndex('idx_perf_data_indicator_period');
            $table->dropIndex('idx_perf_data_status');
            $table->dropIndex('idx_perf_data_submitted_at');
            $table->dropIndex('idx_perf_data_validated_at');
        });
    }
};
