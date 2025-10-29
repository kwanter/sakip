<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Creates the reports table for SAKIP module.
     * Stores generated performance reports with metadata.
     */
    public function up(): void
    {
        Schema::create("reports", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table
                ->foreignUuid("instansi_id")
                ->constrained("instansis")
                ->onDelete("cascade");
            $table
                ->foreignUuid("generated_by")
                ->constrained("users")
                ->onDelete("cascade");
            $table->string("report_type", 50); // monthly, quarterly, annual, custom
            $table->string("period", 20); // YYYY-MM format or custom period
            $table->string("file_path", 500)->nullable();
            $table->json("parameters")->nullable(); // Report generation parameters
            $table
                ->enum("status", [
                    "generating",
                    "completed",
                    "failed",
                    "submitted",
                ])
                ->default("generating");
            $table->timestamp("generated_at")->nullable();
            $table->timestamp("submitted_at")->nullable();
            $table->timestamps();

            // Indexes for performance optimization
            $table->index("instansi_id");
            $table->index("report_type");
            $table->index("period");
            $table->index("status");
            $table->index("generated_by");
            $table->index("generated_at");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("reports");
    }
};
