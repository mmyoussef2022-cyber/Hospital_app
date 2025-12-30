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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Full name');
            $table->string('email')->unique()->comment('Email address');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->comment('Encrypted password');
            $table->string('national_id', 20)->unique()->comment('National ID number');
            $table->string('phone')->nullable()->comment('Phone number');
            $table->string('mobile')->nullable()->comment('Mobile number');
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable()->comment('Full address');
            $table->string('employee_id', 20)->nullable()->unique()->comment('Hospital employee ID');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null')->comment('Assigned department');
            $table->string('job_title')->nullable()->comment('Job position');
            $table->string('specialization')->nullable()->comment('Medical specialization if applicable');
            $table->string('license_number')->nullable()->comment('Medical license number');
            $table->date('hire_date')->nullable()->comment('Employment start date');
            $table->enum('employment_status', ['active', 'inactive', 'suspended', 'terminated'])->default('active');
            $table->decimal('salary', 10, 2)->nullable()->comment('Monthly salary');
            $table->json('emergency_contact')->nullable()->comment('Emergency contact information');
            $table->json('qualifications')->nullable()->comment('Educational qualifications');
            $table->string('profile_photo')->nullable()->comment('Profile photo path');
            $table->boolean('is_active')->default(true)->comment('Account status');
            $table->timestamp('last_login_at')->nullable();
            $table->string('preferred_language', 5)->default('ar')->comment('UI language preference');
            $table->json('notification_preferences')->nullable()->comment('Notification settings');
            $table->rememberToken();
            $table->timestamps();
            
            $table->index(['national_id']);
            $table->index(['employee_id']);
            $table->index(['department_id']);
            $table->index(['is_active']);
            $table->index(['employment_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
