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
        Schema::create('medical_record_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->onDelete('cascade');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_name_ar')->nullable(); // Arabic file name
            $table->string('original_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->integer('file_size'); // in bytes
            $table->string('mime_type');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable(); // Arabic description
            $table->enum('category', [
                'lab_result', 'xray', 'scan', 'report', 'image', 'document', 'other'
            ])->default('document');
            $table->boolean('is_confidential')->default(false);
            $table->json('metadata')->nullable(); // Additional file metadata
            $table->timestamps();
            
            // Indexes
            $table->index(['medical_record_id', 'category']);
            $table->index(['uploaded_by', 'created_at']);
            $table->index(['file_type', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_record_attachments');
    }
};
