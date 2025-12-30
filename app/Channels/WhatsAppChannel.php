<?php

namespace App\Channels;

use App\Models\Notification;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WhatsAppChannel
{
    protected $apiUrl;
    protected $apiToken;
    protected $fromNumber;

    public function __construct()
    {
        $this->apiUrl = config('services.whatsapp.api_url');
        $this->apiToken = config('services.whatsapp.api_token');
        $this->fromNumber = config('services.whatsapp.from_number');
    }

    /**
     * إرسال إشعار عبر واتساب
     */
    public function send(Notification $notification, $recipient)
    {
        try {
            // التحقق من وجود رقم واتساب
            $phoneNumber = $this->getRecipientPhone($recipient);
            if (!$phoneNumber) {
                return [
                    'status' => 'failed',
                    'error' => 'رقم الهاتف غير متوفر'
                ];
            }

            // تنسيق الرسالة
            $message = $this->formatMessage($notification, $recipient);

            // إرسال الرسالة
            $response = $this->sendMessage($phoneNumber, $message, $notification);

            if ($response['success']) {
                return [
                    'status' => 'sent',
                    'message_id' => $response['message_id'],
                    'sent_at' => now()->toISOString()
                ];
            } else {
                return [
                    'status' => 'failed',
                    'error' => $response['error']
                ];
            }

        } catch (Exception $e) {
            Log::error('فشل إرسال واتساب: ' . $e->getMessage(), [
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
     * الحصول على رقم هاتف المستلم
     */
    protected function getRecipientPhone($recipient)
    {
        if ($recipient instanceof Patient) {
            return $this->formatPhoneNumber($recipient->phone);
        } elseif ($recipient instanceof User) {
            return $this->formatPhoneNumber($recipient->phone);
        }

        return null;
    }

    /**
     * تنسيق رقم الهاتف
     */
    protected function formatPhoneNumber($phone)
    {
        if (!$phone) {
            return null;
        }

        // إزالة المسافات والرموز
        $phone = preg_replace('/[^\d+]/', '', $phone);

        // إضافة رمز الدولة إذا لم يكن موجوداً
        if (!str_starts_with($phone, '+')) {
            if (str_starts_with($phone, '966')) {
                $phone = '+' . $phone;
            } elseif (str_starts_with($phone, '05')) {
                $phone = '+966' . substr($phone, 1);
            } else {
                $phone = '+966' . $phone;
            }
        }

        return $phone;
    }

    /**
     * تنسيق الرسالة
     */
    protected function formatMessage(Notification $notification, $recipient)
    {
        $template = $this->getTemplate($notification->type);
        
        return view($template, [
            'notification' => $notification,
            'recipient' => $recipient,
            'data' => $notification->data
        ])->render();
    }

    /**
     * الحصول على قالب الرسالة
     */
    protected function getTemplate($notificationType)
    {
        $templates = [
            'appointment_reminder' => 'notifications.whatsapp.appointment-reminder',
            'appointment_confirmation' => 'notifications.whatsapp.appointment-confirmation',
            'lab_result_ready' => 'notifications.whatsapp.lab-result-ready',
            'lab_critical' => 'notifications.whatsapp.lab-critical',
            'radiology_result_ready' => 'notifications.whatsapp.radiology-result-ready',
            'radiology_urgent' => 'notifications.whatsapp.radiology-urgent',
            'payment_reminder' => 'notifications.whatsapp.payment-reminder',
            'payment_overdue' => 'notifications.whatsapp.payment-overdue',
            'medical_critical' => 'notifications.whatsapp.medical-critical',
            'system_alert' => 'notifications.whatsapp.system-alert',
            'escalation' => 'notifications.whatsapp.escalation',
            'general' => 'notifications.whatsapp.general'
        ];

        return $templates[$notificationType] ?? $templates['general'];
    }

    /**
     * إرسال الرسالة عبر API
     */
    protected function sendMessage($phoneNumber, $message, $notification)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . '/messages', [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => 'text',
                'text' => [
                    'body' => $message
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message_id' => $data['messages'][0]['id'] ?? null
                ];
            } else {
                return [
                    'success' => false,
                    'error' => $response->json()['error']['message'] ?? 'خطأ غير معروف'
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * إرسال رسالة بقالب
     */
    public function sendTemplate($phoneNumber, $templateName, $parameters = [])
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . '/messages', [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => 'template',
                'template' => [
                    'name' => $templateName,
                    'language' => [
                        'code' => 'ar'
                    ],
                    'components' => [
                        [
                            'type' => 'body',
                            'parameters' => $parameters
                        ]
                    ]
                ]
            ]);

            return $response->successful();

        } catch (Exception $e) {
            Log::error('فشل إرسال قالب واتساب: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * إرسال رسالة مع أزرار
     */
    public function sendInteractive($phoneNumber, $message, $buttons = [])
    {
        try {
            $interactive = [
                'type' => 'button',
                'body' => [
                    'text' => $message
                ],
                'action' => [
                    'buttons' => []
                ]
            ];

            foreach ($buttons as $index => $button) {
                $interactive['action']['buttons'][] = [
                    'type' => 'reply',
                    'reply' => [
                        'id' => $button['id'] ?? "btn_{$index}",
                        'title' => $button['title']
                    ]
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . '/messages', [
                'messaging_product' => 'whatsapp',
                'to' => $phoneNumber,
                'type' => 'interactive',
                'interactive' => $interactive
            ]);

            return $response->successful();

        } catch (Exception $e) {
            Log::error('فشل إرسال رسالة تفاعلية واتساب: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * التحقق من حالة الرسالة
     */
    public function getMessageStatus($messageId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken
            ])->get($this->apiUrl . "/messages/{$messageId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (Exception $e) {
            Log::error('فشل التحقق من حالة رسالة واتساب: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * معالجة webhook واتساب
     */
    public function handleWebhook($payload)
    {
        try {
            if (isset($payload['entry'][0]['changes'][0]['value']['statuses'])) {
                $statuses = $payload['entry'][0]['changes'][0]['value']['statuses'];
                
                foreach ($statuses as $status) {
                    $this->updateMessageStatus($status);
                }
            }

            if (isset($payload['entry'][0]['changes'][0]['value']['messages'])) {
                $messages = $payload['entry'][0]['changes'][0]['value']['messages'];
                
                foreach ($messages as $message) {
                    $this->handleIncomingMessage($message);
                }
            }

        } catch (Exception $e) {
            Log::error('فشل معالجة webhook واتساب: ' . $e->getMessage());
        }
    }

    /**
     * تحديث حالة الرسالة
     */
    protected function updateMessageStatus($status)
    {
        $messageId = $status['id'];
        $statusType = $status['status']; // sent, delivered, read, failed

        // البحث عن الإشعار بناءً على message_id
        $notifications = Notification::whereJsonContains('delivery_status->whatsapp->message_id', $messageId)->get();

        foreach ($notifications as $notification) {
            $deliveryStatus = $notification->delivery_status;
            $deliveryStatus['whatsapp']['status'] = $statusType;
            $deliveryStatus['whatsapp']['updated_at'] = now()->toISOString();

            $notification->update(['delivery_status' => $deliveryStatus]);

            // تحديث حالة القراءة إذا تم قراءة الرسالة
            if ($statusType === 'read' && !$notification->read_at) {
                $notification->markAsRead();
            }
        }
    }

    /**
     * معالجة الرسائل الواردة
     */
    protected function handleIncomingMessage($message)
    {
        $from = $message['from'];
        $text = $message['text']['body'] ?? '';

        // يمكن إضافة منطق للرد التلقائي أو معالجة الردود
        Log::info('رسالة واردة من واتساب', [
            'from' => $from,
            'text' => $text
        ]);
    }
}