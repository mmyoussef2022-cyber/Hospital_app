<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pre_operative_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgery_id')->constrained()->onDelete('cascade');
            $table->foreignId('assessed_by')->constrained('users');
            
            $table->datetime('assessment_date');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'requires_clearance'])->default('pending');
            
            // Vital Signs
            $table->string('blood_pressure')->nullable();
            $table->integer('heart_rate')->nullable();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->integer('respiratory_rate')->nullable();
            $table->integer('oxygen_saturation')->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            
            // Medical History
            $table->json('allergies')->nullable();
            $table->json('current_medications')->nullable();
            $table->json('medical_conditions')->nullable();
            $table->json('previous_surgeries')->nullable();
            $table->json('family_history')->nullable();
            
            // Risk Assessment
            $table->enum('asa_classification', ['I', 'II', 'III', 'IV', 'V', 'VI'])->nullable();
            $table->enum('cardiac_risk', ['low', 'intermediate', 'high'])->default('low');
            $table->enum('pulmonary_risk', ['low', 'intermediate', 'high'])->default('low');
            $table->enum('bleeding_risk', ['low', 'intermediate', 'high'])->default('low');
            
            // Laboratory Results
            $table->json('lab_results')->nullable(); // CBC, chemistry, coagulation
            $table->json('imaging_results')->nullable(); // X-ray, ECG, etc.
            
            // Anesthesia Assessment
            $table->enum('airway_assessment', ['normal', 'difficult', 'very_difficult'])->default('normal');
            $table->text('anesthesia_plan')->nullable();
            $table->text('anesthesia_notes')->nullable();
            
            // Clearances
            $table->boolean('cardiac_clearance')->default(false);
            $table->boolean('pulmonary_clearance')->default(false);
            $table->boolean('medical_clearance')->default(false);
            $table->boolean('anesthesia_clearance')->default(false);
            
            $table->datetime('cardiac_clearance_date')->nullable();
            $table->datetime('pulmonary_clearance_date')->nullable();
            $table->datetime('medical_clearance_date')->nullable();
            $table->datetime('anesthesia_clearance_date')->nullable();
            
            // Instructions
            $table->text('pre_op_instructions')->nullable();
            $table->text('diet_instructions')->nullable();
            $table->text('medication_instructions')->nullable();
            $table->text('special_preparations')->nullable();
            
            // Consent
            $table->boolean('informed_consent')->default(false);
            $table->datetime('consent_date')->nullable();
            $table->foreignId('consent_witness')->nullable()->constrained('users');
            
            $table->text('assessment_notes')->nullable();
            $table->text('concerns')->nullable();
            $table->text('recommendations')->nullable();
            
            $table->boolean('is_cleared_for_surgery')->default(false);
            $table->datetime('cleared_at')->nullable();
            $table->foreignId('cleared_by')->nullable()->constrained('users');
            
            $table->timestamps();
            
            $table->index(['surgery_id', 'status']);
            $table->index(['assessment_date', 'status']);
            $table->index(['is_cleared_for_surgery']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_operative_assessments');
    }
};
