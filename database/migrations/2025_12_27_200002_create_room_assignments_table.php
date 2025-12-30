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
        Schema::create('room_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->foreignId('bed_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade'); // staff member who assigned
            $table->timestamp('assigned_at');
            $table->timestamp('expected_discharge_at')->nullable();
            $table->timestamp('actual_discharge_at')->nullable();
            $table->enum('assignment_type', ['admission', 'transfer', 'emergency'])->default('admission');
            $table->enum('status', ['active', 'discharged', 'transferred', 'cancelled'])->default('active');
            $table->text('assignment_notes')->nullable();
            $table->text('discharge_notes')->nullable();
            $table->decimal('total_charges', 10, 2)->default(0);
            $table->timestamps();
            
            $table->index(['patient_id', 'status']);
            $table->index(['room_id', 'status']);
            $table->index(['assigned_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_assignments');
    }
};