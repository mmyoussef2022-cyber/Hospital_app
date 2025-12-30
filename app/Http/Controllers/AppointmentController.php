<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Http\Requests\AppointmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of appointments.
     */
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor']);

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->byDateRange($request->start_date, $request->end_date);
        } elseif ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        } else {
            // Default to today's appointments
            $query->today();
        }

        // Filter by doctor
        if ($request->filled('doctor_id')) {
            $query->byDoctor($request->doctor_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        // Filter by patient
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        $appointments = $query->orderBy('appointment_date')
                            ->orderBy('appointment_time')
                            ->paginate(20);

        // Get doctors - try role first, fallback to users with doctor relationship
        try {
            $doctors = User::role('doctor')->get();
        } catch (\Exception $e) {
            // Fallback: get users who have doctor profiles
            $doctors = User::whereHas('doctor')->get();
        }
        
        // If still empty, get all users (for development)
        if ($doctors->isEmpty()) {
            $doctors = User::all();
        }
        
        $patients = Patient::all();

        return view('appointments.index', compact('appointments', 'doctors', 'patients'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create(Request $request)
    {
        // Get doctors - try role first, fallback to users with doctor relationship
        try {
            $doctors = User::role('doctor')->get();
        } catch (\Exception $e) {
            // Fallback: get users who have doctor profiles
            $doctors = User::whereHas('doctor')->get();
        }
        
        // If still empty, get all users (for development)
        if ($doctors->isEmpty()) {
            $doctors = User::all();
        }
        
        $patients = Patient::all();
        
        $selectedPatient = null;
        if ($request->filled('patient_id')) {
            $selectedPatient = Patient::find($request->patient_id);
        }

        return view('appointments.create', compact('doctors', 'patients', 'selectedPatient'));
    }

    /**
     * Store a newly created appointment.
     */
    public function store(AppointmentRequest $request)
    {
        $validated = $request->validated();

        // Check for conflicts
        if (Appointment::hasConflict(
            $validated['doctor_id'],
            $validated['appointment_date'],
            $validated['appointment_time'],
            $validated['duration'] ?? 30
        )) {
            return back()->withErrors([
                'appointment_time' => 'يوجد تعارض في المواعيد. يرجى اختيار وقت آخر.'
            ])->withInput();
        }

        $appointment = Appointment::create($validated);

        // TODO: Send notification to patient and doctor
        
        return redirect()->route('appointments.show', $appointment)
                        ->with('success', 'تم حجز الموعد بنجاح');
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'doctor']);
        
        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the appointment.
     */
    public function edit(Appointment $appointment)
    {
        // Get doctors - try role first, fallback to users with doctor relationship
        try {
            $doctors = User::role('doctor')->get();
        } catch (\Exception $e) {
            // Fallback: get users who have doctor profiles
            $doctors = User::whereHas('doctor')->get();
        }
        
        // If still empty, get all users (for development)
        if ($doctors->isEmpty()) {
            $doctors = User::all();
        }
        
        $patients = Patient::all();

        return view('appointments.edit', compact('appointment', 'doctors', 'patients'));
    }

    /**
     * Update the specified appointment.
     */
    public function update(AppointmentRequest $request, Appointment $appointment)
    {
        $validated = $request->validated();

        // Check for conflicts (excluding current appointment)
        if (Appointment::hasConflict(
            $validated['doctor_id'],
            $validated['appointment_date'],
            $validated['appointment_time'],
            $validated['duration'] ?? 30,
            $appointment->id
        )) {
            return back()->withErrors([
                'appointment_time' => 'يوجد تعارض في المواعيد. يرجى اختيار وقت آخر.'
            ])->withInput();
        }

        $appointment->update($validated);

        return redirect()->route('appointments.show', $appointment)
                        ->with('success', 'تم تحديث الموعد بنجاح');
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy(Appointment $appointment)
    {
        if (!$appointment->canBeCancelled()) {
            return back()->with('error', 'لا يمكن إلغاء هذا الموعد');
        }

        $appointment->update(['status' => 'cancelled']);

        return redirect()->route('appointments.index')
                        ->with('success', 'تم إلغاء الموعد بنجاح');
    }

    /**
     * Get available time slots for a doctor on a specific date.
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'nullable|integer|min:15|max:120'
        ]);

        $slots = Appointment::getAvailableSlots(
            $request->doctor_id,
            $request->date,
            $request->duration ?? 30
        );

        return response()->json([
            'slots' => $slots,
            'date' => $request->date,
            'doctor_id' => $request->doctor_id
        ]);
    }

    /**
     * Update appointment status.
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:scheduled,confirmed,in_progress,completed,cancelled,no_show'
        ]);

        $appointment->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الموعد بنجاح',
            'status' => $appointment->status,
            'status_display' => $appointment->status_display,
            'status_color' => $appointment->status_color
        ]);
    }

    /**
     * Get appointments for calendar view.
     */
    public function calendar(Request $request)
    {
        $start = $request->get('start', now()->startOfMonth());
        $end = $request->get('end', now()->endOfMonth());
        $doctorId = $request->get('doctor_id');

        $query = Appointment::with(['patient', 'doctor'])
            ->byDateRange($start, $end);

        if ($doctorId) {
            $query->byDoctor($doctorId);
        }

        $appointments = $query->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'title' => $appointment->patient->name . ' - ' . $appointment->type_display,
                    'start' => $appointment->full_date_time->toISOString(),
                    'end' => $appointment->end_time->toISOString(),
                    'backgroundColor' => $this->getStatusColor($appointment->status),
                    'borderColor' => $this->getStatusColor($appointment->status),
                    'textColor' => '#fff',
                    'extendedProps' => [
                        'patient_name' => $appointment->patient->name,
                        'patient_phone' => $appointment->patient->phone,
                        'doctor_name' => $appointment->doctor->name,
                        'type' => $appointment->type_display,
                        'status' => $appointment->status_display,
                        'notes' => $appointment->notes,
                        'duration' => $appointment->duration,
                        'appointment_id' => $appointment->id
                    ]
                ];
            });

        return response()->json($appointments);
    }

    /**
     * Doctor-specific calendar view
     */
    public function doctorCalendar(Request $request)
    {
        // Get doctor ID from request or current user
        $doctorId = $request->get('doctor_id');
        
        // If no doctor specified, try to get from current user
        if (!$doctorId && auth()->user() && auth()->user()->doctor) {
            $doctorId = auth()->user()->doctor->user_id;
        }
        
        // Get doctors - try role first, fallback to users with doctor relationship
        try {
            $doctors = User::role('doctor')->get();
        } catch (\Exception $e) {
            // Fallback: get users who have doctor profiles
            $doctors = User::whereHas('doctor')->get();
        }
        
        // If still empty, get all users (for development/admin access)
        if ($doctors->isEmpty()) {
            $doctors = User::all();
        }
        
        // If no doctor specified and we have doctors, use the first one
        if (!$doctorId && $doctors->isNotEmpty()) {
            $doctorId = $doctors->first()->id;
        }
        
        // If still no doctor, show page with empty state
        if (!$doctorId || $doctors->isEmpty()) {
            return view('appointments.doctor-calendar', [
                'doctor' => null,
                'doctors' => collect(),
                'error' => 'لا توجد أطباء متاحون في النظام. يرجى إضافة أطباء أولاً.'
            ]);
        }

        $doctor = User::with('doctor')->find($doctorId);
        
        // If doctor not found, use first available doctor
        if (!$doctor && $doctors->isNotEmpty()) {
            $doctor = $doctors->first();
            $doctorId = $doctor->id;
        }
        
        // Final check - if we still don't have a valid doctor
        if (!$doctor) {
            return view('appointments.doctor-calendar', [
                'doctor' => null,
                'doctors' => $doctors,
                'error' => 'الطبيب المحدد غير موجود. يرجى اختيار طبيب آخر.'
            ]);
        }
        
        return view('appointments.doctor-calendar', compact('doctor', 'doctors'));
    }

    /**
     * Move appointment (drag and drop)
     */
    public function moveAppointment(Request $request, Appointment $appointment)
    {
        $request->validate([
            'start' => 'required|date',
            'doctor_id' => 'nullable|exists:users,id'
        ]);

        $newStart = Carbon::parse($request->start);
        $newDate = $newStart->format('Y-m-d');
        $newTime = $newStart->format('H:i');
        $newDoctorId = $request->doctor_id ?? $appointment->doctor_id;

        // Check for conflicts
        if (Appointment::hasConflict(
            $newDoctorId,
            $newDate,
            $newTime,
            $appointment->duration,
            $appointment->id
        )) {
            return response()->json([
                'success' => false,
                'message' => 'يوجد تعارض في المواعيد. لا يمكن نقل الموعد إلى هذا الوقت.'
            ], 422);
        }

        // Check if doctor is working at this time
        $doctor = User::with('doctor')->find($newDoctorId);
        if (!$this->isDoctorWorkingAt($doctor->doctor, $newStart)) {
            return response()->json([
                'success' => false,
                'message' => 'الطبيب غير متاح في هذا الوقت.'
            ], 422);
        }

        $appointment->update([
            'appointment_date' => $newDate,
            'appointment_time' => $newTime,
            'doctor_id' => $newDoctorId
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم نقل الموعد بنجاح'
        ]);
    }

    /**
     * Resize appointment (change duration)
     */
    public function resizeAppointment(Request $request, Appointment $appointment)
    {
        $request->validate([
            'end' => 'required|date|after:start',
            'start' => 'required|date'
        ]);

        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        $newDuration = $start->diffInMinutes($end);

        // Check for conflicts with new duration
        if (Appointment::hasConflict(
            $appointment->doctor_id,
            $appointment->appointment_date->format('Y-m-d'),
            $appointment->appointment_time,
            $newDuration,
            $appointment->id
        )) {
            return response()->json([
                'success' => false,
                'message' => 'يوجد تعارض مع المواعيد الأخرى. لا يمكن تغيير مدة الموعد.'
            ], 422);
        }

        $appointment->update(['duration' => $newDuration]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث مدة الموعد بنجاح'
        ]);
    }

    /**
     * Get doctor working hours for calendar
     */
    public function getDoctorWorkingHours(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id'
        ]);

        $doctor = User::with('doctor')->findOrFail($request->doctor_id);
        
        if (!$doctor->doctor) {
            return response()->json([
                'success' => false,
                'message' => 'الطبيب غير موجود'
            ], 404);
        }

        $workingHours = [];
        $daysOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        foreach ($daysOfWeek as $index => $day) {
            $dayHours = $doctor->doctor->working_hours[$day] ?? null;
            
            if ($dayHours && $dayHours['is_working']) {
                $workingHours[] = [
                    'daysOfWeek' => [$index], // FullCalendar uses 0=Sunday
                    'startTime' => $dayHours['start'],
                    'endTime' => $dayHours['end']
                ];
            }
        }

        return response()->json([
            'success' => true,
            'working_hours' => $workingHours,
            'doctor_name' => $doctor->name
        ]);
    }

    /**
     * Quick create appointment from calendar
     */
    public function quickCreate(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id',
            'patient_id' => 'required|exists:patients,id',
            'start' => 'required|date',
            'duration' => 'nullable|integer|min:15|max:240',
            'type' => 'nullable|in:consultation,follow_up,emergency,surgery',
            'notes' => 'nullable|string|max:500'
        ]);

        $start = Carbon::parse($request->start);
        $duration = $request->duration ?? 30;

        // Check for conflicts
        if (Appointment::hasConflict(
            $request->doctor_id,
            $start->format('Y-m-d'),
            $start->format('H:i'),
            $duration
        )) {
            return response()->json([
                'success' => false,
                'message' => 'يوجد تعارض في المواعيد. يرجى اختيار وقت آخر.'
            ], 422);
        }

        // Check if doctor is working
        $doctor = User::with('doctor')->find($request->doctor_id);
        if (!$this->isDoctorWorkingAt($doctor->doctor, $start)) {
            return response()->json([
                'success' => false,
                'message' => 'الطبيب غير متاح في هذا الوقت.'
            ], 422);
        }

        $appointment = Appointment::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $start->format('Y-m-d'),
            'appointment_time' => $start->format('H:i'),
            'duration' => $duration,
            'type' => $request->type ?? 'consultation',
            'status' => 'scheduled',
            'notes' => $request->notes
        ]);

        $appointment->load(['patient', 'doctor']);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الموعد بنجاح',
            'appointment' => [
                'id' => $appointment->id,
                'title' => $appointment->patient->name . ' - ' . $appointment->type_display,
                'start' => $appointment->full_date_time->toISOString(),
                'end' => $appointment->end_time->toISOString(),
                'backgroundColor' => $this->getStatusColor($appointment->status),
                'borderColor' => $this->getStatusColor($appointment->status),
                'textColor' => '#fff',
                'extendedProps' => [
                    'patient_name' => $appointment->patient->name,
                    'patient_phone' => $appointment->patient->phone,
                    'doctor_name' => $appointment->doctor->name,
                    'type' => $appointment->type_display,
                    'status' => $appointment->status_display,
                    'notes' => $appointment->notes,
                    'duration' => $appointment->duration,
                    'appointment_id' => $appointment->id
                ]
            ]
        ]);
    }

    /**
     * Check if doctor is working at specific time
     */
    private function isDoctorWorkingAt($doctor, Carbon $dateTime)
    {
        if (!$doctor || !$doctor->working_hours) {
            return false;
        }

        $dayName = strtolower($dateTime->format('l'));
        $dayHours = $doctor->working_hours[$dayName] ?? null;

        if (!$dayHours || !$dayHours['is_working']) {
            return false;
        }

        $workStart = Carbon::parse($dateTime->format('Y-m-d') . ' ' . $dayHours['start']);
        $workEnd = Carbon::parse($dateTime->format('Y-m-d') . ' ' . $dayHours['end']);

        return $dateTime->between($workStart, $workEnd);
    }

    /**
     * Get today's appointments for dashboard.
     */
    public function todayAppointments()
    {
        try {
            $appointments = Appointment::with(['patient', 'doctor'])
                ->today()
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->get();

            return view('appointments.today-final', compact('appointments'));
        } catch (\Exception $e) {
            return response('Error: ' . $e->getMessage() . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }

    private function getStatusColor($status)
    {
        return match($status) {
            'scheduled' => '#007bff',
            'confirmed' => '#28a745',
            'in_progress' => '#ffc107',
            'completed' => '#17a2b8',
            'cancelled' => '#dc3545',
            'no_show' => '#6c757d',
            default => '#007bff'
        };
    }
}
