<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // تسجيل خدمات النظام المتقدم
        $this->app->singleton(\App\Services\AdvancedPermissionService::class);
        $this->app->singleton(\App\Services\AuditLogService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // تسجيل Model Observers للأحداث التلقائية
        \App\Models\Appointment::observe(\App\Observers\AppointmentObserver::class);
        \App\Models\Payment::observe(\App\Observers\PaymentObserver::class);
        \App\Models\Patient::observe(\App\Observers\PatientObserver::class);
    }
}