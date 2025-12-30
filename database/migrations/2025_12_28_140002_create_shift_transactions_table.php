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
        Schema::create('shift_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->foreignId('shift_id')->constrained()->onDelete('cascade');
            $table->foreignId('cash_register_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('transaction_type', ['payment', 'refund', 'adjustment', 'opening_balance', 'closing_balance', 'expense', 'deposit'])->default('payment');
            $table->enum('payment_method', ['cash', 'card', 'bank_transfer', 'check', 'insurance', 'online'])->default('cash');
            $table->decimal('amount', 10, 2);
            $table->decimal('received_amount', 10, 2)->nullable();
            $table->decimal('change_amount', 10, 2)->default(0);
            $table->string('reference_number')->nullable();
            $table->string('card_last_four')->nullable();
            $table->string('card_type')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('check_number')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded'])->default('completed');
            $table->timestamp('transaction_date');
            $table->foreignId('processed_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->json('transaction_details')->nullable();
            $table->json('audit_trail')->nullable();
            $table->timestamps();

            $table->index(['shift_id', 'transaction_date']);
            $table->index(['cash_register_id', 'transaction_date']);
            $table->index(['patient_id', 'transaction_date']);
            $table->index(['transaction_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_transactions');
    }
};