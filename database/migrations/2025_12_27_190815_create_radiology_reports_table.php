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
        Schema::create('radiology_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('radiology_order_id')->constrained('radiology_orders')->onDelete('cascade');
            $table->text('technique')->nullable()->comment('Imaging technique used');
            $table->text('findings')->comment('Radiological findings');
            $table->text('impression')->comment('Radiologist impression/conclusion');
            $table->text('recommendations')->nullable()->comment('Recommendations for follow-up');
            $table->enum('urgency_level', ['routine', 'urgent', 'critical'])->default('routine');
            $table->boolean('has_urgent_findings')->default(false)->comment('Contains urgent findings');
            $table->text('urgent_findings')->nullable()->comment('Description of urgent findings');
            $table->foreignId('radiologist_id')->constrained('users')->comment('Radiologist who wrote the report');
            $table->foreignId('verified_by')->nullable()->constrained('users')->comment('Senior radiologist who verified');
            $table->datetime('dictated_at')->nullable()->comment('When report was dictated');
            $table->datetime('transcribed_at')->nullable()->comment('When report was transcribed');
            $table->datetime('verified_at')->nullable()->comment('When report was verified');
            $table->datetime('finalized_at')->nullable()->comment('When report was finalized');
            $table->json('dicom_files')->nullable()->comment('DICOM file references');
            $table->json('image_attachments')->nullable()->comment('Additional image attachments');
            $table->text('addendum')->nullable()->comment('Report addendum if needed');
            $table->timestamps();
            
            $table->index(['radiology_order_id']);
            $table->index(['radiologist_id']);
            $table->index(['urgency_level']);
            $table->index(['has_urgent_findings']);
            $table->index(['finalized_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('radiology_reports');
    }
};
