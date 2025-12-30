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
        Schema::create('medical_record_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // created, updated, viewed, deleted, etc.
            $table->string('action_ar')->nullable(); // Arabic action description
            $table->json('old_values')->nullable(); // Previous values before change
            $table->json('new_values')->nullable(); // New values after change
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->text('notes_ar')->nullable(); // Arabic notes
            $table->timestamp('performed_at');
            $table->timestamps();
            
            // Indexes
            $table->index(['medical_record_id', 'performed_at']);
            $table->index(['user_id', 'action']);
            $table->index(['action', 'performed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_record_audits');
    }
};
