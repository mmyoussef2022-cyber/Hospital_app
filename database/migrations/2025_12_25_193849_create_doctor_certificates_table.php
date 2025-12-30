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
        Schema::create('doctor_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->string('title'); // عنوان الشهادة
            $table->string('type'); // نوع الشهادة (degree, certificate, course, etc.)
            $table->string('institution'); // المؤسسة المانحة
            $table->string('country')->nullable(); // البلد
            $table->date('issue_date'); // تاريخ الإصدار
            $table->date('expiry_date')->nullable(); // تاريخ الانتهاء
            $table->string('certificate_number')->nullable(); // رقم الشهادة
            $table->string('file_path')->nullable(); // مسار الملف
            $table->string('file_type')->nullable(); // نوع الملف
            $table->integer('file_size')->nullable(); // حجم الملف
            $table->text('description')->nullable(); // وصف الشهادة
            $table->boolean('is_verified')->default(false); // مُتحقق منها
            $table->timestamp('verified_at')->nullable(); // تاريخ التحقق
            $table->foreignId('verified_by')->nullable()->constrained('users'); // من قام بالتحقق
            $table->timestamps();
            
            // Indexes
            $table->index('type');
            $table->index('is_verified');
            $table->index('issue_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_certificates');
    }
};
