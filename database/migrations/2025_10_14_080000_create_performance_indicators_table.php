<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Creates the performance_indicators table for SAKIP module.
     * Stores performance indicator definitions with measurement criteria.
     */
    public function up(): void
    {
        Schema::create("performance_indicators", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table
                ->foreignUuid("instansi_id")
                ->constrained("instansis")
                ->onDelete("cascade");
            $table->string("code", 50)->unique();
            $table->string("name");
            $table->text("description")->nullable();
            $table->string("measurement_unit", 100);
            $table->string("data_source", 255)->nullable();
            $table->string("collection_method", 100)->nullable();
            $table->json("calculation_formula")->nullable();
            $table->enum("frequency", [
                "monthly",
                "quarterly",
                "semester",
                "annual",
            ]);
            $table->enum("category", ["input", "output", "outcome", "impact"]);
            $table->decimal("weight", 5, 2)->default(1.0);
            $table->boolean("is_mandatory")->default(false);
            $table->timestamps();

            // Indexes for performance optimization
            $table->index("instansi_id");
            $table->index("category");
            $table->index("frequency");
            $table->index("code");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("performance_indicators");
    }
};
