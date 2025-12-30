<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ChatbotSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'chatbot_enabled',
        'chatbot_name',
        'welcome_message',
        'chatbot_position',
        'chatbot_theme',
        'auto_responses_enabled',
        'quick_replies',
        'whatsapp_integration',
        'whatsapp_redirect_message',
        'booking_integration',
        'booking_redirect_message',
        'phone_integration',
        'phone_redirect_message',
        'collect_user_info',
        'name_collection_message',
        'phone_collection_message',
        'ai_enabled',
        'analytics_enabled',
    ];

    protected $casts = [
        'chatbot_enabled' => 'boolean',
        'auto_responses_enabled' => 'boolean',
        'whatsapp_integration' => 'boolean',
        'booking_integration' => 'boolean',
        'phone_integration' => 'boolean',
        'collect_user_info' => 'boolean',
        'ai_enabled' => 'boolean',
        'analytics_enabled' => 'boolean',
        'quick_replies' => 'array',
    ];

    /**
     * Get the cached settings
     */
    public static function getCached()
    {
        return Cache::remember('chatbot_settings', 3600, function () {
            return self::first() ?? new self();
        });
    }

    /**
     * Clear the cache when settings are updated
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            Cache::forget('chatbot_settings');
        });

        static::deleted(function () {
            Cache::forget('chatbot_settings');
        });
    }
}
