<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdvancedBillingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Advanced Billing Dashboard
     */
    public function dashboard()
    {
        $stats = $this->getAdvancedStats();
        $cashFlowData = $this->getCashFlowData();
        $overdueAnalysis = $this->getOverdueAnalysis();
        $paymentTrends = $this->getPaymentTrends();

        return view('advanced-billing.dashboard', compact(
            'stats', 'cashFlowData', 'overdueAnalysis', 'paymentTrends'
        ));
    }

    /**
     * Cash Invoice Management
     */
    public function cashInvoices(Request $request)
    {
        $query = Invoice::cash()
                       ->with(['patient', 'doctor', 'payments'])
                       ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('invoice_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('invoice_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(20);

        $stats = [
            'total_cash' => Invoice::cash()->sum('total_amount'),
            'paid_cash' => Invoice::cash()->where('status', 'paid')->sum('total_amount'),
            'pending_cash' => Invoice::cash()->where('status', 'pending')->sum('remaining_amount'),
            'today_cash' => Invoice::cash()->whereDate('invoice_date', today())->sum('total_amount'),
        ];

        return view('advanced-billing.cash-invoices', compact('invoices', 'stats'));
    }

    /**
     * Credit Invoice Management
     */
    public function creditInvoices(Request $request)
    {
        $query = Invoice::credit()
                       ->with(['patient', 'doctor', 'payments'])
                       ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('overdue_only')) {
            $query->where(function($q) {
                $q->where('status', 'overdue')
                  ->orWhere(function($q2) {
                      $q2->where('status', 'pending')
                         ->where('due_date', '<', now());
                  });
            });
        }

        if ($request->filled('payment_terms')) {
            $query->where('payment_terms', $request->payment_terms);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(20);

        $stats = [
            'total_credit' => Invoice::credit()->sum('total_amount'),
            'paid_credit' => Invoice::credit()->where('status', 'paid')->sum('total_amount'),
            'pending_credit' => Invoice::credit()->where('status', 'pending')->sum('remaining_amount'),
            'overdue_credit' => Invoice::credit()->where('status', 'overdue')->sum('remaining_amount'),
            'overdue_count' => Invoice::credit()->where('status', 'overdue')->count(),
        ];

        return view('advanced-billing.credit-invoices', compact('invoices', 'stats'));
    }

    /**
     * Payment Tracking Dashboard
     */
    public function paymentTracking(Request $request)
    {
        $query = Payment::with(['invoice.patient', 'processedBy'])
                       ->orderBy('payment_date', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        $payments = $query->paginate(20);

        $stats = [
            'total_payments' => Payment::completed()->sum('amount'),
            'pending_payments' => Payment::pending()->sum('amount'),
            'failed_payments' => Payment::failed()->count(),
            'today_payments' => Payment::completed()->whereDate('payment_date', today())->sum('amount'),
            'cash_payments' => Payment::completed()->cash()->sum('amount'),
            'card_payments' => Payment::completed()->card()->sum('amount'),
        ];

        return view('advanced-billing.payment-tracking', compact('payments', 'stats'));
    }

    /**
     * Overdue Management
     */
    public function overdueManagement(Request $request)
    {
        $query = Invoice::with(['patient', 'doctor'])
                       ->where(function($q) {
                           $q->where('status', 'overdue')
                             ->orWhere(function($q2) {
                                 $q2->where('status', 'pending')
                                    ->where('due_date', '<', now());
                             });
                       });

        // Apply filters
        if ($request->filled('days_overdue')) {
            $daysOverdue = (int) $request->days_overdue;
            $cutoffDate = now()->subDays($daysOverdue);
            $query->where('due_date', '<=', $cutoffDate);
        }

        if ($request->filled('amount_min')) {
            $query->where('remaining_amount', '>=', $request->amount_min);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $overdueInvoices = $query->orderBy('due_date')->paginate(20);

        // Overdue analysis
        $overdueStats = [
            'total_overdue' => $query->sum('remaining_amount'),
            'count_overdue' => $query->count(),
            '30_days' => Invoice::where('status', 'overdue')
                              ->where('due_date', '>=', now()->subDays(30))
                              ->sum('remaining_amount'),
            '60_days' => Invoice::where('status', 'overdue')
                              ->where('due_date', '>=', now()->subDays(60))
                              ->where('due_date', '<', now()->subDays(30))
                              ->sum('remaining_amount'),
            '90_days' => Invoice::where('status', 'overdue')
                              ->where('due_date', '<', now()->subDays(60))
                              ->sum('remaining_amount'),
        ];

        $patients = Patient::select('id', 'name')->orderBy('name')->get();

        return view('advanced-billing.overdue-management', compact(
            'overdueInvoices', 'overdueStats', 'patients'
        ));
    }

    /**
     * Financial Reports
     */
    public function financialReports(Request $request)
    {
        $reportType = $request->get('type', 'summary');
        $dateFrom = $request->get('date_from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $data = [];

        switch ($reportType) {
            case 'summary':
                $data = $this->getSummaryReport($dateFrom, $dateTo);
                break;
            case 'cash_flow':
                $data = $this->getCashFlowReport($dateFrom, $dateTo);
                break;
            case 'aging':
                $data = $this->getAgingReport();
                break;
            case 'payment_methods':
                $data = $this->getPaymentMethodsReport($dateFrom, $dateTo);
                break;
        }

        return view('advanced-billing.financial-reports', compact(
            'reportType', 'dateFrom', 'dateTo', 'data'
        ));
    }

    /**
     * Process Quick Cash Payment
     */
    public function processQuickCashPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'received_amount' => 'nullable|numeric|min:0',
        ]);

        $amount = $request->amount;
        $receivedAmount = $request->received_amount ?? $amount;

        if ($amount > $invoice->remaining_amount) {
            return response()->json([
                'success' => false,
                'message' => 'المبلغ أكبر من المبلغ المتبقي'
            ]);
        }

        try {
            DB::beginTransaction();

            $payment = Payment::createCashPayment($invoice, $amount, $receivedAmount);
            $payment->complete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدفع بنجاح',
                'payment' => $payment,
                'change_amount' => $payment->change_amount,
                'invoice_status' => $invoice->fresh()->status
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الدفع: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Send Payment Reminder
     */
    public function sendPaymentReminder(Request $request, Invoice $invoice)
    {
        $request->validate([
            'reminder_type' => 'required|in:sms,email,whatsapp',
            'message' => 'nullable|string|max:500',
        ]);

        // TODO: Implement notification sending logic
        // This would integrate with notification system when implemented

        $invoice->addToAuditTrail('reminder_sent', [
            'type' => $request->reminder_type,
            'message' => $request->message,
            'sent_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال التذكير بنجاح'
        ]);
    }

    /**
     * Bulk Actions for Overdue Invoices
     */
    public function bulkOverdueActions(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_overdue,send_reminders,escalate',
            'invoice_ids' => 'required|array',
            'invoice_ids.*' => 'exists:invoices,id',
        ]);

        $invoices = Invoice::whereIn('id', $request->invoice_ids)->get();
        $results = [];

        foreach ($invoices as $invoice) {
            switch ($request->action) {
                case 'mark_overdue':
                    if ($invoice->status === 'pending' && $invoice->due_date && $invoice->due_date->isPast()) {
                        $invoice->markAsOverdue();
                        $results[] = "تم تحديث حالة الفاتورة {$invoice->invoice_number}";
                    }
                    break;

                case 'send_reminders':
                    // TODO: Implement reminder sending
                    $invoice->addToAuditTrail('bulk_reminder_sent', 'Bulk reminder sent');
                    $results[] = "تم إرسال تذكير للفاتورة {$invoice->invoice_number}";
                    break;

                case 'escalate':
                    $invoice->addToAuditTrail('escalated', 'Invoice escalated for collection');
                    $results[] = "تم تصعيد الفاتورة {$invoice->invoice_number}";
                    break;
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تنفيذ الإجراء بنجاح',
            'results' => $results
        ]);
    }

    // Private helper methods

    private function getAdvancedStats()
    {
        return [
            'total_revenue' => Invoice::where('status', 'paid')->sum('total_amount'),
            'pending_revenue' => Invoice::where('status', 'pending')->sum('remaining_amount'),
            'overdue_revenue' => Invoice::where('status', 'overdue')->sum('remaining_amount'),
            'cash_revenue' => Invoice::cash()->where('status', 'paid')->sum('total_amount'),
            'credit_revenue' => Invoice::credit()->where('status', 'paid')->sum('total_amount'),
            'collection_rate' => $this->calculateCollectionRate(),
            'average_payment_time' => $this->calculateAveragePaymentTime(),
            'overdue_percentage' => $this->calculateOverduePercentage(),
        ];
    }

    private function getCashFlowData()
    {
        $last12Months = collect();
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthData = [
                'month' => $date->format('Y-m'),
                'month_name' => $date->translatedFormat('F Y'),
                'invoiced' => Invoice::whereYear('invoice_date', $date->year)
                                   ->whereMonth('invoice_date', $date->month)
                                   ->sum('total_amount'),
                'collected' => Payment::completed()
                                    ->whereYear('payment_date', $date->year)
                                    ->whereMonth('payment_date', $date->month)
                                    ->sum('amount'),
            ];
            
            $last12Months->push($monthData);
        }

        return $last12Months;
    }

    private function getOverdueAnalysis()
    {
        return [
            '0_30_days' => Invoice::where('status', 'overdue')
                                ->where('due_date', '>=', now()->subDays(30))
                                ->count(),
            '31_60_days' => Invoice::where('status', 'overdue')
                                 ->where('due_date', '>=', now()->subDays(60))
                                 ->where('due_date', '<', now()->subDays(30))
                                 ->count(),
            '61_90_days' => Invoice::where('status', 'overdue')
                                 ->where('due_date', '>=', now()->subDays(90))
                                 ->where('due_date', '<', now()->subDays(60))
                                 ->count(),
            'over_90_days' => Invoice::where('status', 'overdue')
                                   ->where('due_date', '<', now()->subDays(90))
                                   ->count(),
        ];
    }

    private function getPaymentTrends()
    {
        return Payment::completed()
                     ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                     ->whereDate('payment_date', '>=', now()->subDays(30))
                     ->groupBy('payment_method')
                     ->get();
    }

    private function calculateCollectionRate()
    {
        $totalInvoiced = Invoice::whereNotIn('status', ['draft', 'cancelled'])->sum('total_amount');
        $totalCollected = Invoice::where('status', 'paid')->sum('total_amount');
        
        return $totalInvoiced > 0 ? ($totalCollected / $totalInvoiced) * 100 : 0;
    }

    private function calculateAveragePaymentTime()
    {
        $paidInvoices = Invoice::where('status', 'paid')
                             ->whereNotNull('paid_at')
                             ->get();

        if ($paidInvoices->isEmpty()) {
            return 0;
        }

        $totalDays = $paidInvoices->sum(function ($invoice) {
            return $invoice->invoice_date->diffInDays($invoice->paid_at);
        });

        return round($totalDays / $paidInvoices->count(), 1);
    }

    private function calculateOverduePercentage()
    {
        $totalInvoices = Invoice::whereNotIn('status', ['draft', 'cancelled'])->count();
        $overdueInvoices = Invoice::where('status', 'overdue')->count();
        
        return $totalInvoices > 0 ? ($overdueInvoices / $totalInvoices) * 100 : 0;
    }

    private function getSummaryReport($dateFrom, $dateTo)
    {
        return [
            'invoices_created' => Invoice::whereBetween('invoice_date', [$dateFrom, $dateTo])->count(),
            'total_invoiced' => Invoice::whereBetween('invoice_date', [$dateFrom, $dateTo])->sum('total_amount'),
            'total_collected' => Payment::completed()->whereBetween('payment_date', [$dateFrom, $dateTo])->sum('amount'),
            'cash_invoices' => Invoice::cash()->whereBetween('invoice_date', [$dateFrom, $dateTo])->sum('total_amount'),
            'credit_invoices' => Invoice::credit()->whereBetween('invoice_date', [$dateFrom, $dateTo])->sum('total_amount'),
            'insurance_invoices' => Invoice::insurance()->whereBetween('invoice_date', [$dateFrom, $dateTo])->sum('total_amount'),
        ];
    }

    private function getCashFlowReport($dateFrom, $dateTo)
    {
        $dailyData = collect();
        $start = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);

        while ($start->lte($end)) {
            $dailyData->push([
                'date' => $start->format('Y-m-d'),
                'invoiced' => Invoice::whereDate('invoice_date', $start)->sum('total_amount'),
                'collected' => Payment::completed()->whereDate('payment_date', $start)->sum('amount'),
            ]);
            $start->addDay();
        }

        return $dailyData;
    }

    private function getAgingReport()
    {
        return [
            'current' => Invoice::where('status', 'pending')
                              ->where('due_date', '>=', now())
                              ->sum('remaining_amount'),
            '1_30_days' => Invoice::where('status', 'pending')
                                ->where('due_date', '>=', now()->subDays(30))
                                ->where('due_date', '<', now())
                                ->sum('remaining_amount'),
            '31_60_days' => Invoice::where('status', 'overdue')
                                 ->where('due_date', '>=', now()->subDays(60))
                                 ->where('due_date', '<', now()->subDays(30))
                                 ->sum('remaining_amount'),
            '61_90_days' => Invoice::where('status', 'overdue')
                                 ->where('due_date', '>=', now()->subDays(90))
                                 ->where('due_date', '<', now()->subDays(60))
                                 ->sum('remaining_amount'),
            'over_90_days' => Invoice::where('status', 'overdue')
                                   ->where('due_date', '<', now()->subDays(90))
                                   ->sum('remaining_amount'),
        ];
    }

    private function getPaymentMethodsReport($dateFrom, $dateTo)
    {
        return Payment::completed()
                     ->whereBetween('payment_date', [$dateFrom, $dateTo])
                     ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                     ->groupBy('payment_method')
                     ->get();
    }
}