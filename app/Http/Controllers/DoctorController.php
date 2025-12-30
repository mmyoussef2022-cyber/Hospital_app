<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Department;
use App\Models\DoctorCertificate;
use App\Models\DoctorService;
use App\Http\Requests\DoctorRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
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
     * Display a listing of doctors.
     */
    public function index(Request $request)
    {
        $query = Doctor::with(['user', 'user.department']);

        // Search filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('doctor_number', 'like', "%{$search}%")
              ->orWhere('license_number', 'like', "%{$search}%");
        }

        if ($request->filled('specialization')) {
            $query->where('specialization', $request->specialization);
        }

        if ($request->filled('department_id')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('availability')) {
            if ($request->availability === 'available') {
                $query->available();
            } elseif ($request->availability === 'unavailable') {
                $query->where('is_available', false);
            }
        }

        $doctors = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $departments = Department::all();
        $specializations = Doctor::getSpecializations();

        return view('doctors.index', compact('doctors', 'departments', 'specializations'));
    }

    /**
     * Show the form for creating a new doctor.
     */
    public function create()
    {
        $departments = Department::all();
        $specializations = Doctor::getSpecializations();
        $degrees = Doctor::getDegrees();
        
        return view('doctors.create', compact('departments', 'specializations', 'degrees'));
    }

    /**
     * Store a newly created doctor.
     */
    public function store(DoctorRequest $request)
    {
        $validated = $request->validated();

        // Create user first
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
            'mobile' => $validated['mobile'],
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['date_of_birth'],
            'address' => $validated['address'],
            'department_id' => $validated['department_id'],
            'job_title' => $validated['job_title'] ?? 'طبيب',
            'is_active' => true
        ]);

        // Assign doctor role
        $user->assignRole('doctor');

        // Handle profile photo upload
        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('doctors/photos', 'public');
        }

        // Create doctor profile
        $doctor = Doctor::create([
            'user_id' => $user->id,
            'national_id' => $validated['national_id'],
            'license_number' => $validated['license_number'],
            'specialization' => $validated['specialization'],
            'sub_specializations' => $validated['sub_specializations'],
            'degree' => $validated['degree'],
            'university' => $validated['university'],
            'experience_years' => $validated['experience_years'],
            'languages' => $validated['languages'],
            'biography' => $validated['biography'],
            'consultation_fee' => $validated['consultation_fee'],
            'follow_up_fee' => $validated['follow_up_fee'],
            'room_number' => $validated['room_number'],
            'phone' => $validated['doctor_phone'],
            'email' => $validated['doctor_email'],
            'profile_photo' => $profilePhotoPath,
            'working_hours' => $validated['working_hours'] ?? $this->getDefaultWorkingHours(),
            'is_active' => true,
            'is_available' => true
        ]);

        return redirect()->route('doctors.show', $doctor)
                        ->with('success', 'تم إضافة الطبيب بنجاح');
    }

    /**
     * Display the specified doctor.
     */
    public function show(Doctor $doctor)
    {
        $doctor->load(['user', 'user.department', 'certificates', 'services']);
        
        $todayAppointments = $doctor->getTodayAppointments();
        $upcomingAppointments = $doctor->getUpcomingAppointments();
        
        return view('doctors.show', compact('doctor', 'todayAppointments', 'upcomingAppointments'));
    }

    /**
     * Show the form for editing the doctor.
     */
    public function edit(Doctor $doctor)
    {
        $doctor->load(['user', 'user.department']);
        $departments = Department::all();
        $specializations = Doctor::getSpecializations();
        $degrees = Doctor::getDegrees();
        
        return view('doctors.edit', compact('doctor', 'departments', 'specializations', 'degrees'));
    }

    /**
     * Update the specified doctor.
     */
    public function update(DoctorRequest $request, Doctor $doctor)
    {
        $validated = $request->validated();

        // Update user
        $doctor->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'mobile' => $validated['mobile'],
            'gender' => $validated['gender'],
            'date_of_birth' => $validated['date_of_birth'],
            'address' => $validated['address'],
            'department_id' => $validated['department_id'],
            'job_title' => $validated['job_title'] ?? 'طبيب'
        ]);

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo
            if ($doctor->profile_photo) {
                Storage::disk('public')->delete($doctor->profile_photo);
            }
            $validated['profile_photo'] = $request->file('profile_photo')->store('doctors/photos', 'public');
        }

        // Update doctor profile
        $doctor->update([
            'national_id' => $validated['national_id'],
            'license_number' => $validated['license_number'],
            'specialization' => $validated['specialization'],
            'sub_specializations' => $validated['sub_specializations'],
            'degree' => $validated['degree'],
            'university' => $validated['university'],
            'experience_years' => $validated['experience_years'],
            'languages' => $validated['languages'],
            'biography' => $validated['biography'],
            'consultation_fee' => $validated['consultation_fee'],
            'follow_up_fee' => $validated['follow_up_fee'],
            'room_number' => $validated['room_number'],
            'phone' => $validated['doctor_phone'],
            'email' => $validated['doctor_email'],
            'working_hours' => $validated['working_hours'] ?? $doctor->working_hours,
            'is_active' => $validated['is_active'] ?? $doctor->is_active,
            'is_available' => $validated['is_available'] ?? $doctor->is_available,
            'profile_photo' => $validated['profile_photo'] ?? $doctor->profile_photo
        ]);

        return redirect()->route('doctors.show', $doctor)
                        ->with('success', 'تم تحديث بيانات الطبيب بنجاح');
    }

    /**
     * Remove the specified doctor.
     */
    public function destroy(Doctor $doctor)
    {
        // Check if doctor has appointments
        if ($doctor->appointments()->exists()) {
            return back()->with('error', 'لا يمكن حذف الطبيب لوجود مواعيد مرتبطة به');
        }

        // Delete profile photo
        if ($doctor->profile_photo) {
            Storage::disk('public')->delete($doctor->profile_photo);
        }

        // Delete certificates files
        foreach ($doctor->certificates as $certificate) {
            if ($certificate->file_path) {
                Storage::disk('public')->delete($certificate->file_path);
            }
        }

        // Delete doctor and user
        $user = $doctor->user;
        $doctor->delete();
        $user->delete();

        return redirect()->route('doctors.index')
                        ->with('success', 'تم حذف الطبيب بنجاح');
    }

    /**
     * Doctor dashboard
     */
    public function dashboard()
    {
        $doctor = auth()->user()->doctor;
        
        if (!$doctor) {
            abort(403, 'غير مصرح لك بالوصول لهذه الصفحة');
        }

        $todayAppointments = $doctor->getTodayAppointments();
        $upcomingAppointments = $doctor->getUpcomingAppointments();
        $activeServices = $doctor->getActiveServices();
        
        $stats = [
            'today_appointments' => $todayAppointments->count(),
            'upcoming_appointments' => $upcomingAppointments->count(),
            'total_services' => $activeServices->count(),
            'certificates' => $doctor->certificates()->verified()->count()
        ];

        return view('doctors.dashboard', compact('doctor', 'todayAppointments', 'upcomingAppointments', 'activeServices', 'stats'));
    }

    /**
     * Toggle doctor availability
     */
    public function toggleAvailability(Doctor $doctor)
    {
        $doctor->update(['is_available' => !$doctor->is_available]);
        
        $status = $doctor->is_available ? 'متاح' : 'غير متاح';
        return response()->json([
            'success' => true,
            'message' => "تم تحديث حالة الطبيب إلى: {$status}",
            'is_available' => $doctor->is_available
        ]);
    }

    /**
     * Update doctor working hours
     */
    public function updateWorkingHours(Request $request, Doctor $doctor)
    {
        $request->validate([
            'working_hours' => 'required|array',
            'working_hours.*.is_working' => 'required|boolean',
            'working_hours.*.start' => 'nullable|date_format:H:i',
            'working_hours.*.end' => 'nullable|date_format:H:i|after:working_hours.*.start'
        ]);

        $workingHours = [];
        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        
        foreach ($days as $day) {
            $dayData = $request->working_hours[$day] ?? [];
            $workingHours[$day] = [
                'is_working' => $dayData['is_working'] ?? false,
                'start' => $dayData['is_working'] ? ($dayData['start'] ?? '08:00') : null,
                'end' => $dayData['is_working'] ? ($dayData['end'] ?? '17:00') : null
            ];
        }

        $doctor->update(['working_hours' => $workingHours]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث ساعات العمل بنجاح',
            'working_hours' => $workingHours
        ]);
    }

    /**
     * Show working hours management page
     */
    public function workingHours(Doctor $doctor)
    {
        return view('doctors.working-hours', compact('doctor'));
    }

    /**
     * Get default working hours
     */
    private function getDefaultWorkingHours()
    {
        return [
            'sunday' => ['is_working' => true, 'start' => '08:00', 'end' => '16:00'],
            'monday' => ['is_working' => true, 'start' => '08:00', 'end' => '16:00'],
            'tuesday' => ['is_working' => true, 'start' => '08:00', 'end' => '16:00'],
            'wednesday' => ['is_working' => true, 'start' => '08:00', 'end' => '16:00'],
            'thursday' => ['is_working' => true, 'start' => '08:00', 'end' => '16:00'],
            'friday' => ['is_working' => false, 'start' => '00:00', 'end' => '00:00'],
            'saturday' => ['is_working' => false, 'start' => '00:00', 'end' => '00:00']
        ];
    }
}
