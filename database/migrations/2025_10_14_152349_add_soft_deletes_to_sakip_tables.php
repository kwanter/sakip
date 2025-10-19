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
        // Add soft deletes to performance_data
        Schema::table('performance_data', function (Blueprint $table) {
            if (!Schema::hasColumn('performance_data', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Add soft deletes to targets
        Schema::table('targets', function (Blueprint $table) {
            if (!Schema::hasColumn('targets', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Add soft deletes to reports
        Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Add soft deletes to assessments
        Schema::table('assessments', function (Blueprint $table) {
            if (!Schema::hasColumn('assessments', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('performance_data', 'deleted_at')) {
            Schema::table('performance_data', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('targets', 'deleted_at')) {
            Schema::table('targets', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('reports', 'deleted_at')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('assessments', 'deleted_at')) {
            Schema::table('assessments', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
