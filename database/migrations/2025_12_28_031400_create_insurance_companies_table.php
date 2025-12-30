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
        Schema::create('insurance_companies', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('code')->unique();
            $table->string('commercial_registration')->nullable();
            $table->string('tax_number')->nullable();
            
            // Contact Information
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->text('address_ar')->nullable();
            $table->text('address_en')->nullable();
            
            // Coverage Information
            $table->decimal('default_coverage_percentage', 5, 2)->default(80);
            $table->decimal('max_coverage_amount', 10, 2)->nullable();
            $table->decimal('deductible_amount', 10, 2)->default(0);
            $table->json('covered_services')->nullable(); // Array of covered service types
            $table->json('excluded_services')->nullable(); // Array of excluded service types
            
            // Payment Terms
            $table->integer('payment_terms_days')->default(30);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->enum('payment_method', ['bank_transfer', 'check', 'online'])->default('bank_transfer');
            
            // Bank Information
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('iban')->nullable();
            $table->string('swift_code')->nullable();
            
            // Contract Information
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();
            $table->enum('contract_status', ['active', 'suspended', 'terminated'])->default('active');
            
            // Additional Information
            $table->text('notes')->nullable();
            $table->json('settings')->nullable(); // For storing company-specific settings
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['is_active', 'contract_status']);
            $table->index('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurance_companies');
    }
};