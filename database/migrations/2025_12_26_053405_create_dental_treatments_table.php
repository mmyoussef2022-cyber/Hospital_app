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
        Schema::create('dental_treatments', function (Blueprint $table) {
            $table->id();
            $table->string('treatment_number')->unique();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->string('treatment_type'); // orthodontics, implants, cosmetic, general, surgery
            $table->string('title');
            $table->text('description');
            $table->json('teeth_involved')->nullable(); // Array of tooth numbers
            $table->decimal('total_cost', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2);
            $table->integer('total_sessions');
            $table->integer('completed_sessions')->default(0);
            $table->enum('payment_type', ['cash', 'installments', 'insurance'])->default('cash');
            $table->integer('installment_months')->nullable();
            $table->decimal('monthly_installment', 8, 2)->nullable();
            $table->date('start_date');
            $table->date('expected_end_date');
            $table->date('actual_end_date')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled', 'on_hold'])->default('planned');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('notes')->nullable();
            $table->json('treatment_plan')->nullable(); // Detailed treatment steps
            $table->json('before_photos')->nullable();
            $table->json('after_photos')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'status']);
            $table->index('treatment_type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_treatments');
    }
};