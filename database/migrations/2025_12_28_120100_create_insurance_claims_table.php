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
        Schema::create('insurance_claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_number')->unique();
            $table->foreignId('insurance_company_id')->constrained()->onDelete('cascade');
            $table->foreignId('insurance_policy_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained()->onDelete('set null');
            $table->date('service_date');
            $table->date('claim_date');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('covered_amount', 10, 2);
            $table->decimal('deductible_amount', 8, 2)->default(0.00);
            $table->decimal('co_payment_amount', 8, 2)->default(0.00);
            $table->decimal('patient_responsibility', 10, 2);
            $table->decimal('approved_amount', 10, 2)->nullable();
            $table->decimal('paid_amount', 10, 2)->default(0.00);
            $table->enum('status', [
                'draft', 'submitted', 'under_review', 'approved', 
                'partially_approved', 'rejected', 'paid', 'cancelled'
            ])->default('draft');
            $table->enum('priority', ['normal', 'urgent', 'emergency'])->default('normal');
            $table->string('diagnosis_code')->nullable();
            $table->text('diagnosis_description')->nullable();
            $table->json('services_provided')->nullable(); // Array of services with codes and amounts
            $table->json('supporting_documents')->nullable(); // Array of document paths
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['insurance_company_id', 'status']);
            $table->index(['patient_id', 'service_date']);
            $table->index(['claim_date', 'status']);
            $table->index(['status', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_claims');
    }
};