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
        Schema::create('dental_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_number')->unique();
            $table->foreignId('dental_treatment_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('session_order'); // 1, 2, 3, etc.
            $table->string('session_title');
            $table->text('session_description');
            $table->json('procedures_performed')->nullable(); // Array of procedures
            $table->json('materials_used')->nullable(); // Array of materials
            $table->decimal('session_cost', 8, 2);
            $table->decimal('session_payment', 8, 2)->default(0);
            $table->date('scheduled_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->time('duration')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->text('session_notes')->nullable();
            $table->json('session_photos')->nullable();
            $table->text('next_session_plan')->nullable();
            $table->json('complications')->nullable(); // Any complications or issues
            $table->decimal('pain_level_before', 2, 1)->nullable(); // 0-10 scale
            $table->decimal('pain_level_after', 2, 1)->nullable(); // 0-10 scale
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->timestamps();
            
            $table->index(['dental_treatment_id', 'session_order']);
            $table->index(['scheduled_date', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_sessions');
    }
};