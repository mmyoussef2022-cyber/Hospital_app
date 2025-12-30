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
        Schema::create('post_operative_cares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgery_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_nurse')->nullable()->constrained('users');
            $table->foreignId('attending_physician')->nullable()->constrained('users');
            
            $table->datetime('care_start_time');
            $table->datetime('care_end_time')->nullable();
            $table->enum('status', ['active', 'completed', 'transferred', 'discharged'])->default('active');
            
            // Recovery Location
            $table->enum('recovery_location', [
                'pacu', // Post Anesthesia Care Unit
                'icu',
                'ward',
                'step_down',
                'day_surgery',
                'home'
            ])->default('pacu');
            
            $table->foreignId('recovery_room_id')->nullable()->constrained('rooms');
            $table->foreignId('recovery_bed_id')->nullable()->constrained('beds');
            
            // Vital Signs Monitoring
            $table->json('vital_signs_log')->nullable(); // time-series data
            $table->integer('monitoring_frequency')->default(15); // minutes
            $table->boolean('continuous_monitoring')->default(false);
            
            // Pain Management
            $table->enum('pain_scale', ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'])->nullable();
            $table->json('pain_medications')->nullable();
            $table->text('pain_management_plan')->nullable();
            
            // Wound Care
            $table->json('wound_assessments')->nullable();
            $table->text('wound_care_instructions')->nullable();
            $table->boolean('drain_present')->default(false);
            $table->json('drain_output')->nullable();
            
            // Mobility and Activity
            $table->enum('mobility_level', [
                'bed_rest',
                'chair_transfer',
                'ambulate_assistance',
                'ambulate_independent',
                'full_activity'
            ])->default('bed_rest');
            
            $table->text('activity_restrictions')->nullable();
            $table->text('physical_therapy_orders')->nullable();
            
            // Diet and Nutrition
            $table->enum('diet_status', [
                'npo', // nothing by mouth
                'clear_liquids',
                'full_liquids',
                'soft_diet',
                'regular_diet',
                'special_diet'
            ])->default('npo');
            
            $table->text('diet_instructions')->nullable();
            $table->boolean('nausea_present')->default(false);
            $table->json('anti_nausea_medications')->nullable();
            
            // Medications
            $table->json('post_op_medications')->nullable();
            $table->json('prn_medications')->nullable(); // as needed
            $table->text('medication_schedule')->nullable();
            
            // Complications and Concerns
            $table->json('complications')->nullable();
            $table->text('nursing_notes')->nullable();
            $table->text('physician_notes')->nullable();
            
            // Discharge Planning
            $table->boolean('ready_for_discharge')->default(false);
            $table->text('discharge_criteria')->nullable();
            $table->text('discharge_instructions')->nullable();
            $table->text('follow_up_appointments')->nullable();
            $table->text('home_care_instructions')->nullable();
            
            // Quality Metrics
            $table->integer('length_of_stay')->nullable(); // hours
            $table->boolean('readmission_within_30_days')->default(false);
            $table->enum('patient_satisfaction', ['1', '2', '3', '4', '5'])->nullable();
            
            $table->timestamps();
            
            $table->index(['surgery_id', 'status']);
            $table->index(['recovery_location', 'status']);
            $table->index(['care_start_time', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_operative_cares');
    }
};
