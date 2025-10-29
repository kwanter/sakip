<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds soft deletes (deleted_at column) to core SAKIP tables.
     */
    public function up(): void
    {
        // Add soft deletes to targets
        Schema::table('targets', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        // Add soft deletes to performance_data
        Schema::table('performance_data', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        // Add soft deletes to reports
        Schema::table('reports', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });

        // Add soft deletes to assessments
        Schema::table('assessments', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('performance_data', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('assessments', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
