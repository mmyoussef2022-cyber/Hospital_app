<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Lab;
use App\Models\Radiology;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MedicalProcedureService
{
    /**
     * إجراء كشف طبي متكامل
     */
    public function conductComprehensiveExamination($patientId, $doctorId, $examinationData)
    {
        DB::beginTransaction();
        try {
            $patient = Patient::findOrFail($patientId);
            $doctor = Doctor::findOrFail($doctorId);

            // إنشاء السجل الطبي
            $medicalRecord = MedicalRecord::create([
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'appointment_id' => $examinationData['appointment_id'] ?? null,
                'chief_complaint' => $examinationData['chief_complaint'],
                'symptoms' => json_encode($examinationData['symptoms']),
                'vital_signs' => json_encode($examinationData['vital_signs']),
                'physical_examination' => $examinationData['physical_examination'],
                'diagnosis' => $examinationData['diagnosis'],
                'treatment_plan' => $examinationData['treatment_plan'],
                'follow_up_date' => $examinationData['follow_up_date'] ?? null,
                'notes' => $examinationData['notes'] ?? null,
                'examination_date' => now(),
                'status' => 'completed'
            ]);

            // تحديث حالة الموعد
            if (isset($examinationData['appointment_id'])) {
                Appointment::where('id', $examinationData['appointment_id'])
                    ->update(['status' => 'completed']);
            }

            // تحليل الأعراض والتشخيص
            $analysisResults = $this->analyzeSymptoms($examinationData['symptoms'], $examinationData['diagnosis']);
            
            // اقتراح الإجراءات التالية
            $recommendations = $this->generateRecommendations($patient, $medicalRecord, $analysisResults);

            DB::commit();

            return [
                'success' => true,
                'medical_record' => $medicalRecord,
                'analysis' => $analysisResults,
                'recommendations' => $recommendations
            ];

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * إنشاء وصفة طبية ذكية
     */
    public function createSmartPrescription($patientId, $doctorId, $prescriptionData)
    {
        $patient = Patient::findOrFail($patientId);
        
        // التحقق من الحساسية
        $allergyCheck = $this->checkAllergies($patient, $prescriptionData['medications']);
        if (!$allergyCheck['safe']) {
            return [
                'success' => false,
                'error' => 'تم اكتشاف حساسية للأدوية المحددة',
                'allergies' => $allergyCheck['conflicts']
            ];
        }

        // التحقق من التفاعلات الدوائية
        $interactionCheck = $this->checkDrugInteractions($patient, $prescriptionData['medications']);
        if (!$interactionCheck['safe']) {
            return [
                'success' => false,
                'error' => 'تم اكتشاف تفاعلات دوائية',
                'interactions' => $interactionCheck['interactions']
            ];
        }

        DB::beginTransaction();
        try {
            // إنشاء الوصفة
            $prescription = Prescription::create([
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'medical_record_id' => $prescriptionData['medical_record_id'] ?? null,
                'prescription_date' => now(),
                'notes' => $prescriptionData['notes'] ?? null,
                'status' => 'active'
            ]);

            // إضافة الأدوية
            foreach ($prescriptionData['medications'] as $medication) {
                $prescription->prescriptionItems()->create([
                    'medication_id' => $medication['medication_id'],
                    'dosage' => $medication['dosage'],
                    'frequency' => $medication['frequency'],
                    'duration' => $medication['duration'],
                    'instructions' => $medication['instructions'] ?? null
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'prescription' => $prescription,
                'warnings' => array_merge($allergyCheck['warnings'], $interactionCheck['warnings'])
            ];

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * طلب فحوصات مخبرية ذكية
     */
    public function orderSmartLabTests($patientId, $doctorId, $testData)
    {
        $patient = Patient::findOrFail($patientId);
        
        // تحليل التشخيص واقتراح فحوصات إضافية
        $suggestedTests = $this->suggestAdditionalTests($patient, $testData);
        
        // التحقق من الفحوصات المكررة
        $duplicateCheck = $this->checkDuplicateTests($patient, $testData['tests']);

        DB::beginTransaction();
        try {
            $labOrder = Lab::create([
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'medical_record_id' => $testData['medical_record_id'] ?? null,
                'test_ids' => json_encode($testData['tests']),
                'priority' => $testData['priority'],
                'clinical_notes' => $testData['clinical_notes'] ?? null,
                'fasting_required' => $testData['fasting_required'] ?? false,
                'collection_date' => $testData['collection_date'] ?? now(),
                'order_date' => now(),
                'status' => 'ordered'
            ]);

            DB::commit();

            return [
                'success' => true,
                'lab_order' => $labOrder,
                'suggested_tests' => $suggestedTests,
                'duplicate_warnings' => $duplicateCheck
            ];

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * طلب أشعة ذكية
     */
    public function orderSmartRadiology($patientId, $doctorId, $radiologyData)
    {
        $patient = Patient::findOrFail($patientId);
        
        // التحقق من الأشعة السابقة
        $previousStudies = $this->checkPreviousRadiology($patient, $radiologyData['study_id']);
        
        // اقتراح بروتوكولات إضافية
        $protocolSuggestions = $this->suggestRadiologyProtocols($patient, $radiologyData);

        DB::beginTransaction();
        try {
            $radiologyOrder = Radiology::create([
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'medical_record_id' => $radiologyData['medical_record_id'] ?? null,
                'study_id' => $radiologyData['study_id'],
                'priority' => $radiologyData['priority'],
                'clinical_indication' => $radiologyData['clinical_indication'],
                'contrast_required' => $radiologyData['contrast_required'] ?? false,
                'preparation_instructions' => $radiologyData['preparation_instructions'] ?? null,
                'scheduled_date' => $radiologyData['scheduled_date'] ?? now()->addDay(),
                'order_date' => now(),
                'status' => 'ordered'
            ]);

            DB::commit();

            return [
                'success' => true,
                'radiology_order' => $radiologyOrder,
                'previous_studies' => $previousStudies,
                'protocol_suggestions' => $protocolSuggestions
            ];

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * حجز استشارة طبية
     */
    public function bookConsultation($patientId, $requestingDoctorId, $consultationData)
    {
        DB::beginTransaction();
        try {
            // البحث عن الطبيب المختص المتاح
            $availableDoctor = $this->findAvailableSpecialist(
                $consultationData['specialty'],
                $consultationData['preferred_date'] ?? now()->addDay()
            );

            if (!$availableDoctor) {
                return [
                    'success' => false,
                    'error' => 'لا يوجد طبيب مختص متاح في التاريخ المحدد'
                ];
            }

            // إنشاء موعد الاستشارة
            $consultation = Appointment::create([
                'patient_id' => $patientId,
                'doctor_id' => $availableDoctor->id,
                'referring_doctor_id' => $requestingDoctorId,
                'appointment_date' => $consultationData['preferred_date'] ?? now()->addDay(),
                'appointment_time' => $availableDoctor->next_available_slot,
                'appointment_type_id' => $this->getConsultationTypeId(),
                'reason' => $consultationData['reason'],
                'notes' => $consultationData['clinical_summary'] ?? null,
                'status' => 'scheduled',
                'is_consultation' => true
            ]);

            DB::commit();

            return [
                'success' => true,
                'consultation' => $consultation,
                'consulting_doctor' => $availableDoctor
            ];

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * تحويل مريض للقسم الداخلي
     */
    public function admitPatient($patientId, $doctorId, $admissionData)
    {
        $patient = Patient::findOrFail($patientId);
        
        // البحث عن سرير متاح
        $availableBed = $this->findAvailableBed($admissionData['room_type'], $admissionData['department']);
        
        if (!$availableBed) {
            return [
                'success' => false,
                'error' => 'لا توجد أسرة متاحة في القسم المحدد'
            ];
        }

        DB::beginTransaction();
        try {
            // تحديث حالة المريض
            $patient->update([
                'status' => 'inpatient',
                'admission_date' => now(),
                'attending_doctor_id' => $doctorId
            ]);

            // حجز السرير
            $availableBed->update([
                'status' => 'occupied',
                'patient_id' => $patientId,
                'admission_date' => now()
            ]);

            // إنشاء سجل الدخول
            $admission = DB::table('patient_admissions')->insert([
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'bed_id' => $availableBed->id,
                'admission_date' => now(),
                'admission_reason' => $admissionData['reason'],
                'medical_condition' => $admissionData['condition'],
                'treatment_plan' => $admissionData['treatment_plan'],
                'estimated_discharge_date' => $admissionData['estimated_discharge'] ?? null,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return [
                'success' => true,
                'bed' => $availableBed,
                'admission_id' => $admission
            ];

        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * تحليل الأعراض والتشخيص
     */
    private function analyzeSymptoms($symptoms, $diagnosis)
    {
        // خوارزمية بسيطة لتحليل الأعراض
        $commonSymptoms = ['fever', 'headache', 'cough', 'fatigue', 'nausea'];
        $criticalSymptoms = ['chest_pain', 'difficulty_breathing', 'severe_headache', 'high_fever'];
        
        $analysis = [
            'severity_score' => 0,
            'risk_level' => 'low',
            'recommendations' => []
        ];

        foreach ($symptoms as $symptom) {
            if (in_array($symptom, $criticalSymptoms)) {
                $analysis['severity_score'] += 3;
            } elseif (in_array($symptom, $commonSymptoms)) {
                $analysis['severity_score'] += 1;
            }
        }

        if ($analysis['severity_score'] >= 6) {
            $analysis['risk_level'] = 'high';
            $analysis['recommendations'][] = 'يحتاج متابعة عاجلة';
        } elseif ($analysis['severity_score'] >= 3) {
            $analysis['risk_level'] = 'medium';
            $analysis['recommendations'][] = 'يحتاج متابعة خلال 24 ساعة';
        }

        return $analysis;
    }

    /**
     * توليد التوصيات الطبية
     */
    private function generateRecommendations($patient, $medicalRecord, $analysis)
    {
        $recommendations = [];

        // توصيات بناء على مستوى الخطر
        if ($analysis['risk_level'] === 'high') {
            $recommendations[] = [
                'type' => 'follow_up',
                'priority' => 'urgent',
                'description' => 'متابعة عاجلة خلال 24 ساعة'
            ];
        }

        // توصيات بناء على العمر
        if ($patient->age > 65) {
            $recommendations[] = [
                'type' => 'monitoring',
                'priority' => 'medium',
                'description' => 'مراقبة إضافية للمريض كبير السن'
            ];
        }

        // توصيات بناء على التشخيص
        if (str_contains(strtolower($medicalRecord->diagnosis), 'diabetes')) {
            $recommendations[] = [
                'type' => 'lab_test',
                'priority' => 'medium',
                'description' => 'فحص مستوى السكر في الدم'
            ];
        }

        return $recommendations;
    }

    /**
     * التحقق من الحساسية
     */
    private function checkAllergies($patient, $medications)
    {
        $allergies = $patient->allergies ?? [];
        $conflicts = [];
        $warnings = [];

        foreach ($medications as $medication) {
            // فحص بسيط للحساسية
            foreach ($allergies as $allergy) {
                if (str_contains(strtolower($medication['name'] ?? ''), strtolower($allergy))) {
                    $conflicts[] = [
                        'medication' => $medication,
                        'allergy' => $allergy
                    ];
                }
            }
        }

        return [
            'safe' => empty($conflicts),
            'conflicts' => $conflicts,
            'warnings' => $warnings
        ];
    }

    /**
     * التحقق من التفاعلات الدوائية
     */
    private function checkDrugInteractions($patient, $newMedications)
    {
        // الحصول على الأدوية الحالية
        $currentMedications = Prescription::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->with('prescriptionItems.medication')
            ->get()
            ->pluck('prescriptionItems')
            ->flatten();

        $interactions = [];
        $warnings = [];

        // فحص بسيط للتفاعلات
        foreach ($newMedications as $newMed) {
            foreach ($currentMedications as $currentMed) {
                // هنا يمكن إضافة قاعدة بيانات التفاعلات الدوائية
                if ($this->hasKnownInteraction($newMed, $currentMed)) {
                    $interactions[] = [
                        'new_medication' => $newMed,
                        'current_medication' => $currentMed,
                        'severity' => 'moderate'
                    ];
                }
            }
        }

        return [
            'safe' => empty($interactions),
            'interactions' => $interactions,
            'warnings' => $warnings
        ];
    }

    /**
     * اقتراح فحوصات إضافية
     */
    private function suggestAdditionalTests($patient, $testData)
    {
        $suggestions = [];

        // اقتراحات بناء على العمر
        if ($patient->age > 50) {
            $suggestions[] = [
                'test' => 'Lipid Profile',
                'reason' => 'فحص دوري للمرضى فوق 50 سنة'
            ];
        }

        return $suggestions;
    }

    /**
     * التحقق من الفحوصات المكررة
     */
    private function checkDuplicateTests($patient, $tests)
    {
        $recentTests = Lab::where('patient_id', $patient->id)
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        $duplicates = [];

        foreach ($tests as $testId) {
            foreach ($recentTests as $recentTest) {
                $recentTestIds = json_decode($recentTest->test_ids, true);
                if (in_array($testId, $recentTestIds)) {
                    $duplicates[] = [
                        'test_id' => $testId,
                        'previous_date' => $recentTest->created_at
                    ];
                }
            }
        }

        return $duplicates;
    }

    // وظائف مساعدة أخرى...
    private function checkPreviousRadiology($patient, $studyId) { return []; }
    private function suggestRadiologyProtocols($patient, $data) { return []; }
    private function findAvailableSpecialist($specialty, $date) { return null; }
    private function getConsultationTypeId() { return 1; }
    private function findAvailableBed($roomType, $department) { return null; }
    private function hasKnownInteraction($med1, $med2) { return false; }
}