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
        Schema::create("report_templates", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table->string("name");
            $table->text("description")->nullable();
            $table->string("module")->default("sakip");
            $table->string("type")->default("general");
            $table->longText("content")->nullable();
            $table->string("template_file")->nullable();
            $table->string("instansi_id")->nullable();
            $table->boolean("is_active")->default(true);
            $table->string("created_by")->nullable();
            $table->string("updated_by")->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for better performance
            $table->index(["module", "is_active"]);
            $table->index(["type", "is_active"]);
            $table->index("instansi_id");
            $table->index("created_by");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("report_templates");
    }
};
