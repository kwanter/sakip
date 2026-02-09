<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * PERFORMANCE: Add indexes to audit_logs table for optimized queries
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Composite index for user + created_at (user activity history)
            $table->index(['user_id', 'created_at'], 'idx_audit_logs_user_created');
            
            // Composite index for institution + created_at (org audit trail)
            $table->index(['instansi_id', 'created_at'], 'idx_audit_logs_instansi_created');
            
            // Index for action filtering (common filter)
            $table->index('action', 'idx_audit_logs_action');
            
            // Index for module filtering
            $table->index('module', 'idx_audit_logs_module');
            
            // Index for created_at ordering (audit log timeline)
            $table->index('created_at', 'idx_audit_logs_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex('idx_audit_logs_user_created');
            $table->dropIndex('idx_audit_logs_instansi_created');
            $table->dropIndex('idx_audit_logs_action');
            $table->dropIndex('idx_audit_logs_module');
            $table->dropIndex('idx_audit_logs_created_at');
        });
    }
};
