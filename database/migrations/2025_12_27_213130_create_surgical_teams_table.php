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
        Schema::create('surgical_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surgery_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->enum('role', [
                'primary_surgeon',
                'assistant_surgeon', 
                'anesthesiologist',
                'scrub_nurse',
                'circulating_nurse',
                'surgical_technician',
                'resident',
                'medical_student',
                'perfusionist',
                'other'
            ]);
            
            $table->string('role_description')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_confirmed')->default(false);
            
            $table->datetime('assigned_at')->nullable();
            $table->datetime('confirmed_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('commission_percentage', 5, 2)->default(0);
            
            $table->timestamps();
            
            $table->unique(['surgery_id', 'user_id', 'role']);
            $table->index(['surgery_id', 'role']);
            $table->index(['user_id', 'assigned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgical_teams');
    }
};
