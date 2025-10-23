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
        Schema::create("sasaran_strategis", function (Blueprint $table) {
            $table->uuid("id")->primary();
            $table
                ->foreignUuid("instansi_id")
                ->constrained("instansis")
                ->onDelete("cascade");
            $table->string("kode_sasaran_strategis")->unique();
            $table->string("nama_strategis");
            $table->text("deskripsi")->nullable();
            $table->enum("status", ["aktif", "nonaktif"])->default("aktif");
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index("instansi_id");
            $table->index("kode_sasaran_strategis");
            $table->index("status");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("sasaran_strategis");
    }
};
