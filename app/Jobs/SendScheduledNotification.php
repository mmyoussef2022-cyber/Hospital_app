<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendScheduledNotification implements ShouldQueue
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
    public function handle(NotificationService $notificationService)
    {
        try {
            $notification = Notification::find($this->notificationId);
            
            if (!$notification) {
                Log::warning('الإشعار المجدول غير موجود', ['notification_id' => $this->notificationId]);
                return;
            }

            if ($notification->status !== 'pending') {
                Log::info('الإشعار المجدول تم إرساله مسبقاً أو ملغي', [
                    'notification_id' => $this->notificationId,
                    'status' => $notification->status
                ]);
                return;
            }

            // معالجة الإشعار المجدول
            $notificationService->processNotification($notification);

            Log::info('تم إرسال الإشعار المجدول بنجاح', [
                'notification_id' => $this->notificationId
            ]);

        } catch (\Exception $e) {
            Log::error('فشل في إرسال الإشعار المجدول: ' . $e->getMessage(), [
                'notification_id' => $this->notificationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // إعادة المحاولة أو تحديد الإشعار كفاشل
            if ($this->attempts() < 3) {
                $this->release(60); // إعادة المحاولة بعد دقيقة
            } else {
                $notification = Notification::find($this->notificationId);
                if ($notification) {
                    $notification->markAsFailed('فشل في الإرسال بعد 3 محاولات');
                }
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception)
    {
        Log::error('فشل نهائي في إرسال الإشعار المجدول', [
            'notification_id' => $this->notificationId,
            'error' => $exception->getMessage()
        ]);

        $notification = Notification::find($this->notificationId);
        if ($notification) {
            $notification->markAsFailed('فشل نهائي في الإرسال: ' . $exception->getMessage());
        }
    }
}