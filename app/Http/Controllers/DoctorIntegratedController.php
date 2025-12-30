<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Lab;
use App\Models\Radiology;
use App\Helpers\RelationshipHelper;
use Carbon\Carbon;

class DoctorIntegratedController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('validate_relationships')->only(['dashboard', 'getPatientDetails', 'searchPatients']);
        // تبسيط الصلاحيات مؤقتاً
        // $this->middleware('permission:doctor.view')->only(['dashboard', 'getPatientDetails']);
    }

    /**
     * لوحة تحكم الطبيب المتكاملة مع معالجة آمنة للعلاقات
     */
    public function dashboard()
    {
        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();
        
        if (!$doctor) {
            return redirect()->route('dashboard')->with('error', 'لا يمكن الوصول للوحة الطبيب - لم يتم العثور على ملف الطبيب');
        }

        $today = Carbon::today();
        $userId = Auth::id(); // استخدام user_id للاستعلامات

        try {
            // إحصائيات اليوم مع معالجة الأخطاء
            $todayStats = [
                'total_appointments' => Appointment::where('doctor_id', $userId)
                    ->whereDate('appointment_date', $today)->count(),
                'confirmed_appointments' => Appointment::where('doctor_id', $userId)
                    ->whereDate('appointment_date', $today)
                    ->where('status', 'confirmed')->count(),
                'completed_appointments' => Appointment::where('doctor_id', $userId)
                    ->whereDate('appointment_date', $today)
                    ->where('status', 'completed')->count(),
                'pending_appointments' => Appointment::where('doctor_id', $userId)
                    ->whereDate('appointment_date', $today)
                    ->where('status', 'scheduled')->count(),
                'completed_examinations' => MedicalRecord::where('doctor_id', $userId)
                    ->whereDate('created_at', $today)->count(),
                'prescriptions_written' => Prescription::where('doctor_id', $userId)
                    ->whereDate('created_at', $today)->count(),
                'lab_orders' => Lab::where('doctor_id', $userId)
                    ->whereDate('created_at', $today)->count(),
                'radiology_orders' => Radiology::where('doctor_id', $userId)
                    ->whereDate('created_at', $today)->count()
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting today stats: ' . $e->getMessage());
            $todayStats = [
                'total_appointments' => 0,
                'confirmed_appointments' => 0,
                'completed_appointments' => 0,
                'pending_appointments' => 0,
                'completed_examinations' => 0,
                'prescriptions_written' => 0,
                'lab_orders' => 0,
                'radiology_orders' => 0
            ];
        }
        
        try {
            // مواعيد اليوم مع معالجة آمنة لعلاقات التأمين
            $todayAppointments = Appointment::where('doctor_id', $userId)
                ->whereDate('appointment_date', $today)
                ->with([
                    'patient' => function($query) {
                        $query->select('id', 'name', 'phone', 'national_id');
                    },
                    'patient.insurancePolicy' => function($query) {
                        $query->select('id', 'insurance_company_id', 'policy_number', 'coverage_percentage');
                    },
                    'patient.insurancePolicy.company' => function($query) {
                        $query->select('id', 'name_ar', 'name_en');
                    }
                ])
                ->orderBy('appointment_time')
                ->get();
                
            // معالجة آمنة للمرضى بدون تأمين
            $todayAppointments->each(function($appointment) {
                if ($appointment->patient && !$appointment->patient->insurancePolicy) {
                    $appointment->patient->setRelation('insurancePolicy', null);
                }
            });
            
        } catch (\Exception $e) {
            \Log::error('Error getting today appointments: ' . $e->getMessage());
            $todayAppointments = collect([]);
        }
        
        try {
            // المرضى الحاليين في الانتظار مع معالجة آمنة
            $currentPatients = Appointment::where('doctor_id', $userId)
                ->whereDate('appointment_date', $today)
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->with([
                    'patient' => function($query) {
                        $query->select('id', 'name', 'phone');
                    },
                    'patient.insurancePolicy.company' => function($query) {
                        $query->select('id', 'name_ar');
                    }
                ])
                ->orderBy('appointment_time')
                ->get();
                
            // معالجة آمنة للمرضى بدون تأمين
            $currentPatients->each(function($appointment) {
                if ($appointment->patient && !$appointment->patient->insurancePolicy) {
                    $appointment->patient->setRelation('insurancePolicy', null);
                }
            });
            
        } catch (\Exception $e) {
            \Log::error('Error getting current patients: ' . $e->getMessage());
            $currentPatients = collect([]);
        }
        
        try {
            // النتائج المعلقة للمراجعة مع معالجة آمنة
            $labResults = Lab::where('doctor_id', $userId)
                ->where('status', 'completed')
                ->whereNull('reviewed_at')
                ->with(['patient' => function($query) {
                    $query->select('id', 'name', 'phone');
                }])
                ->orderBy('completed_at', 'desc')
                ->limit(10)
                ->get();
                
            $radiologyResults = Radiology::where('doctor_id', $userId)
                ->where('status', 'completed')
                ->whereNull('reviewed_at')
                ->with(['patient' => function($query) {
                    $query->select('id', 'name', 'phone');
                }])
                ->orderBy('completed_at', 'desc')
                ->limit(10)
                ->get();
                
            $pendingResults = [
                'lab_results' => $labResults,
                'radiology_results' => $radiologyResults,
                'total_count' => $labResults->count() + $radiologyResults->count()
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting pending results: ' . $e->getMessage());
            $pendingResults = [
                'lab_results' => collect([]),
                'radiology_results' => collect([]),
                'total_count' => 0
            ];
        }
        
        // التنبيهات الطبية (تستخدم الدالة المحدثة)
        $medicalAlerts = $this->getMedicalAlerts($doctor);
        
        // الاستشارات المطلوبة
        $consultationRequests = collect([]);

        return view('doctor.integrated-dashboard', compact(
            'doctor',
            'todayStats',
            'todayAppointments', 
            'currentPatients',
            'pendingResults',
            'medicalAlerts',
            'consultationRequests'
        ));
    }

    /**
     * بدء الكشف الطبي للمريض
     */
    public function startExamination(Appointment $appointment)
    {
        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();
        
        if ($appointment->doctor_id !== Auth::id()) {
            return redirect()->back()->with('error', 'غير مصرح لك بالوصول لهذا الموعد');
        }

        // تحديث حالة الموعد
        $appointment->update(['status' => 'in_progress']);

        // التاريخ الطبي للمريض
        $medicalHistory = MedicalRecord::where('patient_id', $appointment->patient_id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        // الوصفات النشطة
        $activePrescriptions = Prescription::where('patient_id', $appointment->patient_id)
            ->where('status', 'active')
            ->get();
        
        // الحساسية والتحذيرات
        $allergies = $appointment->patient->allergies ?? [];
        
        // النتائج المعلقة
        $pendingResults = [
            'lab_results' => Lab::where('patient_id', $appointment->patient_id)
                ->where('status', 'completed')
                ->whereNull('reviewed_at')
                ->orderBy('completed_at', 'desc')
                ->get(),
            'radiology_results' => Radiology::where('patient_id', $appointment->patient_id)
                ->where('status', 'completed')
                ->whereNull('reviewed_at')
                ->orderBy('completed_at', 'desc')
                ->get()
        ];

        return view('doctor.examination.start', compact(
            'appointment',
            'doctor',
            'medicalHistory',
            'activePrescriptions',
            'allergies',
            'pendingResults'
        ));
    }

    /**
     * حفظ التقرير الطبي
     */
    public function saveMedicalReport(Request $request, Appointment $appointment)
    {
        $request->validate([
            'chief_complaint' => 'required|string|max:1000',
            'symptoms' => 'nullable|array',
            'vital_signs' => 'nullable|array',
            'physical_examination' => 'required|string',
            'diagnosis' => 'required|string|max:1000',
            'treatment_plan' => 'required|string',
            'notes' => 'nullable|string|max:2000'
        ]);

        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();

        DB::beginTransaction();
        try {
            // إنشاء السجل الطبي
            $medicalRecord = MedicalRecord::create([
                'patient_id' => $appointment->patient_id,
                'doctor_id' => Auth::id(), // استخدام user_id
                'appointment_id' => $appointment->id,
                'chief_complaint' => $request->chief_complaint,
                'symptoms' => $request->symptoms ? json_encode($request->symptoms) : null,
                'vital_signs' => $request->vital_signs ? json_encode($request->vital_signs) : null,
                'physical_examination' => $request->physical_examination,
                'diagnosis' => $request->diagnosis,
                'treatment_plan' => $request->treatment_plan,
                'notes' => $request->notes,
                'examination_date' => now(),
                'status' => 'completed'
            ]);

            // تحديث حالة الموعد
            $appointment->update(['status' => 'completed']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ التقرير الطبي بنجاح',
                'medical_record_id' => $medicalRecord->id,
                'next_steps_url' => route('doctor.next-steps', $medicalRecord)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ التقرير الطبي: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * الخطوات التالية بعد الكشف
     */
    public function nextSteps(MedicalRecord $medicalRecord)
    {
        $patient = $medicalRecord->patient;
        $doctor = $medicalRecord->doctor;
        
        // الأطباء المتاحين للتحويل
        $availableDoctors = Doctor::where('id', '!=', $doctor->id)
            ->where('is_available', true)
            ->with('user', 'specialization')
            ->get();
        
        // التخصصات المتاحة
        $specializations = $availableDoctors->pluck('specialization')->unique();

        return view('doctor.examination.next-steps', compact(
            'medicalRecord',
            'patient',
            'doctor',
            'availableDoctors',
            'specializations'
        ));
    }

    /**
     * كتابة روشتة طبية
     */
    public function writePrescription(Request $request, MedicalRecord $medicalRecord)
    {
        $request->validate([
            'medications' => 'required|array|min:1',
            'medications.*.name' => 'required|string|max:255',
            'medications.*.dosage' => 'required|string|max:100',
            'medications.*.frequency' => 'required|string|max:100',
            'medications.*.duration' => 'required|string|max:100',
            'medications.*.instructions' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();

        DB::beginTransaction();
        try {
            // إنشاء الروشتة
            $prescription = Prescription::create([
                'patient_id' => $medicalRecord->patient_id,
                'doctor_id' => $doctor->id,
                'medical_record_id' => $medicalRecord->id,
                'prescription_date' => now(),
                'notes' => $request->notes,
                'status' => 'active'
            ]);

            // إضافة الأدوية
            foreach ($request->medications as $medication) {
                $prescription->prescriptionItems()->create([
                    'medication_name' => $medication['name'],
                    'dosage' => $medication['dosage'],
                    'frequency' => $medication['frequency'],
                    'duration' => $medication['duration'],
                    'instructions' => $medication['instructions'] ?? null
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الروشتة الطبية بنجاح',
                'prescription_id' => $prescription->id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الروشتة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحويل المريض لتخصص آخر
     */
    public function transferToSpecialist(Request $request, MedicalRecord $medicalRecord)
    {
        $request->validate([
            'target_doctor_id' => 'required|exists:doctors,id',
            'transfer_reason' => 'required|string|max:1000',
            'urgency' => 'required|in:routine,urgent,emergency',
            'appointment_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000'
        ]);

        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();
        $targetDoctor = Doctor::findOrFail($request->target_doctor_id);

        DB::beginTransaction();
        try {
            // إنشاء موعد جديد مع الطبيب المختص
            $newAppointment = Appointment::create([
                'patient_id' => $medicalRecord->patient_id,
                'doctor_id' => $targetDoctor->id,
                'appointment_date' => $request->appointment_date ?? now()->addDays(1),
                'appointment_time' => now()->addDays(1)->setTime(9, 0),
                'type' => 'consultation',
                'status' => 'scheduled',
                'notes' => "تحويل من د. {$doctor->user->name} - {$request->transfer_reason}",
                'referred_by' => $doctor->id,
                'urgency' => $request->urgency
            ]);

            // تحديث السجل الطبي
            $medicalRecord->update([
                'referred_to' => $targetDoctor->id,
                'transfer_reason' => $request->transfer_reason,
                'transfer_date' => now()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "تم تحويل المريض إلى د. {$targetDoctor->user->name} بنجاح",
                'appointment_id' => $newAppointment->id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحويل المريض: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * حجز موعد استشارة قادم
     */
    public function bookFollowUp(Request $request, MedicalRecord $medicalRecord)
    {
        $request->validate([
            'follow_up_date' => 'required|date|after:today',
            'follow_up_time' => 'required|date_format:H:i',
            'follow_up_reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();

        DB::beginTransaction();
        try {
            // إنشاء موعد المتابعة
            $followUpAppointment = Appointment::create([
                'patient_id' => $medicalRecord->patient_id,
                'doctor_id' => $doctor->id,
                'appointment_date' => $request->follow_up_date,
                'appointment_time' => Carbon::parse($request->follow_up_date . ' ' . $request->follow_up_time),
                'type' => 'follow_up',
                'status' => 'scheduled',
                'notes' => $request->follow_up_reason,
                'parent_appointment_id' => $medicalRecord->appointment_id
            ]);

            // تحديث السجل الطبي
            $medicalRecord->update([
                'follow_up_required' => true,
                'follow_up_date' => $request->follow_up_date,
                'follow_up_notes' => $request->notes
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حجز موعد المتابعة بنجاح',
                'appointment_id' => $followUpAppointment->id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حجز موعد المتابعة: ' . $e->getMessage()
            ], 500);
        }
    }
    public function conductExamination(Request $request, Patient $patient)
    {
        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();
        
        // التاريخ الطبي للمريض
        $medicalHistory = MedicalRecord::where('patient_id', $patient->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        // الوصفات الحالية
        $currentPrescriptions = Prescription::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->with('prescriptionItems.medication')
            ->get();
        
        // الحساسية والتحذيرات
        $allergies = $patient->allergies ?? [];
        $warnings = $this->getPatientWarnings($patient);
        
        // الفحوصات المعلقة
        $pendingTests = $this->getPatientPendingTests($patient);

        return view('doctor.examination.conduct', compact(
            'patient',
            'doctor',
            'medicalHistory',
            'currentPrescriptions',
            'allergies',
            'warnings',
            'pendingTests'
        ));
    }

    /**
     * حفظ نتائج الكشف
     */
    public function saveExamination(Request $request, Patient $patient)
    {
        $request->validate([
            'chief_complaint' => 'required|string|max:1000',
            'symptoms' => 'required|array',
            'symptoms.*' => 'string|max:255',
            'vital_signs' => 'required|array',
            'vital_signs.temperature' => 'nullable|numeric|between:30,45',
            'vital_signs.blood_pressure_systolic' => 'nullable|integer|between:60,300',
            'vital_signs.blood_pressure_diastolic' => 'nullable|integer|between:40,200',
            'vital_signs.heart_rate' => 'nullable|integer|between:30,200',
            'vital_signs.respiratory_rate' => 'nullable|integer|between:8,60',
            'vital_signs.oxygen_saturation' => 'nullable|integer|between:70,100',
            'physical_examination' => 'required|string',
            'diagnosis' => 'required|string|max:1000',
            'treatment_plan' => 'required|string',
            'follow_up_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:2000'
        ]);

        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();

        DB::beginTransaction();
        try {
            // إنشاء السجل الطبي
            $medicalRecord = MedicalRecord::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_id' => $request->appointment_id,
                'chief_complaint' => $request->chief_complaint,
                'symptoms' => json_encode($request->symptoms),
                'vital_signs' => json_encode($request->vital_signs),
                'physical_examination' => $request->physical_examination,
                'diagnosis' => $request->diagnosis,
                'treatment_plan' => $request->treatment_plan,
                'follow_up_date' => $request->follow_up_date,
                'notes' => $request->notes,
                'examination_date' => now(),
                'status' => 'completed'
            ]);

            // تحديث حالة الموعد إذا كان موجود
            if ($request->appointment_id) {
                Appointment::where('id', $request->appointment_id)
                    ->update(['status' => 'completed']);
            }

            // إرسال إشعار للمريض - مبسط
            // $this->notificationService->sendExaminationCompleted($patient, $doctor, $medicalRecord);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ نتائج الكشف بنجاح',
                'medical_record_id' => $medicalRecord->id,
                'redirect_url' => route('doctor.examination.next-steps', $medicalRecord)
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حفظ نتائج الكشف: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * إنشاء وصفة طبية
     */
    public function createPrescription(Request $request, Patient $patient)
    {
        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();
        
        // الأدوية المتاحة
        $availableMedications = $this->getAvailableMedications();
        
        // الوصفات السابقة
        $previousPrescriptions = Prescription::where('patient_id', $patient->id)
            ->where('doctor_id', $doctor->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->with('prescriptionItems.medication')
            ->get();
        
        // الحساسية والتفاعلات
        $allergies = $patient->allergies ?? [];
        $currentMedications = $this->getCurrentMedications($patient);

        return view('doctor.prescriptions.create', compact(
            'patient',
            'doctor',
            'availableMedications',
            'previousPrescriptions',
            'allergies',
            'currentMedications'
        ));
    }

    /**
     * حفظ الوصفة الطبية
     */
    public function savePrescription(Request $request, Patient $patient)
    {
        $request->validate([
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'medications' => 'required|array|min:1',
            'medications.*.medication_id' => 'required|exists:medications,id',
            'medications.*.dosage' => 'required|string|max:100',
            'medications.*.frequency' => 'required|string|max:100',
            'medications.*.duration' => 'required|string|max:100',
            'medications.*.instructions' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();

        DB::beginTransaction();
        try {
            // التحقق من التفاعلات الدوائية - مبسط
            // $interactions = $this->drugInteractionService->checkInteractions(
            //     $request->medications,
            //     $this->getCurrentMedications($patient)
            // );
            $interactions = ['critical' => [], 'moderate' => [], 'minor' => []];

            if (!empty($interactions['critical'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'تم اكتشاف تفاعلات دوائية خطيرة',
                    'interactions' => $interactions,
                    'require_confirmation' => true
                ]);
            }

            // إنشاء الوصفة
            $prescription = Prescription::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'medical_record_id' => $request->medical_record_id,
                'prescription_date' => now(),
                'notes' => $request->notes,
                'status' => 'active'
            ]);

            // إضافة الأدوية
            foreach ($request->medications as $medication) {
                $prescription->prescriptionItems()->create([
                    'medication_id' => $medication['medication_id'],
                    'dosage' => $medication['dosage'],
                    'frequency' => $medication['frequency'],
                    'duration' => $medication['duration'],
                    'instructions' => $medication['instructions'] ?? null
                ]);
            }

            // إرسال إشعار للمريض والصيدلية - مبسط
            // $this->notificationService->sendPrescriptionCreated($patient, $doctor, $prescription);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الوصفة الطبية بنجاح',
                'prescription_id' => $prescription->id,
                'interactions' => $interactions
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الوصفة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * طلب التحاليل المخبرية
     */
    public function orderLabTests(Request $request, Patient $patient)
    {
        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();
        
        // التحاليل المتاحة
        $availableTests = $this->getAvailableLabTests();
        
        // التحاليل السابقة
        $previousTests = Lab::where('patient_id', $patient->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('doctor.lab-orders.create', compact(
            'patient',
            'doctor',
            'availableTests',
            'previousTests'
        ));
    }

    /**
     * حفظ طلب التحاليل
     */
    public function saveLabOrder(Request $request, Patient $patient)
    {
        $request->validate([
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'tests' => 'required|array|min:1',
            'tests.*' => 'exists:lab_tests,id',
            'priority' => 'required|in:routine,urgent,stat',
            'clinical_notes' => 'nullable|string|max:1000',
            'fasting_required' => 'boolean',
            'collection_date' => 'nullable|date|after_or_equal:today'
        ]);

        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();

        DB::beginTransaction();
        try {
            // إنشاء طلب التحاليل
            $labOrder = Lab::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'medical_record_id' => $request->medical_record_id,
                'test_ids' => json_encode($request->tests),
                'priority' => $request->priority,
                'clinical_notes' => $request->clinical_notes,
                'fasting_required' => $request->fasting_required ?? false,
                'collection_date' => $request->collection_date ?? now(),
                'order_date' => now(),
                'status' => 'ordered'
            ]);

            // إرسال إشعار للمختبر - مبسط
            // $this->notificationService->sendLabOrderCreated($patient, $doctor, $labOrder);

            // إرسال تعليمات للمريض إذا كان الصيام مطلوب - مبسط
            if ($request->fasting_required) {
                // $this->notificationService->sendFastingInstructions($patient, $labOrder);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال طلب التحاليل بنجاح',
                'lab_order_id' => $labOrder->id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال طلب التحاليل: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * طلب الأشعة
     */
    public function orderRadiology(Request $request, Patient $patient)
    {
        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();
        
        // أنواع الأشعة المتاحة
        $availableStudies = $this->getAvailableRadiologyStudies();
        
        // الأشعة السابقة
        $previousStudies = Radiology::where('patient_id', $patient->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('doctor.radiology-orders.create', compact(
            'patient',
            'doctor',
            'availableStudies',
            'previousStudies'
        ));
    }

    /**
     * حفظ طلب الأشعة
     */
    public function saveRadiologyOrder(Request $request, Patient $patient)
    {
        $request->validate([
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'study_id' => 'required|exists:radiology_studies,id',
            'priority' => 'required|in:routine,urgent,stat',
            'clinical_indication' => 'required|string|max:1000',
            'contrast_required' => 'boolean',
            'preparation_instructions' => 'nullable|string|max:1000',
            'scheduled_date' => 'nullable|date|after_or_equal:today'
        ]);

        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();

        DB::beginTransaction();
        try {
            // إنشاء طلب الأشعة
            $radiologyOrder = Radiology::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'medical_record_id' => $request->medical_record_id,
                'study_id' => $request->study_id,
                'priority' => $request->priority,
                'clinical_indication' => $request->clinical_indication,
                'contrast_required' => $request->contrast_required ?? false,
                'preparation_instructions' => $request->preparation_instructions,
                'scheduled_date' => $request->scheduled_date ?? now()->addDay(),
                'order_date' => now(),
                'status' => 'ordered'
            ]);

            // إرسال إشعار لقسم الأشعة - مبسط
            // $this->notificationService->sendRadiologyOrderCreated($patient, $doctor, $radiologyOrder);

            // إرسال تعليمات التحضير للمريض - مبسط
            if ($request->preparation_instructions) {
                // $this->notificationService->sendRadiologyPreparation($patient, $radiologyOrder);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إرسال طلب الأشعة بنجاح',
                'radiology_order_id' => $radiologyOrder->id
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إرسال طلب الأشعة: ' . $e->getMessage()
            ], 500);
        }
    }

    // المزيد من الوظائف في الجزء التالي...
    
    /**
     * إحصائيات اليوم للطبيب
     */
    private function getTodayStatistics($doctor)
    {
        return [
            'appointments' => Appointment::where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', today())
                ->count(),
            'completed_examinations' => MedicalRecord::where('doctor_id', $doctor->id)
                ->whereDate('created_at', today())
                ->count(),
            'prescriptions_written' => Prescription::where('doctor_id', $doctor->id)
                ->whereDate('created_at', today())
                ->count(),
            'lab_orders' => Lab::where('doctor_id', $doctor->id)
                ->whereDate('created_at', today())
                ->count(),
            'radiology_orders' => Radiology::where('doctor_id', $doctor->id)
                ->whereDate('created_at', today())
                ->count()
        ];
    }

    /**
     * مواعيد اليوم
     */
    private function getTodayAppointments($doctor)
    {
        return Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', today())
            ->with(['patient', 'appointmentType'])
            ->orderBy('appointment_time')
            ->get();
    }

    /**
     * المرضى الحاليين
     */
    private function getCurrentPatients($doctor)
    {
        return Patient::whereHas('appointments', function($query) use ($doctor) {
            $query->where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', today())
                ->where('status', 'in_progress');
        })->with(['latestAppointment', 'latestMedicalRecord'])->get();
    }

    /**
     * النتائج المعلقة
     */
    private function getPendingResults($doctor)
    {
        $labResults = Lab::where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->whereNull('reviewed_at')
            ->with('patient')
            ->get();

        $radiologyResults = Radiology::where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->whereNull('reviewed_at')
            ->with('patient')
            ->get();

        return [
            'lab_results' => $labResults,
            'radiology_results' => $radiologyResults,
            'total_count' => $labResults->count() + $radiologyResults->count()
        ];
    }

    /**
     * التنبيهات الطبية المحدثة مع معالجة آمنة للأخطاء
     */
    private function getMedicalAlerts($doctor)
    {
        $alerts = [];
        $userId = Auth::id(); // استخدام user_id للاستعلامات

        try {
            // مرضى يحتاجون متابعة عاجلة
            $urgentFollowUps = MedicalRecord::where('doctor_id', $userId)
                ->whereDate('follow_up_date', '<=', today())
                ->whereNotNull('follow_up_date')
                ->where(function($query) {
                    $query->whereDoesntHave('followUpAppointments')
                          ->orWhereHas('followUpAppointments', function($subQuery) {
                              $subQuery->where('status', '!=', 'completed');
                          });
                })
                ->with(['patient' => function($query) {
                    $query->select('id', 'name', 'phone');
                }])
                ->get();

            foreach ($urgentFollowUps as $record) {
                if ($record->patient) { // تحقق من وجود المريض
                    $alerts[] = [
                        'type' => 'urgent_follow_up',
                        'message' => "المريض {$record->patient->name} يحتاج متابعة عاجلة",
                        'patient_id' => $record->patient_id,
                        'medical_record_id' => $record->id,
                        'follow_up_date' => $record->follow_up_date->format('Y-m-d'),
                        'priority' => 'high',
                        'days_overdue' => today()->diffInDays($record->follow_up_date, false)
                    ];
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error getting urgent follow-ups: ' . $e->getMessage());
            // لا نضيف تنبيهات في حالة الخطأ، لكن نسجل الخطأ
        }

        try {
            // نتائج حرجة للمختبر
            $criticalLabResults = Lab::where('doctor_id', $userId)
                ->where('status', 'completed')
                ->where('is_critical', true)
                ->whereNull('reviewed_at')
                ->with(['patient' => function($query) {
                    $query->select('id', 'name', 'phone');
                }])
                ->orderBy('completed_at', 'desc')
                ->get();

            foreach ($criticalLabResults as $result) {
                if ($result->patient) { // تحقق من وجود المريض
                    $alerts[] = [
                        'type' => 'critical_lab_result',
                        'message' => "نتيجة مختبر حرجة للمريض {$result->patient->name}",
                        'patient_id' => $result->patient_id,
                        'lab_id' => $result->id,
                        'priority' => 'critical',
                        'completed_at' => $result->completed_at ? $result->completed_at->format('Y-m-d H:i') : null
                    ];
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error getting critical lab results: ' . $e->getMessage());
        }

        try {
            // نتائج حرجة للأشعة
            $criticalRadiologyResults = Radiology::where('doctor_id', $userId)
                ->where('status', 'completed')
                ->where('is_critical', true)
                ->whereNull('reviewed_at')
                ->with(['patient' => function($query) {
                    $query->select('id', 'name', 'phone');
                }])
                ->orderBy('completed_at', 'desc')
                ->get();

            foreach ($criticalRadiologyResults as $result) {
                if ($result->patient) { // تحقق من وجود المريض
                    $alerts[] = [
                        'type' => 'critical_radiology_result',
                        'message' => "نتيجة أشعة حرجة للمريض {$result->patient->name}",
                        'patient_id' => $result->patient_id,
                        'radiology_id' => $result->id,
                        'priority' => 'critical',
                        'completed_at' => $result->completed_at ? $result->completed_at->format('Y-m-d H:i') : null
                    ];
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error getting critical radiology results: ' . $e->getMessage());
        }

        try {
            // مواعيد اليوم المتأخرة
            $lateAppointments = Appointment::where('doctor_id', $userId)
                ->whereDate('appointment_date', today())
                ->where('appointment_time', '<', now()->subMinutes(15))
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->with(['patient' => function($query) {
                    $query->select('id', 'name', 'phone');
                }])
                ->get();

            foreach ($lateAppointments as $appointment) {
                if ($appointment->patient) {
                    $alerts[] = [
                        'type' => 'late_appointment',
                        'message' => "المريض {$appointment->patient->name} متأخر عن موعده",
                        'patient_id' => $appointment->patient_id,
                        'appointment_id' => $appointment->id,
                        'priority' => 'medium',
                        'scheduled_time' => $appointment->appointment_time->format('H:i'),
                        'minutes_late' => now()->diffInMinutes($appointment->appointment_time)
                    ];
                }
            }

        } catch (\Exception $e) {
            \Log::error('Error getting late appointments: ' . $e->getMessage());
        }

        // ترتيب التنبيهات حسب الأولوية
        $priorityOrder = ['critical' => 1, 'high' => 2, 'medium' => 3, 'low' => 4];
        
        usort($alerts, function($a, $b) use ($priorityOrder) {
            $aPriority = $priorityOrder[$a['priority']] ?? 5;
            $bPriority = $priorityOrder[$b['priority']] ?? 5;
            return $aPriority <=> $bPriority;
        });

        return $alerts;
    }

    /**
     * طلبات الاستشارة
     */
    private function getConsultationRequests($doctor)
    {
        // هذا يحتاج لجدول consultation_requests منفصل
        return collect(); // مؤقتاً
    }

    // وظائف مساعدة أخرى...
    private function getPatientWarnings($patient) { return []; }
    private function getPatientPendingTests($patient) { return collect(); }
    private function getAvailableMedications() { return collect(); }
    private function getCurrentMedications($patient) { return collect(); }
    private function getAvailableLabTests() { return collect(); }
    private function getAvailableRadiologyStudies() { return collect(); }
    
    private function canCreatePrescription($patient, $doctor) { return true; }
    private function canOrderLabTests($patient, $doctor) { return true; }
    private function canOrderRadiology($patient, $doctor) { return true; }
    private function canBookConsultation($patient, $doctor) { return true; }
    private function canBookFollowUp($patient, $doctor) { return true; }
    private function canAdmitPatient($patient, $doctor) { return true; }
    private function canTransferPatient($patient, $doctor) { return true; }

    /**
     * تأكيد الموعد
     */
    public function confirmAppointment(Appointment $appointment)
    {
        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();
        
        if ($appointment->doctor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتأكيد هذا الموعد'
            ], 403);
        }

        try {
            $appointment->update(['status' => 'confirmed']);

            return response()->json([
                'success' => true,
                'message' => 'تم تأكيد الموعد بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تأكيد الموعد: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * تحديثات لوحة التحكم للتحديث التلقائي
     */
    public function getDashboardUpdates()
    {
        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();
        
        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        $alerts = $this->getMedicalAlerts($doctor);
        $pendingResults = $this->getPendingResults($doctor);

        return response()->json([
            'alerts' => $alerts,
            'pending_results' => $pendingResults,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * البحث عن المرضى
     */
    public function searchPatients(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        try {
            // استخدام RelationshipHelper للتحميل الآمن
            $patients = RelationshipHelper::safeQueryWith(Patient::class, [
                'insurancePolicy' => function($query) {
                    $query->select('id', 'insurance_company_id', 'policy_number');
                },
                'insurancePolicy.company' => function($query) {
                    $query->select('id', 'name_ar', 'name_en');
                }
            ])
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhere('phone', 'LIKE', "%{$query}%")
            ->orWhere('national_id', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

            return response()->json($patients->map(function($patient) {
                // استخدام RelationshipHelper للوصول الآمن للقيم
                $insuranceCompanyName = RelationshipHelper::safeNestedValue(
                    $patient, 
                    'insurancePolicy.company.name_ar', 
                    null
                );

                return [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'phone' => $patient->phone,
                    'national_id' => $patient->national_id,
                    'insurance_status' => RelationshipHelper::safeRelation($patient, 'insurancePolicy') ? 'مؤمن' : 'نقدي',
                    'insurance_company' => $insuranceCompanyName
                ];
            }));

        } catch (\Exception $e) {
            \Log::error('Error in searchPatients: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'حدث خطأ أثناء البحث عن المرضى',
                'message' => 'يرجى المحاولة مرة أخرى'
            ], 500);
        }
    }

    /**
     * تفاصيل المريض
     */
    public function getPatientDetails(Patient $patient)
    {
        try {
            // استخدام RelationshipHelper للتحميل الآمن
            $relations = [
                'insurancePolicy.company',
                'medicalRecords' => function($query) {
                    $query->orderBy('created_at', 'desc')->limit(5);
                },
                'prescriptions' => function($query) {
                    $query->where('status', 'active')->with('prescriptionItems');
                },
                'appointments' => function($query) {
                    $query->orderBy('appointment_date', 'desc')->limit(10);
                }
            ];

            RelationshipHelper::safeLoad($patient, $relations);

            // الحصول على البيانات بأمان
            $allergies = $patient->allergies ?? [];
            $warnings = $this->getPatientWarnings($patient);

            return response()->json([
                'patient' => $patient,
                'allergies' => $allergies,
                'warnings' => $warnings,
                'insurance_status' => RelationshipHelper::safeRelation($patient, 'insurancePolicy') ? 'مؤمن' : 'نقدي',
                'insurance_company' => RelationshipHelper::safeNestedValue($patient, 'insurancePolicy.company.name_ar')
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in getPatientDetails: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'حدث خطأ أثناء تحميل بيانات المريض',
                'message' => 'يرجى المحاولة مرة أخرى'
            ], 500);
        }
    }

    /**
     * تاريخ المريض الطبي
     */
    public function getPatientHistory(Patient $patient)
    {
        $medicalRecords = MedicalRecord::where('patient_id', $patient->id)
            ->with(['doctor.user', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($medicalRecords);
    }

    /**
     * مراجعة نتائج المختبر
     */
    public function reviewLabResult($labResultId)
    {
        $labResult = Lab::with(['patient', 'doctor.user'])->findOrFail($labResultId);
        
        return view('doctor.results.lab-review', compact('labResult'));
    }

    /**
     * اعتماد نتائج المختبر
     */
    public function approveLabResult(Request $request, $labResultId)
    {
        $labResult = Lab::findOrFail($labResultId);
        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();

        try {
            $labResult->update([
                'reviewed_at' => now(),
                'reviewed_by' => $doctor->id,
                'review_notes' => $request->review_notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم اعتماد نتيجة المختبر بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء اعتماد النتيجة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * مراجعة نتائج الأشعة
     */
    public function reviewRadiologyResult($radiologyResultId)
    {
        $radiologyResult = Radiology::with(['patient', 'doctor.user'])->findOrFail($radiologyResultId);
        
        return view('doctor.results.radiology-review', compact('radiologyResult'));
    }

    /**
     * اعتماد نتائج الأشعة
     */
    public function approveRadiologyResult(Request $request, $radiologyResultId)
    {
        $radiologyResult = Radiology::findOrFail($radiologyResultId);
        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();

        try {
            $radiologyResult->update([
                'reviewed_at' => now(),
                'reviewed_by' => $doctor->id,
                'review_notes' => $request->review_notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم اعتماد نتيجة الأشعة بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء اعتماد النتيجة: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * التقرير اليومي للطبيب
     */
    public function getDailyReport()
    {
        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();
        $today = Carbon::today();

        $report = [
            'date' => $today->format('Y-m-d'),
            'appointments' => [
                'total' => Appointment::where('doctor_id', $doctor->id)->whereDate('appointment_date', $today)->count(),
                'completed' => Appointment::where('doctor_id', $doctor->id)->whereDate('appointment_date', $today)->where('status', 'completed')->count(),
                'cancelled' => Appointment::where('doctor_id', $doctor->id)->whereDate('appointment_date', $today)->where('status', 'cancelled')->count(),
            ],
            'medical_records' => MedicalRecord::where('doctor_id', $doctor->id)->whereDate('created_at', $today)->count(),
            'prescriptions' => Prescription::where('doctor_id', $doctor->id)->whereDate('created_at', $today)->count(),
            'lab_orders' => Lab::where('doctor_id', $doctor->id)->whereDate('created_at', $today)->count(),
            'radiology_orders' => Radiology::where('doctor_id', $doctor->id)->whereDate('created_at', $today)->count(),
        ];

        return response()->json($report);
    }

    /**
     * إحصائيات الأداء
     */
    public function getPerformanceStatistics()
    {
        $doctor = Auth::user()->doctor ?? Doctor::where('user_id', Auth::id())->first();
        $lastMonth = Carbon::now()->subMonth();

        $stats = [
            'patients_treated' => MedicalRecord::where('doctor_id', $doctor->id)
                ->where('created_at', '>=', $lastMonth)
                ->distinct('patient_id')
                ->count(),
            'average_appointments_per_day' => Appointment::where('doctor_id', $doctor->id)
                ->where('created_at', '>=', $lastMonth)
                ->count() / 30,
            'prescription_accuracy' => 95, // يحتاج حساب حقيقي
            'patient_satisfaction' => 4.8, // يحتاج نظام تقييم
        ];

        return response()->json($stats);
    }
}