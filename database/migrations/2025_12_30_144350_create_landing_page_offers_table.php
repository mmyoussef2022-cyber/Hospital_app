<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('landing_page_offers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('image')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed', 'free'])->default('percentage');
            $table->decimal('discount_value', 8, 2)->nullable();
            $table->string('discount_badge_text')->nullable();
            $table->datetime('valid_from');
            $table->datetime('valid_until');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('cta_text')->default('احجز الآن');
            $table->string('cta_url')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('current_uses')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('landing_page_offers');
    }
};
