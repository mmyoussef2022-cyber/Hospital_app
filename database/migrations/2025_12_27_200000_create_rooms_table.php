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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique();
            $table->string('room_type'); // ward, icu, emergency, surgery, private, semi_private
            $table->string('department'); // internal_medicine, surgery, pediatrics, etc.
            $table->integer('floor');
            $table->string('wing')->nullable(); // north, south, east, west
            $table->integer('capacity')->default(1); // number of beds
            $table->decimal('daily_rate', 10, 2)->default(0); // daily room rate
            $table->text('description')->nullable();
            $table->json('amenities')->nullable(); // TV, AC, bathroom, etc.
            $table->json('equipment')->nullable(); // medical equipment in room
            $table->enum('status', ['available', 'occupied', 'maintenance', 'cleaning', 'reserved'])->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_cleaned_at')->nullable();
            $table->timestamp('last_maintenance_at')->nullable();
            $table->timestamps();
            
            $table->index(['room_type', 'status']);
            $table->index(['department', 'status']);
            $table->index(['floor', 'wing']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};