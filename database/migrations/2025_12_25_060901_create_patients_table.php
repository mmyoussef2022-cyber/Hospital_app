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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_number', 20)->unique()->comment('Hospital patient number');
            $table->string('national_id', 20)->unique()->comment('National ID number');
            $table->string('name')->comment('Full name');
            $table->string('name_en')->nullable()->comment('Name in English');
            $table->enum('gender', ['male', 'female']);
            $table->date('date_of_birth')->comment('Date of birth');
            $table->string('phone')->nullable()->comment('Phone number');
            $table->string('mobile')->nullable()->comment('Mobile number');
            $table->string('email')->nullable()->comment('Email address');
            $table->text('address')->nullable()->comment('Full address');
            $table->string('city')->nullable()->comment('City');
            $table->string('country')->default('Saudi Arabia')->comment('Country');
            $table->string('nationality')->nullable()->comment('Nationality');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('occupation')->nullable()->comment('Patient occupation');
            $table->string('blood_type', 5)->nullable()->comment('Blood type (A+, B-, etc.)');
            $table->json('emergency_contact')->nullable()->comment('Emergency contact information');
            $table->json('insurance_info')->nullable()->comment('Insurance details');
            $table->json('allergies')->nullable()->comment('Known allergies');
            $table->json('chronic_conditions')->nullable()->comment('Chronic medical conditions');
            $table->text('medical_notes')->nullable()->comment('General medical notes');
            $table->string('family_code', 20)->nullable()->comment('Family group identifier');
            $table->foreignId('family_head_id')->nullable()->constrained('patients')->onDelete('set null')->comment('Family head patient ID');
            $table->enum('family_relation', ['self', 'spouse', 'child', 'parent', 'sibling', 'other'])->default('self');
            $table->string('barcode')->nullable()->unique()->comment('Patient barcode for scanning');
            $table->string('profile_photo')->nullable()->comment('Patient photo path');
            $table->boolean('is_active')->default(true)->comment('Patient status');
            $table->enum('patient_type', ['outpatient', 'inpatient', 'emergency', 'vip'])->default('outpatient');
            $table->timestamp('first_visit_date')->nullable()->comment('First hospital visit');
            $table->timestamp('last_visit_date')->nullable()->comment('Last hospital visit');
            $table->decimal('outstanding_balance', 10, 2)->default(0)->comment('Outstanding payment balance');
            $table->json('preferences')->nullable()->comment('Patient preferences and settings');
            $table->timestamps();
            
            $table->index(['national_id']);
            $table->index(['patient_number']);
            $table->index(['family_code']);
            $table->index(['family_head_id']);
            $table->index(['is_active']);
            $table->index(['patient_type']);
            $table->index(['barcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
