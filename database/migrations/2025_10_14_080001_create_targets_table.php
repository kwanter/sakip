<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the targets table for SAKIP module.
     * Stores annual targets for performance indicators.
     */
    public function up(): void
    {
        Schema::create('targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_indicator_id')->constrained('performance_indicators')->onDelete('cascade');
            $table->integer('year');
            $table->decimal('target_value', 15, 2);
            $table->decimal('minimum_value', 15, 2)->nullable();
            $table->text('justification')->nullable();
            $table->enum('status', ['draft', 'approved', 'rejected', 'revised'])->default('draft');
            $table->timestamps();
            
            // Unique constraint to prevent duplicate targets per indicator per year
            $table->unique(['performance_indicator_id', 'year']);
            
            // Indexes for performance optimization
            $table->index('performance_indicator_id');
            $table->index('year');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('targets');
    }
};