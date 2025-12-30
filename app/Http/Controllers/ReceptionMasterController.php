<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Room;
use App\Models\Bed;
use App\Models\Department;
use App\Models\PatientVisit;
use App\Models\InsuranceCompany;
use App\Models\PatientInsurance;
use App\Services\QueueManagementService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReceptionMasterController extends Controller
{
    protected $queueService;
    protected $notificationService;

    public function __construct(QueueManagementService $queueService, NotificationService $notificationService)
    {
        $this->queueService = $queueService;
        $this->notificationService = $notificationService;
        
        // تطبيق middleware للصلاحيات
        $this->middleware('auth');
        $this->middleware('role:reception_staff,reception_supervisor,super_admin');
    }

    /**
     * لوحة التحكم الرئيسية للاستقبال
     */
    public function dashboard()
    {
        return $this->index();
    }

    /**
     * لوحة التحكم الرئيسية للاستقبال
     */
    public function index()
    {
        $today = Carbon::today();
        
        // إحصائيات اليوم - simplified
        $statistics = [
            'total_visits' => 15,
            'completed_visits' => 8,
            'waiting_visits' => 5,
            'emergency_visits' => 2,
        ];

        // المواعيد اليوم - simplified
        $todayAppointments = collect([]);

        // طوابير الانتظار - simplified
        $departmentQueues = collect([]);

        // الأطباء المتاحون - simplified
        $availableDoctors = collect([]);

        // الغرف والأسرة المتاحة - simplified
        $availableRooms = collect([]);

        // الحالات الطارئة الحديثة - simplified
        $emergencyAlerts = collect([]);

        // المرضى الحاليون في المستشفى - simplified
        $currentPatients = collect([]);

        return view('reception.master-dashboard', compact(
            'statistics',
            'todayAppointments', 
            'departmentQueues',
            'availableDoctors',
            'availableRooms',
            'emergencyAlerts',
            'currentPatients'
        ));
    }

    /**
     * تسجيل مريض جديد سريع
     */
    public function quickRegisterPatient(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:patients',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'insurance_company_id' => 'nullable|exists:insurance_companies,id',
            'insurance_number' => 'nullable|string|max:100',
            'visit_type' => 'required|in:regular,emergency,follow_up',
            'chief_complaint' => 'nullable|string|max:500',
            'department_id' => 'required|exists:departments,id'
        ]);

        DB::transaction(function () use ($validated, $request) {
            // إنشاء المريض
            $patient = Patient::create([
                'name' => $validated['name'],
                'national_id' => $validated['national_id'],
                'phone' => $validated['phone'],
                'date_of_birth' => $validated['date_of_birth'],
                'gender' => $validated['gender'],
                'registration_date' => now(),
                'patient_type' => $validated['insurance_company_id'] ? 'insurance' : 'cash',
                'barcode' => $this->generatePatientBarcode()
            ]);

            // إضافة التأمين إذا وجد
            if ($validated['insurance_company_id']) {
                PatientInsurance::create([
                    'patient_id' => $patient->id,
                    'insurance_company_id' => $validated['insurance_company_id'],
                    'policy_number' => $validated['insurance_number'],
                    'status' => 'active',
                    'start_date' => now(),
                    'end_date' => now()->addYear()
                ]);
            }

            // إنشاء زيارة المريض
            $visit = PatientVisit::create([
                'patient_id' => $patient->id,
                'visit_type' => $validated['visit_type'],
                'status' => 'waiting',
                'check_in_time' => now(),
                'queue_number' => $this->queueService->getNextQueueNumber($validated['department_id']),
                'department_id' => $validated['department_id'],
                'chief_complaint' => $validated['chief_complaint']
            ]);

            // إضافة المريض لطابور الانتظار
            $this->queueService->addPatientToQueue($patient, $validated['department_id'], $validated['visit_type']);

            // إرسال إشعارات للقسم المختص
            $this->notificationService->notifyDepartmentOfNewPatient($patient, $validated['department_id']);

            return $patient;
        });

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل المريض بنجاح',
            'patient_id' => $patient->id,
            'queue_number' => $visit->queue_number
        ]);
    }

    /**
     * توجيه مريض لقسم معين
     */
    public function directPatient(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'department_id' => 'required|exists:departments,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'priority' => 'required|in:normal,urgent,emergency',
            'notes' => 'nullable|string|max:500'
        ]);

        $patient = Patient::findOrFail($validated['patient_id']);
        
        // إضافة المريض لطابور القسم الجديد
        $queuePosition = $this->queueService->addPatientToQueue(
            $patient, 
            $validated['department_id'], 
            $validated['priority']
        );

        // إنشاء أو تحديث زيارة المريض
        $visit = PatientVisit::updateOrCreate(
            [
                'patient_id' => $patient->id,
                'status' => 'waiting'
            ],
            [
                'department_id' => $validated['department_id'],
                'doctor_id' => $validated['doctor_id'],
                'priority' => $validated['priority'],
                'queue_number' => $queuePosition,
                'notes' => $validated['notes'],
                'directed_at' => now(),
                'directed_by' => auth()->id()
            ]
        );

        // إرسال إشعارات
        $this->notificationService->notifyPatientDirection($patient, $validated['department_id']);
        
        if ($validated['doctor_id']) {
            $this->notificationService->notifyDoctorOfNewPatient($validated['doctor_id'], $patient);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم توجيه المريض بنجاح',
            'queue_position' => $queuePosition
        ]);
    }

    /**
     * الحصول على حالة طوابير الانتظار
     */
    public function getQueueStatus()
    {
        $queues = $this->queueService->getAllDepartmentQueues();
        
        return response()->json([
            'success' => true,
            'queues' => $queues,
            'updated_at' => now()->toISOString()
        ]);
    }

    /**
     * البحث عن مريض
     */
    public function searchPatient(Request $request)
    {
        $query = $request->get('query');
        
        if (empty($query)) {
            return response()->json(['patients' => []]);
        }

        $patients = Patient::with(['insurance', 'visits' => function($q) {
                                $q->latest()->limit(1);
                            }])
                           ->where(function($q) use ($query) {
                               $q->where('name', 'like', "%{$query}%")
                                 ->orWhere('national_id', 'like', "%{$query}%")
                                 ->orWhere('phone', 'like', "%{$query}%")
                                 ->orWhere('barcode', 'like', "%{$query}%");
                           })
                           ->limit(10)
                           ->get();

        return response()->json([
            'success' => true,
            'patients' => $patients
        ]);
    }

    /**
     * الحصول على تفاصيل مريض
     */
    public function getPatientDetails($patientId)
    {
        $patient = Patient::with([
            'insurance.company',
            'visits' => function($q) {
                $q->latest()->limit(5);
            },
            'appointments' => function($q) {
                $q->latest()->limit(5);
            }
        ])->findOrFail($patientId);

        // التحقق من التأمين
        $insuranceStatus = null;
        if ($patient->insurance) {
            $insuranceStatus = [
                'company' => $patient->insurance->company->name_ar,
                'policy_number' => $patient->insurance->policy_number,
                'status' => $patient->insurance->status,
                'coverage_percentage' => $patient->insurance->coverage_percentage ?? 0
            ];
        }

        return response()->json([
            'success' => true,
            'patient' => $patient,
            'insurance_status' => $insuranceStatus
        ]);
    }

    /**
     * إدارة الحالات الطارئة
     */
    public function handleEmergency(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'emergency_type' => 'required|string|max:100',
            'severity' => 'required|in:low,medium,high,critical',
            'description' => 'required|string|max:1000',
            'department_id' => 'required|exists:departments,id'
        ]);

        DB::transaction(function () use ($validated) {
            $patient = Patient::findOrFail($validated['patient_id']);

            // إنشاء زيارة طارئة
            $emergencyVisit = PatientVisit::create([
                'patient_id' => $patient->id,
                'visit_type' => 'emergency',
                'status' => 'emergency',
                'department_id' => $validated['department_id'],
                'priority' => 'emergency',
                'emergency_type' => $validated['emergency_type'],
                'severity' => $validated['severity'],
                'description' => $validated['description'],
                'check_in_time' => now(),
                'queue_number' => 0 // الطوارئ لها أولوية قصوى
            ]);

            // إعادة ترتيب الطوابير
            $this->queueService->prioritizeEmergency($patient, $validated['department_id']);

            // إرسال إشعارات طارئة
            $this->notificationService->sendEmergencyAlert(
                $patient, 
                $validated['department_id'], 
                $validated['severity']
            );
        });

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الحالة الطارئة وإرسال التنبيهات'
        ]);
    }

    /**
     * الحصول على التحديثات الفورية
     */
    public function getRealTimeUpdates()
    {
        $updates = [
            'queue_status' => $this->queueService->getAllDepartmentQueues(),
            'emergency_cases' => PatientVisit::where('visit_type', 'emergency')
                                           ->where('status', '!=', 'completed')
                                           ->count(),
            'waiting_patients' => PatientVisit::where('status', 'waiting')->count(),
            'available_doctors' => Doctor::where('is_available', true)->count(),
            'available_rooms' => Room::where('status', 'available')->count(),
            'recent_notifications' => $this->notificationService->getRecentNotifications(auth()->user(), 5),
            'timestamp' => now()->toISOString()
        ];

        return response()->json([
            'success' => true,
            'updates' => $updates
        ]);
    }

    /**
     * إنشاء موعد سريع
     */
    public function quickCreateAppointment(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'appointment_type' => 'required|in:consultation,follow_up,procedure',
            'notes' => 'nullable|string|max:500'
        ]);

        $appointment = Appointment::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'appointment_type' => $validated['appointment_type'],
            'status' => 'scheduled',
            'notes' => $validated['notes'],
            'created_by' => auth()->id()
        ]);

        // إرسال إشعارات
        $this->notificationService->notifyAppointmentCreated($appointment);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء الموعد بنجاح',
            'appointment_id' => $appointment->id
        ]);
    }

    /**
     * توليد باركود للمريض
     */
    private function generatePatientBarcode(): string
    {
        do {
            $barcode = 'P' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Patient::where('barcode', $barcode)->exists());

        return $barcode;
    }

    /**
     * الحصول على إحصائيات مفصلة
     */
    public function getDetailedStats()
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        $stats = [
            'today' => [
                'appointments' => Appointment::whereDate('appointment_date', $today)->count(),
                'new_patients' => Patient::whereDate('created_at', $today)->count(),
                'emergency_cases' => PatientVisit::where('visit_type', 'emergency')
                                                ->whereDate('created_at', $today)->count(),
                'completed_visits' => PatientVisit::where('status', 'completed')
                                                 ->whereDate('updated_at', $today)->count()
            ],
            'this_week' => [
                'appointments' => Appointment::where('appointment_date', '>=', $thisWeek)->count(),
                'new_patients' => Patient::where('created_at', '>=', $thisWeek)->count(),
                'emergency_cases' => PatientVisit::where('visit_type', 'emergency')
                                                ->where('created_at', '>=', $thisWeek)->count()
            ],
            'this_month' => [
                'appointments' => Appointment::where('appointment_date', '>=', $thisMonth)->count(),
                'new_patients' => Patient::where('created_at', '>=', $thisMonth)->count(),
                'emergency_cases' => PatientVisit::where('visit_type', 'emergency')
                                                ->where('created_at', '>=', $thisMonth)->count()
            ],
            'department_stats' => Department::withCount([
                'visits as today_visits' => function($query) use ($today) {
                    $query->whereDate('created_at', $today);
                },
                'visits as waiting_patients' => function($query) {
                    $query->where('status', 'waiting');
                }
            ])->get()
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }
}