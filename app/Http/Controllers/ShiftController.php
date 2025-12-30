<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\CashRegister;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShiftController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of shifts
     */
    public function index(Request $request)
    {
        $query = Shift::with(['user', 'department', 'cashRegister', 'supervisor'])
                     ->orderBy('shift_date', 'desc')
                     ->orderBy('scheduled_start', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('shift_type')) {
            $query->where('shift_type', $request->shift_type);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('shift_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('shift_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('shift_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $shifts = $query->paginate(20);

        // Get filter options
        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        // Statistics
        $stats = [
            'total_shifts' => Shift::count(),
            'active_shifts' => Shift::active()->count(),
            'completed_shifts' => Shift::completed()->count(),
            'today_shifts' => Shift::today()->count(),
            'overdue_shifts' => Shift::getOverdueShifts()->count(),
            'total_revenue_today' => Shift::today()->sum('total_revenue'),
            'shifts_with_discrepancy' => Shift::where('cash_difference', '!=', 0)->count()
        ];

        return view('shifts.index', compact('shifts', 'departments', 'users', 'stats'));
    }

    /**
     * Show the form for creating a new shift
     */
    public function create(Request $request)
    {
        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $supervisors = User::role(['Supervisor', 'Manager', 'Admin'])->orderBy('name')->get();
        
        // Get available cash registers
        $cashRegisters = CashRegister::active()->get();

        // Pre-fill data if provided
        $preselected = [
            'department_id' => $request->department_id,
            'user_id' => $request->user_id,
            'shift_date' => $request->shift_date ?? today()->format('Y-m-d'),
            'shift_type' => $request->shift_type ?? 'morning'
        ];

        return view('shifts.create', compact('departments', 'users', 'supervisors', 'cashRegisters', 'preselected'));
    }

    /**
     * Store a newly created shift
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'shift_type' => 'required|in:morning,afternoon,evening,night,emergency',
            'shift_date' => 'required|date|after_or_equal:today',
            'scheduled_start' => 'required|date_format:H:i',
            'scheduled_end' => 'required|date_format:H:i|after:scheduled_start',
            'cash_register_id' => 'nullable|exists:cash_registers,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'shift_notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $shift = Shift::createScheduledShift([
                'user_id' => $request->user_id,
                'department_id' => $request->department_id,
                'cash_register_id' => $request->cash_register_id,
                'supervisor_id' => $request->supervisor_id,
                'shift_type' => $request->shift_type,
                'shift_date' => $request->shift_date,
                'scheduled_start' => $request->scheduled_start,
                'scheduled_end' => $request->scheduled_end,
                'shift_notes' => $request->shift_notes
            ]);

            DB::commit();

            return redirect()->route('shifts.show', $shift)
                           ->with('success', 'تم إنشاء الوردية بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء إنشاء الوردية: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified shift
     */
    public function show(Shift $shift)
    {
        $shift->load([
            'user', 
            'department', 
            'cashRegister', 
            'supervisor',
            'transactions.patient',
            'report',
            'productivity',
            'handoverFrom',
            'handoverTo'
        ]);

        // Get shift statistics
        $transactionSummary = $shift->transactions()->count() > 0 ? 
                            \App\Models\ShiftTransaction::getShiftSummary($shift->id) : null;

        return view('shifts.show', compact('shift', 'transactionSummary'));
    }

    /**
     * Show the form for editing the specified shift
     */
    public function edit(Shift $shift)
    {
        if (!in_array($shift->status, ['scheduled', 'active'])) {
            return redirect()->route('shifts.show', $shift)
                           ->with('error', 'لا يمكن تعديل هذه الوردية');
        }

        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();
        $supervisors = User::role(['Supervisor', 'Manager', 'Admin'])->orderBy('name')->get();
        $cashRegisters = CashRegister::active()->get();

        return view('shifts.edit', compact('shift', 'departments', 'users', 'supervisors', 'cashRegisters'));
    }

    /**
     * Update the specified shift
     */
    public function update(Request $request, Shift $shift)
    {
        if (!in_array($shift->status, ['scheduled', 'active'])) {
            return redirect()->route('shifts.show', $shift)
                           ->with('error', 'لا يمكن تعديل هذه الوردية');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'shift_type' => 'required|in:morning,afternoon,evening,night,emergency',
            'shift_date' => 'required|date',
            'scheduled_start' => 'required|date_format:H:i',
            'scheduled_end' => 'required|date_format:H:i|after:scheduled_start',
            'cash_register_id' => 'nullable|exists:cash_registers,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'shift_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $shift->update($request->only([
                'user_id', 'department_id', 'cash_register_id', 'supervisor_id',
                'shift_type', 'shift_date', 'scheduled_start', 'scheduled_end', 'shift_notes'
            ]));

            return redirect()->route('shifts.show', $shift)
                           ->with('success', 'تم تحديث الوردية بنجاح');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تحديث الوردية: ' . $e->getMessage());
        }
    }

    /**
     * Start a shift
     */
    public function start(Request $request, Shift $shift)
    {
        if (!$shift->can_start) {
            return back()->with('error', 'لا يمكن بدء هذه الوردية الآن');
        }

        $request->validate([
            'opening_balance' => 'required|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            $shift->start($request->opening_balance, auth()->user());

            DB::commit();

            return redirect()->route('shifts.show', $shift)
                           ->with('success', 'تم بدء الوردية بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء بدء الوردية: ' . $e->getMessage());
        }
    }

    /**
     * End a shift
     */
    public function end(Request $request, Shift $shift)
    {
        if (!$shift->can_end) {
            return back()->with('error', 'لا يمكن إنهاء هذه الوردية الآن');
        }

        $request->validate([
            'closing_balance' => 'required|numeric|min:0',
            'shift_notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $shift->end($request->closing_balance, $request->shift_notes, auth()->user());

            DB::commit();

            return redirect()->route('shifts.show', $shift)
                           ->with('success', 'تم إنهاء الوردية بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'حدث خطأ أثناء إنهاء الوردية: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a shift
     */
    public function cancel(Request $request, Shift $shift)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $shift->cancel($request->reason, auth()->user());

            return redirect()->route('shifts.show', $shift)
                           ->with('success', 'تم إلغاء الوردية بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء إلغاء الوردية: ' . $e->getMessage());
        }
    }

    /**
     * Mark shift as no-show
     */
    public function markNoShow(Request $request, Shift $shift)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        try {
            $shift->markNoShow($request->reason, auth()->user());

            return redirect()->route('shifts.show', $shift)
                           ->with('success', 'تم تسجيل غياب الوردية');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Verify cash balance
     */
    public function verifyCash(Request $request, Shift $shift)
    {
        $request->validate([
            'actual_balance' => 'required|numeric|min:0'
        ]);

        try {
            $shift->verifyCash($request->actual_balance, auth()->user());

            return redirect()->route('shifts.show', $shift)
                           ->with('success', 'تم التحقق من الرصيد النقدي');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Show shift calendar
     */
    public function calendar(Request $request)
    {
        $startDate = $request->start ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end ?? now()->endOfMonth()->format('Y-m-d');

        $shifts = Shift::with(['user', 'department'])
                      ->whereBetween('shift_date', [$startDate, $endDate])
                      ->get();

        $events = $shifts->map(function($shift) {
            $statusColors = [
                'scheduled' => '#007bff',
                'active' => '#28a745',
                'completed' => '#6c757d',
                'cancelled' => '#dc3545',
                'no_show' => '#fd7e14'
            ];

            return [
                'id' => $shift->id,
                'title' => $shift->user->name . ' - ' . $shift->shift_type_display,
                'start' => $shift->shift_date->format('Y-m-d') . 'T' . $shift->scheduled_start,
                'end' => $shift->shift_date->format('Y-m-d') . 'T' . $shift->scheduled_end,
                'backgroundColor' => $statusColors[$shift->status] ?? '#6c757d',
                'borderColor' => $statusColors[$shift->status] ?? '#6c757d',
                'url' => route('shifts.show', $shift),
                'extendedProps' => [
                    'status' => $shift->status_display,
                    'department' => $shift->department->name,
                    'revenue' => $shift->total_revenue
                ]
            ];
        });

        if ($request->ajax()) {
            return response()->json($events);
        }

        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('shifts.calendar', compact('departments', 'users'));
    }

    /**
     * Show dashboard
     */
    public function dashboard()
    {
        $today = today();
        
        // Today's shifts
        $todayShifts = Shift::today()->with(['user', 'department', 'cashRegister'])->get();
        
        // Active shifts
        $activeShifts = Shift::active()->with(['user', 'department', 'cashRegister'])->get();
        
        // Overdue shifts
        $overdueShifts = Shift::getOverdueShifts();
        
        // Upcoming shifts (next 7 days)
        $upcomingShifts = Shift::getUpcomingShifts(7);
        
        // Statistics
        $stats = [
            'today_shifts' => $todayShifts->count(),
            'active_shifts' => $activeShifts->count(),
            'overdue_shifts' => $overdueShifts->count(),
            'upcoming_shifts' => $upcomingShifts->count(),
            'today_revenue' => $todayShifts->sum('total_revenue'),
            'today_transactions' => $todayShifts->sum('total_transactions'),
            'today_patients' => $todayShifts->sum('patients_served'),
            'cash_discrepancies' => $todayShifts->where('has_cash_discrepancy', true)->count()
        ];
        
        // Department summary
        $departmentSummary = Department::where('is_active', true)
                                     ->get()
                                     ->map(function($dept) use ($today) {
                                         return array_merge(
                                             ['department' => $dept->name],
                                             Shift::getDepartmentShiftSummary($dept->id, $today)
                                         );
                                     });

        return view('shifts.dashboard', compact(
            'todayShifts', 'activeShifts', 'overdueShifts', 'upcomingShifts', 
            'stats', 'departmentSummary'
        ));
    }

    /**
     * Get available time slots for scheduling
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'department_id' => 'required|exists:departments,id',
            'user_id' => 'nullable|exists:users,id'
        ]);

        $date = Carbon::parse($request->date);
        $departmentId = $request->department_id;
        $userId = $request->user_id;

        // Get existing shifts for the date
        $existingShifts = Shift::where('department_id', $departmentId)
                             ->whereDate('shift_date', $date)
                             ->when($userId, function($q) use ($userId) {
                                 $q->where('user_id', $userId);
                             })
                             ->get(['scheduled_start', 'scheduled_end']);

        // Define standard shift times
        $standardShifts = [
            'morning' => ['start' => '06:00', 'end' => '14:00'],
            'afternoon' => ['start' => '14:00', 'end' => '22:00'],
            'evening' => ['start' => '18:00', 'end' => '02:00'],
            'night' => ['start' => '22:00', 'end' => '06:00']
        ];

        $availableSlots = [];
        
        foreach ($standardShifts as $type => $times) {
            $isAvailable = true;
            
            foreach ($existingShifts as $existing) {
                if ($this->timesOverlap($times['start'], $times['end'], 
                                      $existing->scheduled_start, $existing->scheduled_end)) {
                    $isAvailable = false;
                    break;
                }
            }
            
            if ($isAvailable) {
                $availableSlots[] = [
                    'type' => $type,
                    'display' => $this->getShiftTypeDisplay($type),
                    'start' => $times['start'],
                    'end' => $times['end']
                ];
            }
        }

        return response()->json($availableSlots);
    }

    private function timesOverlap($start1, $end1, $start2, $end2): bool
    {
        $start1 = Carbon::parse($start1);
        $end1 = Carbon::parse($end1);
        $start2 = Carbon::parse($start2);
        $end2 = Carbon::parse($end2);
        
        return $start1->lt($end2) && $start2->lt($end1);
    }

    private function getShiftTypeDisplay($type): string
    {
        $types = [
            'morning' => 'صباحية',
            'afternoon' => 'بعد الظهر',
            'evening' => 'مسائية',
            'night' => 'ليلية',
            'emergency' => 'طوارئ'
        ];

        return $types[$type] ?? $type;
    }
}