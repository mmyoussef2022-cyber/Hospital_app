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
        Schema::create('patient_insurance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('insurance_company_id')->constrained()->onDelete('cascade');
            $table->foreignId('insurance_policy_id')->constrained()->onDelete('cascade');
            $table->string('member_id')->nullable(); // Insurance member ID
            $table->string('policy_holder_name')->nullable(); // For family policies
            $table->string('policy_holder_relation')->nullable(); // spouse, child, parent, etc.
            $table->string('card_number')->nullable();
            $table->date('coverage_start_date');
            $table->date('coverage_end_date')->nullable();
            $table->enum('status', ['active', 'suspended', 'expired', 'cancelled'])->default('active');
            $table->decimal('annual_limit_used', 10, 2)->default(0.00);
            $table->decimal('annual_limit_remaining', 10, 2)->nullable();
            $table->json('family_members')->nullable(); // For family policies
            $table->boolean('is_primary')->default(true); // Primary or secondary insurance
            $table->integer('priority_order')->default(1); // For multiple insurances
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['patient_id', 'insurance_company_id', 'insurance_policy_id'], 'patient_insurance_unique');
            $table->index(['insurance_company_id', 'status']);
            $table->index(['coverage_start_date', 'coverage_end_date']);
            $table->index(['status', 'is_primary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_insurance');
    }
};