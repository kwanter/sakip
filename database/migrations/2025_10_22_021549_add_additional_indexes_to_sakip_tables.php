<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds additional indexes to improve query performance for SAKIP tables.
     */
    public function up(): void
    {
        // Add composite index for common performance_data queries
        Schema::table("performance_data", function (Blueprint $table) {
            if (
                !Schema::hasIndex(
                    "performance_data",
                    "performance_data_instansi_period_index",
                )
            ) {
                $table->index(
                    ["instansi_id", "period"],
                    "performance_data_instansi_period_index",
                );
            }
            if (
                !Schema::hasIndex(
                    "performance_data",
                    "performance_data_status_period_index",
                )
            ) {
                $table->index(
                    ["status", "period"],
                    "performance_data_status_period_index",
                );
            }
        });

        // Add indexes to audit_logs for temporal queries
        Schema::table("audit_logs", function (Blueprint $table) {
            if (!Schema::hasColumn("audit_logs", "instansi_id")) {
                $table
                    ->foreignUuid("instansi_id")
                    ->nullable()
                    ->after("user_id")
                    ->constrained("instansis")
                    ->onDelete("set null");
            }
            if (
                !Schema::hasIndex("audit_logs", "audit_logs_created_at_index")
            ) {
                $table->index("created_at");
            }
            if (
                !Schema::hasIndex(
                    "audit_logs",
                    "audit_logs_instansi_created_index",
                )
            ) {
                $table->index(
                    ["instansi_id", "created_at"],
                    "audit_logs_instansi_created_index",
                );
            }
            if (
                !Schema::hasIndex("audit_logs", "audit_logs_user_created_index")
            ) {
                $table->index(
                    ["user_id", "created_at"],
                    "audit_logs_user_created_index",
                );
            }
        });

        // Add indexes to sakip_audit_trails
        Schema::table("sakip_audit_trails", function (Blueprint $table) {
            if (
                !Schema::hasIndex(
                    "sakip_audit_trails",
                    "sakip_audit_trails_module_created_index",
                )
            ) {
                $table->index(
                    ["sakip_module", "created_at"],
                    "sakip_audit_trails_module_created_index",
                );
            }
            if (
                !Schema::hasIndex(
                    "sakip_audit_trails",
                    "sakip_audit_trails_record_type_index",
                )
            ) {
                $table->index(
                    ["record_type", "record_id"],
                    "sakip_audit_trails_record_type_index",
                );
            }
        });

        // Add composite indexes for performance_indicators
        Schema::table("performance_indicators", function (Blueprint $table) {
            if (
                !Schema::hasIndex(
                    "performance_indicators",
                    "performance_indicators_instansi_category_index",
                )
            ) {
                $table->index(
                    ["instansi_id", "category"],
                    "performance_indicators_instansi_category_index",
                );
            }
            if (
                !Schema::hasIndex(
                    "performance_indicators",
                    "performance_indicators_instansi_frequency_index",
                )
            ) {
                $table->index(
                    ["instansi_id", "frequency"],
                    "performance_indicators_instansi_frequency_index",
                );
            }
        });

        // Add indexes to targets
        Schema::table("targets", function (Blueprint $table) {
            if (!Schema::hasIndex("targets", "targets_year_status_index")) {
                $table->index(["year", "status"], "targets_year_status_index");
            }
        });

        // Add indexes to assessments
        Schema::table("assessments", function (Blueprint $table) {
            if (
                !Schema::hasIndex(
                    "assessments",
                    "assessments_status_assessed_index",
                )
            ) {
                $table->index(
                    ["status", "assessed_at"],
                    "assessments_status_assessed_index",
                );
            }
        });

        // Add indexes to reports
        Schema::table("reports", function (Blueprint $table) {
            if (!Schema::hasIndex("reports", "reports_instansi_period_index")) {
                $table->index(
                    ["instansi_id", "period"],
                    "reports_instansi_period_index",
                );
            }
            if (!Schema::hasIndex("reports", "reports_type_status_index")) {
                $table->index(
                    ["report_type", "status"],
                    "reports_type_status_index",
                );
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from performance_data
        Schema::table("performance_data", function (Blueprint $table) {
            $table->dropIndex("performance_data_instansi_period_index");
            $table->dropIndex("performance_data_status_period_index");
        });

        // Drop indexes from audit_logs
        Schema::table("audit_logs", function (Blueprint $table) {
            if (Schema::hasColumn("audit_logs", "instansi_id")) {
                $table->dropForeign(["instansi_id"]);
                $table->dropColumn("instansi_id");
            }
            $table->dropIndex("audit_logs_created_at_index");
            $table->dropIndex("audit_logs_instansi_created_index");
            $table->dropIndex("audit_logs_user_created_index");
        });

        // Drop indexes from sakip_audit_trails
        Schema::table("sakip_audit_trails", function (Blueprint $table) {
            $table->dropIndex("sakip_audit_trails_module_created_index");
            $table->dropIndex("sakip_audit_trails_record_type_index");
        });

        // Drop indexes from performance_indicators
        Schema::table("performance_indicators", function (Blueprint $table) {
            $table->dropIndex("performance_indicators_instansi_category_index");
            $table->dropIndex(
                "performance_indicators_instansi_frequency_index",
            );
        });

        // Drop indexes from targets
        Schema::table("targets", function (Blueprint $table) {
            $table->dropIndex("targets_year_status_index");
        });

        // Drop indexes from assessments
        Schema::table("assessments", function (Blueprint $table) {
            $table->dropIndex("assessments_status_assessed_index");
        });

        // Drop indexes from reports
        Schema::table("reports", function (Blueprint $table) {
            $table->dropIndex("reports_instansi_period_index");
            $table->dropIndex("reports_type_status_index");
        });
    }
};
