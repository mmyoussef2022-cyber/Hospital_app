<?php

namespace App\Http\Controllers;

use App\Models\StaffProductivity;
use App\Models\Shift;
use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffProductivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of staff productivity records
     */
    public function index(Request $request)
    {
        $query = StaffProductivity::with(['shift.user', 'shift.department', 'user', 'evaluatedBy'])
                                 ->orderBy('evaluation_date', 'desc')
                                 ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('shift', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('performance_rating')) {
            $query->where('performance_rating', $request->performance_rating);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('evaluation_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('evaluation_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('shift', function($sq) use ($search) {
                    $sq->where('shift_number', 'like', "%{$search}%");
                });
            });
        }

        $productivityRecords = $query->paginate(20);

        // Get filter options
        $users = User::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();

        // Statistics
        $stats = [
            'total_evaluations' => StaffProductivity::count(),
            'average_rating' => StaffProductivity::avg('performance_rating'),
            'excellent_ratings' => StaffProductivity::where('performance_rating', '>=', 4.5)->count(),
            'good_ratings' => StaffProductivity::whereBetween('performance_rating', [3.5, 4.49])->count(),
            'needs_improvement' => StaffProductivity::where('performance_rating', '<', 3.5)->count(),
            'this_month_evaluations' => StaffProductivity::whereMonth('evaluation_date', now()->month)
                                                        ->whereYear('evaluation_date', now()->year)
                                                        ->count()
        ];

        return view('staff-productivity.index', compact('productivityRecords', 'users', 'departments', 'stats'));
    }

    /**
     * Show the form for creating a new productivity evaluation
     */
    public function create(Request $request)
    {
        $shifts = Shift::where('status', 'completed')
                      ->whereDoesntHave('productivity')
                      ->with(['user', 'department'])
                      ->orderBy('shift_date', 'desc')
                      ->get();

        $users = User::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name_ar')->get();

        // Pre-fill data if provided
        $preselected = [
            'shift_id' => $request->shift_id,
            'user_id' => $request->user_id,
            'evaluation_date' => $request->evaluation_date ?? today()->format('Y-m-d')
        ];

        return view('staff-productivity.create', compact('shifts', 'users', 'departments', 'preselected'));
    }

    /**
     * Store a newly created productivity evaluation
     */
    public function store(Request $request)
    {
        $request->validate([
            'shift_id' => 'required|exists:shifts,id',
            'user_id' => 'required|exists:users,id',
            'evaluation_date' => 'required|date',
            'tasks_completed' => 'required|integer|min:0',
            'tasks_assigned' => 'required|integer|min:1',
            'patients_served' => 'required|integer|min:0',
            'revenue_generated' => 'required|numeric|min:0',
            'customer_satisfaction_score' => 'nullable|numeric|between:1,5',
            'punctuality_score' => 'required|numeric|between:1,5',
            'quality_score' => 'required|numeric|between:1,5',
            'efficiency_score' => 'required|numeric|between:1,5',
            'teamwork_score' => 'required|numeric|between:1,5',
            'communication_score' => 'required|numeric|between:1,5',
            'strengths' => 'nullable|string|max:1000',
            'areas_for_improvement' => 'nullable|string|max:1000',
            'goals_for_next_period' => 'nullable|string|max:1000',
            'supervisor_comments' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            // Calculate performance metrics
            $completionRate = ($request->tasks_completed / $request->tasks_assigned) * 100;
            $performanceRating = $this->calculatePerformanceRating([
                'punctuality' => $request->punctuality_score,
                'quality' => $request->quality_score,
                'efficiency' => $request->efficiency_score,
                'teamwork' => $request->teamwork_score,
                'communication' => $request->communication_score,
                'customer_satisfaction' => $request->customer_satisfaction_score ?? 0
            ]);

            $productivity = StaffProductivity::create([
                'shift_id' => $request->shift_id,
                'user_id' => $request->user_id,
                'evaluation_date' => $request->evaluation_date,
                'tasks_completed' => $request->tasks_completed,
                'tasks_assigned' => $request->tasks_assigned,
                'completion_rate' => $completionRate,
                'patients_served' => $request->patients_served,
                'revenue_generated' => $request->revenue_generated,
                'customer_satisfaction_score' => $request->customer_satisfaction_score,
                'punctuality_score' => $request->punctuality_score,
                'quality_score' => $request->quality_score,
                'efficiency_score' => $request->efficiency_score,
                'teamwork_score' => $request->teamwork_score,
                'communication_score' => $request->communication_score,
                'performance_rating' => $performanceRating,
                'strengths' => $request->strengths,
                'areas_for_improvement' => $request->areas_for_improvement,
                'goals_for_next_period' => $request->goals_for_next_period,
                'supervisor_comments' => $request->supervisor_comments,
                'evaluated_by' => auth()->id()
            ]);

            DB::commit();

            return redirect()->route('staff-productivity.show', $productivity)
                           ->with('success', 'تم إنشاء تقييم الإنتاجية بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء إنشاء التقييم: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified productivity evaluation
     */
    public function show(StaffProductivity $staffProductivity)
    {
        $staffProductivity->load([
            'shift.user',
            'shift.department',
            'shift.cashRegister',
            'user',
            'evaluatedBy'
        ]);

        return view('staff-productivity.show', compact('staffProductivity'));
    }

    /**
     * Show the form for editing the specified productivity evaluation
     */
    public function edit(StaffProductivity $staffProductivity)
    {
        $shifts = Shift::where('status', 'completed')
                      ->with(['user', 'department'])
                      ->orderBy('shift_date', 'desc')
                      ->get();

        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('staff-productivity.edit', compact('staffProductivity', 'shifts', 'users'));
    }

    /**
     * Update the specified productivity evaluation
     */
    public function update(Request $request, StaffProductivity $staffProductivity)
    {
        $request->validate([
            'tasks_completed' => 'required|integer|min:0',
            'tasks_assigned' => 'required|integer|min:1',
            'patients_served' => 'required|integer|min:0',
            'revenue_generated' => 'required|numeric|min:0',
            'customer_satisfaction_score' => 'nullable|numeric|between:1,5',
            'punctuality_score' => 'required|numeric|between:1,5',
            'quality_score' => 'required|numeric|between:1,5',
            'efficiency_score' => 'required|numeric|between:1,5',
            'teamwork_score' => 'required|numeric|between:1,5',
            'communication_score' => 'required|numeric|between:1,5',
            'strengths' => 'nullable|string|max:1000',
            'areas_for_improvement' => 'nullable|string|max:1000',
            'goals_for_next_period' => 'nullable|string|max:1000',
            'supervisor_comments' => 'nullable|string|max:1000'
        ]);

        try {
            // Recalculate performance metrics
            $completionRate = ($request->tasks_completed / $request->tasks_assigned) * 100;
            $performanceRating = $this->calculatePerformanceRating([
                'punctuality' => $request->punctuality_score,
                'quality' => $request->quality_score,
                'efficiency' => $request->efficiency_score,
                'teamwork' => $request->teamwork_score,
                'communication' => $request->communication_score,
                'customer_satisfaction' => $request->customer_satisfaction_score ?? 0
            ]);

            $staffProductivity->update([
                'tasks_completed' => $request->tasks_completed,
                'tasks_assigned' => $request->tasks_assigned,
                'completion_rate' => $completionRate,
                'patients_served' => $request->patients_served,
                'revenue_generated' => $request->revenue_generated,
                'customer_satisfaction_score' => $request->customer_satisfaction_score,
                'punctuality_score' => $request->punctuality_score,
                'quality_score' => $request->quality_score,
                'efficiency_score' => $request->efficiency_score,
                'teamwork_score' => $request->teamwork_score,
                'communication_score' => $request->communication_score,
                'performance_rating' => $performanceRating,
                'strengths' => $request->strengths,
                'areas_for_improvement' => $request->areas_for_improvement,
                'goals_for_next_period' => $request->goals_for_next_period,
                'supervisor_comments' => $request->supervisor_comments,
                'updated_by' => auth()->id()
            ]);

            return redirect()->route('staff-productivity.show', $staffProductivity)
                           ->with('success', 'تم تحديث التقييم بنجاح');

        } catch (\Exception $e) {
            return back()->withInput()
                        ->with('error', 'حدث خطأ أثناء تحديث التقييم: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified productivity evaluation
     */
    public function destroy(StaffProductivity $staffProductivity)
    {
        try {
            $staffProductivity->delete();

            return redirect()->route('staff-productivity.index')
                           ->with('success', 'تم حذف التقييم بنجاح');

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حذف التقييم: ' . $e->getMessage());
        }
    }

    /**
     * Evaluate staff productivity
     */
    public function evaluate(Request $request, StaffProductivity $staffProductivity)
    {
        $request->validate([
            'evaluation_status' => 'required|in:approved,needs_review,rejected',
            'evaluation_notes' => 'nullable|string|max:1000'
        ]);

        try {
            $staffProductivity->update([
                'evaluation_status' => $request->evaluation_status,
                'evaluation_notes' => $request->evaluation_notes,
                'final_evaluated_by' => auth()->id(),
                'final_evaluated_at' => now()
            ]);

            $message = match($request->evaluation_status) {
                'approved' => 'تم اعتماد التقييم',
                'needs_review' => 'تم وضع التقييم قيد المراجعة',
                'rejected' => 'تم رفض التقييم'
            };

            return redirect()->route('staff-productivity.show', $staffProductivity)
                           ->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    /**
     * Get productivity statistics
     */
    public function getStatistics(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth()->format('Y-m-d');
        $endDate = $request->end_date ?? now()->endOfMonth()->format('Y-m-d');
        $userId = $request->user_id;

        $query = StaffProductivity::whereBetween('evaluation_date', [$startDate, $endDate]);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $records = $query->with(['user', 'shift.department'])->get();

        $stats = [
            'total_evaluations' => $records->count(),
            'average_performance_rating' => $records->avg('performance_rating'),
            'average_completion_rate' => $records->avg('completion_rate'),
            'total_revenue_generated' => $records->sum('revenue_generated'),
            'total_patients_served' => $records->sum('patients_served'),
            'by_rating' => [
                'excellent' => $records->where('performance_rating', '>=', 4.5)->count(),
                'good' => $records->whereBetween('performance_rating', [3.5, 4.49])->count(),
                'average' => $records->whereBetween('performance_rating', [2.5, 3.49])->count(),
                'needs_improvement' => $records->where('performance_rating', '<', 2.5)->count()
            ],
            'by_department' => $records->groupBy('shift.department.name')->map->count(),
            'top_performers' => $records->sortByDesc('performance_rating')->take(5)->map(function($record) {
                return [
                    'user' => $record->user->name,
                    'rating' => $record->performance_rating,
                    'completion_rate' => $record->completion_rate,
                    'revenue' => $record->revenue_generated
                ];
            }),
            'improvement_areas' => $records->where('areas_for_improvement', '!=', null)->count()
        ];

        return response()->json($stats);
    }

    /**
     * Calculate overall performance rating
     */
    private function calculatePerformanceRating(array $scores): float
    {
        $weights = [
            'punctuality' => 0.15,
            'quality' => 0.25,
            'efficiency' => 0.20,
            'teamwork' => 0.15,
            'communication' => 0.15,
            'customer_satisfaction' => 0.10
        ];

        $totalScore = 0;
        $totalWeight = 0;

        foreach ($scores as $category => $score) {
            if ($score > 0) {
                $totalScore += $score * $weights[$category];
                $totalWeight += $weights[$category];
            }
        }

        return $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : 0;
    }

    /**
     * Get user productivity trends
     */
    public function getUserTrends(Request $request)
    {
        $userId = $request->user_id;
        $months = $request->months ?? 6;

        if (!$userId) {
            return response()->json(['error' => 'User ID is required'], 400);
        }

        $startDate = now()->subMonths($months)->startOfMonth();
        $endDate = now()->endOfMonth();

        $records = StaffProductivity::where('user_id', $userId)
                                   ->whereBetween('evaluation_date', [$startDate, $endDate])
                                   ->orderBy('evaluation_date')
                                   ->get();

        $trends = $records->groupBy(function($record) {
            return $record->evaluation_date->format('Y-m');
        })->map(function($monthRecords) {
            return [
                'average_rating' => $monthRecords->avg('performance_rating'),
                'completion_rate' => $monthRecords->avg('completion_rate'),
                'revenue_generated' => $monthRecords->sum('revenue_generated'),
                'patients_served' => $monthRecords->sum('patients_served'),
                'evaluations_count' => $monthRecords->count()
            ];
        });

        return response()->json($trends);
    }
}