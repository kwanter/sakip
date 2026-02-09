<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * PERFORMANCE: Add indexes to assessments table for optimized queries
     */
    public function up(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            // Composite index for performance_data + status (common filter)
            $table->index(['performance_data_id', 'status'], 'idx_assessments_data_status');
            
            // Composite index for assessor + status (my assessments view)
            $table->index(['assessor_id', 'status'], 'idx_assessments_assessor_status');
            
            // Composite index for reviewer + status (pending reviews)
            $table->index(['reviewer_id', 'status'], 'idx_assessments_reviewer_status');
            
            // Index for submission date ordering
            $table->index('submitted_at', 'idx_assessments_submitted_at');
            
            // Index for approval date ordering
            $table->index('approved_at', 'idx_assessments_approved_at');
            
            // Index for created_at sorting
            $table->index('created_at', 'idx_assessments_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropIndex('idx_assessments_data_status');
            $table->dropIndex('idx_assessments_assessor_status');
            $table->dropIndex('idx_assessments_reviewer_status');
            $table->dropIndex('idx_assessments_submitted_at');
            $table->dropIndex('idx_assessments_approved_at');
            $table->dropIndex('idx_assessments_created_at');
        });
    }
};
