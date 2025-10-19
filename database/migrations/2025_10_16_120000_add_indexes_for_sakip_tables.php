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
        // laporan_kinerjas: composite indexes for common filters
        Schema::table('laporan_kinerjas', function (Blueprint $table) {
            if (!Schema::hasColumn('laporan_kinerjas', 'status_verifikasi')) {
                // Skip if schema differs in certain environments
                return;
            }
            $table->index(['status_verifikasi', 'updated_at'], 'lap_kinerja_status_updated_idx');
            $table->index(['indikator_kinerja_id', 'tahun', 'periode'], 'lap_kinerja_indikator_year_period_idx');
        });

        // reports: composite index for generator + timestamp
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'generated_by') && Schema::hasColumn('reports', 'generated_at')) {
                $table->index(['generated_by', 'generated_at'], 'reports_generated_by_at_idx');
            }
        });

        // audit_logs: composite indexes for user/time and action/time
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'user_id') && Schema::hasColumn('audit_logs', 'created_at')) {
                $table->index(['user_id', 'created_at'], 'audit_logs_user_created_idx');
            }
            if (Schema::hasColumn('audit_logs', 'action') && Schema::hasColumn('audit_logs', 'created_at')) {
                $table->index(['action', 'created_at'], 'audit_logs_action_created_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_kinerjas', function (Blueprint $table) {
            if (Schema::hasColumn('laporan_kinerjas', 'status_verifikasi')) {
                $table->dropIndex('lap_kinerja_status_updated_idx');
                $table->dropIndex('lap_kinerja_indikator_year_period_idx');
            }
        });

        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'generated_by') && Schema::hasColumn('reports', 'generated_at')) {
                $table->dropIndex('reports_generated_by_at_idx');
            }
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'user_id') && Schema::hasColumn('audit_logs', 'created_at')) {
                $table->dropIndex('audit_logs_user_created_idx');
            }
            if (Schema::hasColumn('audit_logs', 'action') && Schema::hasColumn('audit_logs', 'created_at')) {
                $table->dropIndex('audit_logs_action_created_idx');
            }
        });
    }
};
