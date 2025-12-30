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
        Schema::create('doctor_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->foreignId('financial_account_id')->constrained('doctor_financial_accounts')->onDelete('cascade');
            
            // Transaction Details
            $table->string('transaction_number')->unique();
            $table->enum('type', ['credit', 'debit']); // Credit = money in, Debit = money out
            $table->enum('category', [
                'service_payment', 'commission', 'bonus', 'penalty', 
                'withdrawal', 'adjustment', 'refund', 'installment'
            ]);
            
            // Amount Information
            $table->decimal('amount', 15, 2);
            $table->decimal('commission_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2); // Amount after commission/fees
            $table->string('currency', 3)->default('SAR');
            
            // Related Records
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained('doctor_services')->onDelete('set null');
            $table->foreignId('dental_treatment_id')->nullable()->constrained('dental_treatments')->onDelete('set null');
            $table->foreignId('dental_installment_id')->nullable()->constrained('dental_installments')->onDelete('set null');
            
            // Transaction Status
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            // Payment Information
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'insurance', 'installment'])->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('external_transaction_id')->nullable();
            
            // Description and Notes
            $table->string('description');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Additional data
            
            // Audit Trail
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['doctor_id', 'type', 'status']);
            $table->index(['category', 'created_at']);
            $table->index(['appointment_id', 'service_id']);
            $table->index('transaction_number');
            $table->index('processed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_transactions');
    }
};