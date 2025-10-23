<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds kegiatan_id and measurement_type columns to performance_indicators table
     * to support kegiatan (activity) relationship and measurement type specification
     */
    public function up(): void
    {
        Schema::table('performance_indicators', function (Blueprint $table) {
            // Add kegiatan_id column (nullable, optional relationship)
            $table
                ->foreignUuid('kegiatan_id')
                ->nullable()
                ->after('program_id')
                ->constrained('kegiatans')
                ->onDelete('set null');

            // Add measurement_type column to specify type of measurement
            $table
                ->enum('measurement_type', ['percentage', 'number', 'ratio', 'index'])
                ->nullable()
                ->after('measurement_unit');

            // Add indexes for performance
            $table->index('kegiatan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_indicators', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['kegiatan_id']);

            // Drop indexes
            $table->dropIndex(['kegiatan_id']);

            // Drop columns
            $table->dropColumn(['kegiatan_id', 'measurement_type']);
        });
    }
};
