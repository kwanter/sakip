<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds program_id and sasaran_strategis_id to performance_indicators table
     * to support hierarchical relationship: Instansi → Sasaran Strategis → Program → Indikator Kinerja
     */
    public function up(): void
    {
        Schema::table("performance_indicators", function (Blueprint $table) {
            // Add sasaran_strategis_id column (nullable, optional relationship)
            $table
                ->foreignUuid("sasaran_strategis_id")
                ->nullable()
                ->after("instansi_id")
                ->constrained("sasaran_strategis")
                ->onDelete("set null");

            // Add program_id column (nullable, optional relationship)
            $table
                ->foreignUuid("program_id")
                ->nullable()
                ->after("sasaran_strategis_id")
                ->constrained("programs")
                ->onDelete("set null");

            // Add indexes for performance
            $table->index("sasaran_strategis_id");
            $table->index("program_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("performance_indicators", function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(["program_id"]);
            $table->dropForeign(["sasaran_strategis_id"]);

            // Drop indexes
            $table->dropIndex(["program_id"]);
            $table->dropIndex(["sasaran_strategis_id"]);

            // Drop columns
            $table->dropColumn(["program_id", "sasaran_strategis_id"]);
        });
    }
};
