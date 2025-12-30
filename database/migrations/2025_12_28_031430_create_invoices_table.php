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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->enum('type', ['cash', 'credit', 'insurance'])->default('cash');
            $table->enum('status', ['draft', 'pending', 'paid', 'partially_paid', 'overdue', 'cancelled'])->default('draft');
            
            // Patient and Doctor Information
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            
            // Insurance Information
            $table->foreignId('insurance_company_id')->nullable()->constrained()->onDelete('set null');
            $table->string('insurance_policy_number')->nullable();
            $table->decimal('insurance_coverage_percentage', 5, 2)->default(0);
            $table->decimal('insurance_approved_amount', 10, 2)->default(0);
            
            // Financial Information
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2)->default(0);
            
            // Dates
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->timestamp('paid_at')->nullable();
            
            // Additional Information
            $table->text('notes')->nullable();
            $table->text('payment_terms')->nullable();
            $table->json('audit_trail')->nullable(); // For tracking changes
            
            // User tracking
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['patient_id', 'status']);
            $table->index(['doctor_id', 'invoice_date']);
            $table->index(['type', 'status']);
            $table->index('invoice_date');
            $table->index('due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};