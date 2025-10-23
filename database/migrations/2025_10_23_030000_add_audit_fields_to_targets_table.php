<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds audit tracking and approval fields to targets table.
     */
    public function up(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            // Add audit tracking columns
            $table->string('created_by')->nullable()->after('status');
            $table->string('updated_by')->nullable()->after('created_by');
            $table->string('approved_by')->nullable()->after('updated_by');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('notes')->nullable()->after('approved_at');
            $table->json('metadata')->nullable()->after('notes');
            $table->softDeletes()->after('metadata');

            // Add foreign key constraints for audit fields
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();

            // Add indexes for performance
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('targets', function (Blueprint $table) {
            $table->dropForeignKey(['created_by']);
            $table->dropForeignKey(['updated_by']);
            $table->dropForeignKey(['approved_by']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['approved_by']);
            $table->dropColumn([
                'created_by',
                'updated_by',
                'approved_by',
                'approved_at',
                'notes',
                'metadata',
                'deleted_at'
            ]);
        });
    }
};
