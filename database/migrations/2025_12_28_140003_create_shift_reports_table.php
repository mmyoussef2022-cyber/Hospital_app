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
        Schema::create('shift_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_number')->unique();
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->date('report_date');
            $table->time('shift_start');
            $table->time('shift_end');
            $table->decimal('opening_balance', 10, 2)->default(0);
            $table->decimal('closing_balance', 10, 2)->default(0);
            $table->decimal('expected_balance', 10, 2)->default(0);
            $table->decimal('cash_difference', 10, 2)->default(0);
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->decimal('cash_payments', 10, 2)->default(0);
            $table->decimal('card_payments', 10, 2)->default(0);
            $table->decimal('insurance_payments', 10, 2)->default(0);
            $table->decimal('other_payments', 10, 2)->default(0);
            $table->decimal('refunds_issued', 10, 2)->default(0);
            $table->decimal('adjustments_made', 10, 2)->default(0);
            $table->integer('total_transactions')->default(0);
            $table->integer('cash_transactions')->default(0);
            $table->integer('card_transactions')->default(0);
            $table->integer('insurance_transactions')->default(0);
            $table->integer('patients_served')->default(0);
            $table->integer('appointments_handled')->default(0);
            $table->integer('new_registrations')->default(0);
            $table->decimal('average_transaction_amount', 10, 2)->default(0);
            $table->decimal('largest_transaction', 10, 2)->default(0);
            $table->decimal('smallest_transaction', 10, 2)->default(0);
            $table->text('summary_notes')->nullable();
            $table->text('discrepancy_notes')->nullable();
            $table->json('payment_breakdown')->nullable();
            $table->json('hourly_breakdown')->nullable();
            $table->json('service_breakdown')->nullable();
            $table->enum('status', ['draft', 'completed', 'reviewed', 'approved'])->default('draft');
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->json('audit_trail')->nullable();
            $table->timestamps();

            $table->index(['shift_id', 'report_date']);
            $table->index(['department_id', 'report_date']);
            $table->index(['user_id', 'report_date']);
            $table->index(['status', 'report_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_reports');
    }
};