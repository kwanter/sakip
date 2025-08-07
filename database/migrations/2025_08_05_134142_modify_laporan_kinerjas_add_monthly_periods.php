<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite compatibility, we'll recreate the table with new enum values
        Schema::table('laporan_kinerjas', function (Blueprint $table) {
            // Drop the old column
            $table->dropColumn('periode');
        });
        
        Schema::table('laporan_kinerjas', function (Blueprint $table) {
            // Add the new column with monthly and quarterly options
            $table->enum('periode', [
                'januari', 'februari', 'maret', 'april', 'mei', 'juni',
                'juli', 'agustus', 'september', 'oktober', 'november', 'desember',
                'triwulan1', 'triwulan2', 'triwulan3', 'triwulan4', 'tahunan'
            ])->after('tahun');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_kinerjas', function (Blueprint $table) {
            $table->dropColumn('periode');
        });
        
        Schema::table('laporan_kinerjas', function (Blueprint $table) {
            $table->enum('periode', ['triwulan1', 'triwulan2', 'triwulan3', 'triwulan4', 'tahunan'])->after('tahun');
        });
    }
};
