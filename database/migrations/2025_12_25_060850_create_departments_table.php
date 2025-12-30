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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar')->comment('Department name in Arabic');
            $table->string('name_en')->comment('Department name in English');
            $table->string('code', 10)->unique()->comment('Department code (e.g., CARD, ORTH)');
            $table->text('description_ar')->nullable()->comment('Department description in Arabic');
            $table->text('description_en')->nullable()->comment('Department description in English');
            $table->string('location')->nullable()->comment('Physical location in hospital');
            $table->string('phone')->nullable()->comment('Department phone number');
            $table->string('extension')->nullable()->comment('Internal extension number');
            $table->boolean('is_active')->default(true)->comment('Department status');
            $table->integer('capacity')->default(0)->comment('Maximum patient capacity');
            $table->json('working_hours')->nullable()->comment('Department working hours');
            $table->timestamps();
            
            $table->index(['is_active']);
            $table->index(['code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
