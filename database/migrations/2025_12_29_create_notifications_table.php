<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('general');
            $table->enum('priority', ['low', 'normal', 'high', 'critical'])->default('normal');
            $table->json('channels')->nullable();
            $table->string('recipient_type');
            $table->unsignedBigInteger('recipient_id');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->string('status')->default('pending');
            $table->json('delivery_status')->nullable();
            $table->integer('retry_count')->default(0);
            $table->integer('max_retries')->default(3);
            $table->integer('escalation_level')->default(0);
            $table->timestamp('escalated_at')->nullable();
            $table->unsignedBigInteger('escalated_to')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['recipient_type', 'recipient_id']);
            $table->index(['type', 'priority']);
            $table->index(['status', 'scheduled_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
};