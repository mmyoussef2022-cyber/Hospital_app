<?php

namespace App\Listeners;

use App\Models\Appointment;
use App\Services\NotificationService;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class AppointmentEventListener
{
    protected $notificationService;
    protected $auditLogService;

    public function __construct(NotificationService $notificationService, AuditLogService $auditLogService)
    {
        $this->notificationService = $notificationService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * معالجة إنشاء موعد جديد
     */
    public function onAppointmentCreated($appointment)
    {
        try {
            Log::info('تم إنشاء موعد جديد', [
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'appointment_date' => $appointment->appointment_date
            ]);

            // تسجيل العملية في سجل المراجعة
            $this->auditLogService->log([
                'action' => 'appointment_created',
                'model' => 'Appointment',
                'model_id' => $appointment->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $appointment->patient->name,
                    'doctor_name' => $appointment->doctor->name,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                    'appointment_time' => $appointment->appointment_time->format('H:i')
                ]
            ]);

            // إرسال إشعار تأكيد للمريض
            $this->notificationService->send([
                'title' => 'تأكيد موعد طبي',
                'message' => "تم تأكيد موعدك مع د. {$appointment->doctor->name} في {$appointment->appointment_date->format('Y-m-d')} الساعة {$appointment->appointment_time->format('H:i')}",
                'type' => 'appointment_confirmation',
                'priority' => 'normal',
                'recipients' => [$appointment->patient],
                'reference_type' => get_class($appointment),
                'reference_id' => $appointment->id,
                'data' => [
                    'appointment_id' => $appointment->id,
                    'doctor_name' => $appointment->doctor->name,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                    'appointment_time' => $appointment->appointment_time->format('H:i'),
                    'department' => $appointment->doctor->department
                ]
            ]);

            // إرسال إشعار للطبيب
            $this->notificationService->send([
                'title' => 'موعد جديد',
                'message' => "لديك موعد جديد مع المريض {$appointment->patient->name} في {$appointment->appointment_date->format('Y-m-d')} الساعة {$appointment->appointment_time->format('H:i')}",
                'type' => 'appointment_confirmation',
                'priority' => 'normal',
                'recipients' => [$appointment->doctor->user],
                'reference_type' => get_class($appointment),
                'reference_id' => $appointment->id
            ]);

            // جدولة تذكير قبل الموعد بيوم
            $reminderTime = $appointment->appointment_date->copy()->subDay()->setTime(18, 0);
            if ($reminderTime->isFuture()) {
                $this->notificationService->schedule([
                    'title' => 'تذكير بموعد طبي',
                    'message' => "لديك موعد مع د. {$appointment->doctor->name} غداً في {$appointment->appointment_time->format('H:i')}",
                    'type' => 'appointment_reminder',
                    'priority' => 'normal',
                    'recipients' => [$appointment->patient],
                    'reference_type' => get_class($appointment),
                    'reference_id' => $appointment->id,
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'doctor_name' => $appointment->doctor->name,
                        'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                        'appointment_time' => $appointment->appointment_time->format('H:i'),
                        'department' => $appointment->doctor->department
                    ]
                ], $reminderTime);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة إنشاء الموعد: ' . $e->getMessage(), [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة تحديث موعد
     */
    public function onAppointmentUpdated($appointment)
    {
        try {
            Log::info('تم تحديث موعد', [
                'appointment_id' => $appointment->id,
                'changes' => $appointment->getChanges()
            ]);

            // تسجيل التحديث
            $this->auditLogService->log([
                'action' => 'appointment_updated',
                'model' => 'Appointment',
                'model_id' => $appointment->id,
                'user_id' => auth()->id(),
                'data' => [
                    'changes' => $appointment->getChanges(),
                    'patient_name' => $appointment->patient->name,
                    'doctor_name' => $appointment->doctor->name
                ]
            ]);

            // إرسال إشعار بالتحديث إذا تغير التاريخ أو الوقت
            if ($appointment->wasChanged(['appointment_date', 'appointment_time'])) {
                $this->notificationService->send([
                    'title' => 'تحديث موعد طبي',
                    'message' => "تم تحديث موعدك مع د. {$appointment->doctor->name}. الموعد الجديد: {$appointment->appointment_date->format('Y-m-d')} الساعة {$appointment->appointment_time->format('H:i')}",
                    'type' => 'appointment_confirmation',
                    'priority' => 'high',
                    'recipients' => [$appointment->patient],
                    'reference_type' => get_class($appointment),
                    'reference_id' => $appointment->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة تحديث الموعد: ' . $e->getMessage(), [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة إلغاء موعد
     */
    public function onAppointmentCancelled($appointment)
    {
        try {
            Log::info('تم إلغاء موعد', [
                'appointment_id' => $appointment->id,
                'reason' => $appointment->cancellation_reason
            ]);

            // تسجيل الإلغاء
            $this->auditLogService->log([
                'action' => 'appointment_cancelled',
                'model' => 'Appointment',
                'model_id' => $appointment->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $appointment->patient->name,
                    'doctor_name' => $appointment->doctor->name,
                    'cancellation_reason' => $appointment->cancellation_reason,
                    'cancelled_at' => now()
                ]
            ]);

            // إرسال إشعار إلغاء للمريض
            $this->notificationService->send([
                'title' => 'إلغاء موعد طبي',
                'message' => "تم إلغاء موعدك مع د. {$appointment->doctor->name} المقرر في {$appointment->appointment_date->format('Y-m-d')}. يرجى الاتصال لحجز موعد جديد.",
                'type' => 'appointment_cancellation',
                'priority' => 'high',
                'recipients' => [$appointment->patient],
                'reference_type' => get_class($appointment),
                'reference_id' => $appointment->id
            ]);

            // إرسال إشعار للطبيب
            $this->notificationService->send([
                'title' => 'إلغاء موعد',
                'message' => "تم إلغاء موعد المريض {$appointment->patient->name} المقرر في {$appointment->appointment_date->format('Y-m-d')} الساعة {$appointment->appointment_time->format('H:i')}",
                'type' => 'appointment_cancellation',
                'priority' => 'normal',
                'recipients' => [$appointment->doctor->user],
                'reference_type' => get_class($appointment),
                'reference_id' => $appointment->id
            ]);

        } catch (\Exception $e) {
            Log::error('فشل في معالجة إلغاء الموعد: ' . $e->getMessage(), [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة عدم حضور المريض
     */
    public function onAppointmentNoShow($appointment)
    {
        try {
            Log::info('عدم حضور مريض لموعد', [
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id
            ]);

            // تسجيل عدم الحضور
            $this->auditLogService->log([
                'action' => 'appointment_no_show',
                'model' => 'Appointment',
                'model_id' => $appointment->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $appointment->patient->name,
                    'doctor_name' => $appointment->doctor->name,
                    'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
                    'appointment_time' => $appointment->appointment_time->format('H:i')
                ]
            ]);

            // إرسال إشعار للمريض
            $this->notificationService->send([
                'title' => 'فوات موعد طبي',
                'message' => "لقد فاتك موعدك مع د. {$appointment->doctor->name} اليوم. يرجى الاتصال لحجز موعد جديد.",
                'type' => 'appointment_missed',
                'priority' => 'normal',
                'recipients' => [$appointment->patient],
                'reference_type' => get_class($appointment),
                'reference_id' => $appointment->id
            ]);

            // تحديث إحصائيات المريض
            $appointment->patient->increment('no_show_count');

        } catch (\Exception $e) {
            Log::error('فشل في معالجة عدم حضور الموعد: ' . $e->getMessage(), [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة إكمال موعد
     */
    public function onAppointmentCompleted($appointment)
    {
        try {
            Log::info('تم إكمال موعد', [
                'appointment_id' => $appointment->id,
                'completed_at' => now()
            ]);

            // تسجيل الإكمال
            $this->auditLogService->log([
                'action' => 'appointment_completed',
                'model' => 'Appointment',
                'model_id' => $appointment->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $appointment->patient->name,
                    'doctor_name' => $appointment->doctor->name,
                    'completed_at' => now()
                ]
            ]);

            // إرسال طلب تقييم للمريض (بعد ساعة من انتهاء الموعد)
            Queue::later(now()->addHour(), function() use ($appointment) {
                $this->notificationService->send([
                    'title' => 'تقييم الخدمة الطبية',
                    'message' => "نأمل أن تكون راضياً عن الخدمة المقدمة من د. {$appointment->doctor->name}. يرجى تقييم تجربتك معنا.",
                    'type' => 'service_review',
                    'priority' => 'low',
                    'recipients' => [$appointment->patient],
                    'reference_type' => get_class($appointment),
                    'reference_id' => $appointment->id
                ]);
            });

        } catch (\Exception $e) {
            Log::error('فشل في معالجة إكمال الموعد: ' . $e->getMessage(), [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}