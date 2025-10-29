<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Creates the evidence_documents table for SAKIP module.
     * Stores supporting documents and evidence for performance data.
     */
    public function up(): void
    {
        Schema::create("evidence_documents", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table
                ->foreignUuid("performance_data_id")
                ->constrained("performance_data")
                ->onDelete("cascade");
            $table->string("file_name", 255);
            $table->string("file_path", 500);
            $table->string("file_type", 100)->nullable();
            $table->integer("file_size")->nullable();
            $table->text("description")->nullable();
            $table->timestamps();

            // Indexes for performance optimization
            $table->index("performance_data_id");
            $table->index("file_type");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("evidence_documents");
    }
};
