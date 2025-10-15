<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite or databases that struggle altering enum/column types inline,
        // we use a safe two-step approach when necessary.
        // Attempt inline changes first where supported.
        Schema::table('indikator_kinerjas', function (Blueprint $table) {
            // Add missing input column as decimal, nullable
            if (!Schema::hasColumn('indikator_kinerjas', 'input')) {
                $table->decimal('input', 15, 2)->nullable()->after('target');
            }
        });

        // Change target and realisasi from string to decimal(15,2)
        // Use raw SQL where Doctrine does not support changing from string to decimal
        // Note: These SQLs are generic and should work for MySQL/MariaDB and SQLite; adjust as needed.
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE indikator_kinerjas MODIFY target DECIMAL(15,2)');
            DB::statement('ALTER TABLE indikator_kinerjas MODIFY realisasi DECIMAL(15,2)');
        } else if ($driver === 'sqlite') {
            // SQLite lacks ALTER COLUMN; recreate columns via temp table migration strategy
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                // Add temp columns
                $table->decimal('target_tmp', 15, 2)->nullable()->after('satuan');
                $table->decimal('realisasi_tmp', 15, 2)->nullable()->after('input');
            });
            // Copy casted values
            DB::statement('UPDATE indikator_kinerjas SET target_tmp = CAST(target AS REAL)');
            DB::statement('UPDATE indikator_kinerjas SET realisasi_tmp = CAST(realisasi AS REAL)');
            // Drop old columns
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                $table->dropColumn(['target', 'realisasi']);
            });
            // Rename temp columns to original names
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                $table->renameColumn('target_tmp', 'target');
                $table->renameColumn('realisasi_tmp', 'realisasi');
            });
        } else {
            // Fallback: try Doctrine change
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                // Requires doctrine/dbal for change(); if unavailable, raw statements should be used
                if (class_exists(\Doctrine\DBAL\Types\Type::class)) {
                    $table->decimal('target', 15, 2)->change();
                    $table->decimal('realisasi', 15, 2)->change();
                }
            });
        }

        // Update enum 'jenis' to include 'input'
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE indikator_kinerjas MODIFY jenis ENUM('output','outcome','impact','input') DEFAULT 'output'");
        } else if ($driver === 'sqlite') {
            // SQLite does not enforce enum; emulate by recreating column
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                // Create a temp column
                $table->string('jenis_tmp')->default('output')->after('realisasi');
            });
            DB::statement("UPDATE indikator_kinerjas SET jenis_tmp = jenis");
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                $table->dropColumn('jenis');
            });
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                $table->enum('jenis', ['output', 'outcome', 'impact', 'input'])->default('output')->after('realisasi');
            });
            DB::statement("UPDATE indikator_kinerjas SET jenis = jenis_tmp");
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                $table->dropColumn('jenis_tmp');
            });
        } else {
            // Try change via Doctrine if available
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                if (class_exists(\Doctrine\DBAL\Types\Type::class)) {
                    // Many DBs do not support native enum; switch to string with check in app
                    // But to honor request, attempt enum recreation via raw DBAL in app migrations.
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum change
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE indikator_kinerjas MODIFY jenis ENUM('output','outcome','impact') DEFAULT 'output'");
        } else if ($driver === 'sqlite') {
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                // Create a temp column
                $table->string('jenis_tmp')->default('output')->after('realisasi');
            });
            DB::statement("UPDATE indikator_kinerjas SET jenis_tmp = jenis");
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                $table->dropColumn('jenis');
            });
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                $table->enum('jenis', ['output', 'outcome', 'impact'])->default('output')->after('realisasi');
            });
            DB::statement("UPDATE indikator_kinerjas SET jenis = jenis_tmp");
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                $table->dropColumn('jenis_tmp');
            });
        }

        // Revert target and realisasi to string
        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE indikator_kinerjas MODIFY target VARCHAR(255)');
            DB::statement('ALTER TABLE indikator_kinerjas MODIFY realisasi VARCHAR(255)');
        } else if ($driver === 'sqlite') {
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                $table->string('target_tmp')->nullable()->after('satuan');
                $table->string('realisasi_tmp')->nullable()->after('input');
            });
            DB::statement('UPDATE indikator_kinerjas SET target_tmp = CAST(target AS TEXT)');
            DB::statement('UPDATE indikator_kinerjas SET realisasi_tmp = CAST(realisasi AS TEXT)');
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                $table->dropColumn(['target', 'realisasi']);
            });
            Schema::table('indikator_kinerjas', function (Blueprint $table) {
                $table->renameColumn('target_tmp', 'target');
                $table->renameColumn('realisasi_tmp', 'realisasi');
            });
        }

        // Drop input column
        Schema::table('indikator_kinerjas', function (Blueprint $table) {
            if (Schema::hasColumn('indikator_kinerjas', 'input')) {
                $table->dropColumn('input');
            }
        });
    }
};
