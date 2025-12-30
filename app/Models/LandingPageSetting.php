<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LandingPageSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'hospital_name',
        'hospital_logo',
        'hospital_tagline',
        'hospital_description',
        'hero_section_enabled',
        'hero_title',
        'hero_subtitle',
        'hero_background_image',
        'hero_cta_primary_text',
        'hero_cta_secondary_text',
        'about_section_enabled',
        'about_title',
        'about_content',
        'about_images',
        'services_section_enabled',
        'services_title',
        'services_subtitle',
        'doctors_section_enabled',
        'doctors_title',
        'doctors_subtitle',
        'featured_doctors_count',
        'offers_section_enabled',
        'offers_title',
        'offers_subtitle',
        'schedule_section_enabled',
        'schedule_title',
        'schedule_subtitle',
        'location_section_enabled',
        'location_title',
        'address_text',
        'latitude',
        'longitude',
        'map_provider',
        'phone_primary',
        'phone_emergency',
        'whatsapp_number',
        'email_primary',
        'email_appointments',
        'working_hours',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'youtube_url',
        'linkedin_url',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'primary_color',
        'secondary_color',
        'accent_color',
    ];

    protected $casts = [
        'hero_section_enabled' => 'boolean',
        'about_section_enabled' => 'boolean',
        'services_section_enabled' => 'boolean',
        'doctors_section_enabled' => 'boolean',
        'offers_section_enabled' => 'boolean',
        'schedule_section_enabled' => 'boolean',
        'location_section_enabled' => 'boolean',
        'featured_doctors_count' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'about_images' => 'array',
        'working_hours' => 'array',
    ];

    /**
     * Get the cached settings
     */
    public static function getCached()
    {
        return Cache::remember('landing_page_settings', 3600, function () {
            return self::first() ?? new self();
        });
    }

    /**
     * Get the singleton instance of landing page settings
     */
    public static function getInstance()
    {
        return self::getCached();
    }

    /**
     * Clear the cache when settings are updated
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('landing_page_settings');
        });

        static::deleted(function () {
            Cache::forget('landing_page_settings');
        });
    }
}
