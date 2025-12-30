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
        Schema::create('surgeries', function (Blueprint $table) {
            $table->id();
            $table->string('surgery_number')->unique();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('primary_surgeon_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('surgical_procedure_id')->nullable();
            $table->unsignedBigInteger('operating_room_id')->nullable();
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            
            $table->datetime('scheduled_start_time');
            $table->datetime('scheduled_end_time');
            $table->datetime('actual_start_time')->nullable();
            $table->datetime('actual_end_time')->nullable();
            
            $table->enum('priority', ['routine', 'urgent', 'emergency', 'elective'])->default('routine');
            $table->enum('status', ['scheduled', 'pre_op', 'in_progress', 'completed', 'cancelled', 'postponed'])->default('scheduled');
            $table->enum('type', ['inpatient', 'outpatient', 'day_surgery', 'emergency'])->default('inpatient');
            
            $table->text('pre_operative_notes')->nullable();
            $table->text('operative_notes')->nullable();
            $table->text('post_operative_notes')->nullable();
            $table->text('complications')->nullable();
            $table->text('cancellation_reason')->nullable();
            
            $table->json('anesthesia_details')->nullable(); // type, duration, medications
            $table->json('equipment_used')->nullable();
            $table->json('medications_given')->nullable();
            $table->json('blood_loss')->nullable(); // estimated, actual
            
            $table->decimal('estimated_cost', 10, 2)->default(0);
            $table->decimal('actual_cost', 10, 2)->default(0);
            $table->integer('estimated_duration')->default(60); // minutes
            $table->integer('actual_duration')->nullable(); // minutes
            
            $table->boolean('is_emergency')->default(false);
            $table->boolean('requires_icu')->default(false);
            $table->boolean('requires_blood_bank')->default(false);
            $table->boolean('is_completed')->default(false);
            
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['status', 'scheduled_start_time']);
            $table->index(['priority', 'is_emergency']);
            $table->index(['patient_id', 'scheduled_start_time']);
            $table->index(['primary_surgeon_id', 'scheduled_start_time']);
            $table->index(['operating_room_id', 'scheduled_start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgeries');
    }
};
