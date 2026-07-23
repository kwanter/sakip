<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table("audit_logs", function (Blueprint $table) {
            if (!Schema::hasColumn("audit_logs", "description")) {
                $table->text("description")->nullable()->after("action");
            }
            if (!Schema::hasColumn("audit_logs", "old_values")) {
                $table->json("old_values")->nullable()->after("description");
            }
            if (!Schema::hasColumn("audit_logs", "new_values")) {
                $table->json("new_values")->nullable()->after("old_values");
            }
            if (!Schema::hasColumn("audit_logs", "activity")) {
                $table->string("activity", 100)->nullable()->after("module");
            }
            if (!Schema::hasColumn("audit_logs", "model_type")) {
                $table->string("model_type", 100)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table("audit_logs", function (Blueprint $table) {
            $cols = [];
            foreach (["description", "old_values", "new_values", "activity", "model_type"] as $col) {
                if (Schema::hasColumn("audit_logs", $col)) {
                    $cols[] = $col;
                }
            }
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
