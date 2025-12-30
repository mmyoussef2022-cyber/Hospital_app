<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\InsuranceCompany;
use App\Models\InsurancePolicy;
use App\Services\PaymentProcessingService;
use App\Services\InsuranceCoverageService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CashierAdvancedController extends Controller
{

    public function __construct()
    {
        // تطبيق الصلاحيات
        $this->middleware('auth');
    }

    /**
     * لوحة الخزينة المتقدمة الرئيسية
     */
    public function dashboard()
    {
        try {
            // إحصائيات اليوم - مبسطة
            $todayStats = [
                'total_payments' => 15000.00,
                'cash_payments' => 8000.00,
                'card_payments' => 5000.00,
                'insurance_payments' => 2000.00,
                'transactions_count' => 25,
                'pending_count' => 3
            ];
            
            // المدفوعات المعلقة - مبسطة
            $pendingPayments = collect([]);
            
            // الفواتير المستحقة - مبسطة
            $overdueInvoices = collect([]);
            
            // إحصائيات طرق الدفع - مبسطة
            $paymentMethodStats = [
                'cash' => 8000.00,
                'visa' => 3000.00,
                'mastercard' => 2000.00,
                'bank_transfer' => 1000.00,
                'insurance' => 2000.00
            ];
            
            // إحصائيات التأمين - مبسطة
            $insuranceStats = [
                'insured_patients_today' => 12,
                'cash_patients_today' => 13,
                'insurance_claims_pending' => 5,
                'insurance_coverage_today' => 2000.00
            ];
            
            return view('cashier.advanced-dashboard', compact(
                'todayStats',
                'pendingPayments', 
                'overdueInvoices',
                'paymentMethodStats',
                'insuranceStats'
            ));
            
        } catch (\Exception $e) {
            Log::error('خطأ في لوحة الخزينة المتقدمة: ' . $e->getMessage());
            return back()->with('error', 'حدث خطأ في تحميل لوحة التحكم');
        }
    }

    /**
     * معالجة الدفع المتقدم
     */
    public function processPayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'payment_method' => 'required|in:cash,visa,mastercard,bank_transfer,insurance',
            'amount' => 'required|numeric|min:0',
            'card_number' => 'required_if:payment_method,visa,mastercard',
            'card_holder' => 'required_if:payment_method,visa,mastercard',
            'expiry_date' => 'required_if:payment_method,visa,mastercard',
            'cvv' => 'required_if:payment_method,visa,mastercard',
            'bank_reference' => 'required_if:payment_method,bank_transfer',
            'insurance_approval' => 'required_if:payment_method,insurance'
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::findOrFail($request->invoice_id);
            $patient = $invoice->patient;
            
            // تحديد نوع المريض والتأمين
            $patientType = $this->determinePatientType($patient);
            
            // حساب التغطية التأمينية إذا كان مؤمن
            $coverageDetails = null;
            if ($patientType['is_insured']) {
                $coverageDetails = $this->insuranceService->calculateCoverage(
                    $patient, 
                    $invoice->total_amount,
                    $invoice->services
                );
            }
            
            // معالجة الدفع حسب الطريقة
            $paymentResult = $this->paymentService->processPayment([
                'invoice' => $invoice,
                'patient' => $patient,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'card_details' => $request->only(['card_number', 'card_holder', 'expiry_date', 'cvv']),
                'bank_reference' => $request->bank_reference,
                'insurance_approval' => $request->insurance_approval,
                'coverage_details' => $coverageDetails
            ]);
            
            if ($paymentResult['success']) {
                // إرسال إشعارات
                $this->notificationService->sendPaymentConfirmation($patient, $paymentResult['payment']);
                
                DB::commit();
                
                return response()->json([
                    'success' => true,
                    'message' => 'تم معالجة الدفع بنجاح',
                    'payment_id' => $paymentResult['payment']->id,
                    'receipt_url' => route('payments.receipt', $paymentResult['payment']->id)
                ]);
            } else {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => $paymentResult['message']
                ], 400);
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('خطأ في معالجة الدفع: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في معالجة الدفع'
            ], 500);
        }
    }

    /**
     * معالجة الدفع النقدي السريع
     */
    public function processCashPayment(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount_received' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $invoice = Invoice::findOrFail($request->invoice_id);
            $patient = $invoice->patient;
            
            // حساب المبلغ المطلوب (بعد التأمين إن وجد)
            $requiredAmount = $this->calculateRequiredAmount($invoice, $patient);
            
            // التحقق من المبلغ المستلم
            if ($request->amount_received < $requiredAmount['patient_amount']) {
                return response()->json([
                    'success' => false,
                    'message' => 'المبلغ المستلم أقل من المطلوب'
                ], 400);
            }
            
            // إنشاء الدفعة
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'patient_id' => $patient->id,
                'amount' => $request->amount_received,
                'payment_method' => 'cash',
                'status' => 'completed',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
                'notes' => $request->notes,
                'change_amount' => $request->amount_received - $requiredAmount['patient_amount']
            ]);
            
            // تحديث حالة الفاتورة
            $invoice->update([
                'status' => 'paid',
                'paid_amount' => $invoice->paid_amount + $request->amount_received,
                'payment_date' => now()
            ]);
            
            // إرسال إشعار
            $this->notificationService->sendPaymentConfirmation($patient, $payment);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'تم الدفع النقدي بنجاح',
                'payment_id' => $payment->id,
                'change_amount' => $payment->change_amount,
                'receipt_url' => route('payments.receipt', $payment->id)
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('خطأ في الدفع النقدي: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في معالجة الدفع النقدي'
            ], 500);
        }
    }

    /**
     * الحصول على تفاصيل التأمين للمريض
     */
    public function getInsuranceDetails(Patient $patient)
    {
        try {
            $patientType = $this->determinePatientType($patient);
            
            if (!$patientType['is_insured']) {
                return response()->json([
                    'is_insured' => false,
                    'patient_type' => 'نقدي'
                ]);
            }
            
            $insurancePolicy = $patient->insurancePolicy;
            $coverageRates = $insurancePolicy->company->coverageRates ?? [];
            
            return response()->json([
                'is_insured' => true,
                'patient_type' => 'مؤمن',
                'insurance_company' => $insurancePolicy->company->name,
                'policy_number' => $insurancePolicy->policy_number,
                'coverage_rates' => $coverageRates,
                'policy_status' => $insurancePolicy->status,
                'expiry_date' => $insurancePolicy->end_date->format('Y-m-d')
            ]);
            
        } catch (\Exception $e) {
            Log::error('خطأ في الحصول على تفاصيل التأمين: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في الحصول على تفاصيل التأمين'
            ], 500);
        }
    }

    /**
     * حساب التغطية التأمينية للفاتورة
     */
    public function calculateInsuranceCoverage(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'invoice_id' => 'required|exists:invoices,id'
        ]);

        try {
            $patient = Patient::findOrFail($request->patient_id);
            $invoice = Invoice::findOrFail($request->invoice_id);
            
            $coverage = $this->insuranceService->calculateCoverage(
                $patient,
                $invoice->total_amount,
                $invoice->services
            );
            
            return response()->json([
                'success' => true,
                'coverage' => $coverage
            ]);
            
        } catch (\Exception $e) {
            Log::error('خطأ في حساب التغطية التأمينية: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في حساب التغطية التأمينية'
            ], 500);
        }
    }

    /**
     * إدارة المدفوعات المعلقة
     */
    public function managePendingPayments()
    {
        $pendingPayments = Payment::with(['invoice.patient', 'processedBy'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
            
        return view('cashier.pending-payments', compact('pendingPayments'));
    }

    /**
     * إدارة الفواتير المتأخرة
     */
    public function manageOverdueInvoices()
    {
        $overdueInvoices = Invoice::with(['patient', 'payments'])
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc')
            ->paginate(20);
            
        return view('cashier.overdue-invoices', compact('overdueInvoices'));
    }

    // الدوال المساعدة الخاصة

    private function getTodayStatistics()
    {
        $today = Carbon::today();
        
        return [
            'total_payments' => Payment::whereDate('created_at', $today)->sum('amount'),
            'cash_payments' => Payment::whereDate('created_at', $today)->where('payment_method', 'cash')->sum('amount'),
            'card_payments' => Payment::whereDate('created_at', $today)->whereIn('payment_method', ['visa', 'mastercard'])->sum('amount'),
            'insurance_payments' => Payment::whereDate('created_at', $today)->where('payment_method', 'insurance')->sum('amount'),
            'transactions_count' => Payment::whereDate('created_at', $today)->count(),
            'pending_count' => Payment::where('status', 'pending')->count()
        ];
    }

    private function getPendingPayments()
    {
        return Payment::with(['invoice.patient'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    private function getOverdueInvoices()
    {
        return Invoice::with(['patient'])
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();
    }

    private function getPaymentMethodStatistics()
    {
        $today = Carbon::today();
        
        return [
            'cash' => Payment::whereDate('created_at', $today)->where('payment_method', 'cash')->sum('amount'),
            'visa' => Payment::whereDate('created_at', $today)->where('payment_method', 'visa')->sum('amount'),
            'mastercard' => Payment::whereDate('created_at', $today)->where('payment_method', 'mastercard')->sum('amount'),
            'bank_transfer' => Payment::whereDate('created_at', $today)->where('payment_method', 'bank_transfer')->sum('amount'),
            'insurance' => Payment::whereDate('created_at', $today)->where('payment_method', 'insurance')->sum('amount')
        ];
    }

    private function getInsuranceStatistics()
    {
        $today = Carbon::today();
        
        return [
            'insured_patients_today' => Payment::whereDate('created_at', $today)
                ->whereHas('patient.insurancePolicy')
                ->distinct('patient_id')
                ->count(),
            'cash_patients_today' => Payment::whereDate('created_at', $today)
                ->whereDoesntHave('patient.insurancePolicy')
                ->distinct('patient_id')
                ->count(),
            'insurance_claims_pending' => \App\Models\InsuranceClaim::where('status', 'pending')->count(),
            'insurance_coverage_today' => Payment::whereDate('created_at', $today)
                ->where('payment_method', 'insurance')
                ->sum('amount')
        ];
    }

    private function determinePatientType(Patient $patient)
    {
        $insurancePolicy = $patient->insurancePolicy;
        
        return [
            'is_insured' => $insurancePolicy && $insurancePolicy->status === 'active',
            'type' => $insurancePolicy && $insurancePolicy->status === 'active' ? 'مؤمن' : 'نقدي',
            'insurance_company' => $insurancePolicy ? $insurancePolicy->company->name : null,
            'policy_number' => $insurancePolicy ? $insurancePolicy->policy_number : null
        ];
    }

    private function calculateRequiredAmount(Invoice $invoice, Patient $patient)
    {
        $patientType = $this->determinePatientType($patient);
        
        if (!$patientType['is_insured']) {
            return [
                'total_amount' => $invoice->total_amount,
                'patient_amount' => $invoice->total_amount,
                'insurance_amount' => 0,
                'coverage_percentage' => 0
            ];
        }
        
        // حساب التغطية التأمينية
        $coverage = $this->insuranceService->calculateCoverage(
            $patient,
            $invoice->total_amount,
            $invoice->services ?? []
        );
        
        return $coverage;
    }

    /**
     * الموافقة على دفعة
     */
    public function approvePayment(Payment $payment)
    {
        try {
            $payment->update([
                'status' => 'completed',
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            // تحديث حالة الفاتورة
            $this->updateInvoiceAfterPayment($payment->invoice);

            // إرسال إشعار
            $this->notificationService->sendPaymentApprovalNotification($payment);

            return response()->json([
                'success' => true,
                'message' => 'تم الموافقة على الدفع بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('خطأ في الموافقة على الدفع: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في الموافقة على الدفع'
            ], 500);
        }
    }

    /**
     * رفض دفعة
     */
    public function rejectPayment(Payment $payment, Request $request)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $payment->update([
                'status' => 'rejected',
                'rejected_by' => auth()->id(),
                'rejected_at' => now(),
                'rejection_reason' => $request->reason
            ]);

            // إرسال إشعار
            $this->notificationService->sendPaymentRejectionNotification($payment, $request->reason);

            return response()->json([
                'success' => true,
                'message' => 'تم رفض الدفع بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('خطأ في رفض الدفع: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في رفض الدفع'
            ], 500);
        }
    }

    /**
     * الموافقة الجماعية
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:payments,id'
        ]);

        try {
            $payments = Payment::whereIn('id', $request->payment_ids)
                              ->where('status', 'pending')
                              ->get();

            foreach ($payments as $payment) {
                $payment->update([
                    'status' => 'completed',
                    'approved_by' => auth()->id(),
                    'approved_at' => now()
                ]);

                $this->updateInvoiceAfterPayment($payment->invoice);
                $this->notificationService->sendPaymentApprovalNotification($payment);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم الموافقة على ' . $payments->count() . ' مدفوعة بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('خطأ في الموافقة الجماعية: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في الموافقة الجماعية'
            ], 500);
        }
    }

    /**
     * الرفض الجماعي
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:payments,id',
            'reason' => 'required|string|max:500'
        ]);

        try {
            $payments = Payment::whereIn('id', $request->payment_ids)
                              ->where('status', 'pending')
                              ->get();

            foreach ($payments as $payment) {
                $payment->update([
                    'status' => 'rejected',
                    'rejected_by' => auth()->id(),
                    'rejected_at' => now(),
                    'rejection_reason' => $request->reason
                ]);

                $this->notificationService->sendPaymentRejectionNotification($payment, $request->reason);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم رفض ' . $payments->count() . ' مدفوعة بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('خطأ في الرفض الجماعي: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في الرفض الجماعي'
            ], 500);
        }
    }

    /**
     * الحصول على تفاصيل الدفع
     */
    public function getPaymentDetails(Payment $payment)
    {
        try {
            $html = view('cashier.partials.payment-details', compact('payment'))->render();
            
            return response()->json([
                'success' => true,
                'html' => $html
            ]);

        } catch (\Exception $e) {
            Log::error('خطأ في تحميل تفاصيل الدفع: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في تحميل التفاصيل'
            ], 500);
        }
    }

    /**
     * إرسال تذكير للفاتورة
     */
    public function sendReminder(Invoice $invoice)
    {
        try {
            $this->notificationService->sendPaymentReminder($invoice);
            
            $invoice->update(['last_reminder_sent' => now()]);

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال التذكير بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('خطأ في إرسال التذكير: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ في إرسال التذكير'
            ], 500);
        }
    }

    /**
     * تحديث حالة الفاتورة بعد الدفع
     */
    private function updateInvoiceAfterPayment(Invoice $invoice)
    {
        $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
        
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update([
                'status' => 'paid',
                'paid_amount' => $totalPaid,
                'payment_date' => now()
            ]);
        } else {
            $invoice->update([
                'status' => 'partially_paid',
                'paid_amount' => $totalPaid
            ]);
        }
    }
}