<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates the assessment_criteria table for SAKIP module.
     * Stores detailed assessment criteria and scores for comprehensive evaluation.
     */
    public function up(): void
    {
        Schema::create('assessment_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->string('criteria_name', 255);
            $table->decimal('score', 5, 2);
            $table->decimal('weight', 5, 2)->default(1.0);
            $table->text('justification')->nullable();
            $table->timestamps();
            
            // Indexes for performance optimization
            $table->index('assessment_id');
            $table->index('criteria_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_criteria');
    }
};