<?php

namespace App\Http\Controllers;

use App\Models\InsurancePolicy;
use App\Models\InsuranceCompany;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class InsurancePolicyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of insurance policies.
     */
    public function index(Request $request): View
    {
        $query = InsurancePolicy::with(['insuranceCompany'])
                               ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('company_id')) {
            $query->where('insurance_company_id', $request->company_id);
        }

        if ($request->filled('policy_type')) {
            $query->where('policy_type', $request->policy_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('policy_name_ar', 'like', "%{$search}%")
                  ->orWhere('policy_name_en', 'like', "%{$search}%")
                  ->orWhere('policy_number', 'like', "%{$search}%");
            });
        }

        $policies = $query->paginate(15)->withQueryString();
        $companies = InsuranceCompany::active()->get();

        return view('insurance.policies.index', compact('policies', 'companies'));
    }

    /**
     * Show the form for creating a new insurance policy.
     */
    public function create(): View
    {
        $companies = InsuranceCompany::active()->get();
        
        return view('insurance.policies.create', compact('companies'));
    }

    /**
     * Store a newly created insurance policy.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'insurance_company_id' => 'required|exists:insurance_companies,id',
            'policy_number' => 'required|string|max:50|unique:insurance_policies',
            'policy_name_ar' => 'required|string|max:255',
            'policy_name_en' => 'required|string|max:255',
            'policy_type' => 'required|in:individual,family,group,corporate',
            'coverage_percentage' => 'required|numeric|min:0|max:100',
            'deductible_amount' => 'nullable|numeric|min:0',
            'max_coverage_per_year' => 'nullable|numeric|min:0',
            'max_coverage_per_visit' => 'nullable|numeric|min:0',
            'covered_services' => 'nullable|array',
            'excluded_services' => 'nullable|array',
            'requires_pre_approval' => 'boolean',
            'pre_approval_days' => 'nullable|integer|min:0',
            'co_payment_amount' => 'nullable|numeric|min:0',
            'co_payment_percentage' => 'nullable|numeric|min:0|max:100',
            'waiting_period_days' => 'nullable|integer|min:0',
            'effective_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:effective_date',
            'terms_and_conditions' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $policy = InsurancePolicy::create($validated);

        return redirect()->route('insurance-policies.show', $policy)
                        ->with('success', 'تم إنشاء بوليصة التأمين بنجاح');
    }

    /**
     * Display the specified insurance policy.
     */
    public function show(InsurancePolicy $insurancePolicy): View
    {
        $insurancePolicy->load(['insuranceCompany', 'claims', 'patientInsurances']);
        
        // Get statistics
        $stats = [
            'total_claims' => $insurancePolicy->getClaimsCount(),
            'total_claims_amount' => $insurancePolicy->getTotalClaimsAmount(),
            'approved_amount' => $insurancePolicy->getApprovedClaimsAmount(),
            'paid_amount' => $insurancePolicy->getPaidClaimsAmount(),
            'active_patients' => $insurancePolicy->getActivePatientCount()
        ];

        return view('insurance.policies.show', compact('insurancePolicy', 'stats'));
    }

    /**
     * Show the form for editing the specified insurance policy.
     */
    public function edit(InsurancePolicy $insurancePolicy): View
    {
        $companies = InsuranceCompany::active()->get();
        
        return view('insurance.policies.edit', compact('insurancePolicy', 'companies'));
    }

    /**
     * Update the specified insurance policy.
     */
    public function update(Request $request, InsurancePolicy $insurancePolicy): RedirectResponse
    {
        $validated = $request->validate([
            'insurance_company_id' => 'required|exists:insurance_companies,id',
            'policy_number' => 'required|string|max:50|unique:insurance_policies,policy_number,' . $insurancePolicy->id,
            'policy_name_ar' => 'required|string|max:255',
            'policy_name_en' => 'required|string|max:255',
            'policy_type' => 'required|in:individual,family,group,corporate',
            'coverage_percentage' => 'required|numeric|min:0|max:100',
            'deductible_amount' => 'nullable|numeric|min:0',
            'max_coverage_per_year' => 'nullable|numeric|min:0',
            'max_coverage_per_visit' => 'nullable|numeric|min:0',
            'covered_services' => 'nullable|array',
            'excluded_services' => 'nullable|array',
            'requires_pre_approval' => 'boolean',
            'pre_approval_days' => 'nullable|integer|min:0',
            'co_payment_amount' => 'nullable|numeric|min:0',
            'co_payment_percentage' => 'nullable|numeric|min:0|max:100',
            'waiting_period_days' => 'nullable|integer|min:0',
            'effective_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:effective_date',
            'terms_and_conditions' => 'nullable|string',
            'notes' => 'nullable|string'
        ]);

        $insurancePolicy->update($validated);

        return redirect()->route('insurance-policies.show', $insurancePolicy)
                        ->with('success', 'تم تحديث بوليصة التأمين بنجاح');
    }

    /**
     * Remove the specified insurance policy.
     */
    public function destroy(InsurancePolicy $insurancePolicy): RedirectResponse
    {
        // Check if policy has active claims or patients
        if ($insurancePolicy->claims()->count() > 0 || $insurancePolicy->patientInsurances()->count() > 0) {
            return redirect()->back()
                           ->with('error', 'لا يمكن حذف البوليصة لوجود مطالبات أو مرضى مرتبطين بها');
        }

        $insurancePolicy->delete();

        return redirect()->route('insurance-policies.index')
                        ->with('success', 'تم حذف بوليصة التأمين بنجاح');
    }

    /**
     * Toggle policy status.
     */
    public function toggleStatus(Request $request, InsurancePolicy $insurancePolicy): JsonResponse
    {
        $action = $request->input('action');
        $reason = $request->input('reason');

        try {
            switch ($action) {
                case 'activate':
                    $insurancePolicy->activate();
                    $message = 'تم تفعيل البوليصة بنجاح';
                    break;
                    
                case 'suspend':
                    $insurancePolicy->suspend($reason);
                    $message = 'تم تعليق البوليصة بنجاح';
                    break;
                    
                case 'cancel':
                    $insurancePolicy->cancel($reason);
                    $message = 'تم إلغاء البوليصة بنجاح';
                    break;
                    
                default:
                    return response()->json(['success' => false, 'message' => 'إجراء غير صحيح']);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $insurancePolicy->status,
                'status_display' => $insurancePolicy->status_display
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث حالة البوليصة'
            ]);
        }
    }

    /**
     * Calculate coverage for a given amount and service.
     */
    public function calculateCoverage(Request $request, InsurancePolicy $insurancePolicy): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'service_type' => 'nullable|string',
            'annual_limit_used' => 'nullable|numeric|min:0'
        ]);

        $options = [];
        if ($validated['annual_limit_used']) {
            $options['annual_limit_used'] = $validated['annual_limit_used'];
        }

        $coverage = $insurancePolicy->calculateCoverage(
            $validated['amount'],
            $validated['service_type'] ?? null,
            $options
        );

        return response()->json([
            'success' => true,
            'coverage' => $coverage
        ]);
    }

    /**
     * Get policies by company.
     */
    public function getByCompany(Request $request): JsonResponse
    {
        $companyId = $request->input('company_id');
        $policyType = $request->input('policy_type');

        $query = InsurancePolicy::where('insurance_company_id', $companyId)
                               ->active();

        if ($policyType) {
            $query->where('policy_type', $policyType);
        }

        $policies = $query->get(['id', 'policy_number', 'policy_name_ar', 'policy_name_en', 'policy_type']);

        return response()->json([
            'success' => true,
            'policies' => $policies
        ]);
    }

    /**
     * Get expiring policies.
     */
    public function expiringSoon(Request $request): View
    {
        $days = $request->input('days', 30);
        
        $policies = InsurancePolicy::expiringSoon($days)
                                  ->with(['insuranceCompany'])
                                  ->get();

        return view('insurance.policies.expiring', compact('policies', 'days'));
    }

    /**
     * Bulk actions on policies.
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:activate,suspend,cancel',
            'policy_ids' => 'required|array',
            'policy_ids.*' => 'exists:insurance_policies,id',
            'reason' => 'nullable|string'
        ]);

        $policies = InsurancePolicy::whereIn('id', $validated['policy_ids'])->get();
        $successCount = 0;

        foreach ($policies as $policy) {
            try {
                switch ($validated['action']) {
                    case 'activate':
                        $policy->activate();
                        break;
                    case 'suspend':
                        $policy->suspend($validated['reason']);
                        break;
                    case 'cancel':
                        $policy->cancel($validated['reason']);
                        break;
                }
                $successCount++;
            } catch (\Exception $e) {
                // Continue with other policies
            }
        }

        return response()->json([
            'success' => true,
            'message' => "تم تنفيذ الإجراء على {$successCount} من أصل " . count($policies) . " بوليصة"
        ]);
    }

    /**
     * Get policy statistics.
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_policies' => InsurancePolicy::count(),
            'active_policies' => InsurancePolicy::where('status', 'active')->count(),
            'suspended_policies' => InsurancePolicy::where('status', 'suspended')->count(),
            'expired_policies' => InsurancePolicy::where('status', 'expired')->count(),
            'expiring_soon' => InsurancePolicy::expiringSoon(30)->count(),
            'by_type' => [
                'individual' => InsurancePolicy::where('policy_type', 'individual')->count(),
                'family' => InsurancePolicy::where('policy_type', 'family')->count(),
                'group' => InsurancePolicy::where('policy_type', 'group')->count(),
                'corporate' => InsurancePolicy::where('policy_type', 'corporate')->count()
            ]
        ];

        return response()->json([
            'success' => true,
            'statistics' => $stats
        ]);
    }
}