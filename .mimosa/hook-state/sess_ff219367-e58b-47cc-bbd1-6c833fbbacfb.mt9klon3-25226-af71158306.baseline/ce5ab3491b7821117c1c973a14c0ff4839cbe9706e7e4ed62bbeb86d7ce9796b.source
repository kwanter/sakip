<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds audit tracking and additional fields to performance_data table.
     */
    public function up(): void
    {
        Schema::table("performance_data", function (Blueprint $table) {
            $table
                ->foreignUuid("validated_by")
                ->nullable()
                ->after("submitted_by")
                ->constrained("users")
                ->onDelete("set null");

            $table
                ->string("data_source", 255)
                ->nullable()
                ->after("actual_value");

            $table
                ->string("collection_method", 100)
                ->nullable()
                ->after("data_source");

            $table
                ->timestamp("collected_at")
                ->nullable()
                ->after("collection_method");

            $table->text("validation_notes")->nullable()->after("validated_at");

            $table
                ->foreignUuid("created_by")
                ->nullable()
                ->after("validation_notes")
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
            $table->index("validated_by");
            $table->index("created_by");
            $table->index("updated_by");
            $table->index("collected_at");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("performance_data", function (Blueprint $table) {
            $table->dropForeign(["validated_by"]);
            $table->dropForeign(["created_by"]);
            $table->dropForeign(["updated_by"]);
            $table->dropIndex(["validated_by"]);
            $table->dropIndex(["created_by"]);
            $table->dropIndex(["updated_by"]);
            $table->dropIndex(["collected_at"]);
            $table->dropColumn([
                "validated_by",
                "data_source",
                "collection_method",
                "collected_at",
                "validation_notes",
                "created_by",
                "updated_by",
                "metadata",
            ]);
        });
    }
};
