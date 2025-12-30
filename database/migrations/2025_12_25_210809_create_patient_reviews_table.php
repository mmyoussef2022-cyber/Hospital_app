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
        Schema::create('patient_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('rating')->unsigned()->comment('Rating from 1 to 5');
            $table->text('review_text')->nullable();
            $table->json('rating_aspects')->nullable()->comment('Detailed ratings for different aspects');
            $table->boolean('is_anonymous')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('admin_notes')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, hidden
            $table->timestamps();
            
            // Indexes
            $table->index(['doctor_id', 'is_approved']);
            $table->index(['patient_id', 'doctor_id']);
            $table->index(['rating', 'is_approved']);
            $table->index('status');
            
            // Ensure one review per patient per appointment
            $table->unique(['patient_id', 'appointment_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_reviews');
    }
};
