<?php

namespace App\Http\Controllers;

use App\Models\ShiftReport;
use App\Models\Shift;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ShiftReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of shift reports
     */
    public function index(Request $request)
    {
        $query = ShiftReport::with(['shift.user', 'shift.department', 'user', 'reviewedBy', 'approvedBy'])
                           ->orderBy('report_date', 'desc')
                           ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('report_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('report_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('report_number', 'like', "%{$search}%")
                  ->orWhereHas('shift', function($sq) use ($search) {
                      $sq->where('shift_number', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $reports = $query->paginate(20);

        // Get filter options
        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        // Statistics
        $stats = [
            'total_reports' => ShiftReport::count(),
            'pending_reports' => ShiftReport::where('status', 'pending')->count(),
            'approved_reports' => ShiftReport::where('status', 'approved')->count(),
            'rejected_reports' => ShiftReport::where('status', 'rejected')->count(),
            'today_reports' => ShiftReport::whereDate('report_date', today())->count(),
            'this_week_reports' => ShiftReport::whereBetween('report_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count()
        ];

        return view('shift-reports.index', compact('reports', 'departments', 'users', 'stats'));
    }

    /**
     * Show the form for creating a new shift report
     */
    public function create(Request $request)
    {
        $shifts = Shift::where('status', 'completed')
                      ->whereDoesntHave('report')
                      ->with(['user', 'department'])
                      ->orderBy('shift_date', 'desc')
                      ->get();

        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        // Pre-fill data if provided
        $preselected = [
            'shift_id' => $request->shift_id,
            'department_id' => $request->department_id,
            'user_id' => $request->user_id,
            'report_date' => $request->report_date ?? today()->format('Y-m-d')
        ];

        return view('shift-reports.create', compact('shifts', 'departments', 'users', 'preselected'));
    }

    /**
     * Store a newly created shift report
     */
    public function store(Request $request)
    {
        $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'report_summary' => 'required|string|max:1000',
            'issues_encountered' => 'nullable|string|max:2000',
            'recommendations' => 'nullable|string|max:1000',
            'additional_notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $shift = Shift::findOrFail($request->shift_id);

            $report = ShiftReport::create([
                'report_number' => ShiftReport::generateReportNumber(),
                'shift_id' => $shift->id,
                'user_id' => $shift->user_id,
                'department_id' => $shift->department_id,
                'report_date' => $shift->shift_date,
                'shift_start' => $shift->scheduled_start,
                'shift_end' => $shift->scheduled_end,
                'actual_start' => $shift->actual_start,
                'actual_end' => $shift->actual_end,
                'report_summary' => $request->report_summary,
                'issues_encountered' => $request->issues_encountered,
                'recommendations' => $request->recommendations,
                'additional_notes' => $request->additional_notes,
                'status' => 'pending',
                'created_by' => auth()->id()
            ]);

            // Update report with shift data
            $report->updateFromShift($shift);

            DB::commit();

            return redirect()->route('shift-reports.show', $report)
                           ->with('success', 'تم إنشاء تقرير الوردية بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء إنشاء التقرير: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified shift report
     */
    public function show(ShiftReport $shiftReport)
    {
        $shiftReport->load([
            'shift.user',
            'shift.department',
            'shift.cashRegister',
            'shift.supervisor',
            'shift.transactions.patient',
            'user',
            'reviewedBy',
            'approvedBy'
        ]);

        return view('shift-reports.show', compact('shiftReport'));
    }

    /**
     * Show the form for editing the specified shift report
     */
    public function edit(ShiftReport $shiftReport)
    {
        if (!in_array($shiftReport->status, ['pending', 'rejected'])) {
            return redirect()->route('shift-reports.show', $shiftReport)
                           ->with('error', 'لا يمكن تعديل هذا التقرير');
        }

        return view('shift-reports.edit', compact('shiftReport'));
    }

    /**
     * Update the specified shift report
     */
    public function update(Request $request, ShiftReport $shiftReport)
    {
        if (!in_array($shiftReport->status, ['pending', 'rejected'])) {
            return redirect()->route('shift-reports.show', $shiftReport)
                           ->with('error', 'لا يمكن تعديل هذا التقرير');
        }

        $request->validate([
            'report_summary' => 'required|string|max:1000',
            'issues_encountered' => 'nullable|string|max:2000',
            'recommendations' => 'nullable|string|max:1000',
            'additional_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $shiftReport->update([
                'report_summary' => $request->report_summary,
                'issues_encountered' => $request->issues_encountered,
                'recommendations' => $request->recommendations,
                'additional_notes' => $request->additional_notes,
                'status' => 'pending', // Reset to pending if it was rejected
                'updated_by' => auth()->id()
            ]);

            return redirect()->route('shift-reports.show', $shiftReport)
                           ->with('success', 'تم تحديث التقرير بنجاح');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تحديث التقرير: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified shift report
     */
    public function destroy(ShiftReport $shiftReport)
    {
        if ($shiftReport->status === 'approved') {
            return back()->with('error', 'لا يمكن حذف التقارير المعتمدة');
        }

        try {
            $shiftReport->delete();

            return redirect()->route('shift-reports.index')
                           ->with('success', 'تم حذف التقرير بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حذف التقرير: ' . $e->getMessage());
        }
    }

    /**
     * Review a shift report
     */
    public function review(Request $request, ShiftReport $shiftReport)
    {
        $request->validate([
            'review_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $shiftReport->update([
                'status' => 'under_review',
                'review_notes' => $request->review_notes,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now()
            ]);

            return redirect()->route('shift-reports.show', $shiftReport)
                           ->with('success', 'تم وضع التقرير قيد المراجعة');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Approve a shift report
     */
    public function approve(Request $request, ShiftReport $shiftReport)
    {
        $request->validate([
            'approval_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $shiftReport->update([
                'status' => 'approved',
                'approval_notes' => $request->approval_notes,
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);

            return redirect()->route('shift-reports.show', $shiftReport)
                           ->with('success', 'تم اعتماد التقرير بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Reject a shift report
     */
    public function reject(Request $request, ShiftReport $shiftReport)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000'
        ]);

        try {
            $shiftReport->update([
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now()
            ]);

            return redirect()->route('shift-reports.show', $shiftReport)
                           ->with('success', 'تم رفض التقرير');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Get reports statistics
     */
    public function getStatistics(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');

        $reports = ShiftReport::whereBetween('report_date', [$startDate, $endDate])
                             ->with(['shift', 'department'])
                             ->get();

        $stats = [
            'total_reports' => $reports->count(),
            'by_status' => $reports->groupBy('status')->map->count(),
            'by_department' => $reports->groupBy('department.name')->map->count(),
            'average_completion_time' => $reports->where('status', 'approved')
                                               ->avg(function($report) {
                                                   return $report->created_at->diffInHours($report->approved_at);
                                               }),
            'reports_with_issues' => $reports->whereNotNull('issues_encountered')->count(),
            'reports_with_recommendations' => $reports->whereNotNull('recommendations')->count()
        ];

        return response()->json($stats);
    }
}