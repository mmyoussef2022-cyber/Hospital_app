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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_number')->unique();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            
            // Payment Information
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'check', 'insurance', 'online'])->default('cash');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending');
            $table->decimal('amount', 10, 2);
            $table->decimal('received_amount', 10, 2)->nullable(); // Actual amount received (for cash payments)
            $table->decimal('change_amount', 10, 2)->default(0);
            
            // Payment Details
            $table->string('reference_number')->nullable(); // Bank reference, card transaction ID, etc.
            $table->string('card_last_four')->nullable();
            $table->string('card_type')->nullable(); // visa, mastercard, etc.
            $table->string('bank_name')->nullable();
            $table->string('check_number')->nullable();
            
            // Insurance Payment Details
            $table->foreignId('insurance_company_id')->nullable()->constrained()->onDelete('set null');
            $table->string('insurance_claim_number')->nullable();
            $table->date('insurance_approval_date')->nullable();
            
            // Dates
            $table->timestamp('payment_date');
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('cleared_at')->nullable(); // For checks and bank transfers
            
            // Additional Information
            $table->text('notes')->nullable();
            $table->json('payment_details')->nullable(); // For storing payment gateway responses, etc.
            $table->json('audit_trail')->nullable();
            
            // User tracking
            $table->foreignId('processed_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['invoice_id', 'status']);
            $table->index(['patient_id', 'payment_date']);
            $table->index(['payment_method', 'status']);
            $table->index('payment_date');
            $table->index('reference_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};