<?php

namespace App\Http\Controllers;

use App\Models\ShiftHandover;
use App\Models\Shift;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShiftHandoverController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of shift handovers
     */
    public function index(Request $request)
    {
        $query = ShiftHandover::with([
                    'fromShift.user',
                    'toShift.user',
                    'fromUser',
                    'toUser',
                    'department',
                    'cashRegister',
                    'witness'
                ])
                ->orderBy('handover_date', 'desc')
                ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('from_user_id')) {
            $query->where('from_user_id', $request->from_user_id);
        }

        if ($request->filled('to_user_id')) {
            $query->where('to_user_id', $request->to_user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('handover_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('handover_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('handover_number', 'like', "%{$search}%")
                  ->orWhereHas('fromUser', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('toUser', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $handovers = $query->paginate(20);

        // Get filter options
        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        // Statistics
        $stats = [
            'total_handovers' => ShiftHandover::count(),
            'pending_handovers' => ShiftHandover::where('status', 'pending')->count(),
            'completed_handovers' => ShiftHandover::where('status', 'completed')->count(),
            'disputed_handovers' => ShiftHandover::where('status', 'disputed')->count(),
            'today_handovers' => ShiftHandover::whereDate('handover_date', today())->count(),
            'overdue_handovers' => ShiftHandover::where('status', 'pending')
                                                ->where('handover_date', '<', today())
                                                ->count()
        ];

        return view('shift-handovers.index', compact('handovers', 'departments', 'users', 'stats'));
    }

    /**
     * Show the form for creating a new shift handover
     */
    public function create(Request $request)
    {
        $fromShifts = Shift::where('status', 'completed')
                          ->whereDoesntHave('handoverFrom')
                          ->with(['user', 'department'])
                          ->orderBy('shift_date', 'desc')
                          ->get();

        $toShifts = Shift::where('status', 'scheduled')
                        ->where('shift_date', '>=', today())
                        ->with(['user', 'department'])
                        ->orderBy('shift_date')
                        ->orderBy('scheduled_start')
                        ->get();

        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        // Pre-fill data if provided
        $preselected = [
            'from_shift_id' => $request->from_shift_id,
            'to_shift_id' => $request->to_shift_id,
            'department_id' => $request->department_id,
            'handover_date' => $request->handover_date ?? today()->format('Y-m-d')
        ];

        return view('shift-handovers.create', compact('fromShifts', 'toShifts', 'departments', 'users', 'preselected'));
    }

    /**
     * Store a newly created shift handover
     */
    public function store(Request $request)
    {
        $request->validate([
            'from_shift_id' => 'required|exists:shifts,id',
            'to_shift_id' => 'nullable|exists:shifts,id',
            'from_user_id' => 'required|exists:users,id',
            'to_user_id' => 'nullable|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'cash_register_id' => 'nullable|exists:cash_registers,id',
            'handover_date' => 'required|date',
            'cash_balance_handed_over' => 'required|numeric|min:0',
            'handover_notes' => 'nullable|string|max:1000',
            'items_handed_over' => 'nullable|array',
            'items_handed_over.*' => 'string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $handover = ShiftHandover::create([
                'handover_number' => ShiftHandover::generateHandoverNumber(),
                'from_shift_id' => $request->from_shift_id,
                'to_shift_id' => $request->to_shift_id,
                'from_user_id' => $request->from_user_id,
                'to_user_id' => $request->to_user_id,
                'department_id' => $request->department_id,
                'cash_register_id' => $request->cash_register_id,
                'handover_date' => $request->handover_date,
                'cash_balance_handed_over' => $request->cash_balance_handed_over,
                'handover_notes' => $request->handover_notes,
                'items_handed_over' => $request->items_handed_over ?? [],
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);

            DB::commit();

            return redirect()->route('shift-handovers.show', $handover)
                           ->with('success', 'تم إنشاء تسليم الوردية بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء إنشاء التسليم: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified shift handover
     */
    public function show(ShiftHandover $shiftHandover)
    {
        $shiftHandover->load([
            'fromShift.user',
            'fromShift.department',
            'fromShift.cashRegister',
            'toShift.user',
            'toShift.department',
            'fromUser',
            'toUser',
            'department',
            'cashRegister',
            'witness',
            'createdBy'
        ]);

        return view('shift-handovers.show', compact('shiftHandover'));
    }

    /**
     * Show the form for editing the specified shift handover
     */
    public function edit(ShiftHandover $shiftHandover)
    {
        if (!in_array($shiftHandover->status, ['pending', 'disputed'])) {
            return redirect()->route('shift-handovers.show', $shiftHandover)
                           ->with('error', 'لا يمكن تعديل هذا التسليم');
        }

        $fromShifts = Shift::where('status', 'completed')
                          ->with(['user', 'department'])
                          ->orderBy('shift_date', 'desc')
                          ->get();

        $toShifts = Shift::where('status', 'scheduled')
                        ->where('shift_date', '>=', today())
                        ->with(['user', 'department'])
                        ->orderBy('shift_date')
                        ->orderBy('scheduled_start')
                        ->get();

        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('shift-handovers.edit', compact('shiftHandover', 'fromShifts', 'toShifts', 'departments', 'users'));
    }

    /**
     * Update the specified shift handover
     */
    public function update(Request $request, ShiftHandover $shiftHandover)
    {
        if (!in_array($shiftHandover->status, ['pending', 'disputed'])) {
            return redirect()->route('shift-handovers.show', $shiftHandover)
                           ->with('error', 'لا يمكن تعديل هذا التسليم');
        }

        $request->validate([
            'to_shift_id' => 'nullable|exists:shifts,id',
            'to_user_id' => 'nullable|exists:users,id',
            'cash_balance_handed_over' => 'required|numeric|min:0',
            'handover_notes' => 'nullable|string|max:1000',
            'items_handed_over' => 'nullable|array',
            'items_handed_over.*' => 'string|max:255'
        ]);

        try {
            $shiftHandover->update([
                'to_shift_id' => $request->to_shift_id,
                'to_user_id' => $request->to_user_id,
                'cash_balance_handed_over' => $request->cash_balance_handed_over,
                'handover_notes' => $request->handover_notes,
                'items_handed_over' => $request->items_handed_over ?? [],
                'updated_by' => auth()->id()
            ]);

            return redirect()->route('shift-handovers.show', $shiftHandover)
                           ->with('success', 'تم تحديث التسليم بنجاح');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تحديث التسليم: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified shift handover
     */
    public function destroy(ShiftHandover $shiftHandover)
    {
        if ($shiftHandover->status === 'completed') {
            return back()->with('error', 'لا يمكن حذف التسليمات المكتملة');
        }

        try {
            $shiftHandover->delete();

            return redirect()->route('shift-handovers.index')
                           ->with('success', 'تم حذف التسليم بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حذف التسليم: ' . $e->getMessage());
        }
    }

    /**
     * Start a shift handover
     */
    public function start(Request $request, ShiftHandover $shiftHandover)
    {
        if ($shiftHandover->status !== 'pending') {
            return back()->with('error', 'لا يمكن بدء هذا التسليم');
        }

        try {
            $shiftHandover->update([
                'status' => 'in_progress',
                'handover_started_at' => now(),
                'started_by' => auth()->id()
            ]);

            return redirect()->route('shift-handovers.show', $shiftHandover)
                           ->with('success', 'تم بدء عملية التسليم');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Complete a shift handover
     */
    public function complete(Request $request, ShiftHandover $shiftHandover)
    {
        $request->validate([
            'cash_balance_received' => 'required|numeric|min:0',
            'completion_notes' => 'nullable|string|max:1000',
            'items_received' => 'nullable|array',
            'items_received.*' => 'string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $shiftHandover->update([
                'status' => 'completed',
                'cash_balance_received' => $request->cash_balance_received,
                'cash_difference' => $request->cash_balance_received - $shiftHandover->cash_balance_handed_over,
                'completion_notes' => $request->completion_notes,
                'items_received' => $request->items_received ?? [],
                'handover_completed_at' => now(),
                'completed_by' => auth()->id()
            ]);

            // Update cash register if applicable
            if ($shiftHandover->cashRegister) {
                $shiftHandover->cashRegister->update([
                    'current_balance' => $request->cash_balance_received
                ]);
            }

            DB::commit();

            return redirect()->route('shift-handovers.show', $shiftHandover)
                           ->with('success', 'تم إكمال التسليم بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Dispute a shift handover
     */
    public function dispute(Request $request, ShiftHandover $shiftHandover)
    {
        $request->validate([
            'dispute_reason' => 'required|string|max:1000',
            'disputed_amount' => 'nullable|numeric',
            'disputed_items' => 'nullable|array',
            'disputed_items.*' => 'string|max:255'
        ]);

        try {
            $shiftHandover->update([
                'status' => 'disputed',
                'dispute_reason' => $request->dispute_reason,
                'disputed_amount' => $request->disputed_amount,
                'disputed_items' => $request->disputed_items ?? [],
                'disputed_at' => now(),
                'disputed_by' => auth()->id()
            ]);

            return redirect()->route('shift-handovers.show', $shiftHandover)
                           ->with('success', 'تم تسجيل النزاع');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Resolve a disputed shift handover
     */
    public function resolve(Request $request, ShiftHandover $shiftHandover)
    {
        $request->validate([
            'resolution_notes' => 'required|string|max:1000',
            'final_cash_balance' => 'required|numeric|min:0',
            'resolution_action' => 'required|in:accept_as_is,adjust_balance,escalate'
        ]);

        try {
            $status = $request->resolution_action === 'escalate' ? 'escalated' : 'resolved';

            $shiftHandover->update([
                'status' => $status,
                'resolution_notes' => $request->resolution_notes,
                'final_cash_balance' => $request->final_cash_balance,
                'resolution_action' => $request->resolution_action,
                'resolved_at' => now(),
                'resolved_by' => auth()->id()
            ]);

            $message = $status === 'escalated' ? 'تم تصعيد النزاع' : 'تم حل النزاع';

            return redirect()->route('shift-handovers.show', $shiftHandover)
                           ->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Add a witness to the handover
     */
    public function addWitness(Request $request, ShiftHandover $shiftHandover)
    {
        $request->validate([
            'witness_id' => 'required|exists:users,id',
            'witness_notes' => 'nullable|string|max:500'
        ]);

        try {
            $shiftHandover->update([
                'witness_id' => $request->witness_id,
                'witness_notes' => $request->witness_notes,
                'witnessed_at' => now()
            ]);

            return redirect()->route('shift-handovers.show', $shiftHandover)
                           ->with('success', 'تم إضافة الشاهد بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Get handover statistics
     */
    public function getStatistics(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');

        $handovers = ShiftHandover::whereBetween('handover_date', [$startDate, $endDate])
                                 ->with(['department'])
                                 ->get();

        $stats = [
            'total_handovers' => $handovers->count(),
            'by_status' => $handovers->groupBy('status')->map->count(),
            'by_department' => $handovers->groupBy('department.name')->map->count(),
            'average_completion_time' => $handovers->where('status', 'completed')
                                                  ->avg(function($handover) {
                                                      return $handover->handover_started_at?->diffInMinutes($handover->handover_completed_at);
                                                  }),
            'handovers_with_discrepancy' => $handovers->where('cash_difference', '!=', 0)->count(),
            'total_cash_discrepancy' => $handovers->sum('cash_difference'),
            'disputed_handovers' => $handovers->where('status', 'disputed')->count()
        ];

        return response()->json($stats);
    }
}