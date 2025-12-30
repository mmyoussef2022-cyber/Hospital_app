<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CashRegisterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of cash registers
     */
    public function index(Request $request)
    {
        $query = CashRegister::with(['department', 'lastReconciledBy'])
                            ->orderBy('register_number');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('needs_reconciliation')) {
            if ($request->needs_reconciliation === '1') {
                $query->where(function($q) {
                    $q->where('status', 'reconciling')
                      ->orWhere('reconciliation_difference', '!=', 0);
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('register_number', 'like', "%{$search}%")
                  ->orWhere('register_name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $registers = $query->paginate(20);

        // Get filter options
        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();

        // Statistics
        $stats = [
            'total_registers' => CashRegister::count(),
            'active_registers' => CashRegister::active()->count(),
            'inactive_registers' => CashRegister::inactive()->count(),
            'registers_needing_reconciliation' => CashRegister::needsReconciliation()->count(),
            'total_balance' => CashRegister::active()->sum('current_balance'),
            'total_discrepancy' => CashRegister::sum('reconciliation_difference')
        ];

        return view('cash-registers.index', compact('registers', 'departments', 'stats'));
    }

    /**
     * Show the form for creating a new cash register
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();
        
        return view('cash-registers.create', compact('departments'));
    }

    /**
     * Store a newly created cash register
     */
    public function store(Request $request)
    {
        $request->validate([
            'register_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'location' => 'nullable|string|max:255',
            'opening_balance' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $register = CashRegister::create([
                'register_name' => $request->register_name,
                'department_id' => $request->department_id,
                'location' => $request->location,
                'opening_balance' => $request->opening_balance,
                'current_balance' => $request->opening_balance,
                'expected_balance' => $request->opening_balance,
                'status' => 'active'
            ]);

            DB::commit();

            return redirect()->route('cash-registers.show', $register)
                           ->with('success', 'تم إنشاء الصندوق بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء إنشاء الصندوق: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified cash register
     */
    public function show(CashRegister $cashRegister)
    {
        $cashRegister->load([
            'department',
            'lastReconciledBy',
            'shifts' => function($q) {
                $q->latest()->limit(10);
            },
            'transactions' => function($q) {
                $q->latest()->limit(20);
            }
        ]);

        // Get today's statistics
        $todayStats = [
            'transactions_count' => $cashRegister->getTodayTransactionCount(),
            'revenue' => $cashRegister->getTodayRevenue(),
            'current_shift' => $cashRegister->getCurrentShift()
        ];

        return view('cash-registers.show', compact('cashRegister', 'todayStats'));
    }

    /**
     * Show the form for editing the specified cash register
     */
    public function edit(CashRegister $cashRegister)
    {
        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();
        
        return view('cash-registers.edit', compact('cashRegister', 'departments'));
    }

    /**
     * Update the specified cash register
     */
    public function update(Request $request, CashRegister $cashRegister)
    {
        $request->validate([
            'register_name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'location' => 'nullable|string|max:255'
        ]);

        try {
            $cashRegister->update($request->only([
                'register_name', 'department_id', 'location'
            ]));

            return redirect()->route('cash-registers.show', $cashRegister)
                           ->with('success', 'تم تحديث الصندوق بنجاح');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تحديث الصندوق: ' . $e->getMessage());
        }
    }

    /**
     * Open cash register
     */
    public function open(Request $request, CashRegister $cashRegister)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0'
        ]);

        try {
            $cashRegister->openRegister($request->opening_balance, auth()->user());

            return redirect()->route('cash-registers.show', $cashRegister)
                           ->with('success', 'تم فتح الصندوق بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء فتح الصندوق: ' . $e->getMessage());
        }
    }

    /**
     * Close cash register
     */
    public function close(Request $request, CashRegister $cashRegister)
    {
        $request->validate([
            'closing_balance' => 'required|numeric|min:0'
        ]);

        try {
            $cashRegister->closeRegister($request->closing_balance, auth()->user());

            return redirect()->route('cash-registers.show', $cashRegister)
                           ->with('success', 'تم إغلاق الصندوق بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء إغلاق الصندوق: ' . $e->getMessage());
        }
    }

    /**
     * Reconcile cash register
     */
    public function reconcile(Request $request, CashRegister $cashRegister)
    {
        $request->validate([
            'actual_balance' => 'required|numeric|min:0',
            'reconciliation_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $cashRegister->reconcile(
                $request->actual_balance,
                auth()->user(),
                $request->reconciliation_notes
            );

            return redirect()->route('cash-registers.show', $cashRegister)
                           ->with('success', 'تم تسوية الصندوق بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تسوية الصندوق: ' . $e->getMessage());
        }
    }

    /**
     * Adjust cash register balance
     */
    public function adjust(Request $request, CashRegister $cashRegister)
    {
        $request->validate([
            'adjustment_amount' => 'required|numeric',
            'adjustment_reason' => 'required|string|max:500'
        ]);

        try {
            $cashRegister->adjustBalance(
                $request->adjustment_amount,
                $request->adjustment_reason,
                auth()->user()
            );

            return redirect()->route('cash-registers.show', $cashRegister)
                           ->with('success', 'تم تعديل رصيد الصندوق بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تعديل الرصيد: ' . $e->getMessage());
        }
    }

    /**
     * Set cash register to maintenance
     */
    public function setMaintenance(Request $request, CashRegister $cashRegister)
    {
        $request->validate([
            'maintenance_reason' => 'nullable|string|max:500'
        ]);

        try {
            $cashRegister->setMaintenance($request->maintenance_reason, auth()->user());

            return redirect()->route('cash-registers.show', $cashRegister)
                           ->with('success', 'تم تعيين الصندوق للصيانة');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Activate cash register
     */
    public function activate(CashRegister $cashRegister)
    {
        try {
            $cashRegister->activate(auth()->user());

            return redirect()->route('cash-registers.show', $cashRegister)
                           ->with('success', 'تم تفعيل الصندوق بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تفعيل الصندوق: ' . $e->getMessage());
        }
    }

    /**
     * Deactivate cash register
     */
    public function deactivate(Request $request, CashRegister $cashRegister)
    {
        $request->validate([
            'deactivation_reason' => 'nullable|string|max:500'
        ]);

        try {
            $cashRegister->deactivate($request->deactivation_reason, auth()->user());

            return redirect()->route('cash-registers.show', $cashRegister)
                           ->with('success', 'تم إلغاء تفعيل الصندوق');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Show reconciliation report
     */
    public function reconciliationReport(Request $request)
    {
        $date = $request->date ? Carbon::parse($request->date) : today();
        
        $report = CashRegister::getDailyReconciliationReport($date);
        
        return view('cash-registers.reconciliation-report', compact('report', 'date'));
    }

    /**
     * Show dashboard
     */
    public function dashboard()
    {
        $stats = [
            'total_registers' => CashRegister::count(),
            'active_registers' => CashRegister::active()->count(),
            'registers_needing_reconciliation' => CashRegister::needsReconciliation()->count(),
            'total_balance' => CashRegister::active()->sum('current_balance'),
            'total_discrepancy' => CashRegister::sum('reconciliation_difference')
        ];

        // Get registers by status
        $registersByStatus = CashRegister::with('department')
                                       ->get()
                                       ->groupBy('status');

        // Get recent reconciliations
        $recentReconciliations = CashRegister::with(['department', 'lastReconciledBy'])
                                           ->whereNotNull('last_reconciled_at')
                                           ->orderBy('last_reconciled_at', 'desc')
                                           ->limit(10)
                                           ->get();

        // Get registers with discrepancies
        $discrepancies = CashRegister::with('department')
                                   ->where('reconciliation_difference', '!=', 0)
                                   ->orderBy('reconciliation_difference', 'desc')
                                   ->get();

        return view('cash-registers.dashboard', compact(
            'stats', 'registersByStatus', 'recentReconciliations', 'discrepancies'
        ));
    }

    /**
     * Get available registers for department
     */
    public function getAvailableRegisters(Request $request)
    {
        $departmentId = $request->department_id;
        
        $registers = CashRegister::getAvailableRegisters($departmentId);
        
        return response()->json($registers->map(function($register) {
            return [
                'id' => $register->id,
                'number' => $register->register_number,
                'name' => $register->register_name,
                'location' => $register->location,
                'current_balance' => $register->current_balance
            ];
        }));
    }
}