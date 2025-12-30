<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('user_type');
            $table->unsignedBigInteger('user_id');
            $table->string('token')->unique();
            $table->enum('platform', ['android', 'ios', 'web'])->default('web');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index(['user_type', 'user_id']);
            $table->index(['platform', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('device_tokens');
    }
};