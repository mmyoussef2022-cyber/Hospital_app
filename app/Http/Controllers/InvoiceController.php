<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\User;
use App\Models\Appointment;
use App\Models\InsuranceCompany;
use App\Models\LabTest;
use App\Models\RadiologyStudy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['patient', 'doctor', 'insuranceCompany', 'createdBy'])
                       ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
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
                      $pq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        $invoices = $query->paginate(20);

        // Get filter options
        $patients = Patient::select('id', 'name')->orderBy('name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->select('id', 'name')->orderBy('name')->get();

        // Statistics
        $stats = [
            'total_invoices' => Invoice::count(),
            'pending_amount' => Invoice::where('status', 'pending')->sum('remaining_amount'),
            'overdue_amount' => Invoice::where('status', 'overdue')->sum('remaining_amount'),
            'paid_today' => Invoice::whereDate('paid_at', today())->sum('total_amount'),
        ];

        return view('invoices.index', compact('invoices', 'patients', 'doctors', 'stats'));
    }

    /**
     * Show the form for creating a new invoice
     */
    public function create(Request $request)
    {
        $patients = Patient::select('id', 'name', 'phone')->orderBy('name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->select('id', 'name')->orderBy('name')->get();
        
        $insuranceCompanies = InsuranceCompany::active()->get();
        $labTests = LabTest::where('is_active', true)->get();
        $radiologyStudies = RadiologyStudy::where('is_active', true)->get();

        // Pre-fill from appointment if provided
        $appointment = null;
        if ($request->filled('appointment_id')) {
            $appointment = Appointment::with(['patient', 'doctor'])->find($request->appointment_id);
        }

        return view('invoices.create', compact(
            'patients', 'doctors', 'insuranceCompanies', 
            'labTests', 'radiologyStudies', 'appointment'
        ));
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:users,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'type' => 'required|in:cash,credit,insurance',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'insurance_company_id' => 'nullable|exists:insurance_companies,id',
            'insurance_policy_number' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|string',
            'items.*.item_name' => 'required|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.discount_percentage' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::beginTransaction();
        try {
            // Create invoice
            $invoice = Invoice::create([
                'invoice_number' => (new Invoice)->generateInvoiceNumber(),
                'type' => $request->type,
                'status' => 'draft',
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'appointment_id' => $request->appointment_id,
                'insurance_company_id' => $request->insurance_company_id,
                'insurance_policy_number' => $request->insurance_policy_number,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'notes' => $request->notes,
                'payment_terms' => $request->payment_terms,
                'created_by' => Auth::id()
            ]);

            // Add invoice items
            foreach ($request->items as $itemData) {
                $item = new InvoiceItem([
                    'item_type' => $itemData['item_type'],
                    'item_name' => $itemData['item_name'],
                    'item_description' => $itemData['item_description'] ?? null,
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                    'discount_percentage' => $itemData['discount_percentage'] ?? 0,
                    'tax_percentage' => $itemData['tax_percentage'] ?? 15,
                ]);

                // Set itemable relationship if provided
                if (isset($itemData['itemable_type']) && isset($itemData['itemable_id'])) {
                    $item->itemable_type = $itemData['itemable_type'];
                    $item->itemable_id = $itemData['itemable_id'];
                }

                $invoice->items()->save($item);
            }

            // Calculate totals
            $invoice->calculateTotals();
            $invoice->save();

            // Update status to pending if not draft
            if ($request->has('finalize')) {
                $invoice->status = 'pending';
                $invoice->addToAuditTrail('finalized', 'Invoice finalized and ready for payment');
                $invoice->save();
            }

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                           ->with('success', 'تم إنشاء الفاتورة بنجاح');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء إنشاء الفاتورة: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'patient', 'doctor', 'appointment', 'insuranceCompany',
            'items.itemable', 'payments.processedBy', 'createdBy', 'updatedBy'
        ]);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified invoice
     */
    public function edit(Invoice $invoice)
    {
        // Only allow editing of draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                           ->with('error', 'لا يمكن تعديل الفاتورة بعد اعتمادها');
        }

        $invoice->load(['items']);
        
        $patients = Patient::select('id', 'name', 'phone')->orderBy('name')->get();
        $doctors = User::whereHas('roles', function($q) {
            $q->where('name', 'doctor');
        })->select('id', 'name')->orderBy('name')->get();
        
        $insuranceCompanies = InsuranceCompany::active()->get();
        $labTests = LabTest::where('is_active', true)->get();
        $radiologyStudies = RadiologyStudy::where('is_active', true)->get();

        return view('invoices.edit', compact(
            'invoice', 'patients', 'doctors', 'insuranceCompanies',
            'labTests', 'radiologyStudies'
        ));
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, Invoice $invoice)
    {
        // Only allow editing of draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                           ->with('error', 'لا يمكن تعديل الفاتورة بعد اعتمادها');
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:users,id',
            'type' => 'required|in:cash,credit,insurance',
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'insurance_company_id' => 'nullable|exists:insurance_companies,id',
            'items' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            // Update invoice
            $invoice->update([
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'type' => $request->type,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'insurance_company_id' => $request->insurance_company_id,
                'insurance_policy_number' => $request->insurance_policy_number,
                'notes' => $request->notes,
                'payment_terms' => $request->payment_terms,
                'updated_by' => Auth::id()
            ]);

            // Delete existing items and recreate
            $invoice->items()->delete();

            foreach ($request->items as $itemData) {
                $item = new InvoiceItem([
                    'item_type' => $itemData['item_type'],
                    'item_name' => $itemData['item_name'],
                    'item_description' => $itemData['item_description'] ?? null,
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                    'discount_percentage' => $itemData['discount_percentage'] ?? 0,
                    'tax_percentage' => $itemData['tax_percentage'] ?? 15,
                ]);

                if (isset($itemData['itemable_type']) && isset($itemData['itemable_id'])) {
                    $item->itemable_type = $itemData['itemable_type'];
                    $item->itemable_id = $itemData['itemable_id'];
                }

                $invoice->items()->save($item);
            }

            // Recalculate totals
            $invoice->calculateTotals();
            $invoice->addToAuditTrail('updated', 'Invoice updated');
            $invoice->save();

            DB::commit();

            return redirect()->route('invoices.show', $invoice)
                           ->with('success', 'تم تحديث الفاتورة بنجاح');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تحديث الفاتورة: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(Invoice $invoice)
    {
        // Only allow deletion of draft invoices
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.index')
                           ->with('error', 'لا يمكن حذف الفاتورة بعد اعتمادها');
        }

        try {
            $invoice->delete();
            return redirect()->route('invoices.index')
                           ->with('success', 'تم حذف الفاتورة بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('invoices.index')
                           ->with('error', 'حدث خطأ أثناء حذف الفاتورة');
        }
    }

    /**
     * Finalize invoice (change from draft to pending)
     */
    public function finalize(Invoice $invoice)
    {
        if ($invoice->status !== 'draft') {
            return redirect()->route('invoices.show', $invoice)
                           ->with('error', 'الفاتورة معتمدة بالفعل');
        }

        $invoice->status = 'pending';
        $invoice->addToAuditTrail('finalized', 'Invoice finalized and ready for payment');
        $invoice->save();

        return redirect()->route('invoices.show', $invoice)
                       ->with('success', 'تم اعتماد الفاتورة بنجاح');
    }

    /**
     * Cancel invoice
     */
    public function cancel(Request $request, Invoice $invoice)
    {
        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            return redirect()->route('invoices.show', $invoice)
                           ->with('error', 'لا يمكن إلغاء هذه الفاتورة');
        }

        $invoice->cancel($request->reason);

        return redirect()->route('invoices.show', $invoice)
                       ->with('success', 'تم إلغاء الفاتورة بنجاح');
    }

    /**
     * Print invoice
     */
    public function print(Invoice $invoice)
    {
        $invoice->load(['patient', 'doctor', 'items', 'payments']);
        return view('invoices.print', compact('invoice'));
    }

    /**
     * Dashboard with statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_invoices' => Invoice::count(),
            'pending_invoices' => Invoice::where('status', 'pending')->count(),
            'overdue_invoices' => Invoice::where('status', 'overdue')->count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'total_amount' => Invoice::sum('total_amount'),
            'pending_amount' => Invoice::where('status', 'pending')->sum('remaining_amount'),
            'overdue_amount' => Invoice::where('status', 'overdue')->sum('remaining_amount'),
            'paid_amount' => Invoice::where('status', 'paid')->sum('total_amount'),
            'today_invoices' => Invoice::whereDate('invoice_date', today())->count(),
            'today_amount' => Invoice::whereDate('invoice_date', today())->sum('total_amount'),
            'month_invoices' => Invoice::whereMonth('invoice_date', now()->month)->count(),
            'month_amount' => Invoice::whereMonth('invoice_date', now()->month)->sum('total_amount'),
        ];

        // Recent invoices
        $recentInvoices = Invoice::with(['patient', 'doctor'])
                                ->orderBy('created_at', 'desc')
                                ->limit(10)
                                ->get();

        // Overdue invoices
        $overdueInvoices = Invoice::with(['patient'])
                                 ->where('status', 'overdue')
                                 ->orWhere(function($q) {
                                     $q->where('status', 'pending')
                                       ->where('due_date', '<', now());
                                 })
                                 ->orderBy('due_date')
                                 ->limit(10)
                                 ->get();

        // Monthly chart data
        $monthlyData = Invoice::selectRaw('MONTH(invoice_date) as month, COUNT(*) as count, SUM(total_amount) as amount')
                             ->whereYear('invoice_date', now()->year)
                             ->groupBy('month')
                             ->orderBy('month')
                             ->get();

        return view('invoices.dashboard', compact('stats', 'recentInvoices', 'overdueInvoices', 'monthlyData'));
    }

    /**
     * Get invoice items for AJAX
     */
    public function getItems(Invoice $invoice)
    {
        $items = $invoice->items()->with('itemable')->get();
        return response()->json($items);
    }

    /**
     * Mark overdue invoices
     */
    public function markOverdue()
    {
        $overdueCount = Invoice::where('status', 'pending')
                              ->where('due_date', '<', now())
                              ->update(['status' => 'overdue']);

        return response()->json([
            'success' => true,
            'message' => "تم تحديث {$overdueCount} فاتورة متأخرة"
        ]);
    }
}