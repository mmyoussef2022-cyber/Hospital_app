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
        Schema::create('surgical_procedures', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // CPT or ICD-10-PCS code
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            
            $table->string('category'); // general, cardiac, orthopedic, neurosurgery, etc.
            $table->string('specialty'); // surgery specialization required
            $table->enum('complexity', ['minor', 'moderate', 'major', 'complex'])->default('moderate');
            $table->enum('urgency_level', ['elective', 'urgent', 'emergency'])->default('elective');
            
            $table->integer('estimated_duration')->default(60); // minutes
            $table->integer('min_duration')->default(30); // minimum time needed
            $table->integer('max_duration')->default(180); // maximum expected time
            
            $table->decimal('base_cost', 10, 2)->default(0);
            $table->decimal('surgeon_fee', 10, 2)->default(0);
            $table->decimal('anesthesia_fee', 10, 2)->default(0);
            $table->decimal('facility_fee', 10, 2)->default(0);
            
            $table->json('required_equipment')->nullable(); // list of required equipment
            $table->json('required_team_roles')->nullable(); // surgeon, anesthesiologist, nurses, etc.
            $table->json('pre_operative_requirements')->nullable(); // tests, preparations needed
            $table->json('post_operative_care')->nullable(); // recovery requirements
            
            $table->boolean('requires_icu')->default(false);
            $table->boolean('requires_blood_bank')->default(false);
            $table->boolean('requires_anesthesia')->default(true);
            $table->boolean('is_outpatient')->default(false);
            $table->boolean('is_active')->default(true);
            
            $table->text('contraindications')->nullable();
            $table->text('complications')->nullable();
            $table->text('recovery_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index(['specialty', 'complexity']);
            $table->index(['urgency_level', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surgical_procedures');
    }
};
