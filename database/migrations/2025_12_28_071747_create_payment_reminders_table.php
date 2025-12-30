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
        Schema::create('payment_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->enum('reminder_type', ['gentle', 'standard', 'urgent', 'final_notice', 'collection']);
            $table->enum('reminder_method', ['sms', 'email', 'whatsapp', 'phone_call', 'letter']);
            $table->datetime('scheduled_date');
            $table->datetime('sent_date')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed', 'responded'])->default('pending');
            $table->text('message');
            $table->boolean('response_received')->default(false);
            $table->datetime('response_date')->nullable();
            $table->json('response_details')->nullable();
            $table->integer('escalation_level')->default(1);
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('sent_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['status', 'scheduled_date']);
            $table->index(['invoice_id', 'reminder_type']);
            $table->index(['patient_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_reminders');
    }
};
