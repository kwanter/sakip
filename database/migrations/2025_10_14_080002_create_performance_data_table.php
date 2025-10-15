<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the performance_data table for SAKIP module.
     * Stores actual performance data collected for indicators.
     */
    public function up(): void
    {
        Schema::create('performance_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_indicator_id')->constrained('performance_indicators')->onDelete('cascade');
            $table->foreignUuid('instansi_id')->constrained('instansis')->onDelete('cascade');
            $table->foreignUuid('submitted_by')->constrained('users')->onDelete('cascade');
            $table->string('period', 7); // YYYY-MM format
            $table->decimal('actual_value', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'submitted', 'validated', 'rejected'])->default('draft');
            $table->enum('data_quality', ['excellent', 'good', 'fair', 'poor'])->default('good');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate data per indicator per period
            $table->unique(['performance_indicator_id', 'instansi_id', 'period'], 'unique_performance_data');
            
            // Indexes for performance optimization
            $table->index('performance_indicator_id');
            $table->index('instansi_id');
            $table->index('period');
            $table->index('status');
            $table->index('submitted_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_data');
    }
};