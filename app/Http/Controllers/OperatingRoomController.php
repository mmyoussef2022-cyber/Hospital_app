<?php

namespace App\Http\Controllers;

use App\Models\OperatingRoom;
use App\Models\Room;
use App\Models\Surgery;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OperatingRoomController extends Controller
{
    public function index(Request $request)
    {
        $query = OperatingRoom::with(['room', 'currentSurgery.patient', 'nextSurgery.patient']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('or_type')) {
            $query->where('or_type', $request->or_type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('or_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        $operatingRooms = $query->orderBy('or_number')->paginate(15);

        return view('operating-rooms.index', compact('operatingRooms'));
    }

    public function dashboard()
    {
        $stats = OperatingRoom::getUtilizationStatistics();
        
        $operatingRooms = OperatingRoom::active()
            ->with(['currentSurgery.patient.user', 'nextSurgery.patient.user'])
            ->orderBy('or_number')
            ->get();

        // Today's schedule overview
        $todaySchedule = Surgery::today()
            ->with(['operatingRoom', 'patient', 'primarySurgeon', 'surgicalProcedure'])
            ->orderBy('scheduled_start_time')
            ->get()
            ->groupBy('operating_room_id');

        return view('operating-rooms.dashboard', compact('stats', 'operatingRooms', 'todaySchedule'));
    }

    public function create()
    {
        // Get available rooms that are not already operating rooms
        $availableRooms = Room::where('room_type', 'surgery')
            ->whereDoesntHave('operatingRoom')
            ->orderBy('room_number')
            ->get();

        return view('operating-rooms.create', compact('availableRooms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id|unique:operating_rooms,room_id',
            'or_number' => 'required|string|max:20|unique:operating_rooms,or_number',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'or_type' => 'required|in:general,cardiac,orthopedic,neurosurgery,ophthalmology,ent,gynecology,urology,plastic,trauma,pediatric,hybrid',
            'capabilities' => 'nullable|array',
            'equipment' => 'nullable|array',
            'monitoring_systems' => 'nullable|array',
            'has_laminar_flow' => 'boolean',
            'has_imaging' => 'boolean',
            'has_robotic_system' => 'boolean',
            'has_cardiac_bypass' => 'boolean',
            'has_neuro_monitoring' => 'boolean',
            'temperature_min' => 'nullable|numeric|min:15|max:30',
            'temperature_max' => 'nullable|numeric|min:15|max:30',
            'humidity_min' => 'nullable|numeric|min:30|max:70',
            'humidity_max' => 'nullable|numeric|min:30|max:70',
            'is_active' => 'boolean',
            'is_emergency_ready' => 'boolean',
        ]);

        $validated['status'] = 'available';

        $operatingRoom = OperatingRoom::create($validated);

        return redirect()->route('operating-rooms.show', $operatingRoom)
            ->with('success', 'تم إنشاء غرفة العمليات بنجاح');
    }

    public function show(OperatingRoom $operatingRoom)
    {
        $operatingRoom->load(['room', 'currentSurgery.patient', 'nextSurgery.patient']);

        // Get today's schedule
        $todaySchedule = $operatingRoom->getScheduleForDate(today());

        // Get utilization rate for current month
        $utilizationRate = $operatingRoom->getUtilizationRate();

        // Get recent surgeries
        $recentSurgeries = $operatingRoom->surgeries()
            ->with(['patient', 'primarySurgeon', 'surgicalProcedure'])
            ->orderBy('scheduled_start_time', 'desc')
            ->take(10)
            ->get();

        return view('operating-rooms.show', compact(
            'operatingRoom', 'todaySchedule', 'utilizationRate', 'recentSurgeries'
        ));
    }

    public function edit(OperatingRoom $operatingRoom)
    {
        return view('operating-rooms.edit', compact('operatingRoom'));
    }

    public function update(Request $request, OperatingRoom $operatingRoom)
    {
        $validated = $request->validate([
            'or_number' => 'required|string|max:20|unique:operating_rooms,or_number,' . $operatingRoom->id,
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'or_type' => 'required|in:general,cardiac,orthopedic,neurosurgery,ophthalmology,ent,gynecology,urology,plastic,trauma,pediatric,hybrid',
            'capabilities' => 'nullable|array',
            'equipment' => 'nullable|array',
            'monitoring_systems' => 'nullable|array',
            'has_laminar_flow' => 'boolean',
            'has_imaging' => 'boolean',
            'has_robotic_system' => 'boolean',
            'has_cardiac_bypass' => 'boolean',
            'has_neuro_monitoring' => 'boolean',
            'temperature_min' => 'nullable|numeric|min:15|max:30',
            'temperature_max' => 'nullable|numeric|min:15|max:30',
            'humidity_min' => 'nullable|numeric|min:30|max:70',
            'humidity_max' => 'nullable|numeric|min:30|max:70',
            'is_active' => 'boolean',
            'is_emergency_ready' => 'boolean',
        ]);

        $operatingRoom->update($validated);

        return redirect()->route('operating-rooms.show', $operatingRoom)
            ->with('success', 'تم تحديث غرفة العمليات بنجاح');
    }

    public function destroy(OperatingRoom $operatingRoom)
    {
        if ($operatingRoom->surgeries()->exists()) {
            return back()->with('error', 'لا يمكن حذف غرفة عمليات مرتبطة بعمليات جراحية');
        }

        $operatingRoom->delete();

        return redirect()->route('operating-rooms.index')
            ->with('success', 'تم حذف غرفة العمليات بنجاح');
    }

    // Status management methods
    public function markCleaned(Request $request, OperatingRoom $operatingRoom)
    {
        $validated = $request->validate([
            'cleaning_notes' => 'nullable|string|max:500',
        ]);

        if ($operatingRoom->markCleaned($validated['cleaning_notes'] ?? null)) {
            return back()->with('success', 'تم تسجيل تنظيف غرفة العمليات');
        }

        return back()->with('error', 'فشل في تسجيل التنظيف');
    }

    public function markMaintenance(Request $request, OperatingRoom $operatingRoom)
    {
        $validated = $request->validate([
            'maintenance_notes' => 'nullable|string|max:500',
        ]);

        if ($operatingRoom->markMaintenance($validated['maintenance_notes'] ?? null)) {
            return back()->with('success', 'تم وضع غرفة العمليات في وضع الصيانة');
        }

        return back()->with('error', 'لا يمكن وضع الغرفة في وضع الصيانة (مشغولة حالياً)');
    }

    public function completeMaintenance(Request $request, OperatingRoom $operatingRoom)
    {
        $validated = $request->validate([
            'maintenance_notes' => 'nullable|string|max:500',
        ]);

        if ($operatingRoom->completeMaintenance($validated['maintenance_notes'] ?? null)) {
            return back()->with('success', 'تم إكمال صيانة غرفة العمليات');
        }

        return back()->with('error', 'فشل في إكمال الصيانة');
    }

    public function startSetup(Request $request, OperatingRoom $operatingRoom)
    {
        $validated = $request->validate([
            'setup_notes' => 'nullable|string|max:500',
        ]);

        if ($operatingRoom->startSetup($validated['setup_notes'] ?? null)) {
            return back()->with('success', 'تم بدء إعداد غرفة العمليات');
        }

        return back()->with('error', 'لا يمكن بدء الإعداد (الغرفة غير متاحة)');
    }

    public function completeSetup(OperatingRoom $operatingRoom)
    {
        if ($operatingRoom->completeSetup()) {
            return back()->with('success', 'تم إكمال إعداد غرفة العمليات');
        }

        return back()->with('error', 'فشل في إكمال الإعداد');
    }

    public function reserveForEmergency(OperatingRoom $operatingRoom)
    {
        if ($operatingRoom->reserveForEmergency()) {
            return back()->with('success', 'تم حجز غرفة العمليات للطوارئ');
        }

        return back()->with('error', 'لا يمكن حجز الغرفة للطوارئ (غير متاحة)');
    }

    public function releaseFromEmergency(OperatingRoom $operatingRoom)
    {
        if ($operatingRoom->releaseFromEmergency()) {
            return back()->with('success', 'تم إلغاء حجز الطوارئ لغرفة العمليات');
        }

        return back()->with('error', 'فشل في إلغاء حجز الطوارئ');
    }

    // AJAX endpoints
    public function getSchedule(Request $request, OperatingRoom $operatingRoom)
    {
        $date = $request->get('date', today()->format('Y-m-d'));
        $schedule = $operatingRoom->getScheduleForDate(Carbon::parse($date));

        return response()->json($schedule);
    }

    public function checkAvailability(Request $request, OperatingRoom $operatingRoom)
    {
        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);

        $available = $operatingRoom->isAvailableAt($startTime, $endTime);

        return response()->json([
            'available' => $available,
            'status' => $operatingRoom->status,
            'current_surgery' => $operatingRoom->current_surgery,
            'next_surgery' => $operatingRoom->next_surgery,
        ]);
    }

    public function getAvailableRooms(Request $request)
    {
        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'procedure_id' => 'nullable|exists:surgical_procedures,id',
        ]);

        $startTime = Carbon::parse($validated['start_time']);
        $endTime = Carbon::parse($validated['end_time']);

        $query = OperatingRoom::active()->available();

        if ($validated['procedure_id']) {
            $procedure = \App\Models\SurgicalProcedure::find($validated['procedure_id']);
            $rooms = $query->get()->filter(function($room) use ($procedure, $startTime, $endTime) {
                return $room->canAccommodateProcedure($procedure) && 
                       $room->isAvailableAt($startTime, $endTime);
            });
        } else {
            $rooms = $query->get()->filter(function($room) use ($startTime, $endTime) {
                return $room->isAvailableAt($startTime, $endTime);
            });
        }

        return response()->json($rooms->values());
    }
}