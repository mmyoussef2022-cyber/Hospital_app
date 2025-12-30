<?php

namespace App\Channels;

use App\Models\Notification;
use App\Models\User;
use App\Models\Patient;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PushNotificationChannel
{
    protected $fcmServerKey;
    protected $fcmUrl;

    public function __construct()
    {
        $this->fcmServerKey = config('services.fcm.server_key');
        $this->fcmUrl = 'https://fcm.googleapis.com/fcm/send';
    }

    /**
     * إرسال إشعار فوري
     */
    public function send(Notification $notification, $recipient)
    {
        try {
            // الحصول على رموز الأجهزة
            $deviceTokens = $this->getRecipientDeviceTokens($recipient);
            
            if (empty($deviceTokens)) {
                return [
                    'status' => 'failed',
                    'error' => 'لا توجد أجهزة مسجلة'
                ];
            }

            // تحضير البيانات
            $payload = $this->preparePayload($notification, $recipient);

            // إرسال للأجهزة
            $results = [];
            foreach ($deviceTokens as $token) {
                $result = $this->sendToDevice($token, $payload);
                $results[] = $result;
            }

            // تحليل النتائج
            $successCount = count(array_filter($results, fn($r) => $r['success']));
            $totalCount = count($results);

            if ($successCount > 0) {
                return [
                    'status' => 'sent',
                    'sent_count' => $successCount,
                    'total_count' => $totalCount,
                    'results' => $results,
                    'sent_at' => now()->toISOString()
                ];
            } else {
                return [
                    'status' => 'failed',
                    'error' => 'فشل الإرسال لجميع الأجهزة',
                    'results' => $results
                ];
            }

        } catch (Exception $e) {
            Log::error('فشل إرسال الإشعار الفوري: ' . $e->getMessage(), [
                'notification_id' => $notification->id,
                'recipient_id' => $recipient->id
            ]);

            return [
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * الحصول على رموز أجهزة المستلم
     */
    protected function getRecipientDeviceTokens($recipient)
    {
        return DeviceToken::where('user_type', get_class($recipient))
            ->where('user_id', $recipient->id)
            ->where('is_active', true)
            ->pluck('token')
            ->toArray();
    }

    /**
     * تحضير بيانات الإشعار
     */
    protected function preparePayload(Notification $notification, $recipient)
    {
        $data = [
            'notification_id' => $notification->id,
            'type' => $notification->type,
            'priority' => $notification->priority,
            'created_at' => $notification->created_at->toISOString()
        ];

        // إضافة البيانات المخصصة
        if ($notification->data) {
            $data = array_merge($data, $notification->data);
        }

        // تحديد الأيقونة والصوت حسب النوع
        $icon = $this->getNotificationIcon($notification->type);
        $sound = $this->getNotificationSound($notification->priority);
        $color = $this->getNotificationColor($notification->priority);

        return [
            'notification' => [
                'title' => $notification->title,
                'body' => $notification->message,
                'icon' => $icon,
                'sound' => $sound,
                'color' => $color,
                'click_action' => $this->getClickAction($notification),
                'tag' => $notification->type
            ],
            'data' => $data,
            'android' => [
                'priority' => $notification->priority === 'critical' ? 'high' : 'normal',
                'notification' => [
                    'channel_id' => $this->getChannelId($notification->type),
                    'importance' => $notification->priority === 'critical' ? 'high' : 'default',
                    'visibility' => 'public'
                ]
            ],
            'apns' => [
                'headers' => [
                    'apns-priority' => $notification->priority === 'critical' ? '10' : '5'
                ],
                'payload' => [
                    'aps' => [
                        'alert' => [
                            'title' => $notification->title,
                            'body' => $notification->message
                        ],
                        'badge' => $this->getUnreadCount($recipient),
                        'sound' => $sound,
                        'category' => $notification->type
                    ]
                ]
            ]
        ];
    }

    /**
     * إرسال لجهاز واحد
     */
    protected function sendToDevice($deviceToken, $payload)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->fcmServerKey,
                'Content-Type' => 'application/json'
            ])->post($this->fcmUrl, array_merge($payload, [
                'to' => $deviceToken
            ]));

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['success'] == 1) {
                    return [
                        'success' => true,
                        'message_id' => $data['results'][0]['message_id'] ?? null,
                        'device_token' => $deviceToken
                    ];
                } else {
                    $error = $data['results'][0]['error'] ?? 'خطأ غير معروف';
                    
                    // إزالة الرمز إذا كان غير صالح
                    if (in_array($error, ['NotRegistered', 'InvalidRegistration'])) {
                        $this->removeInvalidToken($deviceToken);
                    }
                    
                    return [
                        'success' => false,
                        'error' => $error,
                        'device_token' => $deviceToken
                    ];
                }
            } else {
                return [
                    'success' => false,
                    'error' => 'HTTP Error: ' . $response->status(),
                    'device_token' => $deviceToken
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'device_token' => $deviceToken
            ];
        }
    }

    /**
     * إرسال لمجموعة أجهزة
     */
    public function sendToMultiple($deviceTokens, $payload)
    {
        try {
            // تقسيم الرموز إلى مجموعات (FCM يدعم حتى 1000 رمز)
            $chunks = array_chunk($deviceTokens, 1000);
            $allResults = [];

            foreach ($chunks as $chunk) {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $this->fcmServerKey,
                    'Content-Type' => 'application/json'
                ])->post($this->fcmUrl, array_merge($payload, [
                    'registration_ids' => $chunk
                ]));

                if ($response->successful()) {
                    $data = $response->json();
                    $allResults[] = $data;
                    
                    // معالجة الرموز غير الصالحة
                    if (isset($data['results'])) {
                        foreach ($data['results'] as $index => $result) {
                            if (isset($result['error']) && 
                                in_array($result['error'], ['NotRegistered', 'InvalidRegistration'])) {
                                $this->removeInvalidToken($chunk[$index]);
                            }
                        }
                    }
                }
            }

            return $allResults;

        } catch (Exception $e) {
            Log::error('فشل الإرسال المجمع للإشعارات الفورية: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * إرسال لموضوع معين
     */
    public function sendToTopic($topic, $payload)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->fcmServerKey,
                'Content-Type' => 'application/json'
            ])->post($this->fcmUrl, array_merge($payload, [
                'to' => '/topics/' . $topic
            ]));

            return $response->successful() ? $response->json() : null;

        } catch (Exception $e) {
            Log::error('فشل الإرسال للموضوع: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * الحصول على أيقونة الإشعار
     */
    protected function getNotificationIcon($type)
    {
        $icons = [
            'appointment_reminder' => 'ic_calendar',
            'appointment_confirmation' => 'ic_check_circle',
            'lab_result_ready' => 'ic_lab',
            'lab_critical' => 'ic_warning',
            'radiology_result_ready' => 'ic_radiology',
            'radiology_urgent' => 'ic_urgent',
            'payment_reminder' => 'ic_payment',
            'payment_overdue' => 'ic_overdue',
            'medical_critical' => 'ic_medical_emergency',
            'system_alert' => 'ic_info',
            'escalation' => 'ic_escalation'
        ];

        return $icons[$type] ?? 'ic_notification';
    }

    /**
     * الحصول على صوت الإشعار
     */
    protected function getNotificationSound($priority)
    {
        switch ($priority) {
            case 'critical':
                return 'critical_alert.mp3';
            case 'high':
                return 'high_priority.mp3';
            case 'normal':
                return 'default';
            default:
                return 'default';
        }
    }

    /**
     * الحصول على لون الإشعار
     */
    protected function getNotificationColor($priority)
    {
        switch ($priority) {
            case 'critical':
                return '#FF0000'; // أحمر
            case 'high':
                return '#FF8C00'; // برتقالي
            case 'normal':
                return '#1877F2'; // أزرق فيسبوك
            default:
                return '#808080'; // رمادي
        }
    }

    /**
     * الحصول على إجراء النقر
     */
    protected function getClickAction($notification)
    {
        $actions = [
            'appointment_reminder' => 'OPEN_APPOINTMENTS',
            'appointment_confirmation' => 'OPEN_APPOINTMENTS',
            'lab_result_ready' => 'OPEN_LAB_RESULTS',
            'lab_critical' => 'OPEN_LAB_RESULTS',
            'radiology_result_ready' => 'OPEN_RADIOLOGY_RESULTS',
            'radiology_urgent' => 'OPEN_RADIOLOGY_RESULTS',
            'payment_reminder' => 'OPEN_PAYMENTS',
            'payment_overdue' => 'OPEN_PAYMENTS',
            'medical_critical' => 'OPEN_MEDICAL_RECORDS'
        ];

        return $actions[$notification->type] ?? 'OPEN_NOTIFICATIONS';
    }

    /**
     * الحصول على معرف القناة
     */
    protected function getChannelId($type)
    {
        $channels = [
            'appointment_reminder' => 'appointments',
            'appointment_confirmation' => 'appointments',
            'lab_result_ready' => 'lab_results',
            'lab_critical' => 'critical_alerts',
            'radiology_result_ready' => 'radiology_results',
            'radiology_urgent' => 'urgent_alerts',
            'payment_reminder' => 'payments',
            'payment_overdue' => 'payments',
            'medical_critical' => 'critical_alerts'
        ];

        return $channels[$type] ?? 'general';
    }

    /**
     * الحصول على عدد الإشعارات غير المقروءة
     */
    protected function getUnreadCount($recipient)
    {
        return Notification::where('recipient_type', get_class($recipient))
            ->where('recipient_id', $recipient->id)
            ->whereNull('read_at')
            ->count();
    }

    /**
     * إزالة رمز جهاز غير صالح
     */
    protected function removeInvalidToken($token)
    {
        DeviceToken::where('token', $token)->delete();
        Log::info('تم حذف رمز جهاز غير صالح: ' . $token);
    }

    /**
     * تسجيل رمز جهاز جديد
     */
    public function registerDeviceToken($userType, $userId, $token, $platform = 'android')
    {
        return DeviceToken::updateOrCreate(
            [
                'user_type' => $userType,
                'user_id' => $userId,
                'token' => $token
            ],
            [
                'platform' => $platform,
                'is_active' => true,
                'last_used_at' => now()
            ]
        );
    }

    /**
     * إلغاء تسجيل رمز جهاز
     */
    public function unregisterDeviceToken($token)
    {
        return DeviceToken::where('token', $token)->delete();
    }

    /**
     * الاشتراك في موضوع
     */
    public function subscribeToTopic($tokens, $topic)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->fcmServerKey,
                'Content-Type' => 'application/json'
            ])->post('https://iid.googleapis.com/iid/v1:batchAdd', [
                'to' => '/topics/' . $topic,
                'registration_tokens' => is_array($tokens) ? $tokens : [$tokens]
            ]);

            return $response->successful();

        } catch (Exception $e) {
            Log::error('فشل الاشتراك في الموضوع: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * إلغاء الاشتراك من موضوع
     */
    public function unsubscribeFromTopic($tokens, $topic)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $this->fcmServerKey,
                'Content-Type' => 'application/json'
            ])->post('https://iid.googleapis.com/iid/v1:batchRemove', [
                'to' => '/topics/' . $topic,
                'registration_tokens' => is_array($tokens) ? $tokens : [$tokens]
            ]);

            return $response->successful();

        } catch (Exception $e) {
            Log::error('فشل إلغاء الاشتراك من الموضوع: ' . $e->getMessage());
            return false;
        }
    }
}