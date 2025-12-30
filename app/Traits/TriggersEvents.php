<?php

namespace App\Traits;

use App\Services\EventDispatcherService;
use Illuminate\Support\Facades\Log;

trait TriggersEvents
{
    /**
     * إرسال حدث للنموذج
     */
    protected function triggerEvent($model, $eventType, $additionalData = [])
    {
        try {
            $eventDispatcher = app(EventDispatcherService::class);
            
            switch (get_class($model)) {
                case \App\Models\Appointment::class:
                    $eventDispatcher->dispatchAppointmentEvents($model, $eventType);
                    break;
                case \App\Models\Payment::class:
                    $eventDispatcher->dispatchPaymentEvents($model, $eventType);
                    break;
                case \App\Models\LabResult::class:
                    $eventDispatcher->dispatchLabEvents($model, $eventType);
                    break;
                case \App\Models\Patient::class:
                    $eventDispatcher->dispatchPatientEvents($model, $eventType, $additionalData);
                    break;
                case \App\Models\InsuranceClaim::class:
                case \App\Models\InsurancePolicy::class:
                case \App\Models\PatientInsurance::class:
                    $eventDispatcher->dispatchInsuranceEvents($model, $eventType, $additionalData);
                    break;
                default:
                    Log::warning('نوع نموذج غير مدعوم للأحداث', [
                        'model_class' => get_class($model),
                        'model_id' => $model->id ?? null
                    ]);
                    break;
            }
        } catch (\Exception $e) {
            Log::error('فشل في إرسال الحدث: ' . $e->getMessage(), [
                'model_class' => get_class($model),
                'model_id' => $model->id ?? null,
                'event_type' => $eventType
            ]);
        }
    }

    /**
     * إرسال أحداث متعددة
     */
    protected function triggerMultipleEvents($events)
    {
        try {
            $eventDispatcher = app(EventDispatcherService::class);
            $eventDispatcher->dispatchBulkEvents($events);
        } catch (\Exception $e) {
            Log::error('فشل في إرسال الأحداث المتعددة: ' . $e->getMessage());
        }
    }

    /**
     * جدولة حدث مؤجل
     */
    protected function scheduleEvent($model, $eventType, $delay, $additionalData = [])
    {
        try {
            $eventDispatcher = app(EventDispatcherService::class);
            $events = [[
                'model' => $model,
                'event_type' => $eventType,
                'additional_data' => $additionalData
            ]];
            
            $eventDispatcher->scheduleDelayedEvents($events, $delay);
        } catch (\Exception $e) {
            Log::error('فشل في جدولة الحدث: ' . $e->getMessage(), [
                'model_class' => get_class($model),
                'model_id' => $model->id ?? null,
                'event_type' => $eventType
            ]);
        }
    }

    /**
     * إرسال حدث موعد
     */
    protected function triggerAppointmentEvent($appointment, $eventType)
    {
        $this->triggerEvent($appointment, $eventType);
    }

    /**
     * إرسال حدث دفع
     */
    protected function triggerPaymentEvent($payment, $eventType)
    {
        $this->triggerEvent($payment, $eventType);
    }

    /**
     * إرسال حدث مريض
     */
    protected function triggerPatientEvent($patient, $eventType, $additionalData = [])
    {
        $this->triggerEvent($patient, $eventType, $additionalData);
    }

    /**
     * إرسال حدث تأمين
     */
    protected function triggerInsuranceEvent($model, $eventType, $additionalData = [])
    {
        $this->triggerEvent($model, $eventType, $additionalData);
    }

    /**
     * إرسال حدث طبي
     */
    protected function triggerMedicalEvent($model, $eventType)
    {
        $this->triggerEvent($model, $eventType);
    }
}