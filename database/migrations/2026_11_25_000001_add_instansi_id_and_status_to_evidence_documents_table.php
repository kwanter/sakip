<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SECURITY (audit run-3, evidence.instansi_id-missing): EvidenceDocumentPolicy
 * and EvidenceDocumentService both key off an instansi_id that the table never
 * had, so every tenant comparison ran against null (403 for every non-Super-
 * Admin download, SQL error in the service). This adds the tenant column plus
 * the status column the policy also reads, backfills the tenant from the owning
 * performance data row, and indexes the column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evidence_documents', function (Blueprint $table) {
            $table->uuid('instansi_id')->nullable()->after('performance_data_id');
            $table->string('status', 20)->default('draft')->after('file_path');

            $table->index('instansi_id');
            $table->index('status');
        });

        // Backfill: the owning performance data row is the tenant of record.
        DB::table('evidence_documents')
            ->whereNull('instansi_id')
            ->update([
                'instansi_id' => DB::raw(
                    '(SELECT instansi_id FROM performance_data WHERE performance_data.id = evidence_documents.performance_data_id)'
                ),
            ]);
    }

    public function down(): void
    {
        // SQLite rebuilds the table on dropColumn and drops the indexes with it,
        // so index removal is guarded rather than assumed.
        Schema::table('evidence_documents', function (Blueprint $table) {
            if (Schema::hasIndex('evidence_documents', 'evidence_documents_instansi_id_index')) {
                $table->dropIndex('evidence_documents_instansi_id_index');
            }

            if (Schema::hasIndex('evidence_documents', 'evidence_documents_status_index')) {
                $table->dropIndex('evidence_documents_status_index');
            }

            $table->dropColumn(['instansi_id', 'status']);
        });
    }
};
