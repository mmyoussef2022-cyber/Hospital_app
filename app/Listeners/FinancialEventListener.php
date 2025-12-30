<?php

namespace App\Listeners;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\NotificationService;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\Log;

class FinancialEventListener
{
    protected $notificationService;
    protected $auditLogService;

    public function __construct(NotificationService $notificationService, AuditLogService $auditLogService)
    {
        $this->notificationService = $notificationService;
        $this->auditLogService = $auditLogService;
    }

    /**
     * معالجة إنشاء فاتورة جديدة
     */
    public function onInvoiceCreated($invoice)
    {
        try {
            Log::info('تم إنشاء فاتورة جديدة', [
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'total_amount' => $invoice->total_amount
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'invoice_created',
                'model' => 'Invoice',
                'model_id' => $invoice->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $invoice->patient->name,
                    'total_amount' => $invoice->total_amount,
                    'payment_type' => $invoice->payment_type,
                    'insurance_coverage' => $invoice->insurance_coverage_percentage
                ]
            ]);

            // إرسال إشعار للمريض بالفاتورة
            $this->notificationService->send([
                'title' => 'فاتورة جديدة',
                'message' => "تم إصدار فاتورة بقيمة " . number_format($invoice->total_amount, 2) . " ريال. يرجى مراجعة الخزينة للدفع.",
                'type' => 'invoice_created',
                'priority' => 'normal',
                'recipients' => [$invoice->patient],
                'reference_type' => get_class($invoice),
                'reference_id' => $invoice->id,
                'data' => [
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->total_amount,
                    'due_date' => $invoice->due_date?->format('Y-m-d')
                ]
            ]);

            // إشعار الخزينة بفاتورة جديدة
            $cashierUsers = \App\Models\User::role('cashier')->get();
            if ($cashierUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'فاتورة جديدة للدفع',
                    'message' => "فاتورة جديدة للمريض {$invoice->patient->name} بقيمة " . number_format($invoice->total_amount, 2) . " ريال",
                    'type' => 'new_invoice_cashier',
                    'priority' => 'normal',
                    'recipients' => $cashierUsers->toArray(),
                    'reference_type' => get_class($invoice),
                    'reference_id' => $invoice->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة إنشاء الفاتورة: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة دفع فاتورة
     */
    public function onPaymentReceived($payment)
    {
        try {
            Log::info('تم استلام دفعة', [
                'payment_id' => $payment->id,
                'invoice_id' => $payment->invoice_id,
                'amount' => $payment->amount
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'payment_received',
                'model' => 'Payment',
                'model_id' => $payment->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $payment->invoice->patient->name,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'invoice_id' => $payment->invoice_id
                ]
            ]);

            // إرسال إشعار تأكيد الدفع للمريض
            $this->notificationService->send([
                'title' => 'تأكيد الدفع',
                'message' => "تم استلام دفعتك بقيمة " . number_format($payment->amount, 2) . " ريال بنجاح. شكراً لك.",
                'type' => 'payment_confirmation',
                'priority' => 'normal',
                'recipients' => [$payment->invoice->patient],
                'reference_type' => get_class($payment),
                'reference_id' => $payment->id,
                'data' => [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'receipt_number' => $payment->receipt_number
                ]
            ]);

            // التحقق من اكتمال دفع الفاتورة
            $invoice = $payment->invoice;
            if ($invoice->remaining_amount <= 0) {
                $this->onInvoiceFullyPaid($invoice);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة استلام الدفعة: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة اكتمال دفع فاتورة
     */
    public function onInvoiceFullyPaid($invoice)
    {
        try {
            Log::info('تم دفع فاتورة بالكامل', [
                'invoice_id' => $invoice->id,
                'total_amount' => $invoice->total_amount
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'invoice_fully_paid',
                'model' => 'Invoice',
                'model_id' => $invoice->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $invoice->patient->name,
                    'total_amount' => $invoice->total_amount,
                    'paid_at' => now()
                ]
            ]);

            // إرسال إشعار للمريض
            $this->notificationService->send([
                'title' => 'اكتمال الدفع',
                'message' => "تم دفع فاتورتك بالكامل. شكراً لك على التعامل معنا.",
                'type' => 'invoice_paid',
                'priority' => 'normal',
                'recipients' => [$invoice->patient],
                'reference_type' => get_class($invoice),
                'reference_id' => $invoice->id
            ]);

            // إشعار المحاسبة
            $accountingUsers = \App\Models\User::role('accountant')->get();
            if ($accountingUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'فاتورة مدفوعة بالكامل',
                    'message' => "تم دفع فاتورة المريض {$invoice->patient->name} بالكامل - " . number_format($invoice->total_amount, 2) . " ريال",
                    'type' => 'invoice_paid_accounting',
                    'priority' => 'low',
                    'recipients' => $accountingUsers->toArray(),
                    'reference_type' => get_class($invoice),
                    'reference_id' => $invoice->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة اكتمال دفع الفاتورة: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة تأخر دفع فاتورة
     */
    public function onInvoiceOverdue($invoice)
    {
        try {
            Log::info('فاتورة متأخرة الدفع', [
                'invoice_id' => $invoice->id,
                'due_date' => $invoice->due_date,
                'days_overdue' => $invoice->due_date->diffInDays(now())
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'invoice_overdue',
                'model' => 'Invoice',
                'model_id' => $invoice->id,
                'user_id' => null,
                'data' => [
                    'patient_name' => $invoice->patient->name,
                    'amount' => $invoice->remaining_amount,
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                    'days_overdue' => $invoice->due_date->diffInDays(now())
                ]
            ]);

            // إرسال تذكير للمريض
            $this->notificationService->send([
                'title' => 'تذكير بدفعة متأخرة',
                'message' => "لديك دفعة متأخرة بقيمة " . number_format($invoice->remaining_amount, 2) . " ريال. يرجى المراجعة لتسوية الحساب.",
                'type' => 'payment_overdue',
                'priority' => 'high',
                'recipients' => [$invoice->patient],
                'reference_type' => get_class($invoice),
                'reference_id' => $invoice->id,
                'data' => [
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->remaining_amount,
                    'due_date' => $invoice->due_date->format('Y-m-d'),
                    'days_overdue' => $invoice->due_date->diffInDays(now())
                ]
            ]);

            // إشعار إدارة المالية
            $financeUsers = \App\Models\User::role('finance_manager')->get();
            if ($financeUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'فاتورة متأخرة الدفع',
                    'message' => "فاتورة المريض {$invoice->patient->name} متأخرة " . $invoice->due_date->diffInDays(now()) . " يوم - " . number_format($invoice->remaining_amount, 2) . " ريال",
                    'type' => 'overdue_finance',
                    'priority' => 'high',
                    'recipients' => $financeUsers->toArray(),
                    'reference_type' => get_class($invoice),
                    'reference_id' => $invoice->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة تأخر دفع الفاتورة: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة فشل دفعة
     */
    public function onPaymentFailed($payment)
    {
        try {
            Log::info('فشل في دفعة', [
                'payment_id' => $payment->id,
                'failure_reason' => $payment->failure_reason
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'payment_failed',
                'model' => 'Payment',
                'model_id' => $payment->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $payment->invoice->patient->name,
                    'amount' => $payment->amount,
                    'failure_reason' => $payment->failure_reason,
                    'payment_method' => $payment->payment_method
                ]
            ]);

            // إرسال إشعار للمريض
            $this->notificationService->send([
                'title' => 'فشل في الدفع',
                'message' => "فشل في معالجة دفعتك بقيمة " . number_format($payment->amount, 2) . " ريال. يرجى المحاولة مرة أخرى أو استخدام طريقة دفع أخرى.",
                'type' => 'payment_failed',
                'priority' => 'high',
                'recipients' => [$payment->invoice->patient],
                'reference_type' => get_class($payment),
                'reference_id' => $payment->id,
                'data' => [
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                    'failure_reason' => $payment->failure_reason
                ]
            ]);

            // إشعار الخزينة
            $cashierUsers = \App\Models\User::role('cashier')->get();
            if ($cashierUsers->count() > 0) {
                $this->notificationService->send([
                    'title' => 'فشل في دفعة',
                    'message' => "فشل في دفعة المريض {$payment->invoice->patient->name} بقيمة " . number_format($payment->amount, 2) . " ريال",
                    'type' => 'payment_failed_cashier',
                    'priority' => 'normal',
                    'recipients' => $cashierUsers->toArray(),
                    'reference_type' => get_class($payment),
                    'reference_id' => $payment->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error('فشل في معالجة فشل الدفعة: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * معالجة استرداد دفعة
     */
    public function onPaymentRefunded($payment)
    {
        try {
            Log::info('تم استرداد دفعة', [
                'payment_id' => $payment->id,
                'refund_amount' => $payment->refund_amount
            ]);

            // تسجيل العملية
            $this->auditLogService->log([
                'action' => 'payment_refunded',
                'model' => 'Payment',
                'model_id' => $payment->id,
                'user_id' => auth()->id(),
                'data' => [
                    'patient_name' => $payment->invoice->patient->name,
                    'original_amount' => $payment->amount,
                    'refund_amount' => $payment->refund_amount,
                    'refund_reason' => $payment->refund_reason
                ]
            ]);

            // إرسال إشعار للمريض
            $this->notificationService->send([
                'title' => 'استرداد دفعة',
                'message' => "تم استرداد " . number_format($payment->refund_amount, 2) . " ريال إلى حسابك. سيظهر المبلغ خلال 3-5 أيام عمل.",
                'type' => 'payment_refunded',
                'priority' => 'normal',
                'recipients' => [$payment->invoice->patient],
                'reference_type' => get_class($payment),
                'reference_id' => $payment->id,
                'data' => [
                    'payment_id' => $payment->id,
                    'refund_amount' => $payment->refund_amount,
                    'refund_reason' => $payment->refund_reason
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('فشل في معالجة استرداد الدفعة: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}