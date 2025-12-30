<?php

namespace App\Http\Controllers;

use App\Models\RadiologyOrder;
use App\Models\RadiologyStudy;
use App\Models\RadiologyReport;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RadiologyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of radiology orders.
     */
    public function index(Request $request)
    {
        $query = RadiologyOrder::with(['patient', 'doctor', 'radiologyStudy', 'report']);

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        // Filter by patient
        if ($request->filled('patient_id')) {
            $query->byPatient($request->patient_id);
        }

        // Filter by doctor
        if ($request->filled('doctor_id')) {
            $query->byDoctor($request->doctor_id);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('ordered_at', [$request->start_date, $request->end_date]);
        } elseif ($request->filled('date')) {
            $query->whereDate('ordered_at', $request->date);
        } else {
            // Default to today's orders
            $query->today();
        }

        // Filter by urgent findings
        if ($request->filled('urgent_findings')) {
            $query->withUrgentFindings();
        }

        $orders = $query->orderBy('ordered_at', 'desc')
                       ->orderBy('priority', 'desc')
                       ->paginate(20);

        // Get filter options
        $patients = Patient::all();
        $doctors = User::whereHas('appointments')->get();
        $studies = RadiologyStudy::active()->get();

        return view('radiology.index', compact('orders', 'patients', 'doctors', 'studies'));
    }

    /**
     * Show the form for creating a new radiology order.
     */
    public function create(Request $request)
    {
        $patients = Patient::all();
        $doctors = User::whereHas('appointments')->get();
        $studies = RadiologyStudy::active()->get();
        
        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        return view('radiology.create', compact('patients', 'doctors', 'studies', 'selectedPatient'));
    }

    /**
     * Store a newly created radiology order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'radiology_study_id' => 'required|exists:radiology_studies,id',
            'priority' => 'required|in:routine,urgent,stat',
            'clinical_indication' => 'required|string|max:1000',
            'clinical_history' => 'nullable|string|max:1000',
            'special_instructions' => 'nullable|string|max:500',
            'scheduled_at' => 'nullable|date|after:now'
        ]);

        $study = RadiologyStudy::findOrFail($validated['radiology_study_id']);
        
        // Check if urgent study is capable of urgent processing
        if (in_array($validated['priority'], ['urgent', 'stat']) && !$study->canBePerformedUrgently()) {
            return back()->withErrors([
                'priority' => 'هذا الفحص لا يمكن تنفيذه بشكل عاجل'
            ])->withInput();
        }

        $orderData = [
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'radiology_study_id' => $validated['radiology_study_id'],
            'priority' => $validated['priority'],
            'clinical_indication' => $validated['clinical_indication'],
            'clinical_history' => $validated['clinical_history'],
            'special_instructions' => $validated['special_instructions'],
            'total_amount' => $study->price,
            'status' => 'ordered'
        ];

        // If scheduled time is provided, set status to scheduled
        if ($request->filled('scheduled_at')) {
            $orderData['scheduled_at'] = $validated['scheduled_at'];
            $orderData['status'] = 'scheduled';
        }

        $order = RadiologyOrder::create($orderData);

        return redirect()->route('radiology.show', $order)
                        ->with('success', 'تم إنشاء طلب الأشعة بنجاح');
    }

    /**
     * Display the specified radiology order.
     */
    public function show(RadiologyOrder $radiology)
    {
        $radiology->load(['patient', 'doctor', 'radiologyStudy', 'report.radiologist', 'report.verifiedBy']);
        
        return view('radiology.show', compact('radiology'));
    }

    /**
     * Show the form for editing the radiology order.
     */
    public function edit(RadiologyOrder $radiology)
    {
        if (!$radiology->canBeCancelled()) {
            return back()->with('error', 'لا يمكن تعديل هذا الطلب');
        }

        $patients = Patient::all();
        $doctors = User::whereHas('appointments')->get();
        $studies = RadiologyStudy::active()->get();

        return view('radiology.edit', compact('radiology', 'patients', 'doctors', 'studies'));
    }

    /**
     * Update the specified radiology order.
     */
    public function update(Request $request, RadiologyOrder $radiology)
    {
        if (!$radiology->canBeCancelled()) {
            return back()->with('error', 'لا يمكن تعديل هذا الطلب');
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'radiology_study_id' => 'required|exists:radiology_studies,id',
            'priority' => 'required|in:routine,urgent,stat',
            'clinical_indication' => 'required|string|max:1000',
            'clinical_history' => 'nullable|string|max:1000',
            'special_instructions' => 'nullable|string|max:500',
            'scheduled_at' => 'nullable|date|after:now'
        ]);

        $study = RadiologyStudy::findOrFail($validated['radiology_study_id']);
        
        $updateData = [
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'radiology_study_id' => $validated['radiology_study_id'],
            'priority' => $validated['priority'],
            'clinical_indication' => $validated['clinical_indication'],
            'clinical_history' => $validated['clinical_history'],
            'special_instructions' => $validated['special_instructions'],
            'total_amount' => $study->price
        ];

        if ($request->filled('scheduled_at')) {
            $updateData['scheduled_at'] = $validated['scheduled_at'];
            if ($radiology->status === 'ordered') {
                $updateData['status'] = 'scheduled';
            }
        }

        $radiology->update($updateData);

        return redirect()->route('radiology.show', $radiology)
                        ->with('success', 'تم تحديث طلب الأشعة بنجاح');
    }

    /**
     * Remove the specified radiology order.
     */
    public function destroy(RadiologyOrder $radiology)
    {
        if (!$radiology->canBeCancelled()) {
            return back()->with('error', 'لا يمكن إلغاء هذا الطلب');
        }

        $radiology->update(['status' => 'cancelled']);

        return redirect()->route('radiology.index')
                        ->with('success', 'تم إلغاء طلب الأشعة بنجاح');
    }

    /**
     * Update radiology order status.
     */
    public function updateStatus(Request $request, RadiologyOrder $radiology)
    {
        $request->validate([
            'status' => 'required|in:ordered,scheduled,in_progress,completed,cancelled,reported',
            'scheduled_at' => 'nullable|date',
            'contrast_used' => 'nullable|boolean',
            'contrast_notes' => 'nullable|string|max:500'
        ]);

        $updateData = ['status' => $request->status];

        // Handle status-specific updates
        switch ($request->status) {
            case 'scheduled':
                if ($radiology->canBeScheduled() && $request->filled('scheduled_at')) {
                    $updateData['scheduled_at'] = $request->scheduled_at;
                }
                break;
            case 'in_progress':
                if ($radiology->canBeStarted()) {
                    $updateData['started_at'] = now();
                }
                break;
            case 'completed':
                if ($radiology->canBeCompleted()) {
                    $updateData['completed_at'] = now();
                    if ($request->filled('contrast_used')) {
                        $updateData['contrast_used'] = $request->boolean('contrast_used');
                        $updateData['contrast_notes'] = $request->contrast_notes;
                    }
                }
                break;
        }

        $radiology->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الطلب بنجاح',
            'status' => $radiology->status,
            'status_display' => $radiology->status_display,
            'status_color' => $radiology->status_color
        ]);
    }

    /**
     * Create report for radiology order.
     */
    public function createReport(Request $request, RadiologyOrder $radiology)
    {
        if (!$radiology->canBeReported()) {
            return back()->with('error', 'لا يمكن إنشاء تقرير لهذا الطلب');
        }

        $validated = $request->validate([
            'technique' => 'nullable|string|max:1000',
            'findings' => 'required|string',
            'impression' => 'required|string',
            'recommendations' => 'nullable|string',
            'urgency_level' => 'required|in:routine,urgent,critical',
            'has_urgent_findings' => 'boolean',
            'urgent_findings' => 'nullable|string'
        ]);

        $validated['radiology_order_id'] = $radiology->id;
        $validated['radiologist_id'] = Auth::id();

        $report = RadiologyReport::create($validated);

        // Update order status
        $radiology->update(['status' => 'reported']);

        return redirect()->route('radiology.show', $radiology)
                        ->with('success', 'تم إنشاء التقرير بنجاح');
    }

    /**
     * Show report creation form.
     */
    public function showReportForm(RadiologyOrder $radiology)
    {
        if (!$radiology->canBeReported()) {
            return back()->with('error', 'لا يمكن إنشاء تقرير لهذا الطلب');
        }

        return view('radiology.create-report', compact('radiology'));
    }

    /**
     * Verify radiology report.
     */
    public function verifyReport(Request $request, RadiologyReport $report)
    {
        if (!$report->canBeVerified()) {
            return back()->with('error', 'لا يمكن التحقق من هذا التقرير');
        }

        $report->verify(Auth::user());

        return response()->json([
            'success' => true,
            'message' => 'تم التحقق من التقرير بنجاح'
        ]);
    }

    /**
     * Finalize radiology report.
     */
    public function finalizeReport(Request $request, RadiologyReport $report)
    {
        if (!$report->canBeFinalized()) {
            return back()->with('error', 'لا يمكن إنهاء هذا التقرير');
        }

        $report->finalize();

        return response()->json([
            'success' => true,
            'message' => 'تم إنهاء التقرير بنجاح'
        ]);
    }

    /**
     * Get radiology statistics.
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = [
            'total_orders' => RadiologyOrder::whereBetween('ordered_at', [$startDate, $endDate])->count(),
            'completed_orders' => RadiologyOrder::whereBetween('ordered_at', [$startDate, $endDate])
                                               ->where('status', 'completed')->count(),
            'reported_orders' => RadiologyOrder::whereBetween('ordered_at', [$startDate, $endDate])
                                              ->where('status', 'reported')->count(),
            'pending_orders' => RadiologyOrder::whereBetween('ordered_at', [$startDate, $endDate])
                                             ->pending()->count(),
            'urgent_orders' => RadiologyOrder::whereBetween('ordered_at', [$startDate, $endDate])
                                            ->urgent()->count(),
            'urgent_findings' => RadiologyOrder::whereBetween('ordered_at', [$startDate, $endDate])
                                              ->withUrgentFindings()->count(),
            'revenue' => RadiologyOrder::whereBetween('ordered_at', [$startDate, $endDate])
                                      ->where('is_paid', true)->sum('total_amount')
        ];

        // Orders by status
        $ordersByStatus = RadiologyOrder::whereBetween('ordered_at', [$startDate, $endDate])
                                       ->select('status', DB::raw('count(*) as count'))
                                       ->groupBy('status')
                                       ->pluck('count', 'status');

        // Most ordered studies
        $popularStudies = RadiologyOrder::whereBetween('ordered_at', [$startDate, $endDate])
                                       ->with('radiologyStudy')
                                       ->select('radiology_study_id', DB::raw('count(*) as count'))
                                       ->groupBy('radiology_study_id')
                                       ->orderBy('count', 'desc')
                                       ->limit(10)
                                       ->get();

        return view('radiology.statistics', compact('stats', 'ordersByStatus', 'popularStudies', 'startDate', 'endDate'));
    }

    /**
     * Get today's radiology orders for dashboard.
     */
    public function todayOrders()
    {
        $orders = RadiologyOrder::with(['patient', 'doctor', 'radiologyStudy'])
                               ->today()
                               ->orderBy('priority', 'desc')
                               ->orderBy('ordered_at')
                               ->get();

        return view('radiology.today', compact('orders'));
    }

    /**
     * Get scheduled orders for today.
     */
    public function todaySchedule()
    {
        $orders = RadiologyOrder::with(['patient', 'doctor', 'radiologyStudy'])
                               ->scheduledToday()
                               ->orderBy('scheduled_at')
                               ->get();

        return view('radiology.schedule', compact('orders'));
    }

    /**
     * Get urgent findings that need attention.
     */
    public function urgentFindings()
    {
        $orders = RadiologyOrder::with(['patient', 'doctor', 'radiologyStudy', 'report'])
                               ->withUrgentFindings()
                               ->whereNull('urgent_notified_at')
                               ->orderBy('completed_at', 'desc')
                               ->get();

        return view('radiology.urgent-findings', compact('orders'));
    }
}