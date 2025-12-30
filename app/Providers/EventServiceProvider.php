<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        // Appointment Events
        \App\Events\AppointmentCreated::class => [
            \App\Listeners\AppointmentEventListener::class . '@onAppointmentCreated',
        ],
        \App\Events\AppointmentUpdated::class => [
            \App\Listeners\AppointmentEventListener::class . '@onAppointmentUpdated',
        ],
        
        // Financial Events
        \App\Events\PaymentReceived::class => [
            \App\Listeners\FinancialEventListener::class . '@onPaymentReceived',
        ],
        
        // Medical Events
        \App\Events\LabResultCreated::class => [
            \App\Listeners\MedicalEventListener::class . '@onLabResultCreated',
        ],
        
        // Patient Events
        \App\Events\PatientRegistered::class => [
            \App\Listeners\PatientEventListener::class . '@onPatientRegistered',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}