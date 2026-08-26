<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds audit tracking fields to performance_indicators table.
     */
    public function up(): void
    {
        Schema::table("performance_indicators", function (Blueprint $table) {
            $table
                ->foreignUuid("created_by")
                ->nullable()
                ->after("is_mandatory")
                ->constrained("users")
                ->onDelete("set null");

            $table
                ->foreignUuid("updated_by")
                ->nullable()
                ->after("created_by")
                ->constrained("users")
                ->onDelete("set null");

            $table->json("metadata")->nullable()->after("updated_by");

            // Add indexes
            $table->index("created_by");
            $table->index("updated_by");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("performance_indicators", function (Blueprint $table) {
            $table->dropForeign(["created_by"]);
            $table->dropForeign(["updated_by"]);
            $table->dropIndex(["created_by"]);
            $table->dropIndex(["updated_by"]);
            $table->dropColumn(["created_by", "updated_by", "metadata"]);
        });
    }
};
