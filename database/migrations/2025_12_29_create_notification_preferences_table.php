<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->string('user_type');
            $table->unsignedBigInteger('user_id');
            $table->string('notification_type');
            $table->json('channels')->nullable();
            $table->boolean('enabled')->default(true);
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->string('timezone')->default('Asia/Riyadh');
            $table->string('language')->default('ar');
            $table->enum('frequency', ['immediate', 'hourly', 'daily', 'weekly'])->default('immediate');
            $table->boolean('escalation_enabled')->default(false);
            $table->integer('escalation_delay_minutes')->default(60);
            $table->timestamps();

            $table->index(['user_type', 'user_id']);
            $table->index(['notification_type', 'enabled']);
            $table->unique(['user_type', 'user_id', 'notification_type'], 'notification_prefs_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_preferences');
    }
};