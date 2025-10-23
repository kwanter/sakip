<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds soft deletes to remaining SAKIP tables for data recovery capabilities.
     */
    public function up(): void
    {
        // Add soft deletes to evidence_documents
        Schema::table("evidence_documents", function (Blueprint $table) {
            if (!Schema::hasColumn("evidence_documents", "deleted_at")) {
                $table->softDeletes()->after("updated_at");
            }
        });

        // Add soft deletes to assessment_criteria
        Schema::table("assessment_criteria", function (Blueprint $table) {
            if (!Schema::hasColumn("assessment_criteria", "deleted_at")) {
                $table->softDeletes()->after("updated_at");
            }
        });

        // Add soft deletes to report_templates
        Schema::table("report_templates", function (Blueprint $table) {
            if (!Schema::hasColumn("report_templates", "deleted_at")) {
                $table->softDeletes()->after("updated_at");
            }
        });

        // Add soft deletes to system_settings
        Schema::table("system_settings", function (Blueprint $table) {
            if (!Schema::hasColumn("system_settings", "deleted_at")) {
                $table->softDeletes()->after("updated_at");
            }
        });

        // Add soft deletes to instansis (for administrative purposes)
        Schema::table("instansis", function (Blueprint $table) {
            if (!Schema::hasColumn("instansis", "deleted_at")) {
                $table->softDeletes()->after("updated_at");
            }
        });

        // Add soft deletes to programs
        Schema::table("programs", function (Blueprint $table) {
            if (!Schema::hasColumn("programs", "deleted_at")) {
                $table->softDeletes()->after("updated_at");
            }
        });

        // Add soft deletes to kegiatans
        Schema::table("kegiatans", function (Blueprint $table) {
            if (!Schema::hasColumn("kegiatans", "deleted_at")) {
                $table->softDeletes()->after("updated_at");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove soft deletes from evidence_documents
        if (Schema::hasColumn("evidence_documents", "deleted_at")) {
            Schema::table("evidence_documents", function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from assessment_criteria
        if (Schema::hasColumn("assessment_criteria", "deleted_at")) {
            Schema::table("assessment_criteria", function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from report_templates
        if (Schema::hasColumn("report_templates", "deleted_at")) {
            Schema::table("report_templates", function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from system_settings
        if (Schema::hasColumn("system_settings", "deleted_at")) {
            Schema::table("system_settings", function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from instansis
        if (Schema::hasColumn("instansis", "deleted_at")) {
            Schema::table("instansis", function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from programs
        if (Schema::hasColumn("programs", "deleted_at")) {
            Schema::table("programs", function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        // Remove soft deletes from kegiatans
        if (Schema::hasColumn("kegiatans", "deleted_at")) {
            Schema::table("kegiatans", function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
