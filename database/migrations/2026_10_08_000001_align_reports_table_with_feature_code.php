<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The reports table was missing the columns the feature code has always
 * written (title, description, category, content, file_format, approver,
 * audit user ids) and its status enum rejected real workflow values
 * ('draft', 'approved', ...). Align the schema with the code.
 *
 * Column additions are guarded with hasColumn() because some local
 * databases were drifted manually (columns added outside migrations).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (! Schema::hasColumn('reports', 'title')) {
                $table->string('title')->nullable()->after('report_type');
            }
            if (! Schema::hasColumn('reports', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (! Schema::hasColumn('reports', 'category')) {
                $table->string('category', 50)->nullable()->after('description');
            }
            if (! Schema::hasColumn('reports', 'content')) {
                $table->longText('content')->nullable()->after('file_path');
            }
            if (! Schema::hasColumn('reports', 'file_format')) {
                $table->string('file_format', 10)->nullable()->after('file_path');
            }
            if (! Schema::hasColumn('reports', 'approver_id')) {
                $table->foreignUuid('approver_id')->nullable()->after('generated_by');
            }
            if (! Schema::hasColumn('reports', 'created_by')) {
                $table->foreignUuid('created_by')->nullable()->after('submitted_at');
            }
            if (! Schema::hasColumn('reports', 'updated_by')) {
                $table->foreignUuid('updated_by')->nullable()->after('created_by');
            }
        });

        // Enum restricted statuses to generating/completed/failed/submitted,
        // but the workflow uses draft/submitted/approved/rejected too.
        Schema::table('reports', function (Blueprint $table) {
            $table->string('status', 20)->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'category', 'content', 'file_format']);
        });
    }
};
