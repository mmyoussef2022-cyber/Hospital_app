<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chatbot_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->string('visitor_name')->nullable();
            $table->string('visitor_phone')->nullable();
            $table->string('visitor_email')->nullable();
            $table->json('messages'); // Array of conversation messages
            $table->enum('status', ['active', 'completed', 'transferred'])->default('active');
            $table->string('transfer_type')->nullable(); // whatsapp, phone, booking
            $table->timestamp('last_activity');
            $table->timestamps();
            
            $table->index('session_id');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chatbot_conversations');
    }
};
