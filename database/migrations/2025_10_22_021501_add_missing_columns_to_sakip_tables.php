<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * Adds missing columns to SAKIP tables that are referenced in models and services
     * but were not defined in original migrations.
     */
    public function up(): void
    {
        // Add missing columns to users table
        if (!Schema::hasColumn("users", "instansi_id")) {
            Schema::table("users", function (Blueprint $table) {
                $table
                    ->foreignUuid("instansi_id")
                    ->nullable()
                    ->after("id")
                    ->constrained("instansis")
                    ->onDelete("set null");
                $table->index("instansi_id");
            });
        }

        // Add missing columns to performance_indicators table
        Schema::table("performance_indicators", function (Blueprint $table) {
            if (!Schema::hasColumn("performance_indicators", "created_by")) {
                $table
                    ->foreignUuid("created_by")
                    ->nullable()
                    ->after("is_mandatory")
                    ->constrained("users")
                    ->onDelete("set null");
            }
            if (!Schema::hasColumn("performance_indicators", "updated_by")) {
                $table
                    ->foreignUuid("updated_by")
                    ->nullable()
                    ->after("created_by")
                    ->constrained("users")
                    ->onDelete("set null");
            }
            if (
                !Schema::hasColumn("performance_indicators", "measurement_type")
            ) {
                $table
                    ->enum("measurement_type", [
                        "percentage",
                        "ratio",
                        "count",
                        "index",
                        "currency",
                        "time",
                    ])
                    ->default("count")
                    ->after("measurement_unit");
            }
            if (!Schema::hasColumn("performance_indicators", "metadata")) {
                $table->json("metadata")->nullable()->after("is_mandatory");
            }

            // Add indexes
            if (
                !Schema::hasIndex(
                    "performance_indicators",
                    "performance_indicators_created_by_index",
                )
            ) {
                $table->index("created_by");
            }
            if (
                !Schema::hasIndex(
                    "performance_indicators",
                    "performance_indicators_updated_by_index",
                )
            ) {
                $table->index("updated_by");
            }
        });

        // Add missing columns to performance_data table
        Schema::table("performance_data", function (Blueprint $table) {
            if (!Schema::hasColumn("performance_data", "validated_by")) {
                $table
                    ->foreignUuid("validated_by")
                    ->nullable()
                    ->after("submitted_by")
                    ->constrained("users")
                    ->onDelete("set null");
            }
            if (!Schema::hasColumn("performance_data", "created_by")) {
                $table
                    ->foreignUuid("created_by")
                    ->nullable()
                    ->after("validated_at")
                    ->constrained("users")
                    ->onDelete("set null");
            }
            if (!Schema::hasColumn("performance_data", "updated_by")) {
                $table
                    ->foreignUuid("updated_by")
                    ->nullable()
                    ->after("created_by")
                    ->constrained("users")
                    ->onDelete("set null");
            }
            if (!Schema::hasColumn("performance_data", "data_source")) {
                $table
                    ->string("data_source", 255)
                    ->nullable()
                    ->after("actual_value");
            }
            if (!Schema::hasColumn("performance_data", "collection_method")) {
                $table
                    ->string("collection_method", 100)
                    ->nullable()
                    ->after("data_source");
            }
            if (!Schema::hasColumn("performance_data", "collected_at")) {
                $table
                    ->timestamp("collected_at")
                    ->nullable()
                    ->after("collection_method");
            }
            if (!Schema::hasColumn("performance_data", "validation_notes")) {
                $table
                    ->text("validation_notes")
                    ->nullable()
                    ->after("validated_at");
            }
            if (!Schema::hasColumn("performance_data", "metadata")) {
                $table->json("metadata")->nullable()->after("validation_notes");
            }

            // Add indexes
            if (
                !Schema::hasIndex(
                    "performance_data",
                    "performance_data_validated_by_index",
                )
            ) {
                $table->index("validated_by");
            }
            if (
                !Schema::hasIndex(
                    "performance_data",
                    "performance_data_created_by_index",
                )
            ) {
                $table->index("created_by");
            }
            if (
                !Schema::hasIndex(
                    "performance_data",
                    "performance_data_updated_by_index",
                )
            ) {
                $table->index("updated_by");
            }
            if (
                !Schema::hasIndex(
                    "performance_data",
                    "performance_data_collected_at_index",
                )
            ) {
                $table->index("collected_at");
            }
        });

        // Add missing columns to targets table
        Schema::table("targets", function (Blueprint $table) {
            if (!Schema::hasColumn("targets", "approved_by")) {
                $table
                    ->foreignUuid("approved_by")
                    ->nullable()
                    ->after("justification")
                    ->constrained("users")
                    ->onDelete("set null");
            }
            if (!Schema::hasColumn("targets", "approved_at")) {
                $table
                    ->timestamp("approved_at")
                    ->nullable()
                    ->after("approved_by");
            }
            if (!Schema::hasColumn("targets", "notes")) {
                $table->text("notes")->nullable()->after("approved_at");
            }
            if (!Schema::hasColumn("targets", "metadata")) {
                $table->json("metadata")->nullable()->after("notes");
            }
            if (!Schema::hasColumn("targets", "created_by")) {
                $table
                    ->foreignUuid("created_by")
                    ->nullable()
                    ->after("metadata")
                    ->constrained("users")
                    ->onDelete("set null");
            }
            if (!Schema::hasColumn("targets", "updated_by")) {
                $table
                    ->foreignUuid("updated_by")
                    ->nullable()
                    ->after("created_by")
                    ->constrained("users")
                    ->onDelete("set null");
            }

            // Add indexes
            if (!Schema::hasIndex("targets", "targets_approved_by_index")) {
                $table->index("approved_by");
            }
            if (!Schema::hasIndex("targets", "targets_created_by_index")) {
                $table->index("created_by");
            }
            if (!Schema::hasIndex("targets", "targets_updated_by_index")) {
                $table->index("updated_by");
            }
        });

        // Add missing columns to assessments table
        Schema::table("assessments", function (Blueprint $table) {
            if (!Schema::hasColumn("assessments", "metadata")) {
                $table->json("metadata")->nullable()->after("approved_at");
            }
            if (!Schema::hasColumn("assessments", "created_by")) {
                $table
                    ->foreignUuid("created_by")
                    ->nullable()
                    ->after("metadata")
                    ->constrained("users")
                    ->onDelete("set null");
            }
            if (!Schema::hasColumn("assessments", "updated_by")) {
                $table
                    ->foreignUuid("updated_by")
                    ->nullable()
                    ->after("created_by")
                    ->constrained("users")
                    ->onDelete("set null");
            }

            // Add indexes
            if (
                !Schema::hasIndex("assessments", "assessments_created_by_index")
            ) {
                $table->index("created_by");
            }
            if (
                !Schema::hasIndex("assessments", "assessments_updated_by_index")
            ) {
                $table->index("updated_by");
            }
        });

        // Add missing columns to reports table
        Schema::table("reports", function (Blueprint $table) {
            if (!Schema::hasColumn("reports", "created_by")) {
                $table
                    ->foreignUuid("created_by")
                    ->nullable()
                    ->after("submitted_at")
                    ->constrained("users")
                    ->onDelete("set null");
            }
            if (!Schema::hasColumn("reports", "updated_by")) {
                $table
                    ->foreignUuid("updated_by")
                    ->nullable()
                    ->after("created_by")
                    ->constrained("users")
                    ->onDelete("set null");
            }

            // Add indexes
            if (!Schema::hasIndex("reports", "reports_created_by_index")) {
                $table->index("created_by");
            }
            if (!Schema::hasIndex("reports", "reports_updated_by_index")) {
                $table->index("updated_by");
            }
        });

        // Add missing columns to evidence_documents table
        Schema::table("evidence_documents", function (Blueprint $table) {
            if (!Schema::hasColumn("evidence_documents", "document_type")) {
                $table
                    ->string("document_type", 100)
                    ->nullable()
                    ->after("file_type");
            }
            if (!Schema::hasColumn("evidence_documents", "uploaded_by")) {
                $table
                    ->foreignUuid("uploaded_by")
                    ->nullable()
                    ->after("description")
                    ->constrained("users")
                    ->onDelete("set null");
            }
            if (!Schema::hasColumn("evidence_documents", "uploaded_at")) {
                $table
                    ->timestamp("uploaded_at")
                    ->nullable()
                    ->after("uploaded_by");
            }

            // Add indexes
            if (
                !Schema::hasIndex(
                    "evidence_documents",
                    "evidence_documents_document_type_index",
                )
            ) {
                $table->index("document_type");
            }
            if (
                !Schema::hasIndex(
                    "evidence_documents",
                    "evidence_documents_uploaded_by_index",
                )
            ) {
                $table->index("uploaded_by");
            }
        });

        // Add missing columns to assessment_criteria table
        Schema::table("assessment_criteria", function (Blueprint $table) {
            if (!Schema::hasColumn("assessment_criteria", "score")) {
                $table
                    ->decimal("score", 5, 2)
                    ->nullable()
                    ->after("criteria_name");
            }
            if (!Schema::hasColumn("assessment_criteria", "weight")) {
                $table->decimal("weight", 5, 2)->default(1.0)->after("score");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop columns from users table
        Schema::table("users", function (Blueprint $table) {
            if (Schema::hasColumn("users", "instansi_id")) {
                $table->dropForeign(["instansi_id"]);
                $table->dropColumn("instansi_id");
            }
        });

        // Drop columns from performance_indicators table
        Schema::table("performance_indicators", function (Blueprint $table) {
            $columns = [
                "created_by",
                "updated_by",
                "measurement_type",
                "metadata",
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn("performance_indicators", $column)) {
                    if (in_array($column, ["created_by", "updated_by"])) {
                        $table->dropForeign([
                            "performance_indicators_" . $column . "_foreign",
                        ]);
                    }
                    $table->dropColumn($column);
                }
            }
        });

        // Drop columns from performance_data table
        Schema::table("performance_data", function (Blueprint $table) {
            $columns = [
                "validated_by",
                "created_by",
                "updated_by",
                "data_source",
                "collection_method",
                "collected_at",
                "validation_notes",
                "metadata",
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn("performance_data", $column)) {
                    if (
                        in_array($column, [
                            "validated_by",
                            "created_by",
                            "updated_by",
                        ])
                    ) {
                        $table->dropForeign([
                            "performance_data_" . $column . "_foreign",
                        ]);
                    }
                    $table->dropColumn($column);
                }
            }
        });

        // Drop columns from targets table
        Schema::table("targets", function (Blueprint $table) {
            $columns = [
                "approved_by",
                "approved_at",
                "notes",
                "metadata",
                "created_by",
                "updated_by",
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn("targets", $column)) {
                    if (
                        in_array($column, [
                            "approved_by",
                            "created_by",
                            "updated_by",
                        ])
                    ) {
                        $table->dropForeign([
                            "targets_" . $column . "_foreign",
                        ]);
                    }
                    $table->dropColumn($column);
                }
            }
        });

        // Drop columns from assessments table
        Schema::table("assessments", function (Blueprint $table) {
            $columns = ["metadata", "created_by", "updated_by"];
            foreach ($columns as $column) {
                if (Schema::hasColumn("assessments", $column)) {
                    if (in_array($column, ["created_by", "updated_by"])) {
                        $table->dropForeign([
                            "assessments_" . $column . "_foreign",
                        ]);
                    }
                    $table->dropColumn($column);
                }
            }
        });

        // Drop columns from reports table
        Schema::table("reports", function (Blueprint $table) {
            $columns = ["created_by", "updated_by"];
            foreach ($columns as $column) {
                if (Schema::hasColumn("reports", $column)) {
                    $table->dropForeign(["reports_" . $column . "_foreign"]);
                    $table->dropColumn($column);
                }
            }
        });

        // Drop columns from evidence_documents table
        Schema::table("evidence_documents", function (Blueprint $table) {
            $columns = ["document_type", "uploaded_by", "uploaded_at"];
            foreach ($columns as $column) {
                if (Schema::hasColumn("evidence_documents", $column)) {
                    if ($column === "uploaded_by") {
                        $table->dropForeign(["uploaded_by"]);
                    }
                    $table->dropColumn($column);
                }
            }
        });

        // Drop columns from assessment_criteria table
        Schema::table("assessment_criteria", function (Blueprint $table) {
            $columns = ["score", "weight"];
            foreach ($columns as $column) {
                if (Schema::hasColumn("assessment_criteria", $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
