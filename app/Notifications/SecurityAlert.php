<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecurityAlert extends Notification implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $data;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $data)
    {
        $this->title = $title;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('تنبيه أمني: ' . $this->title)
            ->greeting('تنبيه أمني')
            ->line('تم اكتشاف نشاط أمني يتطلب انتباهكم.')
            ->line('**التفاصيل:**');

        foreach ($this->data as $key => $value) {
            $message->line("- **{$key}**: {$value}");
        }

        $message->line('يرجى مراجعة سجلات الأمان للحصول على مزيد من التفاصيل.')
            ->action('عرض سجلات الأمان', url('/security/logs'))
            ->line('هذا تنبيه تلقائي من نظام إدارة المستشفى.');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'data' => $this->data,
            'type' => 'security_alert',
            'severity' => 'high',
            'created_at' => now()->toISOString()
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->getFormattedMessage(),
            'data' => $this->data,
            'type' => 'security_alert',
            'severity' => 'high',
            'action_url' => url('/security/logs')
        ];
    }

    /**
     * Get formatted message for database storage
     */
    protected function getFormattedMessage(): string
    {
        $message = "تنبيه أمني: {$this->title}\n\nالتفاصيل:\n";
        
        foreach ($this->data as $key => $value) {
            $message .= "- {$key}: {$value}\n";
        }

        return $message;
    }
}