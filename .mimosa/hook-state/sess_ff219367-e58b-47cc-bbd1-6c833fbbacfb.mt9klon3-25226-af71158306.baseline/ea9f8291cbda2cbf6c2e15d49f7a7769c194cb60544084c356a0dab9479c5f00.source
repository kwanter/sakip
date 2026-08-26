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
        Schema::table("programs", function (Blueprint $table) {
            // Add sasaran_strategis_id column after instansi_id
            $table
                ->foreignUuid("sasaran_strategis_id")
                ->nullable()
                ->after("instansi_id")
                ->constrained("sasaran_strategis")
                ->onDelete("cascade");

            // Add index for performance
            $table->index("sasaran_strategis_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("programs", function (Blueprint $table) {
            $table->dropForeign(["sasaran_strategis_id"]);
            $table->dropIndex(["sasaran_strategis_id"]);
            $table->dropColumn("sasaran_strategis_id");
        });
    }
};
