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
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('doctor_number')->unique(); // رقم الطبيب
            $table->string('national_id')->unique(); // الرقم القومي
            $table->string('license_number')->unique(); // رقم الترخيص
            $table->string('specialization'); // التخصص الرئيسي
            $table->json('sub_specializations')->nullable(); // التخصصات الفرعية
            $table->string('degree'); // الدرجة العلمية (بكالوريوس، ماجستير، دكتوراه)
            $table->string('university')->nullable(); // الجامعة
            $table->integer('experience_years'); // سنوات الخبرة
            $table->json('languages')->nullable(); // اللغات المتحدث بها
            $table->text('biography')->nullable(); // السيرة الذاتية
            $table->json('working_hours')->nullable(); // ساعات العمل
            $table->decimal('consultation_fee', 8, 2)->default(0); // رسوم الاستشارة
            $table->decimal('follow_up_fee', 8, 2)->default(0); // رسوم المتابعة
            $table->string('room_number')->nullable(); // رقم الغرفة/العيادة
            $table->string('phone')->nullable(); // هاتف مباشر
            $table->string('email')->nullable(); // إيميل مباشر
            $table->json('social_media')->nullable(); // وسائل التواصل الاجتماعي
            $table->string('profile_photo')->nullable(); // صورة شخصية
            $table->boolean('is_available')->default(true); // متاح للمواعيد
            $table->boolean('is_active')->default(true); // نشط في النظام
            $table->decimal('rating', 3, 2)->default(0); // التقييم
            $table->integer('total_reviews')->default(0); // عدد التقييمات
            $table->timestamps();
            
            // Indexes
            $table->index('specialization');
            $table->index('is_available');
            $table->index('is_active');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
