<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds composite indexes to optimize common query patterns
     * identified during performance analysis.
     */
    public function up(): void
    {
        // Performance data table indexes
        Schema::table('performance_data', function (Blueprint $table) {
            // Composite index for: PerformanceData::where('performance_indicator_id', $id)
            //                              ->where('period', $period)
            //                              ->where('status', 'validated')
            $table->index(['performance_indicator_id', 'period', 'status'], 'pd_indicator_period_status_idx');

            // Composite index for: PerformanceData::where('instansi_id', $id)
            //                              ->whereYear('period', $year)
            //                              ->where('status', 'validated')
            $table->index(['instansi_id', 'period', 'status'], 'pd_instansi_period_status_idx');

            // Composite index for: PerformanceData::where('submitted_by', $userId)
            //                              ->whereYear('period', $year)
            $table->index(['submitted_by', 'period'], 'pd_submitted_period_idx');

            // Composite index for: PerformanceData::whereNull('actual_value')
            //                              ->where('status', 'draft')
            $table->index(['status', 'submitted_at'], 'pd_status_submitted_idx');
        });

        // Performance indicators table indexes
        Schema::table('performance_indicators', function (Blueprint $table) {
            // Composite index for: PerformanceIndicator::where('instansi_id', $id)
            //                                     ->where('category', 'output')
            $table->index(['instansi_id', 'category'], 'pi_instansi_category_idx');

            // Composite index for: PerformanceIndicator::where('instansi_id', $id)
            //                                     ->where('is_mandatory', true)
            $table->index(['instansi_id', 'is_mandatory'], 'pi_instansi_mandatory_idx');

            // Composite index for: PerformanceIndicator::where('instansi_id', $id)
            //                                     ->where('frequency', 'monthly')
            $table->index(['instansi_id', 'frequency'], 'pi_instansi_frequency_idx');
        });

        // Assessments table indexes (if exists)
        if (Schema::hasTable('assessments')) {
            Schema::table('assessments', function (Blueprint $table) {
                // Composite index for: Assessment::where('status', 'pending')
                //                           ->orderBy('assessed_at')
                $table->index(['status', 'assessed_at'], 'assessments_status_assessed_at_idx');

                // Composite index for: Assessment::where('assessed_by', $userId)
                //                           ->where('status', 'pending')
                $table->index(['assessed_by', 'status'], 'assessments_by_status_idx');
            });
        }

        // Reports table indexes (if exists)
        if (Schema::hasTable('reports')) {
            Schema::table('reports', function (Blueprint $table) {
                // Composite index for: Report::where('instansi_id', $id)
                //                       ->where('status', 'submitted')
                //                       ->whereYear('period', $year)
                $table->index(['instansi_id', 'status', 'period'], 'reports_instansi_status_period_idx');

                // Composite index for: Report::where('status', 'submitted')
                //                       ->orderBy('period')
                $table->index(['status', 'period'], 'reports_status_period_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_data', function (Blueprint $table) {
            $table->dropIndex('pd_indicator_period_status_idx');
            $table->dropIndex('pd_instansi_period_status_idx');
            $table->dropIndex('pd_submitted_period_idx');
            $table->dropIndex('pd_status_submitted_idx');
        });

        Schema::table('performance_indicators', function (Blueprint $table) {
            $table->dropIndex('pi_instansi_category_idx');
            $table->dropIndex('pi_instansi_mandatory_idx');
            $table->dropIndex('pi_instansi_frequency_idx');
        });

        if (Schema::hasTable('assessments')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->dropIndex('assessments_status_assessed_at_idx');
                $table->dropIndex('assessments_by_status_idx');
            });
        }

        if (Schema::hasTable('reports')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropIndex('reports_instansi_status_period_idx');
                $table->dropIndex('reports_status_period_idx');
            });
        }
    }
};
