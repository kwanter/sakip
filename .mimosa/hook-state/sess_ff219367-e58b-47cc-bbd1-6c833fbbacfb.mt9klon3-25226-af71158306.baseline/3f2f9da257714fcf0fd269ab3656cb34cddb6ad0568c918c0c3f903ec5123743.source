<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds instansi_id foreign key to users table to link users to their institution.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('instansi_id')
                ->nullable()
                ->after('id')
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['instansi_id']);
            $table->dropIndex(['instansi_id']);
            $table->dropColumn('instansi_id');
        });
    }
};
