<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Models\Patient;
use App\Channels\WhatsAppChannel;
use App\Channels\SMSChannel;
use App\Channels\PushNotificationChannel;
use App\Channels\EmailChannel;
use App\Channels\InAppChannel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Exception;

class NotificationService
{
    protected $channels = [
        'whatsapp' => WhatsAppChannel::class,
        'sms' => SMSChannel::class,
        'push' => PushNotificationChannel::class,
        'email' => EmailChannel::class,
        'in_app' => InAppChannel::class
    ];

    /**
     * إرسال إشعار
     */
    public function send($data)
    {
        try {
            // إنشاء الإشعار في قاعدة البيانات
            $notification = $this->createNotification($data);

            // تحديد المستلمين
            $recipients = $this->determineRecipients($data);

            // إرسال الإشعار لكل مستلم
            foreach ($recipients as $recipient) {
                $this->sendToRecipient($notification, $recipient);
            }

            return $notification;

        } catch (Exception $e) {
            Log::error('فشل في إرسال الإشعار: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * إرسال إشعار سريع
     */
    public function sendQuick($title, $message, $recipients, $type = 'general', $priority = 'normal')
    {
        return $this->send([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'priority' => $priority,
            'recipients' => $recipients
        ]);
    }

    /**
     * إرسال إشعار حرج
     */
    public function sendCritical($title, $message, $recipients, $type = 'medical_critical')
    {
        return $this->send([
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'priority' => 'critical',
            'recipients' => $recipients,
            'channels' => ['whatsapp', 'sms', 'push', 'in_app']
        ]);
    }

    /**
     * إرسال تذكير موعد
     */
    public function sendAppointmentReminder($appointment)
    {
        $patient = $appointment->patient;
        $doctor = $appointment->doctor;

        return $this->send([
            'title' => 'تذكير بموعد طبي',
            'message' => "لديك موعد مع د. {$doctor->name} غداً في {$appointment->appointment_time->format('H:i')}",
            'type' => 'appointment_reminder',
            'priority' => 'normal',
            'recipients' => [$patient],
            'reference_type' => get_class($appointment),
            'reference_id' => $appointment->id,
            'data' => [
                'appointment_id' => $appointment->id,
                'doctor_name' => $doctor->name,
                'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                'appointment_time' => $appointment->appointment_time->format('H:i'),
                'department' => $doctor->department
            ]
        ]);
    }

    /**
     * إرسال إشعار نتيجة مختبر حرجة
     */
    public function sendCriticalLabResult($labResult)
    {
        $patient = $labResult->labOrder->patient;
        $doctor = $labResult->labOrder->doctor;

        return $this->sendCritical(
            'نتيجة مختبر حرجة',
            "نتيجة مختبر حرجة للمريض {$patient->name}. يرجى المراجعة فوراً.",
            [$doctor],
            'lab_critical'
        );
    }

    /**
     * إرسال إشعار نتيجة أشعة عاجلة
     */
    public function sendUrgentRadiologyResult($radiologyResult)
    {
        $patient = $radiologyResult->radiologyOrder->patient;
        $doctor = $radiologyResult->radiologyOrder->doctor;

        return $this->send([
            'title' => 'نتيجة أشعة عاجلة',
            'message' => "نتيجة أشعة عاجلة للمريض {$patient->name}. يرجى المراجعة.",
            'type' => 'radiology_urgent',
            'priority' => 'high',
            'recipients' => [$doctor],
            'reference_type' => get_class($radiologyResult),
            'reference_id' => $radiologyResult->id
        ]);
    }

    /**
     * إرسال تذكير دفع
     */
    public function sendPaymentReminder($invoice)
    {
        $patient = $invoice->patient;
        $amount = number_format($invoice->remaining_amount, 2);

        return $this->send([
            'title' => 'تذكير بدفعة مستحقة',
            'message' => "لديك دفعة مستحقة بقيمة {$amount} ريال. يرجى المراجعة لتسوية الحساب.",
            'type' => 'payment_reminder',
            'priority' => 'normal',
            'recipients' => [$patient],
            'reference_type' => get_class($invoice),
            'reference_id' => $invoice->id,
            'data' => [
                'invoice_id' => $invoice->id,
                'amount' => $invoice->remaining_amount,
                'due_date' => $invoice->due_date->format('Y-m-d')
            ]
        ]);
    }

    /**
     * جدولة إشعار
     */
    public function schedule($data, $scheduledAt)
    {
        $data['scheduled_at'] = $scheduledAt;
        $notification = $this->createNotification($data);

        // جدولة المهمة
        Queue::later($scheduledAt, new \App\Jobs\SendScheduledNotification($notification->id));

        return $notification;
    }

    /**
     * إنشاء الإشعار في قاعدة البيانات
     */
    protected function createNotification($data)
    {
        return Notification::create([
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? 'general',
            'priority' => $data['priority'] ?? 'normal',
            'channels' => $data['channels'] ?? $this->determineChannels($data['priority'] ?? 'normal'),
            'sender_id' => $data['sender_id'] ?? auth()->id(),
            'data' => $data['data'] ?? [],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'max_retries' => $data['max_retries'] ?? 3,
            'status' => 'pending'
        ]);
    }

    /**
     * تحديد المستلمين
     */
    protected function determineRecipients($data)
    {
        if (isset($data['recipients'])) {
            return is_array($data['recipients']) ? $data['recipients'] : [$data['recipients']];
        }

        if (isset($data['recipient_type']) && isset($data['recipient_id'])) {
            $model = $data['recipient_type'];
            return [$model::find($data['recipient_id'])];
        }

        return [];
    }

    /**
     * إرسال الإشعار لمستلم واحد
     */
    protected function sendToRecipient($notification, $recipient)
    {
        // إنشاء نسخة للمستلم
        $recipientNotification = $notification->replicate();
        $recipientNotification->recipient_type = get_class($recipient);
        $recipientNotification->recipient_id = $recipient->id;
        $recipientNotification->save();

        // الحصول على تفضيلات المستلم
        $preferences = $this->getRecipientPreferences($recipient, $notification->type);

        // التحقق من إمكانية الإرسال
        if (!$preferences->shouldReceiveNotification($notification->type, $notification->priority)) {
            $recipientNotification->update(['status' => 'cancelled']);
            return;
        }

        // تحديد القنوات المناسبة
        $channels = $preferences->getEnabledChannels($notification->priority);
        $recipientNotification->update(['channels' => $channels]);

        // إرسال عبر القنوات
        $this->sendViaChannels($recipientNotification, $recipient, $channels);
    }

    /**
     * الحصول على تفضيلات المستلم
     */
    protected function getRecipientPreferences($recipient, $notificationType)
    {
        $preference = NotificationPreference::forUser(get_class($recipient), $recipient->id)
            ->forNotificationType($notificationType)
            ->enabled()
            ->first();

        if (!$preference) {
            // إنشاء تفضيلات افتراضية
            NotificationPreference::createDefaultPreferences(get_class($recipient), $recipient->id);
            
            $preference = NotificationPreference::forUser(get_class($recipient), $recipient->id)
                ->forNotificationType($notificationType)
                ->first();
        }

        return $preference;
    }

    /**
     * إرسال عبر القنوات المختلفة
     */
    protected function sendViaChannels($notification, $recipient, $channels)
    {
        $deliveryStatus = [];

        foreach ($channels as $channelName) {
            try {
                if (isset($this->channels[$channelName])) {
                    $channelClass = $this->channels[$channelName];
                    $channel = new $channelClass();
                    
                    $result = $channel->send($notification, $recipient);
                    $deliveryStatus[$channelName] = $result;
                    
                    Log::info("تم إرسال الإشعار عبر {$channelName}", [
                        'notification_id' => $notification->id,
                        'recipient_id' => $recipient->id,
                        'result' => $result
                    ]);
                }
            } catch (Exception $e) {
                $deliveryStatus[$channelName] = [
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ];
                
                Log::error("فشل إرسال الإشعار عبر {$channelName}", [
                    'notification_id' => $notification->id,
                    'recipient_id' => $recipient->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // تحديث حالة التسليم
        $notification->update([
            'delivery_status' => $deliveryStatus,
            'sent_at' => now(),
            'status' => $this->determineOverallStatus($deliveryStatus)
        ]);

        // جدولة التحقق من التصعيد إذا كان الإشعار حرجاً
        if ($notification->priority === 'critical') {
            $this->scheduleEscalationCheck($notification);
        }
    }

    /**
     * تحديد القنوات بناءً على الأولوية
     */
    protected function determineChannels($priority)
    {
        switch ($priority) {
            case 'critical':
                return ['whatsapp', 'sms', 'push', 'in_app'];
            case 'high':
                return ['whatsapp', 'push', 'in_app'];
            case 'normal':
                return ['in_app', 'email'];
            default:
                return ['in_app'];
        }
    }

    /**
     * تحديد الحالة العامة للإشعار
     */
    protected function determineOverallStatus($deliveryStatus)
    {
        $statuses = array_column($deliveryStatus, 'status');
        
        if (in_array('delivered', $statuses)) {
            return 'delivered';
        } elseif (in_array('sent', $statuses)) {
            return 'sent';
        } else {
            return 'failed';
        }
    }

    /**
     * جدولة فحص التصعيد
     */
    protected function scheduleEscalationCheck($notification)
    {
        $delay = match($notification->type) {
            'medical_critical' => 5,  // 5 دقائق
            'lab_critical' => 15,     // 15 دقيقة
            'radiology_urgent' => 30, // 30 دقيقة
            default => 60             // ساعة واحدة
        };

        Queue::later(
            now()->addMinutes($delay),
            new \App\Jobs\CheckNotificationEscalation($notification->id)
        );
    }

    /**
     * معالجة الإشعارات المجدولة
     */
    public function processScheduledNotifications()
    {
        $notifications = Notification::where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($notifications as $notification) {
            $this->processNotification($notification);
        }
    }

    /**
     * معالجة إشعار واحد
     */
    public function processNotification($notification)
    {
        $recipient = $notification->recipient;
        
        if (!$recipient) {
            $notification->markAsFailed('المستلم غير موجود');
            return;
        }

        $this->sendViaChannels(
            $notification,
            $recipient,
            $notification->channels ?? ['in_app']
        );
    }

    /**
     * فحص الإشعارات التي تحتاج تصعيد
     */
    public function checkEscalations()
    {
        $notifications = Notification::where('priority', 'critical')
            ->whereNotNull('sent_at')
            ->whereNull('read_at')
            ->where('escalation_level', '<', 3)
            ->get();

        foreach ($notifications as $notification) {
            if ($notification->shouldEscalate()) {
                $notification->escalate();
            }
        }
    }

    /**
     * إحصائيات الإشعارات
     */
    public function getStatistics($period = 'today')
    {
        $query = Notification::query();

        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month);
                break;
        }

        return [
            'total' => $query->count(),
            'sent' => $query->where('status', 'sent')->count(),
            'delivered' => $query->where('status', 'delivered')->count(),
            'failed' => $query->where('status', 'failed')->count(),
            'critical' => $query->where('priority', 'critical')->count(),
            'escalated' => $query->where('escalation_level', '>', 0)->count(),
            'by_type' => $query->groupBy('type')->selectRaw('type, count(*) as count')->pluck('count', 'type'),
            'by_channel' => $this->getChannelStatistics($query)
        ];
    }

    /**
     * إحصائيات القنوات
     */
    protected function getChannelStatistics($query)
    {
        $notifications = $query->whereNotNull('delivery_status')->get();
        $stats = [];

        foreach ($notifications as $notification) {
            foreach ($notification->delivery_status as $channel => $status) {
                if (!isset($stats[$channel])) {
                    $stats[$channel] = ['sent' => 0, 'delivered' => 0, 'failed' => 0];
                }
                $stats[$channel][$status['status']]++;
            }
        }

        return $stats;
    }
}