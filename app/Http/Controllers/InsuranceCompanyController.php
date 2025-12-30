<?php

namespace App\Http\Controllers;

use App\Models\InsuranceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceCompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of insurance companies
     */
    public function index(Request $request)
    {
        $query = InsuranceCompany::orderBy('name_ar');

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)->where('contract_status', 'active');
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false)->orWhere('contract_status', '!=', 'active');
            }
        }

        if ($request->filled('contract_status')) {
            $query->where('contract_status', $request->contract_status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('commercial_registration', 'like', "%{$search}%");
            });
        }

        $companies = $query->paginate(20);

        // Statistics
        $stats = [
            'total_companies' => InsuranceCompany::count(),
            'active_companies' => InsuranceCompany::active()->count(),
            'expiring_soon' => InsuranceCompany::expiringSoon()->count(),
            'total_invoices' => InsuranceCompany::withCount('invoices')->get()->sum('invoices_count'),
        ];

        return view('insurance-companies.index', compact('companies', 'stats'));
    }

    /**
     * Show the form for creating a new insurance company
     */
    public function create()
    {
        return view('insurance-companies.create');
    }

    /**
     * Store a newly created insurance company
     */
    public function store(Request $request)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:insurance_companies,code',
            'commercial_registration' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'address_ar' => 'nullable|string',
            'address_en' => 'nullable|string',
            'default_coverage_percentage' => 'required|numeric|min:0|max:100',
            'max_coverage_amount' => 'nullable|numeric|min:0',
            'deductible_amount' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'required|integer|min:1|max:365',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'required|in:bank_transfer,check,online',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:34',
            'swift_code' => 'nullable|string|max:11',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'contract_status' => 'required|in:active,suspended,terminated',
            'covered_services' => 'nullable|array',
            'excluded_services' => 'nullable|array',
        ]);

        try {
            InsuranceCompany::create($request->all());

            return redirect()->route('insurance-companies.index')
                           ->with('success', 'تم إنشاء شركة التأمين بنجاح');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء إنشاء شركة التأمين: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified insurance company
     */
    public function show(InsuranceCompany $insuranceCompany)
    {
        $insuranceCompany->load(['invoices.patient', 'payments']);

        // Statistics
        $stats = [
            'total_invoices' => $insuranceCompany->invoices()->count(),
            'total_amount' => $insuranceCompany->getTotalInvoicesAmount(),
            'paid_amount' => $insuranceCompany->getTotalPaidAmount(),
            'pending_amount' => $insuranceCompany->getPendingAmount(),
            'monthly_stats' => $insuranceCompany->getMonthlyStatistics(),
        ];

        // Recent invoices
        $recentInvoices = $insuranceCompany->invoices()
                                          ->with(['patient', 'doctor'])
                                          ->orderBy('created_at', 'desc')
                                          ->limit(10)
                                          ->get();

        // Recent payments
        $recentPayments = $insuranceCompany->payments()
                                          ->with(['invoice.patient'])
                                          ->orderBy('payment_date', 'desc')
                                          ->limit(10)
                                          ->get();

        return view('insurance-companies.show', compact('insuranceCompany', 'stats', 'recentInvoices', 'recentPayments'));
    }

    /**
     * Show the form for editing the specified insurance company
     */
    public function edit(InsuranceCompany $insuranceCompany)
    {
        return view('insurance-companies.edit', compact('insuranceCompany'));
    }

    /**
     * Update the specified insurance company
     */
    public function update(Request $request, InsuranceCompany $insuranceCompany)
    {
        $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:insurance_companies,code,' . $insuranceCompany->id,
            'commercial_registration' => 'nullable|string|max:255',
            'tax_number' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'address_ar' => 'nullable|string',
            'address_en' => 'nullable|string',
            'default_coverage_percentage' => 'required|numeric|min:0|max:100',
            'max_coverage_amount' => 'nullable|numeric|min:0',
            'deductible_amount' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'required|integer|min:1|max:365',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'payment_method' => 'required|in:bank_transfer,check,online',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:255',
            'iban' => 'nullable|string|max:34',
            'swift_code' => 'nullable|string|max:11',
            'contract_start_date' => 'nullable|date',
            'contract_end_date' => 'nullable|date|after:contract_start_date',
            'contract_status' => 'required|in:active,suspended,terminated',
            'covered_services' => 'nullable|array',
            'excluded_services' => 'nullable|array',
        ]);

        try {
            $insuranceCompany->update($request->all());

            return redirect()->route('insurance-companies.show', $insuranceCompany)
                           ->with('success', 'تم تحديث شركة التأمين بنجاح');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تحديث شركة التأمين: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified insurance company
     */
    public function destroy(InsuranceCompany $insuranceCompany)
    {
        // Check if company has invoices
        if ($insuranceCompany->invoices()->count() > 0) {
            return redirect()->route('insurance-companies.index')
                           ->with('error', 'لا يمكن حذف شركة التأمين لوجود فواتير مرتبطة بها');
        }

        try {
            $insuranceCompany->delete();
            return redirect()->route('insurance-companies.index')
                           ->with('success', 'تم حذف شركة التأمين بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('insurance-companies.index')
                           ->with('error', 'حدث خطأ أثناء حذف شركة التأمين');
        }
    }

    /**
     * Suspend insurance company
     */
    public function suspend(Request $request, InsuranceCompany $insuranceCompany)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $insuranceCompany->suspend($request->reason);
            return redirect()->route('insurance-companies.show', $insuranceCompany)
                           ->with('success', 'تم تعليق شركة التأمين بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('insurance-companies.show', $insuranceCompany)
                           ->with('error', 'حدث خطأ أثناء تعليق شركة التأمين');
        }
    }

    /**
     * Activate insurance company
     */
    public function activate(InsuranceCompany $insuranceCompany)
    {
        try {
            $insuranceCompany->activate();
            return redirect()->route('insurance-companies.show', $insuranceCompany)
                           ->with('success', 'تم تفعيل شركة التأمين بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('insurance-companies.show', $insuranceCompany)
                           ->with('error', 'حدث خطأ أثناء تفعيل شركة التأمين');
        }
    }

    /**
     * Terminate insurance company contract
     */
    public function terminate(Request $request, InsuranceCompany $insuranceCompany)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $insuranceCompany->terminate($request->reason);
            return redirect()->route('insurance-companies.show', $insuranceCompany)
                           ->with('success', 'تم إنهاء عقد شركة التأمين بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('insurance-companies.show', $insuranceCompany)
                           ->with('error', 'حدث خطأ أثناء إنهاء عقد شركة التأمين');
        }
    }

    /**
     * Renew insurance company contract
     */
    public function renew(Request $request, InsuranceCompany $insuranceCompany)
    {
        $request->validate([
            'contract_end_date' => 'required|date|after:today',
            'default_coverage_percentage' => 'nullable|numeric|min:0|max:100',
            'max_coverage_amount' => 'nullable|numeric|min:0',
            'deductible_amount' => 'nullable|numeric|min:0',
            'payment_terms_days' => 'nullable|integer|min:1|max:365',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $terms = $request->only([
                'default_coverage_percentage',
                'max_coverage_amount',
                'deductible_amount',
                'payment_terms_days',
                'discount_percentage'
            ]);

            $insuranceCompany->renewContract($request->contract_end_date, array_filter($terms));

            return redirect()->route('insurance-companies.show', $insuranceCompany)
                           ->with('success', 'تم تجديد عقد شركة التأمين بنجاح');
        } catch (\Exception $e) {
            return redirect()->route('insurance-companies.show', $insuranceCompany)
                           ->with('error', 'حدث خطأ أثناء تجديد عقد شركة التأمين');
        }
    }

    /**
     * Calculate coverage for a service
     */
    public function calculateCoverage(Request $request, InsuranceCompany $insuranceCompany)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'service_type' => 'nullable|string'
        ]);

        try {
            $coverage = $insuranceCompany->calculateCoverage(
                $request->amount,
                $request->service_type
            );

            return response()->json([
                'success' => true,
                'coverage' => $coverage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حساب التغطية'
            ], 500);
        }
    }

    /**
     * Get companies expiring soon
     */
    public function expiringSoon()
    {
        $companies = InsuranceCompany::expiringSoon(30)
                                   ->orderBy('contract_end_date')
                                   ->get();

        return view('insurance-companies.expiring-soon', compact('companies'));
    }

    /**
     * Dashboard with insurance statistics
     */
    public function dashboard()
    {
        $stats = [
            'total_companies' => InsuranceCompany::count(),
            'active_companies' => InsuranceCompany::active()->count(),
            'suspended_companies' => InsuranceCompany::where('contract_status', 'suspended')->count(),
            'terminated_companies' => InsuranceCompany::where('contract_status', 'terminated')->count(),
            'expiring_soon' => InsuranceCompany::expiringSoon(30)->count(),
            'total_invoices' => DB::table('invoices')->where('type', 'insurance')->count(),
            'total_amount' => DB::table('invoices')->where('type', 'insurance')->sum('total_amount'),
            'pending_amount' => DB::table('invoices')->where('type', 'insurance')->where('status', '!=', 'paid')->sum('remaining_amount'),
        ];

        // Top companies by volume
        $topCompanies = InsuranceCompany::withCount('invoices')
                                      ->with(['invoices' => function($q) {
                                          $q->selectRaw('insurance_company_id, SUM(total_amount) as total_amount')
                                            ->groupBy('insurance_company_id');
                                      }])
                                      ->orderBy('invoices_count', 'desc')
                                      ->limit(10)
                                      ->get();

        // Companies expiring soon
        $expiringSoon = InsuranceCompany::expiringSoon(30)
                                      ->orderBy('contract_end_date')
                                      ->limit(5)
                                      ->get();

        // Monthly statistics
        $monthlyStats = DB::table('invoices')
                         ->where('type', 'insurance')
                         ->whereYear('invoice_date', now()->year)
                         ->selectRaw('MONTH(invoice_date) as month, COUNT(*) as count, SUM(total_amount) as amount')
                         ->groupBy('month')
                         ->orderBy('month')
                         ->get();

        return view('insurance-companies.dashboard', compact('stats', 'topCompanies', 'expiringSoon', 'monthlyStats'));
    }

    /**
     * Get active companies for AJAX
     */
    public function getActive()
    {
        $companies = InsuranceCompany::active()
                                   ->select('id', 'name_ar', 'name_en', 'code', 'default_coverage_percentage')
                                   ->orderBy('name_ar')
                                   ->get();

        return response()->json($companies);
    }
}