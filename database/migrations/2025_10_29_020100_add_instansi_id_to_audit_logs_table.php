<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds instansi_id to audit_logs table to track which institution the audit log belongs to.
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->foreignUuid('instansi_id')
                ->nullable()
                ->after('user_id')
                ->constrained('instansis')
                ->onDelete('set null');

            $table->index('instansi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropForeign(['instansi_id']);
            $table->dropIndex(['instansi_id']);
            $table->dropColumn('instansi_id');
        });
    }
};
