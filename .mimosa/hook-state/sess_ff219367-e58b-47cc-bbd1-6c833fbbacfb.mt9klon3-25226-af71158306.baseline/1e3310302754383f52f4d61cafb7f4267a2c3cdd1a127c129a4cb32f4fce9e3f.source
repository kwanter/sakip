<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds document type and upload tracking fields to evidence_documents table.
     */
    public function up(): void
    {
        Schema::table("evidence_documents", function (Blueprint $table) {
            $table
                ->string("document_type", 100)
                ->nullable()
                ->after("file_type");

            $table
                ->foreignUuid("uploaded_by")
                ->nullable()
                ->after("description")
                ->constrained("users")
                ->onDelete("set null");

            $table->timestamp("uploaded_at")->nullable()->after("uploaded_by");

            // Add indexes
            $table->index("document_type");
            $table->index("uploaded_by");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("evidence_documents", function (Blueprint $table) {
            $table->dropForeign(["uploaded_by"]);
            $table->dropIndex(["document_type"]);
            $table->dropIndex(["uploaded_by"]);
            $table->dropColumn(["document_type", "uploaded_by", "uploaded_at"]);
        });
    }
};
