<?php

namespace App\Http\Controllers;

use App\Models\DentalTreatment;
use App\Models\Patient;
use App\Models\User;
use App\Models\Doctor;
use App\Http\Requests\DentalTreatmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DentalTreatmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:doctors.view')->only(['index', 'show']);
        $this->middleware('permission:doctors.create')->only(['create', 'store']);
        $this->middleware('permission:doctors.edit')->only(['edit', 'update']);
        $this->middleware('permission:doctors.delete')->only(['destroy']);
    }

    /**
     * Display a listing of dental treatments.
     */
    public function index(Request $request)
    {
        $query = DentalTreatment::with(['patient', 'doctor', 'sessions', 'installments']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('treatment_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($pq) use ($search) {
                      $pq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('treatment_type')) {
            $query->byTreatmentType($request->treatment_type);
        }

        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('doctor_id')) {
            $query->byDoctor($request->doctor_id);
        }

        if ($request->filled('patient_id')) {
            $query->byPatient($request->patient_id);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        $treatments = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $doctors = User::role('doctor')->whereHas('doctor', function($q) {
            $q->where('specialization', 'like', '%dental%')
              ->orWhere('specialization', 'orthodontics')
              ->orWhere('specialization', 'oral_surgery');
        })->get();
        
        $patients = Patient::all();
        $treatmentTypes = DentalTreatment::getTreatmentTypes();
        $statuses = DentalTreatment::getStatuses();

        return view('dental.treatments.index', compact(
            'treatments', 'doctors', 'patients', 'treatmentTypes', 'statuses'
        ));
    }

    /**
     * Show the form for creating a new dental treatment.
     */
    public function create(Request $request)
    {
        $patients = Patient::all();
        $doctors = User::role('doctor')->whereHas('doctor', function($q) {
            $q->where('specialization', 'like', '%dental%')
              ->orWhere('specialization', 'orthodontics')
              ->orWhere('specialization', 'oral_surgery');
        })->get();
        
        $treatmentTypes = DentalTreatment::getTreatmentTypes();
        $priorities = DentalTreatment::getPriorities();
        
        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        return view('dental.treatments.create', compact(
            'patients', 'doctors', 'treatmentTypes', 'priorities', 'selectedPatient'
        ));
    }

    /**
     * Store a newly created dental treatment.
     */
    public function store(DentalTreatmentRequest $request)
    {
        $validated = $request->validated();

        // Handle before photos upload
        if ($request->hasFile('before_photos')) {
            $beforePhotos = [];
            foreach ($request->file('before_photos') as $photo) {
                $path = $photo->store('dental/before-photos', 'public');
                $beforePhotos[] = $path;
            }
            $validated['before_photos'] = $beforePhotos;
        }

        // Calculate remaining amount
        $validated['remaining_amount'] = $validated['total_cost'] - ($validated['paid_amount'] ?? 0);

        // Calculate monthly installment if payment type is installments
        if ($validated['payment_type'] === 'installments' && $validated['installment_months']) {
            $validated['monthly_installment'] = $validated['total_cost'] / $validated['installment_months'];
        }

        $treatment = DentalTreatment::create($validated);

        // Generate installments if payment type is installments
        if ($treatment->payment_type === 'installments') {
            $treatment->generateInstallments();
        }

        return redirect()->route('dental.treatments.show', $treatment)
                        ->with('success', 'تم إنشاء خطة العلاج بنجاح');
    }

    /**
     * Display the specified dental treatment.
     */
    public function show(DentalTreatment $treatment)
    {
        $treatment->load([
            'patient', 
            'doctor', 
            'sessions' => function($q) {
                $q->orderBy('session_order');
            },
            'installments' => function($q) {
                $q->orderBy('installment_order');
            }
        ]);

        $upcomingSessions = $treatment->sessions()
                                     ->where('status', 'scheduled')
                                     ->orderBy('scheduled_date')
                                     ->limit(3)
                                     ->get();

        $overdueInstallments = $treatment->installments()
                                        ->where('status', 'overdue')
                                        ->orderBy('due_date')
                                        ->get();

        return view('dental.treatments.show', compact(
            'treatment', 'upcomingSessions', 'overdueInstallments'
        ));
    }

    /**
     * Show the form for editing the dental treatment.
     */
    public function edit(DentalTreatment $treatment)
    {
        if (!$treatment->canBeEdited()) {
            return back()->with('error', 'لا يمكن تعديل هذه الخطة العلاجية');
        }

        $patients = Patient::all();
        $doctors = User::role('doctor')->whereHas('doctor', function($q) {
            $q->where('specialization', 'like', '%dental%')
              ->orWhere('specialization', 'orthodontics')
              ->orWhere('specialization', 'oral_surgery');
        })->get();
        
        $treatmentTypes = DentalTreatment::getTreatmentTypes();
        $priorities = DentalTreatment::getPriorities();
        $statuses = DentalTreatment::getStatuses();

        return view('dental.treatments.edit', compact(
            'treatment', 'patients', 'doctors', 'treatmentTypes', 'priorities', 'statuses'
        ));
    }

    /**
     * Update the specified dental treatment.
     */
    public function update(DentalTreatmentRequest $request, DentalTreatment $treatment)
    {
        if (!$treatment->canBeEdited()) {
            return back()->with('error', 'لا يمكن تعديل هذه الخطة العلاجية');
        }

        $validated = $request->validated();

        // Handle before photos upload
        if ($request->hasFile('before_photos')) {
            // Delete old photos
            if ($treatment->before_photos) {
                foreach ($treatment->before_photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }
            
            $beforePhotos = [];
            foreach ($request->file('before_photos') as $photo) {
                $path = $photo->store('dental/before-photos', 'public');
                $beforePhotos[] = $path;
            }
            $validated['before_photos'] = $beforePhotos;
        }

        // Handle after photos upload
        if ($request->hasFile('after_photos')) {
            // Delete old photos
            if ($treatment->after_photos) {
                foreach ($treatment->after_photos as $photo) {
                    Storage::disk('public')->delete($photo);
                }
            }
            
            $afterPhotos = [];
            foreach ($request->file('after_photos') as $photo) {
                $path = $photo->store('dental/after-photos', 'public');
                $afterPhotos[] = $path;
            }
            $validated['after_photos'] = $afterPhotos;
        }

        // Recalculate installments if payment details changed
        $paymentChanged = $treatment->total_cost != $validated['total_cost'] ||
                         $treatment->payment_type != $validated['payment_type'] ||
                         $treatment->installment_months != ($validated['installment_months'] ?? null);

        $treatment->update($validated);

        if ($paymentChanged && $treatment->payment_type === 'installments') {
            $treatment->generateInstallments();
        }

        return redirect()->route('dental.treatments.show', $treatment)
                        ->with('success', 'تم تحديث خطة العلاج بنجاح');
    }

    /**
     * Remove the specified dental treatment.
     */
    public function destroy(DentalTreatment $treatment)
    {
        if (!$treatment->canBeCancelled()) {
            return back()->with('error', 'لا يمكن حذف هذه الخطة العلاجية');
        }

        // Delete photos
        if ($treatment->before_photos) {
            foreach ($treatment->before_photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }
        
        if ($treatment->after_photos) {
            foreach ($treatment->after_photos as $photo) {
                Storage::disk('public')->delete($photo);
            }
        }

        // Delete session photos
        foreach ($treatment->sessions as $session) {
            if ($session->session_photos) {
                foreach ($session->session_photos as $photo) {
                    Storage::disk('public')->delete($photo['path']);
                }
            }
        }

        $treatment->delete();

        return redirect()->route('dental.treatments.index')
                        ->with('success', 'تم حذف خطة العلاج بنجاح');
    }

    /**
     * Update treatment status
     */
    public function updateStatus(Request $request, DentalTreatment $treatment)
    {
        $request->validate([
            'status' => 'required|in:planned,in_progress,completed,cancelled,on_hold'
        ]);

        $oldStatus = $treatment->status;
        $treatment->update(['status' => $request->status]);

        // If marking as completed, set actual end date
        if ($request->status === 'completed' && $oldStatus !== 'completed') {
            $treatment->update(['actual_end_date' => now()->toDateString()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة العلاج بنجاح',
            'status' => $treatment->status,
            'status_display' => $treatment->status_display,
            'status_color' => $treatment->status_color
        ]);
    }

    /**
     * Generate treatment plan
     */
    public function generatePlan(Request $request, DentalTreatment $treatment)
    {
        $request->validate([
            'sessions' => 'required|array|min:1',
            'sessions.*.title' => 'required|string|max:255',
            'sessions.*.description' => 'required|string',
            'sessions.*.cost' => 'required|numeric|min:0',
            'sessions.*.duration' => 'nullable|date_format:H:i'
        ]);

        // Delete existing sessions if any
        $treatment->sessions()->delete();

        foreach ($request->sessions as $index => $sessionData) {
            $treatment->sessions()->create([
                'session_order' => $index + 1,
                'session_title' => $sessionData['title'],
                'session_description' => $sessionData['description'],
                'session_cost' => $sessionData['cost'],
                'duration' => $sessionData['duration'] ?? '01:00',
                'status' => 'scheduled'
            ]);
        }

        // Update total sessions count
        $treatment->update(['total_sessions' => count($request->sessions)]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء خطة العلاج بنجاح',
            'sessions_count' => count($request->sessions)
        ]);
    }

    /**
     * Treatment statistics
     */
    public function statistics(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        $dateFrom = $request->get('date_from', now()->startOfMonth());
        $dateTo = $request->get('date_to', now()->endOfMonth());

        $query = DentalTreatment::whereBetween('created_at', [$dateFrom, $dateTo]);
        
        if ($doctorId) {
            $query->byDoctor($doctorId);
        }

        $stats = [
            'total_treatments' => $query->count(),
            'active_treatments' => $query->where('status', 'in_progress')->count(),
            'completed_treatments' => $query->where('status', 'completed')->count(),
            'total_revenue' => $query->sum('total_cost'),
            'collected_revenue' => $query->sum('paid_amount'),
            'pending_revenue' => $query->sum('remaining_amount'),
            'installment_treatments' => $query->where('payment_type', 'installments')->count(),
            'overdue_installments' => DentalInstallment::whereHas('dentalTreatment', function($q) use ($query) {
                $q->whereIn('id', $query->pluck('id'));
            })->where('status', 'overdue')->count()
        ];

        $treatmentsByType = $query->selectRaw('treatment_type, COUNT(*) as count')
                                 ->groupBy('treatment_type')
                                 ->pluck('count', 'treatment_type')
                                 ->toArray();

        $treatmentsByStatus = $query->selectRaw('status, COUNT(*) as count')
                                   ->groupBy('status')
                                   ->pluck('count', 'status')
                                   ->toArray();

        return response()->json([
            'success' => true,
            'stats' => $stats,
            'treatments_by_type' => $treatmentsByType,
            'treatments_by_status' => $treatmentsByStatus
        ]);
    }

    /**
     * Export treatments
     */
    public function export(Request $request)
    {
        // TODO: Implement export functionality
        return response()->json([
            'success' => false,
            'message' => 'ميزة التصدير قيد التطوير'
        ]);
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,update_status,export',
            'treatment_ids' => 'required|array|min:1',
            'treatment_ids.*' => 'exists:dental_treatments,id',
            'status' => 'required_if:action,update_status|in:planned,in_progress,completed,cancelled,on_hold'
        ]);

        $treatments = DentalTreatment::whereIn('id', $request->treatment_ids);
        $count = $treatments->count();

        switch ($request->action) {
            case 'delete':
                $treatments->each(function($treatment) {
                    if ($treatment->canBeCancelled()) {
                        $treatment->delete();
                    }
                });
                $message = "تم حذف {$count} خطة علاج";
                break;

            case 'update_status':
                $treatments->update(['status' => $request->status]);
                $statusDisplay = DentalTreatment::getStatuses()[$request->status];
                $message = "تم تحديث حالة {$count} خطة علاج إلى {$statusDisplay}";
                break;

            case 'export':
                // TODO: Implement bulk export
                return response()->json([
                    'success' => false,
                    'message' => 'ميزة التصدير قيد التطوير'
                ]);
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}