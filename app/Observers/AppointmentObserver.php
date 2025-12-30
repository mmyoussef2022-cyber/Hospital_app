<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Services\EventDispatcherService;
use Illuminate\Support\Facades\Log;

class AppointmentObserver
{
    protected $eventDispatcher;

    public function __construct(EventDispatcherService $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment)
    {
        try {
            $this->eventDispatcher->dispatchAppointmentEvents($appointment, 'created');
        } catch (\Exception $e) {
            Log::error('فشل في معالجة إنشاء الموعد: ' . $e->getMessage(), [
                'appointment_id' => $appointment->id
            ]);
        }
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment)
    {
        try {
            // التحقق من نوع التحديث
            if ($appointment->wasChanged('status')) {
                switch ($appointment->status) {
                    case 'cancelled':
                        $this->eventDispatcher->dispatchAppointmentEvents($appointment, 'cancelled');
                        break;
                    case 'completed':
                        $this->eventDispatcher->dispatchAppointmentEvents($appointment, 'completed');
                        break;
                    case 'no_show':
                        $this->eventDispatcher->dispatchAppointmentEvents($appointment, 'no_show');
                        break;
                    default:
                        $this->eventDispatcher->dispatchAppointmentEvents($appointment, 'updated');
                        break;
                }
            } else {
                $this->eventDispatcher->dispatchAppointmentEvents($appointment, 'updated');
            }
        } catch (\Exception $e) {
            Log::error('فشل في معالجة تحديث الموعد: ' . $e->getMessage(), [
                'appointment_id' => $appointment->id
            ]);
        }
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment)
    {
        try {
            Log::info('تم حذف موعد', [
                'appointment_id' => $appointment->id,
                'patient_name' => $appointment->patient->name,
                'doctor_name' => $appointment->doctor->name
            ]);
        } catch (\Exception $e) {
            Log::error('فشل في معالجة حذف الموعد: ' . $e->getMessage(), [
                'appointment_id' => $appointment->id
            ]);
        }
    }
}