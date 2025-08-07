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
        Schema::create('laporan_kinerjas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('indikator_kinerja_id')->constrained('indikator_kinerjas')->onDelete('cascade');
            $table->year('tahun');
            $table->enum('periode', ['triwulan1', 'triwulan2', 'triwulan3', 'triwulan4', 'tahunan']);
            $table->decimal('input', 10, 2);
            $table->decimal('nilai_realisasi', 10, 2);
            $table->decimal('persentase_capaian', 5, 2)->nullable();
            $table->text('kendala')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->string('file_pendukung')->nullable();
            $table->enum('status_verifikasi', ['draft', 'submitted', 'verified', 'rejected'])->default('draft');
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_kinerjas');
    }
};
