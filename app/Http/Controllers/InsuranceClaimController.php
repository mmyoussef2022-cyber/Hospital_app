<?php

namespace App\Http\Controllers;

use App\Models\InsuranceClaim;
use App\Models\InsuranceCompany;
use App\Models\InsurancePolicy;
use App\Models\Patient;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class InsuranceClaimController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of insurance claims.
     */
    public function index(Request $request): View
    {
        $query = InsuranceClaim::with(['insuranceCompany', 'insurancePolicy', 'patient', 'invoice', 'doctor'])
                              ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('company_id')) {
            $query->where('insurance_company_id', $request->company_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('service_date', [$request->date_from, $request->date_to]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('claim_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $claims = $query->paginate(15)->withQueryString();
        $companies = InsuranceCompany::active()->get();

        // Statistics
        $stats = [
            'total_claims' => InsuranceClaim::count(),
            'pending_claims' => InsuranceClaim::pending()->count(),
            'approved_claims' => InsuranceClaim::approved()->count(),
            'paid_claims' => InsuranceClaim::paid()->count(),
            'rejected_claims' => InsuranceClaim::rejected()->count(),
            'total_amount' => InsuranceClaim::sum('total_amount'),
            'approved_amount' => InsuranceClaim::approved()->sum('approved_amount'),
            'paid_amount' => InsuranceClaim::paid()->sum('paid_amount')
        ];

        return view('insurance.claims.index', compact('claims', 'companies', 'stats'));
    }

    /**
     * Show the form for creating a new insurance claim.
     */
    public function create(Request $request): View
    {
        $companies = InsuranceCompany::active()->get();
        $patients = Patient::active()->get();
        
        // If invoice_id is provided, pre-fill the form
        $invoice = null;
        if ($request->filled('invoice_id')) {
            $invoice = Invoice::with(['patient', 'items'])->find($request->invoice_id);
        }

        return view('insurance.claims.create', compact('companies', 'patients', 'invoice'));
    }

    /**
     * Store a newly created insurance claim.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'insurance_company_id' => 'required|exists:insurance_companies,id',
            'insurance_policy_id' => 'required|exists:insurance_policies,id',
            'patient_id' => 'required|exists:patients,id',
            'invoice_id' => 'required|exists:invoices,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'service_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'covered_amount' => 'required|numeric|min:0',
            'deductible_amount' => 'nullable|numeric|min:0',
            'co_payment_amount' => 'nullable|numeric|min:0',
            'patient_responsibility' => 'required|numeric|min:0',
            'priority' => 'required|in:normal,urgent,emergency',
            'diagnosis_code' => 'nullable|string|max:20',
            'diagnosis_description' => 'nullable|string',
            'services_provided' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        $validated['claim_date'] = now()->toDateString();
        $validated['status'] = 'draft';

        $claim = InsuranceClaim::create($validated);

        return redirect()->route('insurance-claims.show', $claim)
                        ->with('success', 'تم إنشاء المطالبة التأمينية بنجاح');
    }

    /**
     * Display the specified insurance claim.
     */
    public function show(InsuranceClaim $insuranceClaim): View
    {
        $insuranceClaim->load([
            'insuranceCompany', 
            'insurancePolicy', 
            'patient', 
            'invoice.items', 
            'doctor',
            'reviewedBy',
            'approvedBy'
        ]);

        return view('insurance.claims.show', compact('insuranceClaim'));
    }

    /**
     * Show the form for editing the specified insurance claim.
     */
    public function edit(InsuranceClaim $insuranceClaim): View
    {
        if (!$insuranceClaim->can_be_edited) {
            return redirect()->route('insurance-claims.show', $insuranceClaim)
                           ->with('error', 'لا يمكن تعديل هذه المطالبة في حالتها الحالية');
        }

        $companies = InsuranceCompany::active()->get();
        $patients = Patient::active()->get();

        return view('insurance.claims.edit', compact('insuranceClaim', 'companies', 'patients'));
    }

    /**
     * Update the specified insurance claim.
     */
    public function update(Request $request, InsuranceClaim $insuranceClaim): RedirectResponse
    {
        if (!$insuranceClaim->can_be_edited) {
            return redirect()->route('insurance-claims.show', $insuranceClaim)
                           ->with('error', 'لا يمكن تعديل هذه المطالبة في حالتها الحالية');
        }

        $validated = $request->validate([
            'insurance_company_id' => 'required|exists:insurance_companies,id',
            'insurance_policy_id' => 'required|exists:insurance_policies,id',
            'patient_id' => 'required|exists:patients,id',
            'invoice_id' => 'required|exists:invoices,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'service_date' => 'required|date',
            'total_amount' => 'required|numeric|min:0',
            'covered_amount' => 'required|numeric|min:0',
            'deductible_amount' => 'nullable|numeric|min:0',
            'co_payment_amount' => 'nullable|numeric|min:0',
            'patient_responsibility' => 'required|numeric|min:0',
            'priority' => 'required|in:normal,urgent,emergency',
            'diagnosis_code' => 'nullable|string|max:20',
            'diagnosis_description' => 'nullable|string',
            'services_provided' => 'nullable|array',
            'notes' => 'nullable|string'
        ]);

        $insuranceClaim->update($validated);

        return redirect()->route('insurance-claims.show', $insuranceClaim)
                        ->with('success', 'تم تحديث المطالبة التأمينية بنجاح');
    }

    /**
     * Remove the specified insurance claim.
     */
    public function destroy(InsuranceClaim $insuranceClaim): RedirectResponse
    {
        if (!in_array($insuranceClaim->status, ['draft', 'submitted'])) {
            return redirect()->back()
                           ->with('error', 'لا يمكن حذف المطالبة في حالتها الحالية');
        }

        $insuranceClaim->delete();

        return redirect()->route('insurance-claims.index')
                        ->with('success', 'تم حذف المطالبة التأمينية بنجاح');
    }

    /**
     * Submit claim for review.
     */
    public function submit(InsuranceClaim $insuranceClaim): JsonResponse
    {
        if (!$insuranceClaim->can_be_submitted) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تقديم هذه المطالبة في حالتها الحالية'
            ]);
        }

        try {
            $insuranceClaim->submit();

            return response()->json([
                'success' => true,
                'message' => 'تم تقديم المطالبة للمراجعة بنجاح',
                'status' => $insuranceClaim->status,
                'status_display' => $insuranceClaim->status_display
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تقديم المطالبة'
            ]);
        }
    }

    /**
     * Start reviewing a claim.
     */
    public function startReview(InsuranceClaim $insuranceClaim): JsonResponse
    {
        if (!$insuranceClaim->can_be_reviewed) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن مراجعة هذه المطالبة في حالتها الحالية'
            ]);
        }

        try {
            $insuranceClaim->startReview(Auth::user());

            return response()->json([
                'success' => true,
                'message' => 'تم بدء مراجعة المطالبة بنجاح',
                'status' => $insuranceClaim->status,
                'status_display' => $insuranceClaim->status_display
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء بدء المراجعة'
            ]);
        }
    }

    /**
     * Approve a claim.
     */
    public function approve(Request $request, InsuranceClaim $insuranceClaim): JsonResponse
    {
        if (!$insuranceClaim->can_be_approved) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن الموافقة على هذه المطالبة في حالتها الحالية'
            ]);
        }

        $validated = $request->validate([
            'approved_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        try {
            $insuranceClaim->approve(
                Auth::user(),
                $validated['approved_amount'] ?? null,
                $validated['notes'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'تم الموافقة على المطالبة بنجاح',
                'status' => $insuranceClaim->status,
                'status_display' => $insuranceClaim->status_display
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الموافقة على المطالبة'
            ]);
        }
    }

    /**
     * Reject a claim.
     */
    public function reject(Request $request, InsuranceClaim $insuranceClaim): JsonResponse
    {
        if (!$insuranceClaim->can_be_reviewed) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن رفض هذه المطالبة في حالتها الحالية'
            ]);
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string'
        ]);

        try {
            $insuranceClaim->reject(Auth::user(), $validated['rejection_reason']);

            return response()->json([
                'success' => true,
                'message' => 'تم رفض المطالبة',
                'status' => $insuranceClaim->status,
                'status_display' => $insuranceClaim->status_display
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء رفض المطالبة'
            ]);
        }
    }

    /**
     * Record payment for a claim.
     */
    public function recordPayment(Request $request, InsuranceClaim $insuranceClaim): JsonResponse
    {
        if (!$insuranceClaim->can_be_paid) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تسجيل دفعة لهذه المطالبة في حالتها الحالية'
            ]);
        }

        $validated = $request->validate([
            'payment_amount' => 'required|numeric|min:0.01',
            'payment_reference' => 'nullable|string'
        ]);

        try {
            $insuranceClaim->recordPayment(
                $validated['payment_amount'],
                $validated['payment_reference'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'تم تسجيل الدفعة بنجاح',
                'status' => $insuranceClaim->status,
                'status_display' => $insuranceClaim->status_display,
                'paid_amount' => $insuranceClaim->paid_amount,
                'remaining_amount' => $insuranceClaim->remaining_amount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الدفعة'
            ]);
        }
    }

    /**
     * Cancel a claim.
     */
    public function cancel(Request $request, InsuranceClaim $insuranceClaim): JsonResponse
    {
        if (!$insuranceClaim->can_be_cancelled) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إلغاء هذه المطالبة في حالتها الحالية'
            ]);
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string'
        ]);

        try {
            $insuranceClaim->cancel($validated['cancellation_reason'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'تم إلغاء المطالبة بنجاح',
                'status' => $insuranceClaim->status,
                'status_display' => $insuranceClaim->status_display
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إلغاء المطالبة'
            ]);
        }
    }

    /**
     * Create claim from invoice.
     */
    public function createFromInvoice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'insurance_policy_id' => 'required|exists:insurance_policies,id'
        ]);

        try {
            $invoice = Invoice::find($validated['invoice_id']);
            $policy = InsurancePolicy::find($validated['insurance_policy_id']);

            $claim = InsuranceClaim::createFromInvoice($invoice, $policy);

            if (!$claim) {
                return response()->json([
                    'success' => false,
                    'message' => 'لا توجد تغطية تأمينية لهذه الفاتورة'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء المطالبة التأمينية بنجاح',
                'claim_id' => $claim->id,
                'redirect_url' => route('insurance-claims.show', $claim)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء المطالبة'
            ]);
        }
    }

    /**
     * Get claims dashboard.
     */
    public function dashboard(): View
    {
        $stats = [
            'total_claims' => InsuranceClaim::count(),
            'pending_review' => InsuranceClaim::whereIn('status', ['submitted', 'under_review'])->count(),
            'approved_unpaid' => InsuranceClaim::approved()->where('paid_amount', '<', \DB::raw('COALESCE(approved_amount, covered_amount)'))->count(),
            'rejected_claims' => InsuranceClaim::rejected()->count(),
            'total_amount' => InsuranceClaim::sum('total_amount'),
            'approved_amount' => InsuranceClaim::approved()->sum('approved_amount'),
            'paid_amount' => InsuranceClaim::paid()->sum('paid_amount'),
            'pending_amount' => InsuranceClaim::approved()->sum(\DB::raw('COALESCE(approved_amount, covered_amount) - paid_amount'))
        ];

        // Recent claims
        $recentClaims = InsuranceClaim::with(['patient', 'insuranceCompany'])
                                    ->orderBy('created_at', 'desc')
                                    ->limit(10)
                                    ->get();

        // Claims by status
        $claimsByStatus = InsuranceClaim::selectRaw('status, COUNT(*) as count')
                                      ->groupBy('status')
                                      ->get()
                                      ->pluck('count', 'status');

        // Claims by company
        $claimsByCompany = InsuranceClaim::with('insuranceCompany')
                                       ->selectRaw('insurance_company_id, COUNT(*) as count, SUM(total_amount) as total_amount')
                                       ->groupBy('insurance_company_id')
                                       ->get();

        return view('insurance.claims.dashboard', compact(
            'stats', 'recentClaims', 'claimsByStatus', 'claimsByCompany'
        ));
    }

    /**
     * Bulk actions on claims.
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:submit,approve,reject,cancel',
            'claim_ids' => 'required|array',
            'claim_ids.*' => 'exists:insurance_claims,id',
            'reason' => 'nullable|string'
        ]);

        $claims = InsuranceClaim::whereIn('id', $validated['claim_ids'])->get();
        $successCount = 0;

        foreach ($claims as $claim) {
            try {
                switch ($validated['action']) {
                    case 'submit':
                        if ($claim->can_be_submitted) {
                            $claim->submit();
                            $successCount++;
                        }
                        break;
                    case 'approve':
                        if ($claim->can_be_approved) {
                            $claim->approve(Auth::user());
                            $successCount++;
                        }
                        break;
                    case 'reject':
                        if ($claim->can_be_reviewed) {
                            $claim->reject(Auth::user(), $validated['reason'] ?? 'رفض جماعي');
                            $successCount++;
                        }
                        break;
                    case 'cancel':
                        if ($claim->can_be_cancelled) {
                            $claim->cancel($validated['reason']);
                            $successCount++;
                        }
                        break;
                }
            } catch (\Exception $e) {
                // Continue with other claims
            }
        }

        return response()->json([
            'success' => true,
            'message' => "تم تنفيذ الإجراء على {$successCount} من أصل " . count($claims) . " مطالبة"
        ]);
    }
}