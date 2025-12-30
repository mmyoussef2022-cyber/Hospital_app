<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\InsuranceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $query = Payment::with(['invoice.patient', 'patient', 'insuranceCompany', 'processedBy'])
                       ->orderBy('payment_date', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('payment_number', 'like', "%{$search}%")
                  ->orWhere('reference_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('invoice', function($iq) use ($search) {
                      $iq->where('invoice_number', 'like', "%{$search}%");
                  });
            });
        }

        $payments = $query->paginate(20);

        // Get filter options
        $patients = Patient::select('id', 'name')->orderBy('name')->get();

        // Statistics
        $stats = [
            'total_payments' => Payment::where('status', 'completed')->count(),
            'total_amount' => Payment::where('status', 'completed')->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'pending_amount' => Payment::where('status', 'pending')->sum('amount'),
            'today_payments' => Payment::whereDate('payment_date', today())->where('status', 'completed')->count(),
            'today_amount' => Payment::whereDate('payment_date', today())->where('status', 'completed')->sum('amount'),
        ];

        return view('payments.index', compact('payments', 'patients', 'stats'));
    }

    /**
     * Show the form for creating a new payment
     */
    public function create(Request $request)
    {
        $invoice = null;
        if ($request->filled('invoice_id')) {
            $invoice = Invoice::with(['patient', 'doctor', 'items'])->find($request->invoice_id);
            if (!$invoice || $invoice->status === 'paid') {
                return redirect()->route('payments.index')
                               ->with('error', 'الفاتورة غير متاحة للدفع');
            }
        }

        $patients = Patient::select('id', 'name', 'phone')->orderBy('name')->get();
        $insuranceCompanies = InsuranceCompany::active()->get();

        return view('payments.create', compact('invoice', 'patients', 'insuranceCompanies'));
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'payment_method' => 'required|in:cash,card,bank_transfer,check,insurance,online',
            'amount' => 'required|numeric|min:0.01',
            'received_amount' => 'nullable|numeric|min:0',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string',
            'card_last_four' => 'nullable|string|size:4',
            'card_type' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'check_number' => 'nullable|string',
            'insurance_company_id' => 'nullable|exists:insurance_companies,id',
            'insurance_claim_number' => 'nullable|string',
        ]);

        $invoice = Invoice::find($request->invoice_id);
        
        // Validate payment amount
        if ($request->amount > $invoice->remaining_amount) {
            return back()->withInput()
                        ->with('error', 'مبلغ الدفع أكبر من المبلغ المتبقي');
        }

        DB::beginTransaction();
        try {
            $paymentData = [
                'payment_number' => $this->generatePaymentNumber(),
                'invoice_id' => $request->invoice_id,
                'patient_id' => $invoice->patient_id,
                'payment_method' => $request->payment_method,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'status' => $request->payment_method === 'cash' ? 'completed' : 'pending',
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'processed_by' => Auth::id()
            ];

            // Add method-specific data
            if ($request->payment_method === 'cash') {
                $paymentData['received_amount'] = $request->received_amount ?? $request->amount;
                $paymentData['change_amount'] = max(0, ($request->received_amount ?? $request->amount) - $request->amount);
            } elseif ($request->payment_method === 'card') {
                $paymentData['card_last_four'] = $request->card_last_four;
                $paymentData['card_type'] = $request->card_type;
            } elseif ($request->payment_method === 'bank_transfer') {
                $paymentData['bank_name'] = $request->bank_name;
            } elseif ($request->payment_method === 'check') {
                $paymentData['check_number'] = $request->check_number;
                $paymentData['bank_name'] = $request->bank_name;
            } elseif ($request->payment_method === 'insurance') {
                $paymentData['insurance_company_id'] = $request->insurance_company_id;
                $paymentData['insurance_claim_number'] = $request->insurance_claim_number;
                $paymentData['insurance_approval_date'] = $request->insurance_approval_date;
            }

            $payment = Payment::create($paymentData);

            // Auto-complete cash payments
            if ($request->payment_method === 'cash') {
                $payment->complete();
            }

            DB::commit();

            return redirect()->route('payments.show', $payment)
                           ->with('success', 'تم تسجيل الدفعة بنجاح');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تسجيل الدفعة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment)
    {
        $payment->load([
            'invoice.patient', 'invoice.doctor', 'invoice.items',
            'patient', 'insuranceCompany', 'processedBy', 'approvedBy'
        ]);

        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment
     */
    public function edit(Payment $payment)
    {
        // Only allow editing of pending payments
        if ($payment->status !== 'pending') {
            return redirect()->route('payments.show', $payment)
                           ->with('error', 'لا يمكن تعديل الدفعة بعد معالجتها');
        }

        $insuranceCompanies = InsuranceCompany::active()->get();

        return view('payments.edit', compact('payment', 'insuranceCompanies'));
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, Payment $payment)
    {
        // Only allow editing of pending payments
        if ($payment->status !== 'pending') {
            return redirect()->route('payments.show', $payment)
                           ->with('error', 'لا يمكن تعديل الدفعة بعد معالجتها');
        }

        $request->validate([
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        try {
            $payment->update([
                'payment_date' => $request->payment_date,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
            ]);

            $payment->addToAuditTrail('updated', 'Payment details updated');

            return redirect()->route('payments.show', $payment)
                           ->with('success', 'تم تحديث الدفعة بنجاح');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تحديث الدفعة');
        }
    }

    /**
     * Remove the specified payment
     */
    public function destroy(Payment $payment)
    {
        // Only allow deletion of pending payments
        if ($payment->status !== 'pending') {
            return redirect()->route('payments.index')
                           ->with('error', 'لا يمكن حذف الدفعة بعد معالجتها');
        }

        try {
            $payment->delete();
            return redirect()->route('payments.index')
                           ->with('success', 'تم حذف الدفعة بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('payments.index')
                           ->with('error', 'حدث خطأ أثناء حذف الدفعة');
        }
    }

    /**
     * Complete a pending payment
     */
    public function complete(Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return redirect()->route('payments.show', $payment)
                           ->with('error', 'الدفعة مكتملة بالفعل أو ملغية');
        }

        try {
            $payment->complete();
            return redirect()->route('payments.show', $payment)
                           ->with('success', 'تم إكمال الدفعة بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('payments.show', $payment)
                           ->with('error', 'حدث خطأ أثناء إكمال الدفعة');
        }
    }

    /**
     * Fail a pending payment
     */
    public function fail(Request $request, Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return redirect()->route('payments.show', $payment)
                           ->with('error', 'لا يمكن تعديل حالة هذه الدفعة');
        }

        try {
            $payment->fail($request->reason);
            return redirect()->route('payments.show', $payment)
                           ->with('success', 'تم تسجيل فشل الدفعة');
        } catch (\Exception $e) {
            return redirect()->route('payments.show', $payment)
                           ->with('error', 'حدث خطأ أثناء تحديث حالة الدفعة');
        }
    }

    /**
     * Cancel a payment
     */
    public function cancel(Request $request, Payment $payment)
    {
        if (in_array($payment->status, ['completed', 'cancelled'])) {
            return redirect()->route('payments.show', $payment)
                           ->with('error', 'لا يمكن إلغاء هذه الدفعة');
        }

        try {
            $payment->cancel($request->reason);
            return redirect()->route('payments.show', $payment)
                           ->with('success', 'تم إلغاء الدفعة بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('payments.show', $payment)
                           ->with('error', 'حدث خطأ أثناء إلغاء الدفعة');
        }
    }

    /**
     * Refund a completed payment
     */
    public function refund(Request $request, Payment $payment)
    {
        if ($payment->status !== 'completed') {
            return redirect()->route('payments.show', $payment)
                           ->with('error', 'لا يمكن استرداد هذه الدفعة');
        }

        $request->validate([
            'refund_amount' => 'required|numeric|min:0.01|max:' . $payment->amount,
            'reason' => 'required|string',
        ]);

        try {
            $payment->refund($request->refund_amount, $request->reason);
            return redirect()->route('payments.show', $payment)
                           ->with('success', 'تم استرداد الدفعة بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('payments.show', $payment)
                           ->with('error', 'حدث خطأ أثناء استرداد الدفعة');
        }
    }

    /**
     * Approve a payment
     */
    public function approve(Payment $payment)
    {
        try {
            $payment->approve();
            return redirect()->route('payments.show', $payment)
                           ->with('success', 'تم اعتماد الدفعة بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('payments.show', $payment)
                           ->with('error', 'حدث خطأ أثناء اعتماد الدفعة');
        }
    }

    /**
     * Clear a payment (mark as cleared)
     */
    public function clear(Payment $payment)
    {
        try {
            $payment->clear();
            return redirect()->route('payments.show', $payment)
                           ->with('success', 'تم تسوية الدفعة بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('payments.show', $payment)
                           ->with('error', 'حدث خطأ أثناء تسوية الدفعة');
        }
    }

    /**
     * Print payment receipt
     */
    public function receipt(Payment $payment)
    {
        $payment->load(['invoice.patient', 'invoice.items', 'processedBy']);
        return view('payments.receipt', compact('payment'));
    }

    /**
     * Dashboard with payment statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_payments' => Payment::where('status', 'completed')->count(),
            'total_amount' => Payment::where('status', 'completed')->sum('amount'),
            'pending_payments' => Payment::where('status', 'pending')->count(),
            'pending_amount' => Payment::where('status', 'pending')->sum('amount'),
            'failed_payments' => Payment::where('status', 'failed')->count(),
            'today_payments' => Payment::whereDate('payment_date', today())->where('status', 'completed')->count(),
            'today_amount' => Payment::whereDate('payment_date', today())->where('status', 'completed')->sum('amount'),
            'month_payments' => Payment::whereMonth('payment_date', now()->month)->where('status', 'completed')->count(),
            'month_amount' => Payment::whereMonth('payment_date', now()->month)->where('status', 'completed')->sum('amount'),
        ];

        // Payment method breakdown
        $paymentMethods = Payment::where('status', 'completed')
                                ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
                                ->groupBy('payment_method')
                                ->get();

        // Recent payments
        $recentPayments = Payment::with(['invoice.patient', 'processedBy'])
                                ->orderBy('payment_date', 'desc')
                                ->limit(10)
                                ->get();

        // Pending payments
        $pendingPayments = Payment::with(['invoice.patient'])
                                 ->where('status', 'pending')
                                 ->orderBy('payment_date')
                                 ->limit(10)
                                 ->get();

        // Daily chart data for current month
        $dailyData = Payment::where('status', 'completed')
                           ->whereMonth('payment_date', now()->month)
                           ->whereYear('payment_date', now()->year)
                           ->selectRaw('DAY(payment_date) as day, COUNT(*) as count, SUM(amount) as amount')
                           ->groupBy('day')
                           ->orderBy('day')
                           ->get();

        return view('payments.dashboard', compact(
            'stats', 'paymentMethods', 'recentPayments', 
            'pendingPayments', 'dailyData'
        ));
    }

    /**
     * Get invoice details for payment form
     */
    public function getInvoiceDetails(Invoice $invoice)
    {
        $invoice->load(['patient', 'doctor', 'items']);
        
        return response()->json([
            'invoice' => $invoice,
            'remaining_amount' => $invoice->remaining_amount,
            'can_pay' => $invoice->remaining_amount > 0 && !in_array($invoice->status, ['paid', 'cancelled'])
        ]);
    }

    /**
     * Generate payment number
     */
    private function generatePaymentNumber(): string
    {
        $prefix = 'PAY';
        $year = date('Y');
        $month = date('m');
        
        $lastPayment = Payment::whereYear('created_at', $year)
                            ->whereMonth('created_at', $month)
                            ->orderBy('id', 'desc')
                            ->first();
        
        $sequence = $lastPayment ? (int)substr($lastPayment->payment_number, -4) + 1 : 1;
        
        return sprintf('%s-%s%s-%04d', $prefix, $year, $month, $sequence);
    }
}