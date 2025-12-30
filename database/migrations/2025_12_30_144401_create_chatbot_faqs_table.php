<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chatbot_faqs', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->json('keywords')->nullable(); // Array of keywords for matching
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('action_buttons')->nullable(); // Array of action buttons
            $table->integer('sort_order')->default(0);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chatbot_faqs');
    }
};
