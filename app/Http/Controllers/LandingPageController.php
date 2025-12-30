<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LandingPageSetting;
use App\Models\LandingPageOffer;
use App\Models\Doctor;
use App\Models\DoctorService;
use App\Models\Department;
use App\Models\Appointment;
use App\Models\Patient;
use Carbon\Carbon;

class LandingPageController extends Controller
{
    public function index()
    {
        $settings = LandingPageSetting::getInstance();
        
        // Get featured doctors
        $featuredDoctors = Doctor::where('is_active', true)
            ->limit($settings->featured_doctors_count ?? 6)
            ->get();

        // Get active offers
        $offers = LandingPageOffer::where('is_active', true)
            ->where(function($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', Carbon::today());
            })
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        // Get departments
        $departments = Department::where('is_active', true)->limit(8)->get();

        // Statistics
        $statistics = [
            'doctors_count' => Doctor::where('is_active', true)->count(),
            'departments_count' => Department::where('is_active', true)->count(),
            'patients_count' => Patient::count() ?? 5000, // Default if no patients table
            'appointments_count' => Appointment::count() ?? 1200, // Default if no appointments
        ];

        return view('public.landing-page', compact(
            'settings',
            'featuredDoctors',
            'offers',
            'departments',
            'statistics'
        ));
    }

    public function doctors()
    {
        $settings = LandingPageSetting::getCached();
        
        $doctors = Doctor::where('is_active', true)
            ->with(['department'])
            ->orderBy('name')
            ->paginate(12);

        $departments = Department::get();

        return view('public.doctors.index', compact('settings', 'doctors', 'departments'));
    }

    public function doctorProfile($id)
    {
        $settings = LandingPageSetting::getCached();
        
        $doctor = Doctor::where('is_active', true)
            ->where('id', $id)
            ->with(['department'])
            ->firstOrFail();

        // Get available appointments for the next 7 days
        $availableSlots = $this->getAvailableSlots($doctor, 7);

        return view('public.doctors.profile', compact('settings', 'doctor', 'availableSlots'));
    }

    public function services()
    {
        $settings = LandingPageSetting::getCached();
        
        $services = DoctorService::with(['department'])
            ->orderBy('name')
            ->paginate(12);

        $departments = Department::get();

        return view('public.services.index', compact('settings', 'services', 'departments'));
    }

    public function serviceDetails($id)
    {
        $settings = LandingPageSetting::getCached();
        
        $service = DoctorService::with(['department'])
            ->findOrFail($id);

        $relatedServices = DoctorService::where('department_id', $service->department_id)
            ->where('id', '!=', $id)
            ->limit(4)
            ->get();

        return view('public.services.details', compact('settings', 'service', 'relatedServices'));
    }

    public function bookingForm(Request $request)
    {
        $settings = LandingPageSetting::getCached();
        
        $selectedDoctor = null;
        $selectedService = null;
        $selectedOffer = null;
        $selectedDate = null;
        $selectedTime = null;

        // Pre-fill form based on URL parameters
        if ($request->has('doctor')) {
            $selectedDoctor = Doctor::find($request->doctor);
        }

        if ($request->has('service')) {
            $selectedService = DoctorService::find($request->service);
        }

        if ($request->has('offer')) {
            $selectedOffer = LandingPageOffer::find($request->offer);
        }

        if ($request->has('date')) {
            $selectedDate = $request->date;
        }

        if ($request->has('time')) {
            $selectedTime = $request->time;
        }

        $doctors = Doctor::where('is_active', true)
            ->with('department')
            ->orderBy('name')
            ->get();

        $services = DoctorService::with('department')
            ->orderBy('name')
            ->get();

        return view('public.booking.form', compact(
            'settings',
            'doctors',
            'services',
            'selectedDoctor',
            'selectedService',
            'selectedOffer',
            'selectedDate',
            'selectedTime'
        ));
    }

    public function storeBooking(Request $request)
    {
        $request->validate([
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|max:20',
            'patient_email' => 'nullable|email|max:255',
            'doctor_id' => 'required|exists:doctors,id',
            'service_id' => 'nullable|exists:doctor_services,id',
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check if the slot is still available
        $existingAppointment = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $request->appointment_time)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingAppointment) {
            return back()->withErrors(['appointment_time' => 'هذا الموعد محجوز بالفعل. يرجى اختيار موعد آخر.']);
        }

        // Create or find patient
        $patient = Patient::firstOrCreate(
            ['phone' => $request->patient_phone],
            [
                'name' => $request->patient_name,
                'email' => $request->patient_email,
                'date_of_birth' => null,
                'gender' => 'unknown',
                'address' => null,
            ]
        );

        // Create appointment
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status' => 'scheduled',
            'notes' => $request->notes,
        ]);

        return redirect()->route('public.booking.success', $appointment->id)
            ->with('success', 'تم حجز موعدك بنجاح! سيتم التواصل معك قريباً لتأكيد الموعد.');
    }

    public function bookingSuccess($appointmentId)
    {
        $settings = LandingPageSetting::getCached();
        
        $appointment = Appointment::with(['patient', 'doctor'])
            ->findOrFail($appointmentId);

        return view('public.booking.success', compact('settings', 'appointment'));
    }

    public function contact()
    {
        $settings = LandingPageSetting::getCached();
        
        return view('public.contact', compact('settings'));
    }

    public function storeContact(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        return back()->with('success', 'تم إرسال رسالتك بنجاح! سنتواصل معك قريباً.');
    }

    private function getAvailableSlots($doctor, $days = 7)
    {
        $slots = [];
        $startDate = Carbon::today();
        
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $daySlots = $this->getDaySlots($doctor, $date);
            
            if (!empty($daySlots)) {
                $slots[$date->format('Y-m-d')] = [
                    'date' => $date,
                    'slots' => $daySlots,
                ];
            }
        }
        
        return $slots;
    }

    private function getDaySlots($doctor, $date)
    {
        $dayOfWeek = strtolower($date->format('l'));
        
        if ($dayOfWeek === 'friday') {
            return [];
        }
        
        $workingHours = [
            'morning' => ['09:00', '10:00', '11:00', '12:00'],
            'evening' => ['17:00', '18:00', '19:00', '20:00', '21:00'],
        ];
        
        $bookedTimes = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('appointment_time')
            ->map(function ($time) {
                return Carbon::parse($time)->format('H:i');
            })
            ->toArray();
        
        $availableSlots = [
            'morning' => array_diff($workingHours['morning'], $bookedTimes),
            'evening' => array_diff($workingHours['evening'], $bookedTimes),
        ];
        
        return $availableSlots;
    }
}
