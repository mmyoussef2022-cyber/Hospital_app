<?php

namespace App\Http\Controllers;

use App\Models\LabOrder;
use App\Models\LabTest;
use App\Models\LabResult;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LabController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of lab orders.
     */
    public function index(Request $request)
    {
        $query = LabOrder::with(['patient', 'doctor', 'labTest', 'results']);

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

        $orders = $query->orderBy('ordered_at', 'desc')
                       ->orderBy('priority', 'desc')
                       ->paginate(20);

        // Get filter options
        $patients = Patient::all();
        $doctors = User::whereHas('appointments')->get(); // Doctors who have appointments
        $labTests = LabTest::active()->get();

        return view('lab.index', compact('orders', 'patients', 'doctors', 'labTests'));
    }

    /**
     * Show the form for creating a new lab order.
     */
    public function create(Request $request)
    {
        $patients = Patient::all();
        $doctors = User::whereHas('appointments')->get();
        $labTests = LabTest::active()->get();
        
        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        return view('lab.create', compact('patients', 'doctors', 'labTests', 'selectedPatient'));
    }

    /**
     * Store a newly created lab order.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'lab_test_id' => 'required|exists:lab_tests,id',
            'priority' => 'required|in:routine,urgent,stat',
            'clinical_notes' => 'nullable|string|max:1000'
        ]);

        $labTest = LabTest::findOrFail($validated['lab_test_id']);
        
        $order = LabOrder::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'lab_test_id' => $validated['lab_test_id'],
            'priority' => $validated['priority'],
            'clinical_notes' => $validated['clinical_notes'],
            'total_amount' => $labTest->price,
            'status' => 'ordered'
        ]);

        return redirect()->route('lab.show', $order)
                        ->with('success', 'تم إنشاء طلب المختبر بنجاح');
    }

    /**
     * Display the specified lab order.
     */
    public function show(LabOrder $lab)
    {
        $lab->load(['patient', 'doctor', 'labTest', 'results.verifiedBy']);
        
        return view('lab.show', compact('lab'));
    }

    /**
     * Show the form for editing the lab order.
     */
    public function edit(LabOrder $lab)
    {
        if (!$lab->canBeCancelled()) {
            return back()->with('error', 'لا يمكن تعديل هذا الطلب');
        }

        $patients = Patient::all();
        $doctors = User::whereHas('appointments')->get();
        $labTests = LabTest::active()->get();

        return view('lab.edit', compact('lab', 'patients', 'doctors', 'labTests'));
    }

    /**
     * Update the specified lab order.
     */
    public function update(Request $request, LabOrder $lab)
    {
        if (!$lab->canBeCancelled()) {
            return back()->with('error', 'لا يمكن تعديل هذا الطلب');
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:users,id',
            'lab_test_id' => 'required|exists:lab_tests,id',
            'priority' => 'required|in:routine,urgent,stat',
            'clinical_notes' => 'nullable|string|max:1000'
        ]);

        $labTest = LabTest::findOrFail($validated['lab_test_id']);
        
        $lab->update([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'lab_test_id' => $validated['lab_test_id'],
            'priority' => $validated['priority'],
            'clinical_notes' => $validated['clinical_notes'],
            'total_amount' => $labTest->price
        ]);

        return redirect()->route('lab.show', $lab)
                        ->with('success', 'تم تحديث طلب المختبر بنجاح');
    }

    /**
     * Remove the specified lab order.
     */
    public function destroy(LabOrder $lab)
    {
        if (!$lab->canBeCancelled()) {
            return back()->with('error', 'لا يمكن إلغاء هذا الطلب');
        }

        $lab->update(['status' => 'cancelled']);

        return redirect()->route('lab.index')
                        ->with('success', 'تم إلغاء طلب المختبر بنجاح');
    }

    /**
     * Update lab order status.
     */
    public function updateStatus(Request $request, LabOrder $lab)
    {
        $request->validate([
            'status' => 'required|in:ordered,collected,processing,completed,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);

        $updateData = ['status' => $request->status];

        // Set timestamps based on status
        switch ($request->status) {
            case 'collected':
                if ($lab->canBeCollected()) {
                    $updateData['collected_at'] = now();
                    $updateData['collection_notes'] = $request->notes;
                }
                break;
            case 'completed':
                if ($lab->canBeCompleted()) {
                    $updateData['completed_at'] = now();
                }
                break;
        }

        $lab->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الطلب بنجاح',
            'status' => $lab->status,
            'status_display' => $lab->status_display,
            'status_color' => $lab->status_color
        ]);
    }

    /**
     * Add results to lab order.
     */
    public function addResults(Request $request, LabOrder $lab)
    {
        if (!$lab->canBeProcessed() && !$lab->canBeCompleted()) {
            return back()->with('error', 'لا يمكن إضافة نتائج لهذا الطلب');
        }

        $validated = $request->validate([
            'results' => 'required|array',
            'results.*.parameter_name' => 'required|string|max:255',
            'results.*.value' => 'required|string|max:255',
            'results.*.unit' => 'nullable|string|max:50',
            'results.*.reference_range' => 'nullable|string|max:255',
            'results.*.flag' => 'required|in:normal,high,low,critical_high,critical_low,abnormal',
            'results.*.notes' => 'nullable|string|max:500'
        ]);

        DB::transaction(function () use ($lab, $validated) {
            // Delete existing results
            $lab->results()->delete();

            // Add new results
            foreach ($validated['results'] as $resultData) {
                $lab->results()->create($resultData);
            }

            // Update order status to processing if not already
            if ($lab->status === 'collected') {
                $lab->update(['status' => 'processing']);
            }
        });

        return redirect()->route('lab.show', $lab)
                        ->with('success', 'تم إضافة النتائج بنجاح');
    }

    /**
     * Verify lab results.
     */
    public function verifyResults(Request $request, LabOrder $lab)
    {
        $request->validate([
            'result_ids' => 'required|array',
            'result_ids.*' => 'exists:lab_results,id'
        ]);

        $results = LabResult::whereIn('id', $request->result_ids)
                           ->where('lab_order_id', $lab->id)
                           ->whereNull('verified_at')
                           ->get();

        foreach ($results as $result) {
            $result->verify(Auth::user());
        }

        // If all results are verified, mark order as completed
        if ($lab->results()->unverified()->count() === 0) {
            $lab->update([
                'status' => 'completed',
                'completed_at' => now()
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم التحقق من النتائج بنجاح',
            'verified_count' => $results->count()
        ]);
    }

    /**
     * Get lab statistics.
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $stats = [
            'total_orders' => LabOrder::whereBetween('ordered_at', [$startDate, $endDate])->count(),
            'completed_orders' => LabOrder::whereBetween('ordered_at', [$startDate, $endDate])
                                         ->where('status', 'completed')->count(),
            'pending_orders' => LabOrder::whereBetween('ordered_at', [$startDate, $endDate])
                                       ->pending()->count(),
            'urgent_orders' => LabOrder::whereBetween('ordered_at', [$startDate, $endDate])
                                      ->urgent()->count(),
            'critical_results' => LabResult::whereHas('labOrder', function($q) use ($startDate, $endDate) {
                                            $q->whereBetween('ordered_at', [$startDate, $endDate]);
                                          })->critical()->count(),
            'revenue' => LabOrder::whereBetween('ordered_at', [$startDate, $endDate])
                                ->where('is_paid', true)->sum('total_amount')
        ];

        // Orders by status
        $ordersByStatus = LabOrder::whereBetween('ordered_at', [$startDate, $endDate])
                                 ->select('status', DB::raw('count(*) as count'))
                                 ->groupBy('status')
                                 ->pluck('count', 'status');

        // Most ordered tests
        $popularTests = LabOrder::whereBetween('ordered_at', [$startDate, $endDate])
                               ->with('labTest')
                               ->select('lab_test_id', DB::raw('count(*) as count'))
                               ->groupBy('lab_test_id')
                               ->orderBy('count', 'desc')
                               ->limit(10)
                               ->get();

        return view('lab.statistics', compact('stats', 'ordersByStatus', 'popularTests', 'startDate', 'endDate'));
    }

    /**
     * Get today's lab orders for dashboard.
     */
    public function todayOrders()
    {
        $orders = LabOrder::with(['patient', 'doctor', 'labTest'])
                         ->today()
                         ->orderBy('priority', 'desc')
                         ->orderBy('ordered_at')
                         ->get();

        return view('lab.today', compact('orders'));
    }

    /**
     * Get results form for lab order (AJAX).
     */
    public function getResultsForm(LabOrder $lab)
    {
        $order = $lab->load(['patient', 'doctor', 'labTest', 'results']);
        
        return view('lab.results-form', compact('order'));
    }
}
