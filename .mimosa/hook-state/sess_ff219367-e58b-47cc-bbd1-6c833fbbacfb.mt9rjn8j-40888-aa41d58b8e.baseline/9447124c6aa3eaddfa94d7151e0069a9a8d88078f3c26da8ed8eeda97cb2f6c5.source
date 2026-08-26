<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');

        Schema::disableForeignKeyConstraints();

        // Drop foreign keys from pivot tables
        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
            $table->dropForeign(['role_id']);
        });
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
            $table->dropForeign(['permission_id']);
        });
        Schema::table($tableNames['role_has_permissions'], function (Blueprint $table) {
            $table->dropForeign(['permission_id']);
            $table->dropForeign(['role_id']);
        });

        // Change primary key columns to UUID
        Schema::table($tableNames['roles'], function (Blueprint $table) {
            $table->uuid('id')->change();
        });
        Schema::table($tableNames['permissions'], function (Blueprint $table) {
            $table->uuid('id')->change();
        });

        // Change foreign key columns in pivot tables to UUID
        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($columnNames) {
            $table->uuid('role_id')->change();
            $table->uuid($columnNames['model_morph_key'])->change();
        });

        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($columnNames) {
            $table->uuid('permission_id')->change();
            $table->uuid($columnNames['model_morph_key'])->change();
        });

        Schema::table($tableNames['role_has_permissions'], function (Blueprint $table) {
            $table->uuid('permission_id')->change();
            $table->uuid('role_id')->change();
        });

        // Add foreign keys back
        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) use ($tableNames) {
            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');
        });
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
        });
        Schema::table($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->foreign('permission_id')
                ->references('id')
                ->on($tableNames['permissions'])
                ->onDelete('cascade');
            $table->foreign('role_id')
                ->references('id')
                ->on($tableNames['roles'])
                ->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Not easily reversible
    }
};
