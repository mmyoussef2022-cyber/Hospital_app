<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'user_id',
        'notification_type',
        'channels',
        'enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'timezone',
        'language',
        'frequency',
        'escalation_enabled',
        'escalation_delay_minutes'
    ];

    protected $casts = [
        'channels' => 'array',
        'enabled' => 'boolean',
        'escalation_enabled' => 'boolean',
        'quiet_hours_start' => 'datetime:H:i',
        'quiet_hours_end' => 'datetime:H:i'
    ];

    // العلاقات
    public function user()
    {
        return $this->morphTo();
    }

    // النطاقات
    public function scopeForUser($query, $userType, $userId)
    {
        return $query->where('user_type', $userType)
                    ->where('user_id', $userId);
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeForNotificationType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    // الطرق المساعدة
    public function isInQuietHours()
    {
        if (!$this->quiet_hours_start || !$this->quiet_hours_end) {
            return false;
        }

        $now = now($this->timezone ?? config('app.timezone'));
        $currentTime = $now->format('H:i');
        
        $start = $this->quiet_hours_start->format('H:i');
        $end = $this->quiet_hours_end->format('H:i');

        if ($start <= $end) {
            return $currentTime >= $start && $currentTime <= $end;
        } else {
            // عبر منتصف الليل
            return $currentTime >= $start || $currentTime <= $end;
        }
    }

    public function shouldReceiveNotification($notificationType, $priority = 'normal')
    {
        if (!$this->enabled) {
            return false;
        }

        if ($this->notification_type !== $notificationType && $this->notification_type !== 'all') {
            return false;
        }

        // الإشعارات الحرجة تتجاهل الساعات الهادئة
        if ($priority === 'critical') {
            return true;
        }

        return !$this->isInQuietHours();
    }

    public function getEnabledChannels($priority = 'normal')
    {
        $channels = $this->channels ?? [];

        // تحديد القنوات بناءً على الأولوية
        switch ($priority) {
            case 'critical':
                return array_intersect($channels, ['whatsapp', 'sms', 'push', 'in_app']);
            case 'high':
                return array_intersect($channels, ['whatsapp', 'push', 'in_app']);
            case 'normal':
                return array_intersect($channels, ['in_app', 'email']);
            default:
                return array_intersect($channels, ['in_app']);
        }
    }

    // الطرق الثابتة
    public static function getDefaultPreferences($userType, $userId)
    {
        return [
            [
                'user_type' => $userType,
                'user_id' => $userId,
                'notification_type' => 'appointment_reminder',
                'channels' => ['whatsapp', 'in_app'],
                'enabled' => true,
                'escalation_enabled' => false
            ],
            [
                'user_type' => $userType,
                'user_id' => $userId,
                'notification_type' => 'lab_result_ready',
                'channels' => ['whatsapp', 'in_app'],
                'enabled' => true,
                'escalation_enabled' => false
            ],
            [
                'user_type' => $userType,
                'user_id' => $userId,
                'notification_type' => 'lab_critical',
                'channels' => ['whatsapp', 'sms', 'push', 'in_app'],
                'enabled' => true,
                'escalation_enabled' => true,
                'escalation_delay_minutes' => 5
            ],
            [
                'user_type' => $userType,
                'user_id' => $userId,
                'notification_type' => 'payment_reminder',
                'channels' => ['whatsapp', 'in_app'],
                'enabled' => true,
                'escalation_enabled' => false
            ]
        ];
    }

    public static function createDefaultPreferences($userType, $userId)
    {
        $defaults = static::getDefaultPreferences($userType, $userId);
        
        foreach ($defaults as $preference) {
            static::updateOrCreate(
                [
                    'user_type' => $preference['user_type'],
                    'user_id' => $preference['user_id'],
                    'notification_type' => $preference['notification_type']
                ],
                $preference
            );
        }
    }

    // الثوابت
    const NOTIFICATION_TYPES = [
        'all' => 'جميع الإشعارات',
        'appointment_reminder' => 'تذكير المواعيد',
        'appointment_confirmation' => 'تأكيد المواعيد',
        'lab_result_ready' => 'نتائج المختبر',
        'lab_critical' => 'نتائج مختبر حرجة',
        'radiology_result_ready' => 'نتائج الأشعة',
        'radiology_urgent' => 'نتائج أشعة عاجلة',
        'payment_reminder' => 'تذكير الدفع',
        'payment_overdue' => 'دفعات متأخرة',
        'medical_critical' => 'حالات طبية حرجة',
        'system_alert' => 'تنبيهات النظام'
    ];

    const FREQUENCIES = [
        'immediate' => 'فوري',
        'hourly' => 'كل ساعة',
        'daily' => 'يومي',
        'weekly' => 'أسبوعي'
    ];
}