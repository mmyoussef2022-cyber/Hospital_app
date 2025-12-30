<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\DoctorService;
use App\Http\Requests\DoctorServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorServiceController extends Controller
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
     * Display a listing of services.
     */
    public function index(Request $request)
    {
        $query = DoctorService::with(['doctor.user']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('service_name', 'like', "%{$search}%")
                  ->orWhere('service_name_en', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('doctor.user', function($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('appointment_required')) {
            if ($request->appointment_required === 'yes') {
                $query->requiresAppointment();
            } elseif ($request->appointment_required === 'no') {
                $query->where('requires_appointment', false);
            }
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $services = $query->ordered()->paginate(15);
        $categories = DoctorService::getCategories();
        $doctors = Doctor::with('user')->active()->get();

        return view('doctors.services.index', compact('services', 'categories', 'doctors'));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create(Doctor $doctor)
    {
        $categories = DoctorService::getCategories();
        
        return view('doctors.services.create', compact('doctor', 'categories'));
    }

    /**
     * Store a newly created service.
     */
    public function store(DoctorServiceRequest $request, Doctor $doctor)
    {
        $validated = $request->validated();
        $validated['doctor_id'] = $doctor->id;
        $validated['sort_order'] = DoctorService::getNextSortOrder($doctor->id);

        // Process requirements and preparation instructions
        if ($request->filled('requirements_list')) {
            $requirements = array_filter(explode("\n", $request->requirements_list));
            $validated['requirements'] = array_map('trim', $requirements);
        }

        if ($request->filled('preparation_list')) {
            $preparation = array_filter(explode("\n", $request->preparation_list));
            $validated['preparation_instructions'] = array_map('trim', $preparation);
        }

        $service = DoctorService::create($validated);

        return redirect()->route('doctors.show', $doctor)
                        ->with('success', 'تم إضافة الخدمة بنجاح');
    }

    /**
     * Display the specified service.
     */
    public function show(Doctor $doctor, DoctorService $service)
    {
        $service->load(['doctor.user']);
        
        return view('doctors.services.show', compact('doctor', 'service'));
    }

    /**
     * Show the form for editing the service.
     */
    public function edit(Doctor $doctor, DoctorService $service)
    {
        $categories = DoctorService::getCategories();
        
        return view('doctors.services.edit', compact('doctor', 'service', 'categories'));
    }

    /**
     * Update the specified service.
     */
    public function update(DoctorServiceRequest $request, Doctor $doctor, DoctorService $service)
    {
        $validated = $request->validated();

        // Process requirements and preparation instructions
        if ($request->filled('requirements_list')) {
            $requirements = array_filter(explode("\n", $request->requirements_list));
            $validated['requirements'] = array_map('trim', $requirements);
        } else {
            $validated['requirements'] = [];
        }

        if ($request->filled('preparation_list')) {
            $preparation = array_filter(explode("\n", $request->preparation_list));
            $validated['preparation_instructions'] = array_map('trim', $preparation);
        } else {
            $validated['preparation_instructions'] = [];
        }

        $service->update($validated);

        return redirect()->route('doctors.services.show', [$doctor, $service])
                        ->with('success', 'تم تحديث الخدمة بنجاح');
    }

    /**
     * Remove the specified service.
     */
    public function destroy(Doctor $doctor, DoctorService $service)
    {
        $service->delete();

        return redirect()->route('doctors.show', $doctor)
                        ->with('success', 'تم حذف الخدمة بنجاح');
    }

    /**
     * Toggle service status.
     */
    public function toggleStatus(DoctorService $service)
    {
        $service->update(['is_active' => !$service->is_active]);

        return response()->json([
            'success' => true,
            'message' => $service->is_active ? 'تم تفعيل الخدمة' : 'تم إلغاء تفعيل الخدمة',
            'is_active' => $service->is_active,
            'status_badge' => $service->status_badge
        ]);
    }

    /**
     * Update service sort order.
     */
    public function updateSortOrder(Request $request, Doctor $doctor)
    {
        $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:doctor_services,id'
        ]);

        foreach ($request->service_ids as $index => $serviceId) {
            DoctorService::where('id', $serviceId)
                        ->where('doctor_id', $doctor->id)
                        ->update(['sort_order' => $index + 1]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث ترتيب الخدمات بنجاح'
        ]);
    }

    /**
     * Get services by category.
     */
    public function getByCategory(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'doctor_id' => 'nullable|exists:doctors,id'
        ]);

        $query = DoctorService::active()->byCategory($request->category);
        
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $services = $query->ordered()->get();

        return response()->json([
            'services' => $services,
            'count' => $services->count()
        ]);
    }

    /**
     * Get service statistics.
     */
    public function statistics()
    {
        $stats = [
            'total' => DoctorService::count(),
            'active' => DoctorService::active()->count(),
            'inactive' => DoctorService::where('is_active', false)->count(),
            'requires_appointment' => DoctorService::requiresAppointment()->count(),
            'by_category' => DoctorService::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'average_price' => DoctorService::active()->avg('price'),
            'average_duration' => DoctorService::active()->avg('duration_minutes')
        ];

        return response()->json($stats);
    }

    /**
     * Duplicate a service.
     */
    public function duplicate(Doctor $doctor, DoctorService $service)
    {
        $newService = $service->replicate();
        $newService->service_name = $service->service_name . ' (نسخة)';
        $newService->sort_order = DoctorService::getNextSortOrder($doctor->id);
        $newService->save();

        return redirect()->route('doctors.services.edit', [$doctor, $newService])
                        ->with('success', 'تم نسخ الخدمة بنجاح');
    }

    /**
     * Bulk operations on services.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'service_ids' => 'required|array',
            'service_ids.*' => 'exists:doctor_services,id'
        ]);

        $services = DoctorService::whereIn('id', $request->service_ids);
        $count = $services->count();

        switch ($request->action) {
            case 'activate':
                $services->update(['is_active' => true]);
                $message = "تم تفعيل {$count} خدمة بنجاح";
                break;
            case 'deactivate':
                $services->update(['is_active' => false]);
                $message = "تم إلغاء تفعيل {$count} خدمة بنجاح";
                break;
            case 'delete':
                $services->delete();
                $message = "تم حذف {$count} خدمة بنجاح";
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'affected_count' => $count
        ]);
    }
}