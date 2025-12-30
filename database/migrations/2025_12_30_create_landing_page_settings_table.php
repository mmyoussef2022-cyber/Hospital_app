<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('landing_page_settings', function (Blueprint $table) {
            $table->id();
            
            // Hospital Basic Info
            $table->string('hospital_name')->default('مركز محمد يوسف لطب الأسنان');
            $table->string('hospital_logo')->nullable();
            $table->text('hospital_tagline')->default('رعاية طبية متميزة بأحدث التقنيات');
            $table->text('hospital_description')->nullable();
            
            // Hero Section
            $table->boolean('hero_section_enabled')->default(true);
            $table->string('hero_title')->default('مرحباً بكم في مركز محمد يوسف لطب الأسنان');
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_background_image')->nullable();
            $table->string('hero_cta_primary_text')->default('احجز موعدك الآن');
            $table->string('hero_cta_secondary_text')->default('تصفح الأطباء');
            
            // About Section
            $table->boolean('about_section_enabled')->default(true);
            $table->string('about_title')->default('نبذة عنا');
            $table->text('about_content')->nullable();
            $table->json('about_images')->nullable(); // Array of image paths
            
            // Services Section
            $table->boolean('services_section_enabled')->default(true);
            $table->string('services_title')->default('خدماتنا الطبية');
            $table->text('services_subtitle')->nullable();
            
            // Doctors Section
            $table->boolean('doctors_section_enabled')->default(true);
            $table->string('doctors_title')->default('أطباؤنا المتميزون');
            $table->text('doctors_subtitle')->nullable();
            $table->integer('featured_doctors_count')->default(6);
            
            // Offers Section
            $table->boolean('offers_section_enabled')->default(true);
            $table->string('offers_title')->default('العروض والخصومات');
            $table->text('offers_subtitle')->nullable();
            
            // Schedule Section
            $table->boolean('schedule_section_enabled')->default(true);
            $table->string('schedule_title')->default('مواعيد الأطباء');
            $table->text('schedule_subtitle')->nullable();
            
            // Location Section
            $table->boolean('location_section_enabled')->default(true);
            $table->string('location_title')->default('موقعنا');
            $table->text('address_text')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('map_provider')->default('google'); // google or openstreet
            
            // Contact Info
            $table->string('phone_primary')->nullable();
            $table->string('phone_emergency')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('email_primary')->nullable();
            $table->string('email_appointments')->nullable();
            
            // Working Hours
            $table->json('working_hours')->nullable(); // JSON structure for days and times
            
            // Social Media
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('linkedin_url')->nullable();
            
            // SEO Settings
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            
            // Theme Settings
            $table->string('primary_color')->default('#1877F2');
            $table->string('secondary_color')->default('#42A5F5');
            $table->string('accent_color')->default('#10B981');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('landing_page_settings');
    }
};