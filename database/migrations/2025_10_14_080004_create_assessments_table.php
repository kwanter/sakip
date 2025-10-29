<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Creates the assessments table for SAKIP module.
     * Stores performance assessment results and evaluations.
     */
    public function up(): void
    {
        Schema::create("assessments", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table
                ->foreignUuid("performance_data_id")
                ->constrained("performance_data")
                ->onDelete("cascade");
            $table
                ->foreignUuid("assessed_by")
                ->constrained("users")
                ->onDelete("cascade");
            $table->decimal("overall_score", 5, 2)->nullable();
            $table->text("comments")->nullable();
            $table->text("recommendations")->nullable();
            $table
                ->enum("status", [
                    "pending",
                    "in_review",
                    "completed",
                    "approved",
                ])
                ->default("pending");
            $table->timestamp("assessed_at")->nullable();
            $table->timestamp("approved_at")->nullable();
            $table->timestamps();

            // Unique constraint to ensure one assessment per performance data
            $table->unique("performance_data_id");

            // Indexes for performance optimization
            $table->index("assessed_by");
            $table->index("status");
            $table->index("assessed_at");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("assessments");
    }
};
