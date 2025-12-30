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
        Schema::create('operating_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->onDelete('cascade');
            
            $table->string('or_number')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            
            $table->enum('or_type', [
                'general',
                'cardiac',
                'orthopedic', 
                'neurosurgery',
                'ophthalmology',
                'ent',
                'gynecology',
                'urology',
                'plastic',
                'trauma',
                'pediatric',
                'hybrid'
            ])->default('general');
            
            $table->json('capabilities')->nullable(); // laparoscopic, robotic, imaging, etc.
            $table->json('equipment')->nullable(); // specialized equipment available
            $table->json('monitoring_systems')->nullable(); // available monitoring
            
            $table->boolean('has_laminar_flow')->default(false);
            $table->boolean('has_imaging')->default(false);
            $table->boolean('has_robotic_system')->default(false);
            $table->boolean('has_cardiac_bypass')->default(false);
            $table->boolean('has_neuro_monitoring')->default(false);
            
            $table->integer('temperature_min')->default(18); // celsius
            $table->integer('temperature_max')->default(26); // celsius
            $table->integer('humidity_min')->default(30); // percentage
            $table->integer('humidity_max')->default(60); // percentage
            
            $table->enum('status', [
                'available',
                'occupied', 
                'cleaning',
                'maintenance',
                'setup',
                'turnover',
                'emergency_ready'
            ])->default('available');
            
            $table->datetime('last_cleaned_at')->nullable();
            $table->datetime('last_maintenance_at')->nullable();
            $table->datetime('next_maintenance_due')->nullable();
            
            $table->text('cleaning_notes')->nullable();
            $table->text('maintenance_notes')->nullable();
            $table->text('setup_notes')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->boolean('is_emergency_ready')->default(false);
            
            $table->timestamps();
            
            $table->index(['or_type', 'status']);
            $table->index(['status', 'is_active']);
            $table->index(['is_emergency_ready', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operating_rooms');
    }
};
