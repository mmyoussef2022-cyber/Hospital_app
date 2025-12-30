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
        Schema::create('doctor_financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            
            // Account Information
            $table->string('account_number')->unique();
            $table->enum('account_type', ['revenue', 'commission', 'bonus'])->default('revenue');
            $table->enum('status', ['active', 'suspended', 'closed'])->default('active');
            
            // Financial Balances
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->decimal('total_earned', 15, 2)->default(0);
            $table->decimal('total_withdrawn', 15, 2)->default(0);
            $table->decimal('pending_amount', 15, 2)->default(0);
            
            // Commission Settings
            $table->decimal('commission_rate', 5, 2)->default(0); // Percentage
            $table->decimal('fixed_fee', 10, 2)->default(0);
            $table->enum('commission_type', ['percentage', 'fixed', 'hybrid'])->default('percentage');
            
            // Payment Settings
            $table->enum('payment_frequency', ['daily', 'weekly', 'monthly', 'quarterly'])->default('monthly');
            $table->date('next_payment_date')->nullable();
            $table->decimal('minimum_withdrawal', 10, 2)->default(100);
            
            // Bank Information
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('iban')->nullable();
            $table->string('swift_code')->nullable();
            
            // Metadata
            $table->json('settings')->nullable(); // Additional settings
            $table->text('notes')->nullable();
            $table->timestamp('last_transaction_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['doctor_id', 'account_type']);
            $table->index(['status', 'payment_frequency']);
            $table->index('next_payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_financial_accounts');
    }
};