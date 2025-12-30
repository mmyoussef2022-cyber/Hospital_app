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
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->datetime('visit_date');
            $table->text('chief_complaint');
            $table->text('chief_complaint_ar')->nullable(); // Arabic complaint
            $table->json('diagnosis');
            $table->json('diagnosis_ar')->nullable(); // Arabic diagnosis
            $table->text('treatment');
            $table->text('treatment_ar')->nullable(); // Arabic treatment
            $table->json('medications')->nullable();
            $table->json('vital_signs')->nullable();
            $table->text('notes')->nullable();
            $table->text('notes_ar')->nullable(); // Arabic notes
            $table->json('attachments')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->boolean('is_emergency')->default(false);
            $table->enum('visit_type', ['consultation', 'follow_up', 'emergency', 'routine_checkup', 'procedure'])->default('consultation');
            $table->enum('status', ['active', 'completed', 'cancelled', 'pending'])->default('active');
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['patient_id', 'visit_date']);
            $table->index(['doctor_id', 'visit_date']);
            $table->index(['visit_type', 'status']);
            $table->index('is_emergency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};