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
                ->foreignUuid("approver_id")
                ->nullable()
                ->after("generated_by")
                ->constrained("users")
                ->onDelete("set null");

            $table->index("approver_id");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("reports", function (Blueprint $table) {
            $table->dropForeign(["approver_id"]);
            $table->dropIndex(["approver_id"]);
            $table->dropColumn("approver_id");
        });
    }
};
