<?php

namespace App\Console\Commands;

use App\Models\Patient;
use App\Models\InsurancePolicy;
use App\Models\Invoice;
use App\Models\InsuranceClaim;
use App\Services\EventDispatcherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledEvents extends Command
{
    protected $signature = 'events:process-scheduled';
    protected $description = 'Process scheduled events like birthdays, insurance expiry, overdue payments, etc.';

    protected $eventDispatcher;

    public function __construct(EventDispatcherService $eventDispatcher)
    {
        parent::__construct();
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle()
    {
        $this->info('بدء معالجة الأحداث المجدولة...');

        try {
            // معالجة أعياد الميلاد
            $this->processBirthdays();

            // معالجة انتهاء صلاحية التأمين
            $this->processInsuranceExpiry();

            // معالجة الفواتير المتأخرة
            $this->processOverdueInvoices();

            // معالجة انتهاء صلاحية البوالص
            $this->processPolicyExpiry();

            // معالجة تأخر دفع شركات التأمين
            $this->processDelayedInsurancePayments();

            $this->info('تم إكمال معالجة الأحداث المجدولة بنجاح.');

        } catch (\Exception $e) {
            $this->error('فشل في معالجة الأحداث المجدولة: ' . $e->getMessage());
            Log::error('فشل في معالجة الأحداث المجدولة: ' . $e->getMessage());
        }
    }

    /**
     * معالجة أعياد الميلاد
     */
    protected function processBirthdays()
    {
        $this->info('معالجة أعياد الميلاد...');

        $patients = Patient::whereRaw('DATE_FORMAT(date_of_birth, "%m-%d") = ?', [now()->format('m-d')])
            ->where('is_active', true)
            ->get();

        foreach ($patients as $patient) {
            $this->eventDispatcher->dispatchPatientEvents($patient, 'birthday');
        }

        $this->info("تم معالجة {$patients->count()} عيد ميلاد.");
    }

    /**
     * معالجة انتهاء صلاحية التأمين
     */
    protected function processInsuranceExpiry()
    {
        $this->info('معالجة انتهاء صلاحية التأمين...');

        // المرضى الذين ستنتهي صلاحية تأمينهم خلال 30 يوم
        $patients = Patient::whereNotNull('insurance_expiry_date')
            ->whereBetween('insurance_expiry_date', [now(), now()->addDays(30)])
            ->where('is_active', true)
            ->get();

        foreach ($patients as $patient) {
            $this->eventDispatcher->dispatchPatientEvents($patient, 'insurance_expiring');
        }

        $this->info("تم معالجة {$patients->count()} حالة انتهاء صلاحية تأمين.");
    }

    /**
     * معالجة الفواتير المتأخرة
     */
    protected function processOverdueInvoices()
    {
        $this->info('معالجة الفواتير المتأخرة...');

        $overdueInvoices = Invoice::where('status', 'pending')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('remaining_amount', '>', 0)
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $listener = app(\App\Listeners\FinancialEventListener::class);
            $listener->onInvoiceOverdue($invoice);
        }

        $this->info("تم معالجة {$overdueInvoices->count()} فاتورة متأخرة.");
    }

    /**
     * معالجة انتهاء صلاحية البوالص
     */
    protected function processPolicyExpiry()
    {
        $this->info('معالجة انتهاء صلاحية البوالص...');

        // البوالص التي ستنتهي خلال 60 يوم
        $expiringPolicies = InsurancePolicy::where('status', 'active')
            ->whereBetween('expiry_date', [now(), now()->addDays(60)])
            ->get();

        foreach ($expiringPolicies as $policy) {
            $this->eventDispatcher->dispatchInsuranceEvents($policy, 'policy_expiring');
        }

        $this->info("تم معالجة {$expiringPolicies->count()} بوليصة منتهية الصلاحية.");
    }

    /**
     * معالجة تأخر دفع شركات التأمين
     */
    protected function processDelayedInsurancePayments()
    {
        $this->info('معالجة تأخر دفع شركات التأمين...');

        $delayedClaims = InsuranceClaim::where('status', 'approved')
            ->whereNotNull('expected_payment_date')
            ->where('expected_payment_date', '<', now())
            ->whereNull('payment_received_at')
            ->get();

        foreach ($delayedClaims as $claim) {
            $listener = app(\App\Listeners\InsuranceEventListener::class);
            $listener->onInsurancePaymentDelayed($claim);
        }

        $this->info("تم معالجة {$delayedClaims->count()} مطالبة متأخرة الدفع.");
    }
}