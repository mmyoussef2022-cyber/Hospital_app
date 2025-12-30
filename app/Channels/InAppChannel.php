<?php

namespace App\Channels;

use App\Models\Notification;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;
use Exception;

class InAppChannel
{
    /**
     * إرسال إشعار داخل التطبيق
     */
    public function send(Notification $notification, $recipient)
    {
        try {
            // الإشعارات داخل التطبيق يتم حفظها في قاعدة البيانات فقط
            // وسيتم عرضها في واجهة المستخدم
            
            // يمكن إضافة منطق إضافي هنا مثل:
            // - إرسال إشعار فوري عبر WebSocket
            // - تحديث عداد الإشعارات في الجلسة
            // - إرسال إشعار للمتصفح

            $this->broadcastToUser($notification, $recipient);

            return [
                'status' => 'delivered',
                'delivered_at' => now()->toISOString()
            ];

        } catch (Exception $e) {
            Log::error('فشل إرسال الإشعار داخل التطبيق: ' . $e->getMessage(), [
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
     * بث الإشعار للمستخدم عبر WebSocket أو Server-Sent Events
     */
    protected function broadcastToUser($notification, $recipient)
    {
        try {
            // يمكن استخدام Laravel Broadcasting هنا
            // broadcast(new NotificationSent($notification, $recipient));
            
            // أو استخدام Pusher أو Socket.io
            // $this->pusher->trigger("user.{$recipient->id}", 'notification', [
            //     'id' => $notification->id,
            //     'title' => $notification->title,
            //     'message' => $notification->message,
            //     'type' => $notification->type,
            //     'priority' => $notification->priority
            // ]);

            Log::info('تم بث الإشعار للمستخدم', [
                'notification_id' => $notification->id,
                'recipient_id' => $recipient->id
            ]);

        } catch (Exception $e) {
            Log::warning('فشل بث الإشعار: ' . $e->getMessage());
        }
    }

    /**
     * إرسال إشعار متصفح (Browser Notification)
     */
    protected function sendBrowserNotification($notification, $recipient)
    {
        try {
            // يمكن استخدام Service Worker لإرسال إشعارات المتصفح
            // هذا يتطلب JavaScript في الواجهة الأمامية
            
            $payload = [
                'title' => $notification->title,
                'body' => $notification->message,
                'icon' => asset('images/notification-icon.png'),
                'badge' => asset('images/notification-badge.png'),
                'tag' => $notification->type,
                'data' => [
                    'notification_id' => $notification->id,
                    'url' => route('notifications.show', $notification->id)
                ]
            ];

            // حفظ البيانات في الجلسة أو الكاش لاستخدامها في JavaScript
            session()->push('browser_notifications', $payload);

        } catch (Exception $e) {
            Log::warning('فشل إعداد إشعار المتصفح: ' . $e->getMessage());
        }
    }
}