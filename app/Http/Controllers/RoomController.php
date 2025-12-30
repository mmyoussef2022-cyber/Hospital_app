<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Bed;
use App\Models\Patient;
use App\Models\RoomAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of rooms.
     */
    public function index(Request $request)
    {
        $query = Room::with(['beds', 'currentAssignment.patient']);

        // Filter by room type
        if ($request->filled('room_type')) {
            $query->byType($request->room_type);
        }

        // Filter by department
        if ($request->filled('department')) {
            $query->byDepartment($request->department);
        }

        // Filter by floor
        if ($request->filled('floor')) {
            $query->byFloor($request->floor);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search by room number
        if ($request->filled('search')) {
            $query->where('room_number', 'like', '%' . $request->search . '%');
        }

        // Filter by availability
        if ($request->filled('availability')) {
            if ($request->availability === 'available') {
                $query->available();
            } elseif ($request->availability === 'occupied') {
                $query->occupied();
            }
        }

        $rooms = $query->orderBy('room_number')->paginate(20);

        // Get filter options
        $roomTypes = Room::select('room_type')->distinct()->pluck('room_type');
        $departments = Room::select('department')->distinct()->pluck('department');
        $floors = Room::select('floor')->distinct()->orderBy('floor')->pluck('floor');

        // Get statistics
        $stats = Room::getOccupancyStatistics();

        return view('rooms.index', compact('rooms', 'roomTypes', 'departments', 'floors', 'stats'));
    }

    /**
     * Show the form for creating a new room.
     */
    public function create()
    {
        return view('rooms.create');
    }

    /**
     * Store a newly created room.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:20|unique:rooms,room_number',
            'room_type' => 'required|in:ward,icu,emergency,surgery,private,semi_private',
            'department' => 'required|string|max:100',
            'floor' => 'required|integer|min:1|max:50',
            'wing' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1|max:20',
            'daily_rate' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'equipment' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        DB::transaction(function () use ($validated) {
            // Create room
            $room = Room::create($validated);

            // Create beds for the room
            for ($i = 1; $i <= $validated['capacity']; $i++) {
                Bed::create([
                    'room_id' => $room->id,
                    'bed_number' => $i,
                    'bed_type' => $this->getBedTypeForRoom($validated['room_type']),
                    'status' => 'available',
                    'is_active' => true
                ]);
            }
        });

        return redirect()->route('rooms.index')
                        ->with('success', 'تم إنشاء الغرفة بنجاح');
    }

    /**
     * Display the specified room.
     */
    public function show(Room $room)
    {
        $room->load([
            'beds.currentAssignment.patient',
            'assignments' => function($query) {
                $query->with(['patient', 'assignedBy'])
                      ->orderBy('assigned_at', 'desc')
                      ->limit(10);
            }
        ]);

        // Get room statistics
        $stats = [
            'total_assignments' => $room->assignments()->count(),
            'active_assignments' => $room->activeAssignments()->count(),
            'total_revenue' => $room->assignments()->sum('total_charges'),
            'average_stay' => $room->assignments()
                                  ->where('status', 'discharged')
                                  ->get()
                                  ->avg(function($assignment) {
                                      return $assignment->getDurationAttribute();
                                  }) ?? 0,
            'occupancy_rate' => $room->getOccupancyRateAttribute()
        ];

        return view('rooms.show', compact('room', 'stats'));
    }

    /**
     * Show the form for editing the room.
     */
    public function edit(Room $room)
    {
        return view('rooms.edit', compact('room'));
    }

    /**
     * Update the specified room.
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:20|unique:rooms,room_number,' . $room->id,
            'room_type' => 'required|in:ward,icu,emergency,surgery,private,semi_private',
            'department' => 'required|string|max:100',
            'floor' => 'required|integer|min:1|max:50',
            'wing' => 'nullable|string|max:50',
            'capacity' => 'required|integer|min:1|max:20',
            'daily_rate' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'amenities' => 'nullable|array',
            'equipment' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        DB::transaction(function () use ($room, $validated) {
            $oldCapacity = $room->capacity;
            $newCapacity = $validated['capacity'];

            // Update room
            $room->update($validated);

            // Adjust beds if capacity changed
            if ($newCapacity > $oldCapacity) {
                // Add new beds
                for ($i = $oldCapacity + 1; $i <= $newCapacity; $i++) {
                    Bed::create([
                        'room_id' => $room->id,
                        'bed_number' => $i,
                        'bed_type' => $this->getBedTypeForRoom($validated['room_type']),
                        'status' => 'available',
                        'is_active' => true
                    ]);
                }
            } elseif ($newCapacity < $oldCapacity) {
                // Remove excess beds (only if not occupied)
                $bedsToRemove = $room->beds()
                                   ->where('bed_number', '>', $newCapacity)
                                   ->where('status', '!=', 'occupied')
                                   ->get();
                
                foreach ($bedsToRemove as $bed) {
                    $bed->delete();
                }
            }
        });

        return redirect()->route('rooms.show', $room)
                        ->with('success', 'تم تحديث الغرفة بنجاح');
    }

    /**
     * Remove the specified room.
     */
    public function destroy(Room $room)
    {
        // Check if room has active assignments
        if ($room->activeAssignments()->exists()) {
            return back()->with('error', 'لا يمكن حذف الغرفة لوجود مرضى مقيمين بها');
        }

        $room->delete();

        return redirect()->route('rooms.index')
                        ->with('success', 'تم حذف الغرفة بنجاح');
    }

    /**
     * Assign patient to room.
     */
    public function assignPatient(Request $request, Room $room)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'bed_id' => 'nullable|exists:beds,id',
            'expected_discharge_at' => 'nullable|date|after:now',
            'assignment_notes' => 'nullable|string'
        ]);

        try {
            $patient = Patient::findOrFail($validated['patient_id']);
            
            $assignment = $room->assignPatient(
                $patient,
                auth()->user(),
                $validated['bed_id'] ?? null,
                $validated['assignment_notes'] ?? null
            );

            if (isset($validated['expected_discharge_at'])) {
                $assignment->update(['expected_discharge_at' => $validated['expected_discharge_at']]);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تخصيص المريض للغرفة بنجاح',
                'assignment' => $assignment->load(['patient', 'bed'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Discharge patient from room.
     */
    public function dischargePatient(Request $request, Room $room)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'discharge_notes' => 'nullable|string'
        ]);

        try {
            $patient = Patient::findOrFail($validated['patient_id']);
            
            $success = $room->dischargePatient(
                $patient,
                auth()->user(),
                $validated['discharge_notes'] ?? null
            );

            if ($success) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم خروج المريض من الغرفة بنجاح'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم العثور على تخصيص نشط للمريض'
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
     * Mark room as cleaned.
     */
    public function markCleaned(Room $room)
    {
        $room->markCleaned();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل تنظيف الغرفة بنجاح'
        ]);
    }

    /**
     * Mark room for maintenance.
     */
    public function markMaintenance(Room $room)
    {
        // Check if room has active assignments
        if ($room->activeAssignments()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن وضع الغرفة في الصيانة لوجود مرضى بها'
            ], 400);
        }

        $room->markMaintenance();

        return response()->json([
            'success' => true,
            'message' => 'تم وضع الغرفة في الصيانة'
        ]);
    }

    /**
     * Complete room maintenance.
     */
    public function completeMaintenance(Room $room)
    {
        $room->completeMaintenance();

        return response()->json([
            'success' => true,
            'message' => 'تم إنهاء صيانة الغرفة'
        ]);
    }

    /**
     * Get available rooms for assignment.
     */
    public function getAvailableRooms(Request $request)
    {
        $roomType = $request->get('room_type');
        $department = $request->get('department');

        $rooms = Room::getAvailableRooms($roomType, $department);

        return response()->json($rooms->map(function($room) {
            return [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => $room->room_type_display,
                'department' => $room->department,
                'available_beds' => $room->available_beds_count,
                'daily_rate' => $room->daily_rate
            ];
        }));
    }

    /**
     * Get room occupancy dashboard.
     */
    public function dashboard()
    {
        $stats = Room::getOccupancyStatistics();
        
        // Get rooms by status
        $roomsByStatus = Room::select('status', DB::raw('count(*) as count'))
                            ->groupBy('status')
                            ->pluck('count', 'status');

        // Get rooms by type
        $roomsByType = Room::select('room_type', DB::raw('count(*) as count'))
                          ->groupBy('room_type')
                          ->pluck('count', 'room_type');

        // Get recent assignments
        $recentAssignments = RoomAssignment::with(['patient', 'room', 'bed'])
                                          ->orderBy('assigned_at', 'desc')
                                          ->limit(10)
                                          ->get();

        // Get overdue assignments
        $overdueAssignments = RoomAssignment::getOverdueAssignments();

        return view('rooms.dashboard', compact(
            'stats', 
            'roomsByStatus', 
            'roomsByType', 
            'recentAssignments', 
            'overdueAssignments'
        ));
    }

    /**
     * Get bed type for room type.
     */
    private function getBedTypeForRoom($roomType): string
    {
        return match($roomType) {
            'icu' => 'icu',
            'emergency' => 'standard',
            'surgery' => 'standard',
            default => 'standard'
        };
    }
}