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
        Schema::table("assessment_criteria", function (Blueprint $table) {
            $table
                ->foreignUuid("created_by")
                ->nullable()
                ->after("justification")
                ->constrained("users")
                ->onDelete("set null");

            $table
                ->foreignUuid("updated_by")
                ->nullable()
                ->after("created_by")
                ->constrained("users")
                ->onDelete("set null");

            $table->json("metadata")->nullable()->after("updated_by");

            $table->index("created_by");
            $table->index("updated_by");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("assessment_criteria", function (Blueprint $table) {
            $table->dropForeign(["created_by"]);
            $table->dropForeign(["updated_by"]);
            $table->dropIndex(["created_by"]);
            $table->dropIndex(["updated_by"]);
            $table->dropColumn(["created_by", "updated_by", "metadata"]);
        });
    }
};
