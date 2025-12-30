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
        Schema::create('radiology_studies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Study code');
            $table->string('name')->comment('Study name in Arabic');
            $table->string('name_en')->nullable()->comment('Study name in English');
            $table->text('description')->nullable()->comment('Study description');
            $table->string('category')->comment('Study category (X-Ray, CT, MRI, etc.)');
            $table->string('body_part')->comment('Body part being examined');
            $table->decimal('price', 10, 2)->default(0)->comment('Study price');
            $table->integer('duration_minutes')->default(30)->comment('Expected duration in minutes');
            $table->text('preparation_instructions')->nullable()->comment('Patient preparation instructions');
            $table->text('contrast_instructions')->nullable()->comment('Contrast agent instructions');
            $table->boolean('requires_contrast')->default(false)->comment('Whether contrast is required');
            $table->boolean('requires_fasting')->default(false)->comment('Whether fasting is required');
            $table->boolean('is_urgent_capable')->default(true)->comment('Can be performed urgently');
            $table->boolean('is_active')->default(true)->comment('Whether study is available');
            $table->timestamps();
            
            $table->index(['category']);
            $table->index(['body_part']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiology_studies');
    }
};
