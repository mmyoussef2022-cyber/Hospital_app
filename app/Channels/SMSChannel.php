<?php

namespace App\Channels;

use App\Models\Notification;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class SMSChannel
{
    protected $apiUrl;
    protected $apiKey;
    protected $senderId;

    public function __construct()
    {
        $this->apiUrl = config('services.sms.api_url');
        $this->apiKey = config('services.sms.api_key');
        $this->senderId = config('services.sms.sender_id');
    }

    /**
     * إرسال إشعار عبر SMS
     */
    public function send(Notification $notification, $recipient)
    {
        try {
            // التحقق من وجود رقم الهاتف
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
            $response = $this->sendSMS($phoneNumber, $message);

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
            Log::error('فشل إرسال SMS: ' . $e->getMessage(), [
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
        $phone = preg_replace('/[^\d]/', '', $phone);

        // إضافة رمز الدولة إذا لم يكن موجوداً
        if (str_starts_with($phone, '05')) {
            $phone = '966' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '966')) {
            $phone = '966' . $phone;
        }

        return $phone;
    }

    /**
     * تنسيق الرسالة
     */
    protected function formatMessage(Notification $notification, $recipient)
    {
        // الحد الأقصى لطول رسالة SMS (160 حرف للرسالة الواحدة)
        $maxLength = 160;
        
        $message = $notification->title . "\n" . $notification->message;
        
        // إضافة معلومات إضافية حسب نوع الإشعار
        switch ($notification->type) {
            case 'appointment_reminder':
                if (isset($notification->data['appointment_time'])) {
                    $message .= "\nالوقت: " . $notification->data['appointment_time'];
                }
                break;
                
            case 'lab_critical':
            case 'radiology_urgent':
                $message .= "\nيرجى المراجعة فوراً";
                break;
                
            case 'payment_reminder':
                if (isset($notification->data['amount'])) {
                    $message .= "\nالمبلغ: " . number_format($notification->data['amount'], 2) . " ريال";
                }
                break;
        }

        // اقتطاع الرسالة إذا كانت طويلة
        if (strlen($message) > $maxLength) {
            $message = substr($message, 0, $maxLength - 3) . '...';
        }

        return $message;
    }

    /**
     * إرسال الرسالة عبر API
     */
    protected function sendSMS($phoneNumber, $message)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . '/send', [
                'from' => $this->senderId,
                'to' => $phoneNumber,
                'text' => $message,
                'encoding' => 'auto'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'message_id' => $data['message_id'] ?? $data['id'] ?? null,
                    'cost' => $data['cost'] ?? null
                ];
            } else {
                $error = $response->json();
                return [
                    'success' => false,
                    'error' => $error['message'] ?? $error['error'] ?? 'خطأ غير معروف'
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
     * إرسال رسالة مجمعة
     */
    public function sendBulk($phoneNumbers, $message)
    {
        try {
            $messages = [];
            foreach ($phoneNumbers as $number) {
                $messages[] = [
                    'to' => $this->formatPhoneNumber($number),
                    'text' => $message
                ];
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json'
            ])->post($this->apiUrl . '/send-bulk', [
                'from' => $this->senderId,
                'messages' => $messages
            ]);

            return $response->successful() ? $response->json() : null;

        } catch (Exception $e) {
            Log::error('فشل إرسال SMS مجمع: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * التحقق من حالة الرسالة
     */
    public function getMessageStatus($messageId)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($this->apiUrl . "/status/{$messageId}");

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (Exception $e) {
            Log::error('فشل التحقق من حالة SMS: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * الحصول على رصيد الحساب
     */
    public function getBalance()
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey
            ])->get($this->apiUrl . '/balance');

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (Exception $e) {
            Log::error('فشل الحصول على رصيد SMS: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * معالجة webhook للحالات
     */
    public function handleWebhook($payload)
    {
        try {
            if (isset($payload['message_id']) && isset($payload['status'])) {
                $this->updateMessageStatus($payload['message_id'], $payload['status']);
            }

            // معالجة الردود الواردة
            if (isset($payload['type']) && $payload['type'] === 'inbound') {
                $this->handleIncomingSMS($payload);
            }

        } catch (Exception $e) {
            Log::error('فشل معالجة webhook SMS: ' . $e->getMessage());
        }
    }

    /**
     * تحديث حالة الرسالة
     */
    protected function updateMessageStatus($messageId, $status)
    {
        // البحث عن الإشعار بناءً على message_id
        $notifications = Notification::whereJsonContains('delivery_status->sms->message_id', $messageId)->get();

        foreach ($notifications as $notification) {
            $deliveryStatus = $notification->delivery_status;
            $deliveryStatus['sms']['status'] = $status;
            $deliveryStatus['sms']['updated_at'] = now()->toISOString();

            $notification->update(['delivery_status' => $deliveryStatus]);

            // تحديث الحالة العامة للإشعار
            if ($status === 'delivered') {
                $notification->update(['status' => 'delivered']);
            } elseif ($status === 'failed') {
                $notification->incrementRetry();
            }
        }
    }

    /**
     * معالجة الرسائل الواردة
     */
    protected function handleIncomingSMS($payload)
    {
        $from = $payload['from'];
        $text = $payload['text'];

        Log::info('رسالة SMS واردة', [
            'from' => $from,
            'text' => $text
        ]);

        // يمكن إضافة منطق للرد التلقائي
        // مثل: "شكراً لردك. سيتم التواصل معك قريباً"
    }

    /**
     * إرسال رسالة تأكيد
     */
    public function sendConfirmation($phoneNumber, $code)
    {
        $message = "رمز التأكيد الخاص بك هو: {$code}\nلا تشارك هذا الرمز مع أحد.";
        
        return $this->sendSMS($phoneNumber, $message);
    }

    /**
     * إرسال تذكير موعد مختصر
     */
    public function sendAppointmentReminder($phoneNumber, $doctorName, $time)
    {
        $message = "تذكير: لديك موعد مع د.{$doctorName} غداً {$time}";
        
        return $this->sendSMS($phoneNumber, $message);
    }
}