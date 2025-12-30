<?php

namespace App\Http\Controllers;

use App\Models\Surgery;
use App\Models\Patient;
use App\Models\User;
use App\Models\SurgicalProcedure;
use App\Models\OperatingRoom;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SurgeryController extends Controller
{
    public function index(Request $request)
    {
        $query = Surgery::with(['patient', 'primarySurgeon', 'surgicalProcedure', 'operatingRoom']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('surgeon_id')) {
            $query->where('primary_surgeon_id', $request->surgeon_id);
        }

        if ($request->filled('operating_room_id')) {
            $query->where('operating_room_id', $request->operating_room_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('scheduled_start_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('scheduled_start_time', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('surgery_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%")
                         ->orWhere('national_id', 'like', "%{$search}%");
                  })
                  ->orWhereHas('primarySurgeon', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $surgeries = $query->orderBy('scheduled_start_time', 'desc')->paginate(15);

        // Get filter options
        $surgeons = User::whereHas('doctor')->where('is_active', true)->get();
        $operatingRooms = OperatingRoom::active()->get();

        return view('surgeries.index', compact('surgeries', 'surgeons', 'operatingRooms'));
    }

    public function today()
    {
        $todaySurgeries = Surgery::today()
            ->with(['patient', 'primarySurgeon', 'surgicalProcedure', 'operatingRoom', 'surgicalTeam.user'])
            ->orderBy('scheduled_start_time')
            ->get();

        $statistics = [
            'total' => $todaySurgeries->count(),
            'scheduled' => $todaySurgeries->where('status', 'scheduled')->count(),
            'in_progress' => $todaySurgeries->where('status', 'in_progress')->count(),
            'completed' => $todaySurgeries->where('status', 'completed')->count(),
            'cancelled' => $todaySurgeries->where('status', 'cancelled')->count(),
            'emergency' => $todaySurgeries->where('is_emergency', true)->count(),
        ];

        return view('surgeries.today', compact('todaySurgeries', 'statistics'));
    }

    public function dashboard()
    {
        $today = now();
        $startOfWeek = $today->startOfWeek();
        $endOfWeek = $today->copy()->endOfWeek();

        // Statistics
        $stats = [
            'today_total' => Surgery::today()->count(),
            'today_completed' => Surgery::today()->where('status', 'completed')->count(),
            'today_in_progress' => Surgery::today()->where('status', 'in_progress')->count(),
            'today_scheduled' => Surgery::today()->where('status', 'scheduled')->count(),
            'week_total' => Surgery::whereBetween('scheduled_start_time', [$startOfWeek, $endOfWeek])->count(),
            'emergency_queue' => Surgery::where('is_emergency', true)->whereIn('status', ['scheduled', 'pre_op'])->count(),
            'available_rooms' => OperatingRoom::available()->count(),
            'occupied_rooms' => OperatingRoom::occupied()->count(),
        ];

        // Today's surgeries by status
        $todaySurgeries = Surgery::today()
            ->with(['patient', 'primarySurgeon', 'operatingRoom', 'surgicalProcedure'])
            ->orderBy('scheduled_start_time')
            ->get();

        // Emergency queue
        $emergencyQueue = Surgery::getEmergencyQueue()
            ->with(['patient', 'primarySurgeon', 'surgicalProcedure'])
            ->take(5)
            ->get();

        // Operating room status
        $operatingRooms = OperatingRoom::active()
            ->with(['currentSurgery.patient', 'nextSurgery.patient'])
            ->get();

        // Weekly surgery statistics
        $weeklyStats = Surgery::whereBetween('scheduled_start_time', [$startOfWeek, $endOfWeek])
            ->selectRaw('DATE(scheduled_start_time) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('surgeries.dashboard', compact(
            'stats', 'todaySurgeries', 'emergencyQueue', 'operatingRooms', 'weeklyStats'
        ));
    }

    public function create()
    {
        $patients = Patient::where('is_active', true)->orderBy('name')->get();
        $surgeons = User::whereHas('doctor')->where('is_active', true)->orderBy('name')->get();
        $procedures = SurgicalProcedure::active()->orderBy('name')->get();
        $operatingRooms = OperatingRoom::active()->orderBy('or_number')->get();
        $appointments = Appointment::where('status', 'confirmed')
            ->whereDate('appointment_date', '>=', today())
            ->with(['patient', 'doctor'])
            ->orderBy('appointment_date')
            ->get();

        return view('surgeries.create', compact(
            'patients', 'surgeons', 'procedures', 'operatingRooms', 'appointments'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'primary_surgeon_id' => 'required|exists:users,id',
            'surgical_procedure_id' => 'required|exists:surgical_procedures,id',
            'operating_room_id' => 'required|exists:operating_rooms,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'scheduled_start_time' => 'required|date|after:now',
            'scheduled_end_time' => 'required|date|after:scheduled_start_time',
            'priority' => 'required|in:routine,urgent,emergency,elective',
            'type' => 'required|in:inpatient,outpatient,day_surgery,emergency',
            'pre_operative_notes' => 'nullable|string|max:1000',
            'estimated_cost' => 'nullable|numeric|min:0',
            'estimated_duration' => 'nullable|integer|min:15',
            'is_emergency' => 'boolean',
            'requires_icu' => 'boolean',
            'requires_blood_bank' => 'boolean',
        ]);

        // Check for conflicts
        $procedure = SurgicalProcedure::find($validated['surgical_procedure_id']);
        $operatingRoom = OperatingRoom::find($validated['operating_room_id']);
        
        $startTime = Carbon::parse($validated['scheduled_start_time']);
        $endTime = Carbon::parse($validated['scheduled_end_time']);

        // Check room availability
        if (!$operatingRoom->isAvailableAt($startTime, $endTime)) {
            return back()->withErrors(['operating_room_id' => 'غرفة العمليات غير متاحة في الوقت المحدد']);
        }

        // Check if room can accommodate procedure
        if (!$operatingRoom->canAccommodateProcedure($procedure)) {
            return back()->withErrors(['operating_room_id' => 'غرفة العمليات غير مناسبة لهذا النوع من العمليات']);
        }

        $validated['created_by'] = auth()->id();
        $validated['estimated_duration'] = $validated['estimated_duration'] ?? $procedure->estimated_duration;
        $validated['estimated_cost'] = $validated['estimated_cost'] ?? $procedure->total_cost;

        try {
            DB::beginTransaction();

            $surgery = Surgery::create($validated);

            // Update appointment if linked
            if ($surgery->appointment_id) {
                $surgery->appointment->update(['status' => 'scheduled_for_surgery']);
            }

            DB::commit();

            return redirect()->route('surgeries.show', $surgery)
                ->with('success', 'تم جدولة العملية الجراحية بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء جدولة العملية: ' . $e->getMessage()]);
        }
    }

    public function show(Surgery $surgery)
    {
        $surgery->load([
            'patient',
            'primarySurgeon.doctor',
            'surgicalProcedure',
            'operatingRoom.room',
            'appointment',
            'surgicalTeam.user',
            'preOperativeAssessment',
            'postOperativeCare',
            'createdBy',
            'updatedBy'
        ]);

        return view('surgeries.show', compact('surgery'));
    }

    public function edit(Surgery $surgery)
    {
        if (in_array($surgery->status, ['completed', 'cancelled'])) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('error', 'لا يمكن تعديل العملية المكتملة أو الملغاة');
        }

        $patients = Patient::where('is_active', true)->orderBy('name')->get();
        $surgeons = User::whereHas('doctor')->where('is_active', true)->orderBy('name')->get();
        $procedures = SurgicalProcedure::active()->orderBy('name')->get();
        $operatingRooms = OperatingRoom::active()->orderBy('or_number')->get();

        return view('surgeries.edit', compact('surgery', 'patients', 'surgeons', 'procedures', 'operatingRooms'));
    }

    public function update(Request $request, Surgery $surgery)
    {
        if (in_array($surgery->status, ['completed', 'cancelled'])) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('error', 'لا يمكن تعديل العملية المكتملة أو الملغاة');
        }

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'primary_surgeon_id' => 'required|exists:users,id',
            'surgical_procedure_id' => 'required|exists:surgical_procedures,id',
            'operating_room_id' => 'required|exists:operating_rooms,id',
            'scheduled_start_time' => 'required|date',
            'scheduled_end_time' => 'required|date|after:scheduled_start_time',
            'priority' => 'required|in:routine,urgent,emergency,elective',
            'type' => 'required|in:inpatient,outpatient,day_surgery,emergency',
            'pre_operative_notes' => 'nullable|string|max:1000',
            'estimated_cost' => 'nullable|numeric|min:0',
            'estimated_duration' => 'nullable|integer|min:15',
            'is_emergency' => 'boolean',
            'requires_icu' => 'boolean',
            'requires_blood_bank' => 'boolean',
        ]);

        $validated['updated_by'] = auth()->id();

        $surgery->update($validated);

        return redirect()->route('surgeries.show', $surgery)
            ->with('success', 'تم تحديث بيانات العملية بنجاح');
    }

    public function destroy(Surgery $surgery)
    {
        if (!$surgery->canBeCancelled()) {
            return back()->with('error', 'لا يمكن حذف هذه العملية');
        }

        $surgery->delete();

        return redirect()->route('surgeries.index')
            ->with('success', 'تم حذف العملية بنجاح');
    }

    // Surgery workflow methods
    public function start(Surgery $surgery)
    {
        if (!$surgery->canBeStarted()) {
            return back()->with('error', 'لا يمكن بدء العملية في الوقت الحالي');
        }

        if ($surgery->startSurgery()) {
            return back()->with('success', 'تم بدء العملية الجراحية');
        }

        return back()->with('error', 'فشل في بدء العملية');
    }

    public function complete(Request $request, Surgery $surgery)
    {
        if (!$surgery->canBeCompleted()) {
            return back()->with('error', 'لا يمكن إكمال العملية في الوقت الحالي');
        }

        $validated = $request->validate([
            'operative_notes' => 'required|string|max:2000',
            'post_operative_notes' => 'nullable|string|max:1000',
            'complications' => 'nullable|string|max:1000',
            'actual_cost' => 'nullable|numeric|min:0',
            'blood_loss' => 'nullable|array',
            'medications_given' => 'nullable|array',
        ]);

        if ($surgery->completeSurgery($validated)) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('success', 'تم إكمال العملية الجراحية بنجاح');
        }

        return back()->with('error', 'فشل في إكمال العملية');
    }

    public function cancel(Request $request, Surgery $surgery)
    {
        if (!$surgery->canBeCancelled()) {
            return back()->with('error', 'لا يمكن إلغاء العملية في الوقت الحالي');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);

        if ($surgery->cancelSurgery($validated['cancellation_reason'])) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('success', 'تم إلغاء العملية الجراحية');
        }

        return back()->with('error', 'فشل في إلغاء العملية');
    }

    public function postpone(Request $request, Surgery $surgery)
    {
        if (!$surgery->canBePostponed()) {
            return back()->with('error', 'لا يمكن تأجيل العملية في الوقت الحالي');
        }

        $validated = $request->validate([
            'new_start_time' => 'required|date|after:now',
            'new_end_time' => 'required|date|after:new_start_time',
            'postpone_reason' => 'nullable|string|max:500',
        ]);

        $newStartTime = Carbon::parse($validated['new_start_time']);
        $newEndTime = Carbon::parse($validated['new_end_time']);

        if ($surgery->postponeSurgery($newStartTime, $newEndTime, $validated['postpone_reason'])) {
            return redirect()->route('surgeries.show', $surgery)
                ->with('success', 'تم تأجيل العملية الجراحية');
        }

        return back()->with('error', 'فشل في تأجيل العملية');
    }

    // AJAX endpoints
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'operating_room_id' => 'required|exists:operating_rooms,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'surgery_id' => 'nullable|exists:surgeries,id',
        ]);

        $operatingRoom = OperatingRoom::find($validated['operating_room_id']);
        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);

        $conflicts = Surgery::where('operating_room_id', $validated['operating_room_id'])
            ->when($validated['surgery_id'], function($q, $surgeryId) {
                $q->where('id', '!=', $surgeryId);
            })
            ->where(function($q) use ($startTime, $endTime) {
                $q->whereBetween('scheduled_start_time', [$startTime, $endTime])
                  ->orWhereBetween('scheduled_end_time', [$startTime, $endTime])
                  ->orWhere(function($q2) use ($startTime, $endTime) {
                      $q2->where('scheduled_start_time', '<=', $startTime)
                         ->where('scheduled_end_time', '>=', $endTime);
                  });
            })
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->with(['patient', 'surgicalProcedure'])
            ->get();

        return response()->json([
            'available' => $conflicts->isEmpty(),
            'conflicts' => $conflicts,
            'room_status' => $operatingRoom->status,
        ]);
    }

    public function getProcedureDetails(SurgicalProcedure $procedure)
    {
        return response()->json([
            'estimated_duration' => $procedure->estimated_duration,
            'total_cost' => $procedure->total_cost,
            'complexity' => $procedure->complexity,
            'required_equipment' => $procedure->required_equipment,
            'requires_icu' => $procedure->requires_icu,
            'requires_blood_bank' => $procedure->requires_blood_bank,
        ]);
    }
}