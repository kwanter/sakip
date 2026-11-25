<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SCHEMA REPAIR: several index migrations reference columns the tables never
 * had (`assessments.assessor_id`, `assessments.reviewer_id`,
 * `assessments.submitted_at`, `targets.approval_status`, `targets.target_period`).
 * SQLite tolerates the create, but it validates every index whenever it
 * rebuilds a table, so any later ALTER TABLE ... DROP COLUMN on those tables
 * fails with "no such column". The unusable indexes are dropped here, which
 * also unblocks column drops on `assessments` and `targets`.
 *
 * Dropped, not recreated: an index on a missing column cannot be used anyway,
 * so `down()` is a no-op. The `2026_01_23_*` index migrations drop these same
 * names with a `Schema::hasIndex` guard, which keeps a full rollback from
 * aborting on a missing index.
 */
return new class extends Migration
{
    /**
     * @var array<string, string> index name => table
     */
    private array $staleIndexes = [
        'idx_assessments_assessor_status' => 'assessments',
        'idx_assessments_reviewer_status' => 'assessments',
        'idx_assessments_submitted_at' => 'assessments',
        'idx_targets_approval_status' => 'targets',
        'idx_targets_period' => 'targets',
    ];

    public function up(): void
    {
        foreach ($this->staleIndexes as $index => $table) {
            if (! Schema::hasIndex($table, $index)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropIndex($index);
            });
        }
    }

    public function down(): void
    {
        // Intentionally a no-op: the dropped indexes could not be used anyway
        // (they reference missing columns) and cannot be recreated. The
        // `2026_01_23_*` migrations guard their own drops, so rollback stays
        // clean. See the class docblock.
    }
};
