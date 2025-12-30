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
        Schema::create('doctor_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            
            // Commission Configuration
            $table->string('name'); // Commission rule name
            $table->text('description')->nullable();
            $table->enum('type', ['service', 'appointment', 'revenue', 'performance'])->default('service');
            $table->enum('calculation_method', ['percentage', 'fixed', 'tiered', 'hybrid'])->default('percentage');
            
            // Commission Rates
            $table->decimal('rate', 8, 4)->default(0); // Can be percentage or fixed amount
            $table->decimal('minimum_amount', 10, 2)->default(0);
            $table->decimal('maximum_amount', 10, 2)->nullable();
            
            // Tiered Commission (JSON structure for multiple tiers)
            $table->json('tier_structure')->nullable(); // [{min: 0, max: 1000, rate: 10}, {min: 1001, max: 5000, rate: 15}]
            
            // Conditions
            $table->json('conditions')->nullable(); // Service types, time periods, etc.
            $table->decimal('minimum_service_amount', 10, 2)->default(0);
            $table->integer('minimum_appointments')->default(0);
            
            // Time-based Settings
            $table->enum('period_type', ['daily', 'weekly', 'monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->date('effective_from');
            $table->date('effective_until')->nullable();
            
            // Status and Priority
            $table->enum('status', ['active', 'inactive', 'expired'])->default('active');
            $table->integer('priority')->default(1); // Higher number = higher priority
            
            // Auto-calculation Settings
            $table->boolean('auto_calculate')->default(true);
            $table->boolean('auto_pay')->default(false);
            $table->enum('payment_trigger', ['immediate', 'end_of_period', 'manual'])->default('end_of_period');
            
            // Specific Service/Department Targeting
            $table->json('applicable_services')->nullable(); // Array of service IDs
            $table->json('applicable_departments')->nullable(); // Array of department names
            
            $table->timestamps();
            
            // Indexes
            $table->index(['doctor_id', 'status', 'effective_from']);
            $table->index(['type', 'calculation_method']);
            $table->index(['effective_from', 'effective_until']);
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_commissions');
    }
};