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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->string('medication_name');
            $table->string('medication_name_ar')->nullable(); // Arabic name
            $table->string('dosage');
            $table->string('frequency');
            $table->string('frequency_ar')->nullable(); // Arabic frequency
            $table->integer('duration_days');
            $table->text('instructions');
            $table->text('instructions_ar')->nullable(); // Arabic instructions
            $table->text('warnings')->nullable();
            $table->text('warnings_ar')->nullable(); // Arabic warnings
            $table->enum('status', ['active', 'completed', 'cancelled', 'expired'])->default('active');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_controlled_substance')->default(false);
            $table->string('pharmacy_notes')->nullable();
            $table->string('pharmacy_notes_ar')->nullable(); // Arabic pharmacy notes
            $table->timestamps();
            
            // Indexes
            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'created_at']);
            $table->index(['medication_name', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
