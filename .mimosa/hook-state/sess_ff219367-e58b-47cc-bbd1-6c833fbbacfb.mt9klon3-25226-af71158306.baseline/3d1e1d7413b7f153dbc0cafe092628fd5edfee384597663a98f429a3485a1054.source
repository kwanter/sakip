<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds performance indexes to audit_logs table for better query performance.
     */
    public function up(): void
    {
        Schema::table("audit_logs", function (Blueprint $table) {
            // Add composite index for action queries
            $table->index(
                ["action", "created_at"],
                "audit_logs_action_date_index",
            );

            // Note: user_id, action, and created_at already have individual indexes
            // from the create_audit_logs_table migration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("audit_logs", function (Blueprint $table) {
            $table->dropIndex("audit_logs_action_date_index");
        });
    }
};
