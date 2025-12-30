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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->integer('duration')->default(30); // Duration in minutes
            $table->enum('type', ['consultation', 'follow_up', 'emergency', 'surgery'])->default('consultation');
            $table->enum('status', ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('appointment_date');
            $table->index(['doctor_id', 'appointment_date']);
            $table->index('patient_id');
            $table->index('status');
            
            // Unique constraint to prevent double booking
            $table->unique(['doctor_id', 'appointment_date', 'appointment_time'], 'unique_doctor_appointment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
