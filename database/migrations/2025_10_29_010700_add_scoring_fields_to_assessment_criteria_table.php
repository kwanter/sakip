<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds score and weight fields to assessment_criteria table if they don't exist.
     */
    public function up(): void
    {
        Schema::table("assessment_criteria", function (Blueprint $table) {
            // Check if score column doesn't exist (it's already in the create migration)
            if (!Schema::hasColumn("assessment_criteria", "score")) {
                $table
                    ->decimal("score", 5, 2)
                    ->nullable()
                    ->after("criteria_name");
            }

            // Check if weight column doesn't exist (it's already in the create migration)
            if (!Schema::hasColumn("assessment_criteria", "weight")) {
                $table->decimal("weight", 5, 2)->default(1.0)->after("score");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to drop - score and weight are in the original create migration
        // This migration only checks if they exist
    }
};
