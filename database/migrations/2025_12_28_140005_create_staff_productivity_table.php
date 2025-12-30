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
        Schema::create('staff_productivity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->date('productivity_date');
            $table->time('shift_start');
            $table->time('shift_end');
            $table->integer('total_working_minutes')->default(0);
            $table->integer('break_minutes')->default(0);
            $table->integer('productive_minutes')->default(0);
            $table->integer('appointments_handled')->default(0);
            $table->integer('patients_registered')->default(0);
            $table->integer('patients_checked_in')->default(0);
            $table->integer('services_provided')->default(0);
            $table->integer('prescriptions_issued')->default(0);
            $table->integer('lab_orders_processed')->default(0);
            $table->integer('radiology_orders_processed')->default(0);
            $table->integer('invoices_generated')->default(0);
            $table->integer('payments_processed')->default(0);
            $table->decimal('revenue_generated', 10, 2)->default(0);
            $table->decimal('collections_made', 10, 2)->default(0);
            $table->integer('phone_calls_handled')->default(0);
            $table->integer('emails_processed')->default(0);
            $table->integer('documents_processed')->default(0);
            $table->decimal('efficiency_score', 5, 2)->default(0);
            $table->decimal('quality_score', 5, 2)->default(0);
            $table->decimal('customer_satisfaction_score', 5, 2)->default(0);
            $table->integer('errors_made')->default(0);
            $table->integer('corrections_needed')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->text('achievements')->nullable();
            $table->text('challenges_faced')->nullable();
            $table->text('improvement_suggestions')->nullable();
            $table->text('supervisor_notes')->nullable();
            $table->json('hourly_breakdown')->nullable();
            $table->json('task_breakdown')->nullable();
            $table->json('performance_metrics')->nullable();
            $table->enum('performance_rating', ['excellent', 'good', 'satisfactory', 'needs_improvement', 'unsatisfactory'])->nullable();
            $table->foreignId('evaluated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('evaluated_at')->nullable();
            $table->json('audit_trail')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'shift_id']);
            $table->index(['user_id', 'productivity_date']);
            $table->index(['department_id', 'productivity_date']);
            $table->index(['performance_rating', 'productivity_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_productivity');
    }
};