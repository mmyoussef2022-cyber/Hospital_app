<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class SecurityAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $suspiciousUser;
    protected $attemptCount;

    public function __construct(User $suspiciousUser, int $attemptCount)
    {
        $this->suspiciousUser = $suspiciousUser;
        $this->attemptCount = $attemptCount;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('تنبيه أمني - محاولات وصول غير مصرح بها')
                    ->greeting('تحية طيبة ' . $notifiable->name)
                    ->line('تم رصد محاولات وصول غير مصرح بها في نظام إدارة المستشفى.')
                    ->line('تفاصيل التنبيه:')
                    ->line('المستخدم: ' . $this->suspiciousUser->name)
                    ->line('البريد الإلكتروني: ' . $this->suspiciousUser->email)
                    ->line('رقم الموظف: ' . $this->suspiciousUser->employee_id)
                    ->line('عدد المحاولات: ' . $this->attemptCount)
                    ->line('الوقت: ' . now()->format('Y-m-d H:i:s'))
                    ->line('يرجى مراجعة سجلات النظام واتخاذ الإجراءات اللازمة.')
                    ->action('مراجعة السجلات', url('/advanced-users/' . $this->suspiciousUser->id))
                    ->line('شكراً لاهتمامكم بأمان النظام.');
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'security_alert',
            'title' => 'تنبيه أمني - محاولات وصول غير مصرح بها',
            'message' => "تم رصد {$this->attemptCount} محاولات وصول غير مصرح بها من المستخدم {$this->suspiciousUser->name}",
            'user_id' => $this->suspiciousUser->id,
            'user_name' => $this->suspiciousUser->name,
            'user_email' => $this->suspiciousUser->email,
            'attempt_count' => $this->attemptCount,
            'timestamp' => now()->toDateTimeString(),
            'action_url' => url('/advanced-users/' . $this->suspiciousUser->id),
            'priority' => 'high',
            'icon' => 'fas fa-exclamation-triangle',
            'color' => 'danger'
        ];
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}