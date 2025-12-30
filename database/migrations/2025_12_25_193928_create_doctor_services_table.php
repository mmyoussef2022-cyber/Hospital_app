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
        Schema::create('doctor_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->string('service_name'); // اسم الخدمة
            $table->string('service_name_en')->nullable(); // اسم الخدمة بالإنجليزية
            $table->text('description')->nullable(); // وصف الخدمة
            $table->string('category'); // فئة الخدمة (consultation, surgery, procedure, etc.)
            $table->decimal('price', 10, 2); // سعر الخدمة
            $table->integer('duration_minutes')->default(30); // مدة الخدمة بالدقائق
            $table->json('requirements')->nullable(); // متطلبات الخدمة
            $table->json('preparation_instructions')->nullable(); // تعليمات التحضير
            $table->boolean('requires_appointment')->default(true); // تحتاج موعد
            $table->boolean('is_active')->default(true); // نشطة
            $table->integer('sort_order')->default(0); // ترتيب العرض
            $table->timestamps();
            
            // Indexes
            $table->index('category');
            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_services');
    }
};
