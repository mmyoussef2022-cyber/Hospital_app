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
        Schema::create('beds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            $table->string('bed_number'); // A, B, C or 1, 2, 3
            $table->string('bed_type'); // standard, icu, pediatric, bariatric
            $table->enum('status', ['available', 'occupied', 'maintenance', 'cleaning', 'reserved'])->default('available');
            $table->json('features')->nullable(); // electric, manual, side_rails, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_occupied_at')->nullable();
            $table->timestamp('last_cleaned_at')->nullable();
            $table->timestamps();
            
            $table->unique(['room_id', 'bed_number']);
            $table->index(['status', 'bed_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beds');
    }
};