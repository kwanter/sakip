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
        Schema::table("reports", function (Blueprint $table) {
            $table
                ->foreignUuid("template_id")
                ->nullable()
                ->after("report_type")
                ->constrained("report_templates")
                ->onDelete("set null");

            $table->index("template_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("reports", function (Blueprint $table) {
            $table->dropForeign(["template_id"]);
            $table->dropIndex(["template_id"]);
            $table->dropColumn("template_id");
        });
    }
};
