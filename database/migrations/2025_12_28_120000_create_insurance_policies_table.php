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
        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_company_id')->constrained()->onDelete('cascade');
            $table->string('policy_number')->unique();
            $table->string('policy_name_ar');
            $table->string('policy_name_en');
            $table->enum('policy_type', ['individual', 'family', 'group', 'corporate']);
            $table->decimal('coverage_percentage', 5, 2)->default(80.00);
            $table->decimal('deductible_amount', 10, 2)->default(0.00);
            $table->decimal('max_coverage_per_year', 12, 2)->nullable();
            $table->decimal('max_coverage_per_visit', 10, 2)->nullable();
            $table->json('covered_services')->nullable(); // Array of covered service types
            $table->json('excluded_services')->nullable(); // Array of excluded service types
            $table->json('coverage_rules')->nullable(); // Complex coverage rules
            $table->boolean('requires_pre_approval')->default(false);
            $table->integer('pre_approval_days')->nullable();
            $table->decimal('co_payment_amount', 8, 2)->default(0.00);
            $table->decimal('co_payment_percentage', 5, 2)->default(0.00);
            $table->integer('waiting_period_days')->default(0);
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'suspended', 'expired', 'cancelled'])->default('active');
            $table->text('terms_and_conditions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['insurance_company_id', 'status']);
            $table->index(['policy_type', 'status']);
            $table->index(['effective_date', 'expiry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_policies');
    }
};