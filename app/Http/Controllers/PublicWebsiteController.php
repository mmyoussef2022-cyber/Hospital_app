<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\DoctorService;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PublicWebsiteController extends Controller
{
    /**
     * عرض الصفحة الرئيسية للموقع العام
     */
    public function index()
    {
        // إحصائيات عامة للموقع
        $statistics = [
            'doctors_count' => Doctor::where('is_active', true)->count(),
            'departments_count' => Department::count(),
            'services_count' => DoctorService::where('is_active', true)->count(),
            'satisfied_patients' => Patient::count(), // يمكن تحسينها لاحقاً بناءً على التقييمات
        ];

        // الأطباء المميزون (أعلى تقييماً أو الأكثر خبرة)
        $featuredDoctors = Doctor::with(['department', 'services'])
            ->where('is_active', true)
            ->where('is_available', true)
            ->orderBy('years_of_experience', 'desc')
            ->limit(6)
            ->get();

        // الأقسام الطبية
        $departments = Department::with(['doctors' => function($query) {
            $query->where('is_active', true)->where('is_available', true);
        }])
        ->whereHas('doctors', function($query) {
            $query->where('is_active', true)->where('is_available', true);
        })
        ->get();

        // الخدمات الطبية الشائعة
        $popularServices = DoctorService::with(['doctor.department'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        return view('public.index', compact(
            'statistics',
            'featuredDoctors', 
            'departments',
            'popularServices'
        ));
    }

    /**
     * عرض قائمة الأطباء
     */
    public function doctors(Request $request)
    {
        $query = Doctor::with(['department', 'services', 'reviews'])
            ->where('is_active', true)
            ->where('is_available', true);

        // تصفية حسب القسم
        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        // تصفية حسب التخصص
        if ($request->filled('specialization')) {
            $query->where('specialization', 'like', '%' . $request->specialization . '%');
        }

        // البحث بالاسم
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('specialization', 'like', '%' . $request->search . '%');
            });
        }

        // ترتيب النتائج
        $sortBy = $request->get('sort', 'name');
        switch ($sortBy) {
            case 'experience':
                $query->orderBy('years_of_experience', 'desc');
                break;
            case 'rating':
                // يمكن تحسينها لاحقاً بناءً على متوسط التقييمات
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        $doctors = $query->paginate(12);
        $departments = Department::all();

        return view('public.doctors.index', compact('doctors', 'departments'));
    }

    /**
     * عرض تفاصيل طبيب معين
     */
    public function doctorProfile($id)
    {
        $doctor = Doctor::with([
            'department', 
            'services', 
            'reviews.patient',
            'certificates' => function($query) {
                $query->where('is_verified', true);
            }
        ])
        ->where('is_active', true)
        ->where('is_available', true)
        ->findOrFail($id);

        // الحصول على المواعيد المتاحة للأسبوع القادم
        $availableSlots = $this->getAvailableSlots($doctor, 7);

        // إحصائيات الطبيب
        $doctorStats = [
            'total_appointments' => Appointment::where('doctor_id', $doctor->id)->count(),
            'completed_appointments' => Appointment::where('doctor_id', $doctor->id)
                ->where('status', 'completed')->count(),
            'average_rating' => $doctor->reviews()->avg('rating') ?? 0,
            'total_reviews' => $doctor->reviews()->count(),
        ];

        return view('public.doctors.profile', compact('doctor', 'availableSlots', 'doctorStats'));
    }

    /**
     * عرض نموذج حجز موعد
     */
    public function bookingForm($doctorId = null)
    {
        $doctor = null;
        if ($doctorId) {
            $doctor = Doctor::where('is_active', true)
                ->where('is_available', true)
                ->findOrFail($doctorId);
        }

        $doctors = Doctor::with('department')
            ->where('is_active', true)
            ->where('is_available', true)
            ->orderBy('name')
            ->get();

        $departments = Department::with(['doctors' => function($query) {
            $query->where('is_active', true)->where('is_available', true);
        }])->get();

        return view('public.booking.form', compact('doctor', 'doctors', 'departments'));
    }

    /**
     * معالجة حجز الموعد
     */
    public function processBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_name' => 'required|string|max:255',
            'patient_phone' => 'required|string|max:20',
            'patient_email' => 'nullable|email|max:255',
            'national_id' => 'required|string|size:10|unique:patients,national_id',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required|date_format:H:i',
            'reason' => 'nullable|string|max:500',
            'emergency' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // التحقق من توفر الطبيب في الوقت المحدد
            $doctor = Doctor::findOrFail($request->doctor_id);
            $appointmentDateTime = Carbon::parse($request->appointment_date . ' ' . $request->appointment_time);

            // التحقق من عدم وجود موعد آخر في نفس الوقت
            $existingAppointment = Appointment::where('doctor_id', $doctor->id)
                ->where('appointment_date', $request->appointment_date)
                ->where('appointment_time', $request->appointment_time)
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->first();

            if ($existingAppointment) {
                return back()->with('error', 'عذراً، هذا الموعد محجوز بالفعل. يرجى اختيار وقت آخر.')
                    ->withInput();
            }

            // إنشاء المريض أو البحث عنه
            $patient = Patient::where('national_id', $request->national_id)->first();
            
            if (!$patient) {
                $patient = Patient::create([
                    'name' => $request->patient_name,
                    'phone' => $request->patient_phone,
                    'email' => $request->patient_email,
                    'national_id' => $request->national_id,
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'address' => $request->address,
                    'emergency_contact' => $request->emergency_contact,
                    'is_active' => true,
                ]);
            }

            // إنشاء الموعد
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'reason' => $request->reason,
                'status' => $request->emergency ? 'urgent' : 'scheduled',
                'type' => 'consultation',
                'duration' => 30, // مدة افتراضية
                'notes' => 'موعد محجوز عبر الموقع الإلكتروني',
                'is_online_booking' => true,
            ]);

            // إرسال رسالة تأكيد (يمكن تطويرها لاحقاً)
            $confirmationData = [
                'appointment' => $appointment,
                'patient' => $patient,
                'doctor' => $doctor,
                'appointment_datetime' => $appointmentDateTime,
            ];

            return view('public.booking.confirmation', $confirmationData);

        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء حجز الموعد. يرجى المحاولة مرة أخرى.')
                ->withInput();
        }
    }

    /**
     * تسجيل زائر جديد
     */
    public function registerVisitor(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'national_id' => 'required|string|size:10|unique:users,national_id',
            'date_of_birth' => 'required|date|before:today',
            'gender' => 'required|in:male,female',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // إنشاء حساب زائر
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'national_id' => $request->national_id,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'user_type' => 'visitor',
                'is_active' => true,
            ]);

            // تسجيل دخول تلقائي
            Auth::login($user);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الحساب بنجاح',
                'redirect' => route('public.dashboard')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الحساب'
            ], 500);
        }
    }

    /**
     * لوحة تحكم الزائر
     */
    public function visitorDashboard()
    {
        if (!Auth::check() || Auth::user()->user_type !== 'visitor') {
            return redirect()->route('public.index');
        }

        $user = Auth::user();
        
        // البحث عن المريض المرتبط بالمستخدم
        $patient = Patient::where('national_id', $user->national_id)->first();
        
        $appointments = [];
        if ($patient) {
            $appointments = Appointment::with(['doctor.department'])
                ->where('patient_id', $patient->id)
                ->orderBy('appointment_date', 'desc')
                ->orderBy('appointment_time', 'desc')
                ->paginate(10);
        }

        return view('public.dashboard', compact('user', 'patient', 'appointments'));
    }

    /**
     * الحصول على المواعيد المتاحة لطبيب معين
     */
    private function getAvailableSlots($doctor, $days = 7)
    {
        $availableSlots = [];
        $startDate = Carbon::now()->addDay();
        
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            // تخطي أيام الجمعة (يمكن تخصيصها حسب أوقات عمل الطبيب)
            if ($date->isFriday()) {
                continue;
            }
            
            $daySlots = [];
            
            // أوقات العمل الافتراضية (يمكن تحسينها لاحقاً من جدول أوقات عمل الأطباء)
            $workingHours = [
                'morning' => ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30'],
                'evening' => ['16:00', '16:30', '17:00', '17:30', '18:00', '18:30']
            ];
            
            foreach ($workingHours as $period => $times) {
                foreach ($times as $time) {
                    // التحقق من عدم وجود موعد محجوز
                    $isBooked = Appointment::where('doctor_id', $doctor->id)
                        ->where('appointment_date', $date->format('Y-m-d'))
                        ->where('appointment_time', $time)
                        ->whereNotIn('status', ['cancelled', 'no_show'])
                        ->exists();
                    
                    if (!$isBooked) {
                        $daySlots[$period][] = $time;
                    }
                }
            }
            
            if (!empty($daySlots)) {
                $availableSlots[$date->format('Y-m-d')] = [
                    'date' => $date,
                    'slots' => $daySlots
                ];
            }
        }
        
        return $availableSlots;
    }

    /**
     * البحث في الأطباء والخدمات
     */
    public function search(Request $request)
    {
        $query = $request->get('q');
        
        if (empty($query)) {
            return response()->json([
                'doctors' => [],
                'services' => []
            ]);
        }

        // البحث في الأطباء
        $doctors = Doctor::with('department')
            ->where('is_active', true)
            ->where('is_available', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('specialization', 'like', '%' . $query . '%');
            })
            ->limit(5)
            ->get();

        // البحث في الخدمات
        $services = DoctorService::with(['doctor.department'])
            ->where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%');
            })
            ->limit(5)
            ->get();

        return response()->json([
            'doctors' => $doctors,
            'services' => $services
        ]);
    }

    /**
     * الحصول على المواعيد المتاحة لطبيب معين (AJAX)
     */
    public function getAvailableSlotsAjax(Request $request)
    {
        $doctorId = $request->get('doctor_id');
        $date = $request->get('date');
        
        if (!$doctorId || !$date) {
            return response()->json(['slots' => []]);
        }

        $doctor = Doctor::find($doctorId);
        if (!$doctor) {
            return response()->json(['slots' => []]);
        }

        $selectedDate = Carbon::parse($date);
        
        // أوقات العمل الافتراضية
        $workingHours = [
            'morning' => ['09:00', '09:30', '10:00', '10:30', '11:00', '11:30'],
            'evening' => ['16:00', '16:30', '17:00', '17:30', '18:00', '18:30']
        ];
        
        $availableSlots = [];
        
        foreach ($workingHours as $period => $times) {
            foreach ($times as $time) {
                // التحقق من عدم وجود موعد محجوز
                $isBooked = Appointment::where('doctor_id', $doctor->id)
                    ->where('appointment_date', $selectedDate->format('Y-m-d'))
                    ->where('appointment_time', $time)
                    ->whereNotIn('status', ['cancelled', 'no_show'])
                    ->exists();
                
                if (!$isBooked) {
                    $availableSlots[$period][] = $time;
                }
            }
        }
        
        return response()->json(['slots' => $availableSlots]);
    }
}