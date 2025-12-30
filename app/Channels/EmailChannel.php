<?php

namespace App\Channels;

use App\Models\Notification;
use App\Models\User;
use App\Models\Patient;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailChannel
{
    /**
     * إرسال إشعار عبر البريد الإلكتروني
     */
    public function send(Notification $notification, $recipient)
    {
        try {
            // التحقق من وجود بريد إلكتروني
            $email = $this->getRecipientEmail($recipient);
            if (!$email) {
                return [
                    'status' => 'failed',
                    'error' => 'البريد الإلكتروني غير متوفر'
                ];
            }

            // إرسال البريد الإلكتروني
            $result = $this->sendEmail($email, $notification, $recipient);

            if ($result) {
                return [
                    'status' => 'sent',
                    'sent_at' => now()->toISOString()
                ];
            } else {
                return [
                    'status' => 'failed',
                    'error' => 'فشل في إرسال البريد الإلكتروني'
                ];
            }

        } catch (Exception $e) {
            Log::error('فشل إرسال البريد الإلكتروني: ' . $e->getMessage(), [
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
     * الحصول على بريد المستلم الإلكتروني
     */
    protected function getRecipientEmail($recipient)
    {
        if ($recipient instanceof Patient) {
            return $recipient->email;
        } elseif ($recipient instanceof User) {
            return $recipient->email;
        }

        return null;
    }

    /**
     * إرسال البريد الإلكتروني
     */
    protected function sendEmail($email, $notification, $recipient)
    {
        try {
            $template = $this->getTemplate($notification->type);
            
            Mail::send($template, [
                'notification' => $notification,
                'recipient' => $recipient,
                'data' => $notification->data
            ], function ($message) use ($email, $notification) {
                $message->to($email)
                        ->subject($notification->title)
                        ->from(
                            config('services.email.from_address'),
                            config('services.email.from_name')
                        );
            });

            return true;

        } catch (Exception $e) {
            Log::error('فشل إرسال البريد الإلكتروني: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * الحصول على قالب البريد الإلكتروني
     */
    protected function getTemplate($notificationType)
    {
        $templates = [
            'appointment_reminder' => 'emails.notifications.appointment-reminder',
            'appointment_confirmation' => 'emails.notifications.appointment-confirmation',
            'lab_result_ready' => 'emails.notifications.lab-result-ready',
            'lab_critical' => 'emails.notifications.lab-critical',
            'radiology_result_ready' => 'emails.notifications.radiology-result-ready',
            'radiology_urgent' => 'emails.notifications.radiology-urgent',
            'payment_reminder' => 'emails.notifications.payment-reminder',
            'payment_overdue' => 'emails.notifications.payment-overdue',
            'medical_critical' => 'emails.notifications.medical-critical',
            'system_alert' => 'emails.notifications.system-alert',
            'escalation' => 'emails.notifications.escalation',
            'general' => 'emails.notifications.general'
        ];

        return $templates[$notificationType] ?? $templates['general'];
    }
}