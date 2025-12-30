<?php

namespace App\Http\Controllers;

use App\Models\Bed;
use App\Models\Room;
use App\Models\Patient;
use Illuminate\Http\Request;

class BedController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of beds.
     */
    public function index(Request $request)
    {
        $query = Bed::with(['room', 'currentAssignment.patient']);

        // Filter by room
        if ($request->filled('room_id')) {
            $query->where('room_id', $request->room_id);
        }

        // Filter by bed type
        if ($request->filled('bed_type')) {
            $query->byType($request->bed_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by room type
        if ($request->filled('room_type')) {
            $query->whereHas('room', function($q) use ($request) {
                $q->where('room_type', $request->room_type);
            });
        }

        // Search by bed number or room number
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('bed_number', 'like', '%' . $search . '%')
                  ->orWhereHas('room', function($roomQuery) use ($search) {
                      $roomQuery->where('room_number', 'like', '%' . $search . '%');
                  });
            });
        }

        $beds = $query->orderBy('room_id')
                     ->orderBy('bed_number')
                     ->paginate(20);

        // Get filter options
        $rooms = Room::select('id', 'room_number', 'room_type')
                    ->orderBy('room_number')
                    ->get();
        
        $bedTypes = Bed::select('bed_type')->distinct()->pluck('bed_type');
        $roomTypes = Room::select('room_type')->distinct()->pluck('room_type');

        return view('beds.index', compact('beds', 'rooms', 'bedTypes', 'roomTypes'));
    }

    /**
     * Show the form for creating a new bed.
     */
    public function create()
    {
        $rooms = Room::select('id', 'room_number', 'room_type', 'capacity')
                    ->with('beds')
                    ->orderBy('room_number')
                    ->get();

        return view('beds.create', compact('rooms'));
    }

    /**
     * Store a newly created bed.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'bed_number' => 'required|string|max:10',
            'bed_type' => 'required|in:standard,icu,pediatric,bariatric',
            'features' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        // Check if bed number already exists in room
        $existingBed = Bed::where('room_id', $validated['room_id'])
                         ->where('bed_number', $validated['bed_number'])
                         ->first();

        if ($existingBed) {
            return back()->withErrors(['bed_number' => 'رقم السرير موجود بالفعل في هذه الغرفة'])
                        ->withInput();
        }

        Bed::create($validated);

        return redirect()->route('beds.index')
                        ->with('success', 'تم إنشاء السرير بنجاح');
    }

    /**
     * Display the specified bed.
     */
    public function show(Bed $bed)
    {
        $bed->load([
            'room',
            'currentAssignment.patient',
            'assignments' => function($query) {
                $query->with(['patient', 'assignedBy'])
                      ->orderBy('assigned_at', 'desc')
                      ->limit(10);
            }
        ]);

        // Get bed statistics
        $stats = [
            'total_assignments' => $bed->assignments()->count(),
            'total_days_occupied' => $bed->assignments()
                                       ->where('status', 'discharged')
                                       ->get()
                                       ->sum(function($assignment) {
                                           return $assignment->getDurationAttribute();
                                       }),
            'average_stay' => $bed->assignments()
                                ->where('status', 'discharged')
                                ->get()
                                ->avg(function($assignment) {
                                    return $assignment->getDurationAttribute();
                                }) ?? 0,
            'current_patient' => $bed->currentPatient(),
            'last_cleaned' => $bed->last_cleaned_at,
            'needs_cleaning' => $bed->needsCleaning()
        ];

        return view('beds.show', compact('bed', 'stats'));
    }

    /**
     * Show the form for editing the bed.
     */
    public function edit(Bed $bed)
    {
        $rooms = Room::select('id', 'room_number', 'room_type')
                    ->orderBy('room_number')
                    ->get();

        return view('beds.edit', compact('bed', 'rooms'));
    }

    /**
     * Update the specified bed.
     */
    public function update(Request $request, Bed $bed)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'bed_number' => 'required|string|max:10',
            'bed_type' => 'required|in:standard,icu,pediatric,bariatric',
            'features' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        // Check if bed number already exists in room (excluding current bed)
        $existingBed = Bed::where('room_id', $validated['room_id'])
                         ->where('bed_number', $validated['bed_number'])
                         ->where('id', '!=', $bed->id)
                         ->first();

        if ($existingBed) {
            return back()->withErrors(['bed_number' => 'رقم السرير موجود بالفعل في هذه الغرفة'])
                        ->withInput();
        }

        $bed->update($validated);

        return redirect()->route('beds.show', $bed)
                        ->with('success', 'تم تحديث السرير بنجاح');
    }

    /**
     * Remove the specified bed.
     */
    public function destroy(Bed $bed)
    {
        // Check if bed has active assignment
        if ($bed->currentAssignment) {
            return back()->with('error', 'لا يمكن حذف السرير لوجود مريض مقيم به');
        }

        $bed->delete();

        return redirect()->route('beds.index')
                        ->with('success', 'تم حذف السرير بنجاح');
    }

    /**
     * Assign patient to bed.
     */
    public function assignPatient(Request $request, Bed $bed)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'expected_discharge_at' => 'nullable|date|after:now',
            'assignment_notes' => 'nullable|string'
        ]);

        try {
            $patient = Patient::findOrFail($validated['patient_id']);
            
            $assignment = $bed->assignPatient(
                $patient,
                auth()->user(),
                $validated['assignment_notes'] ?? null
            );

            if (isset($validated['expected_discharge_at'])) {
                $assignment->update(['expected_discharge_at' => $validated['expected_discharge_at']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تخصيص المريض للسرير بنجاح',
                'assignment' => $assignment->load(['patient', 'room'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Discharge patient from bed.
     */
    public function dischargePatient(Request $request, Bed $bed)
    {
        $validated = $request->validate([
            'discharge_notes' => 'nullable|string'
        ]);

        try {
            $success = $bed->dischargePatient(
                auth()->user(),
                $validated['discharge_notes'] ?? null
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم خروج المريض من السرير بنجاح'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'لا يوجد مريض مقيم في هذا السرير'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Mark bed as cleaned.
     */
    public function markCleaned(Bed $bed)
    {
        $bed->markCleaned();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل تنظيف السرير بنجاح'
        ]);
    }

    /**
     * Mark bed for maintenance.
     */
    public function markMaintenance(Bed $bed)
    {
        // Check if bed has active assignment
        if ($bed->currentAssignment) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن وضع السرير في الصيانة لوجود مريض به'
            ], 400);
        }

        $bed->markMaintenance();

        return response()->json([
            'success' => true,
            'message' => 'تم وضع السرير في الصيانة'
        ]);
    }

    /**
     * Complete bed maintenance.
     */
    public function completeMaintenance(Bed $bed)
    {
        $bed->completeMaintenance();

        return response()->json([
            'success' => true,
            'message' => 'تم إنهاء صيانة السرير'
        ]);
    }

    /**
     * Get available beds for assignment.
     */
    public function getAvailableBeds(Request $request)
    {
        $roomType = $request->get('room_type');
        $bedType = $request->get('bed_type');

        $beds = Bed::getAvailableBeds($roomType, $bedType);

        return response()->json($beds->map(function($bed) {
            return [
                'id' => $bed->id,
                'bed_number' => $bed->full_bed_number,
                'bed_type' => $bed->bed_type_display,
                'room_number' => $bed->room->room_number,
                'room_type' => $bed->room->room_type_display,
                'department' => $bed->room->department,
                'daily_rate' => $bed->room->daily_rate
            ];
        }));
    }

    /**
     * Toggle bed status.
     */
    public function toggleStatus(Bed $bed)
    {
        // Can only toggle between available and maintenance if not occupied
        if ($bed->status === 'occupied') {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن تغيير حالة السرير المشغول'
            ], 400);
        }

        $newStatus = $bed->status === 'available' ? 'maintenance' : 'available';
        $bed->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير حالة السرير بنجاح',
            'status' => $newStatus,
            'status_display' => $bed->status_display,
            'status_color' => $bed->status_color
        ]);
    }
}