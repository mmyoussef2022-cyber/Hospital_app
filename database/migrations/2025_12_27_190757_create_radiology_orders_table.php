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
        Schema::create('radiology_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique()->comment('Radiology order number');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('radiology_study_id')->constrained('radiology_studies')->onDelete('cascade');
            $table->enum('status', ['ordered', 'scheduled', 'in_progress', 'completed', 'cancelled', 'reported'])
                  ->default('ordered')->comment('Order status');
            $table->enum('priority', ['routine', 'urgent', 'stat'])->default('routine');
            $table->datetime('ordered_at')->comment('When the study was ordered');
            $table->datetime('scheduled_at')->nullable()->comment('When study is scheduled');
            $table->datetime('started_at')->nullable()->comment('When study started');
            $table->datetime('completed_at')->nullable()->comment('When study was completed');
            $table->datetime('reported_at')->nullable()->comment('When report was finalized');
            $table->text('clinical_indication')->nullable()->comment('Clinical reason for study');
            $table->text('clinical_history')->nullable()->comment('Relevant clinical history');
            $table->text('special_instructions')->nullable()->comment('Special instructions for technologist');
            $table->boolean('contrast_used')->default(false)->comment('Whether contrast was used');
            $table->text('contrast_notes')->nullable()->comment('Contrast administration notes');
            $table->decimal('total_amount', 10, 2)->default(0)->comment('Total cost');
            $table->boolean('is_paid')->default(false)->comment('Payment status');
            $table->boolean('has_urgent_findings')->default(false)->comment('Has urgent findings requiring immediate attention');
            $table->datetime('urgent_notified_at')->nullable()->comment('When urgent findings were notified');
            $table->timestamps();
            
            $table->index(['patient_id']);
            $table->index(['doctor_id']);
            $table->index(['status']);
            $table->index(['priority']);
            $table->index(['ordered_at']);
            $table->index(['scheduled_at']);
            $table->index(['has_urgent_findings']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiology_orders');
    }
};
