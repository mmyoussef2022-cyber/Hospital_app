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
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('medical_record_id')->nullable()->constrained()->onDelete('set null');
            $table->json('test_ids')->nullable();
            $table->enum('priority', ['routine', 'urgent', 'stat'])->default('routine');
            $table->text('clinical_notes')->nullable();
            $table->boolean('fasting_required')->default(false);
            $table->datetime('collection_date')->nullable();
            $table->datetime('order_date');
            $table->datetime('completed_at')->nullable();
            $table->enum('status', ['ordered', 'collected', 'completed', 'cancelled'])->default('ordered');
            $table->text('results')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->datetime('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('review_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('labs');
    }
};
