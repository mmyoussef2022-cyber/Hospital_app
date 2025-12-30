<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chatbot_settings', function (Blueprint $table) {
            $table->id();
            
            // Basic Settings
            $table->boolean('chatbot_enabled')->default(true);
            $table->string('chatbot_name')->default('مساعد المركز الطبي');
            $table->text('welcome_message')->nullable();
            $table->enum('chatbot_position', ['bottom-right', 'bottom-left', 'top-right', 'top-left'])->default('bottom-right');
            $table->enum('chatbot_theme', ['blue', 'green', 'red', 'purple', 'orange'])->default('blue');
            
            // Response Settings
            $table->boolean('auto_responses_enabled')->default(true);
            $table->json('quick_replies')->nullable(); // Array of quick reply buttons
            
            // Integration Settings
            $table->boolean('whatsapp_integration')->default(false);
            $table->text('whatsapp_redirect_message')->nullable();
            $table->boolean('booking_integration')->default(false);
            $table->text('booking_redirect_message')->nullable();
            $table->boolean('phone_integration')->default(false);
            $table->text('phone_redirect_message')->nullable();
            
            // User Info Collection
            $table->boolean('collect_user_info')->default(false);
            $table->string('name_collection_message')->nullable();
            $table->string('phone_collection_message')->nullable();
            
            // Advanced Features
            $table->boolean('ai_enabled')->default(false);
            $table->boolean('analytics_enabled')->default(true);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chatbot_settings');
    }
};
