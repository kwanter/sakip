<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Extends the existing audit_logs table with SAKIP-specific fields.
     * Adds additional tracking for SAKIP compliance and performance monitoring.
     */
    public function up(): void
    {
        // Add SAKIP-specific columns to existing audit_logs table
        Schema::table("audit_logs", function (Blueprint $table) {
            // Add SAKIP module identifier
            $table->string("module", 50)->nullable();

            // Add compliance tracking fields
            $table
                ->enum("compliance_status", [
                    "compliant",
                    "violation",
                    "warning",
                ])
                ->nullable();
            $table->text("compliance_notes")->nullable();

            // Add performance impact tracking
            $table
                ->enum("impact_level", ["low", "medium", "high", "critical"])
                ->nullable();

            // Add SAKIP-specific indexes
            $table->index("module");
            $table->index("compliance_status");
            $table->index("impact_level");
        });

        // Create a separate table for SAKIP audit trail if more detailed tracking is needed
        Schema::create("sakip_audit_trails", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table
                ->foreignUuid("audit_log_id")
                ->constrained("audit_logs")
                ->onDelete("cascade");
            $table->string("sakip_module", 50); // performance_data, assessment, report, etc.
            $table->integer("record_id");
            $table->string("record_type", 100); // Model class name
            $table->enum("action_category", [
                "data_entry",
                "assessment",
                "approval",
                "report_generation",
                "system_config",
            ]);
            $table->json("performance_impact")->nullable(); // Impact on performance metrics
            $table->json("compliance_check")->nullable(); // Compliance validation results
            $table->string("ip_address", 45)->nullable();
            $table->string("user_agent", 500)->nullable();
            $table->timestamps();

            // Indexes for performance optimization
            $table->index("sakip_module");
            $table->index("action_category");
            $table->index("record_id");
            $table->index("record_type");
            $table->index(["sakip_module", "record_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("sakip_audit_trails");

        Schema::table("audit_logs", function (Blueprint $table) {
            $table->dropIndex(["module"]);
            $table->dropIndex(["compliance_status"]);
            $table->dropIndex(["impact_level"]);

            $table->dropColumn([
                "module",
                "compliance_status",
                "compliance_notes",
                "impact_level",
            ]);
        });
    }
};
