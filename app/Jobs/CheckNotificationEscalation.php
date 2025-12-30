<?php

namespace App\Jobs;

use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckNotificationEscalation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationId;

    /**
     * Create a new job instance.
     */
    public function __construct($notificationId)
    {
        $this->notificationId = $notificationId;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $notification = Notification::find($this->notificationId);
            
            if (!$notification) {
                Log::warning('الإشعار المطلوب فحص تصعيده غير موجود', ['notification_id' => $this->notificationId]);
                return;
            }

            // التحقق من ضرورة التصعيد
            if ($notification->shouldEscalate()) {
                Log::info('بدء تصعيد الإشعار', [
                    'notification_id' => $this->notificationId,
                    'current_level' => $notification->escalation_level,
                    'type' => $notification->type,
                    'priority' => $notification->priority
                ]);

                $notification->escalate();

                Log::info('تم تصعيد الإشعار بنجاح', [
                    'notification_id' => $this->notificationId,
                    'new_level' => $notification->escalation_level,
                    'escalated_to' => $notification->escalated_to
                ]);

                // جدولة فحص التصعيد التالي إذا لم نصل للحد الأقصى
                if ($notification->escalation_level < 3) {
                    $this->scheduleNextEscalationCheck($notification);
                }
            } else {
                Log::info('الإشعار لا يحتاج تصعيد', [
                    'notification_id' => $this->notificationId,
                    'read_at' => $notification->read_at,
                    'sent_at' => $notification->sent_at
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في فحص تصعيد الإشعار: ' . $e->getMessage(), [
                'notification_id' => $this->notificationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * جدولة فحص التصعيد التالي
     */
    protected function scheduleNextEscalationCheck($notification)
    {
        $delay = $this->getEscalationDelay($notification);
        
        static::dispatch($notification->id)
            ->delay(now()->addMinutes($delay));

        Log::info('تم جدولة فحص التصعيد التالي', [
            'notification_id' => $notification->id,
            'delay_minutes' => $delay,
            'next_check_at' => now()->addMinutes($delay)->toDateTimeString()
        ]);
    }

    /**
     * تحديد تأخير التصعيد التالي
     */
    protected function getEscalationDelay($notification)
    {
        // تأخير متزايد بناءً على مستوى التصعيد
        $baseDelay = match($notification->type) {
            'medical_critical' => 5,  // 5 دقائق
            'lab_critical' => 15,     // 15 دقيقة
            'radiology_urgent' => 30, // 30 دقيقة
            default => 60             // ساعة واحدة
        };

        // مضاعفة التأخير مع كل مستوى تصعيد
        return $baseDelay * pow(2, $notification->escalation_level);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('فشل نهائي في فحص تصعيد الإشعار', [
            'notification_id' => $this->notificationId,
            'error' => $exception->getMessage()
        ]);
    }
}