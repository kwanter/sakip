<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds audit tracking and approval fields to targets table.
     */
    public function up(): void
    {
        Schema::table("targets", function (Blueprint $table) {
            $table
                ->foreignUuid("approved_by")
                ->nullable()
                ->after("status")
                ->constrained("users")
                ->onDelete("set null");

            $table->timestamp("approved_at")->nullable()->after("approved_by");

            $table->text("notes")->nullable()->after("approved_at");

            $table->json("metadata")->nullable()->after("notes");

            $table
                ->foreignUuid("created_by")
                ->nullable()
                ->after("metadata")
                ->constrained("users")
                ->onDelete("set null");

            $table
                ->foreignUuid("updated_by")
                ->nullable()
                ->after("created_by")
                ->constrained("users")
                ->onDelete("set null");

            // Add indexes
            $table->index("approved_by");
            $table->index("created_by");
            $table->index("updated_by");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("targets", function (Blueprint $table) {
            $table->dropForeign(["approved_by"]);
            $table->dropForeign(["created_by"]);
            $table->dropForeign(["updated_by"]);
            $table->dropIndex(["approved_by"]);
            $table->dropIndex(["created_by"]);
            $table->dropIndex(["updated_by"]);
            $table->dropColumn([
                "approved_by",
                "approved_at",
                "notes",
                "metadata",
                "created_by",
                "updated_by",
            ]);
        });
    }
};
