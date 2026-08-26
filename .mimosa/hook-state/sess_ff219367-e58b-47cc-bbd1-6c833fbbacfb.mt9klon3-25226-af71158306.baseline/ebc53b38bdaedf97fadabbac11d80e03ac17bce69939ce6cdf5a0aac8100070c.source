<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("indicator_report", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table
                ->foreignUuid("report_id")
                ->constrained("reports")
                ->onDelete("cascade");
            $table
                ->foreignUuid("indicator_id")
                ->constrained("performance_indicators")
                ->onDelete("cascade");
            $table->timestamps();

            // Add unique constraint to prevent duplicate entries
            $table->unique(["report_id", "indicator_id"]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("indicator_report");
    }
};
