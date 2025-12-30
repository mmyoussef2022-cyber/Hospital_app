<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // معالجة الأحداث المجدولة يومياً في الساعة 8 صباحاً
        $schedule->command('events:process-scheduled')
                 ->dailyAt('08:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // معالجة الإشعارات المجدولة كل 5 دقائق
        $schedule->command('queue:work --stop-when-empty')
                 ->everyFiveMinutes()
                 ->withoutOverlapping();

        // فحص تصعيد الإشعارات كل ساعة
        $schedule->call(function () {
            app(\App\Services\NotificationService::class)->checkEscalations();
        })->hourly();

        // صيانة الأمان اليومية في الساعة 2 صباحاً
        $schedule->command('security:maintenance')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // فحص صحة النظام كل 6 ساعات
        $schedule->call(function () {
            app(\App\Services\SecurityService::class)->performSecurityHealthCheck();
        })->everySixHours();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}