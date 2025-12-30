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
        Schema::create('dental_installments', function (Blueprint $table) {
            $table->id();
            $table->string('installment_number')->unique();
            $table->foreignId('dental_treatment_id')->constrained()->onDelete('cascade');
            $table->integer('installment_order'); // 1, 2, 3, etc.
            $table->decimal('amount', 8, 2);
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->decimal('paid_amount', 8, 2)->default(0);
            $table->decimal('late_fee', 8, 2)->default(0);
            $table->enum('status', ['pending', 'paid', 'overdue', 'partial', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'check'])->nullable();
            $table->string('payment_reference')->nullable();
            $table->text('payment_notes')->nullable();
            $table->integer('days_overdue')->default(0);
            $table->boolean('reminder_sent')->default(false);
            $table->timestamp('reminder_sent_at')->nullable();
            $table->json('payment_history')->nullable(); // Track partial payments
            $table->timestamps();
            
            $table->index(['dental_treatment_id', 'installment_order']);
            $table->index(['due_date', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dental_installments');
    }
};