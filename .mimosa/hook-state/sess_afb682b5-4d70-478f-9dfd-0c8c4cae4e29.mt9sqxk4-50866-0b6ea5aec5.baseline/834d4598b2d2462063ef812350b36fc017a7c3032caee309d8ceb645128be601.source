<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('instansi_id')->constrained('instansis')->onDelete('cascade');
            $table->string('kode_program')->unique();
            $table->string('nama_program');
            $table->text('deskripsi')->nullable();
            $table->decimal('anggaran', 15, 2)->nullable();
            $table->unsignedSmallInteger('tahun');
            $table->enum('status', ['draft', 'aktif', 'selesai'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
