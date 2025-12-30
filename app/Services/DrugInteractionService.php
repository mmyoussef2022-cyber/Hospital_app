<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Medication;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DrugInteractionService
{
    /**
     * قاعدة بيانات التفاعلات الدوائية المعروفة
     */
    private $knownInteractions = [
        // تفاعلات حرجة
        'critical' => [
            'warfarin_aspirin' => 'زيادة خطر النزيف',
            'digoxin_furosemide' => 'زيادة سمية الديجوكسين',
            'lithium_ace_inhibitors' => 'زيادة مستوى الليثيوم في الدم',
            'metformin_contrast_agents' => 'خطر الحماض اللاكتيكي',
        ],
        // تفاعلات متوسطة
        'moderate' => [
            'simvastatin_amlodipine' => 'زيادة خطر اعتلال العضلات',
            'omeprazole_clopidogrel' => 'تقليل فعالية الكلوبيدوجريل',
            'ciprofloxacin_theophylline' => 'زيادة مستوى الثيوفيلين',
        ],
        // تفاعلات بسيطة
        'minor' => [
            'calcium_iron' => 'تقليل امتصاص الحديد',
            'antacids_antibiotics' => 'تقليل امتصاص المضادات الحيوية',
        ]
    ];

    /**
     * مجموعات الأدوية التي لا يجب دمجها
     */
    private $contraindications = [
        'maoi_ssri' => [
            'drugs' => ['phenelzine', 'tranylcypromine', 'fluoxetine', 'sertraline'],
            'warning' => 'خطر متلازمة السيروتونين - قد تكون مميتة'
        ],
        'ace_arb' => [
            'drugs' => ['lisinopril', 'enalapril', 'losartan', 'valsartan'],
            'warning' => 'تجنب الدمج - زيادة خطر انخفاض ضغط الدم والفشل الكلوي'
        ]
    ];

    /**
     * فحص التفاعلات الدوائية الشامل
     */
    public function checkInteractions($newMedications, $currentMedications = [])
    {
        $interactions = [
            'critical' => [],
            'moderate' => [],
            'minor' => [],
            'contraindications' => []
        ];

        // دمج الأدوية الجديدة مع الحالية
        $allMedications = array_merge($newMedications, $currentMedications->toArray());

        // فحص التفاعلات بين جميع الأدوية
        for ($i = 0; $i < count($allMedications); $i++) {
            for ($j = $i + 1; $j < count($allMedications); $j++) {
                $interaction = $this->checkPairInteraction(
                    $allMedications[$i],
                    $allMedications[$j]
                );

                if ($interaction) {
                    $interactions[$interaction['severity']][] = $interaction;
                }
            }
        }

        // فحص موانع الاستعمال
        $contraindications = $this->checkContraindications($allMedications);
        $interactions['contraindications'] = $contraindications;

        // حساب درجة الخطر الإجمالية
        $riskScore = $this->calculateRiskScore($interactions);

        return [
            'interactions' => $interactions,
            'risk_score' => $riskScore,
            'safe_to_prescribe' => $riskScore < 7,
            'recommendations' => $this->generateRecommendations($interactions, $riskScore)
        ];
    }

    /**
     * فحص التفاعل بين دوائين
     */
    private function checkPairInteraction($med1, $med2)
    {
        $drug1 = $this->normalizeDrugName($med1['name'] ?? $med1['medication_name'] ?? '');
        $drug2 = $this->normalizeDrugName($med2['name'] ?? $med2['medication_name'] ?? '');

        // فحص التفاعلات الحرجة
        foreach ($this->knownInteractions['critical'] as $pair => $description) {
            if ($this->matchesPair($drug1, $drug2, $pair)) {
                return [
                    'severity' => 'critical',
                    'drug1' => $med1,
                    'drug2' => $med2,
                    'description' => $description,
                    'recommendation' => 'تجنب الدمج - استشر الطبيب فوراً'
                ];
            }
        }

        // فحص التفاعلات المتوسطة
        foreach ($this->knownInteractions['moderate'] as $pair => $description) {
            if ($this->matchesPair($drug1, $drug2, $pair)) {
                return [
                    'severity' => 'moderate',
                    'drug1' => $med1,
                    'drug2' => $med2,
                    'description' => $description,
                    'recommendation' => 'مراقبة دقيقة مطلوبة'
                ];
            }
        }

        // فحص التفاعلات البسيطة
        foreach ($this->knownInteractions['minor'] as $pair => $description) {
            if ($this->matchesPair($drug1, $drug2, $pair)) {
                return [
                    'severity' => 'minor',
                    'drug1' => $med1,
                    'drug2' => $med2,
                    'description' => $description,
                    'recommendation' => 'فصل أوقات الجرعات'
                ];
            }
        }

        return null;
    }

    /**
     * فحص موانع الاستعمال
     */
    private function checkContraindications($medications)
    {
        $contraindications = [];
        $drugNames = array_map(function($med) {
            return $this->normalizeDrugName($med['name'] ?? $med['medication_name'] ?? '');
        }, $medications);

        foreach ($this->contraindications as $groupName => $group) {
            $matchingDrugs = array_intersect($drugNames, $group['drugs']);
            
            if (count($matchingDrugs) > 1) {
                $contraindications[] = [
                    'group' => $groupName,
                    'drugs' => $matchingDrugs,
                    'warning' => $group['warning'],
                    'severity' => 'critical'
                ];
            }
        }

        return $contraindications;
    }

    /**
     * فحص التفاعلات مع الحساسية
     */
    public function checkAllergies(Patient $patient, $medications)
    {
        $allergies = $patient->allergies ?? [];
        $allergyConflicts = [];

        foreach ($medications as $medication) {
            $drugName = $this->normalizeDrugName($medication['name'] ?? $medication['medication_name'] ?? '');
            
            foreach ($allergies as $allergy) {
                $allergyName = $this->normalizeDrugName($allergy);
                
                if ($this->isAllergicReaction($drugName, $allergyName)) {
                    $allergyConflicts[] = [
                        'medication' => $medication,
                        'allergy' => $allergy,
                        'severity' => $this->getAllergySeverity($allergy),
                        'recommendation' => 'تجنب تماماً - خطر رد فعل تحسسي'
                    ];
                }
            }
        }

        return [
            'safe' => empty($allergyConflicts),
            'conflicts' => $allergyConflicts,
            'total_conflicts' => count($allergyConflicts)
        ];
    }

    /**
     * فحص التفاعلات مع الحالات الطبية
     */
    public function checkMedicalConditions(Patient $patient, $medications)
    {
        $conditions = $patient->medical_conditions ?? [];
        $conditionConflicts = [];

        // قاعدة بيانات التفاعلات مع الحالات الطبية
        $conditionInteractions = [
            'kidney_disease' => ['metformin', 'nsaids', 'ace_inhibitors'],
            'liver_disease' => ['acetaminophen', 'statins', 'warfarin'],
            'heart_failure' => ['nsaids', 'calcium_channel_blockers'],
            'diabetes' => ['corticosteroids', 'thiazide_diuretics'],
            'hypertension' => ['nsaids', 'decongestants']
        ];

        foreach ($medications as $medication) {
            $drugName = $this->normalizeDrugName($medication['name'] ?? $medication['medication_name'] ?? '');
            
            foreach ($conditions as $condition) {
                $conditionKey = $this->normalizeConditionName($condition);
                
                if (isset($conditionInteractions[$conditionKey])) {
                    if (in_array($drugName, $conditionInteractions[$conditionKey])) {
                        $conditionConflicts[] = [
                            'medication' => $medication,
                            'condition' => $condition,
                            'severity' => $this->getConditionInteractionSeverity($conditionKey, $drugName),
                            'recommendation' => $this->getConditionRecommendation($conditionKey, $drugName)
                        ];
                    }
                }
            }
        }

        return [
            'safe' => empty($conditionConflicts),
            'conflicts' => $conditionConflicts,
            'total_conflicts' => count($conditionConflicts)
        ];
    }

    /**
     * فحص الجرعات والتكرار
     */
    public function checkDosageInteractions($medications)
    {
        $dosageWarnings = [];

        foreach ($medications as $medication) {
            $warnings = $this->validateDosage($medication);
            if (!empty($warnings)) {
                $dosageWarnings[] = [
                    'medication' => $medication,
                    'warnings' => $warnings
                ];
            }
        }

        return $dosageWarnings;
    }

    /**
     * توليد تقرير شامل للتفاعلات
     */
    public function generateInteractionReport(Patient $patient, $medications)
    {
        $currentMedications = $this->getCurrentMedications($patient);
        
        $report = [
            'patient_info' => [
                'name' => $patient->name,
                'age' => $patient->age,
                'weight' => $patient->weight,
                'allergies' => $patient->allergies ?? []
            ],
            'drug_interactions' => $this->checkInteractions($medications, $currentMedications),
            'allergy_check' => $this->checkAllergies($patient, $medications),
            'condition_check' => $this->checkMedicalConditions($patient, $medications),
            'dosage_check' => $this->checkDosageInteractions($medications),
            'generated_at' => now(),
            'recommendations' => []
        ];

        // توليد التوصيات الشاملة
        $report['recommendations'] = $this->generateComprehensiveRecommendations($report);

        return $report;
    }

    /**
     * حساب درجة الخطر الإجمالية
     */
    private function calculateRiskScore($interactions)
    {
        $score = 0;
        
        $score += count($interactions['critical']) * 5;
        $score += count($interactions['moderate']) * 3;
        $score += count($interactions['minor']) * 1;
        $score += count($interactions['contraindications']) * 10;

        return $score;
    }

    /**
     * توليد التوصيات
     */
    private function generateRecommendations($interactions, $riskScore)
    {
        $recommendations = [];

        if ($riskScore >= 10) {
            $recommendations[] = 'خطر عالي جداً - مراجعة فورية مع الطبيب المختص مطلوبة';
        } elseif ($riskScore >= 7) {
            $recommendations[] = 'خطر عالي - مراقبة دقيقة ومتابعة منتظمة';
        } elseif ($riskScore >= 3) {
            $recommendations[] = 'خطر متوسط - مراقبة الأعراض الجانبية';
        } else {
            $recommendations[] = 'خطر منخفض - متابعة روتينية';
        }

        if (!empty($interactions['critical'])) {
            $recommendations[] = 'تجنب الأدوية ذات التفاعلات الحرجة';
        }

        if (!empty($interactions['contraindications'])) {
            $recommendations[] = 'مراجعة موانع الاستعمال فوراً';
        }

        return $recommendations;
    }

    /**
     * وظائف مساعدة
     */
    private function normalizeDrugName($name)
    {
        return strtolower(trim($name));
    }

    private function normalizeConditionName($condition)
    {
        return strtolower(str_replace(' ', '_', trim($condition)));
    }

    private function matchesPair($drug1, $drug2, $pair)
    {
        $pairDrugs = explode('_', $pair);
        return (in_array($drug1, $pairDrugs) && in_array($drug2, $pairDrugs));
    }

    private function isAllergicReaction($drug, $allergy)
    {
        return str_contains($drug, $allergy) || str_contains($allergy, $drug);
    }

    private function getAllergySeverity($allergy)
    {
        // تحديد شدة الحساسية بناء على نوعها
        $severeAllergies = ['penicillin', 'sulfa', 'latex'];
        return in_array(strtolower($allergy), $severeAllergies) ? 'severe' : 'moderate';
    }

    private function getConditionInteractionSeverity($condition, $drug)
    {
        // تحديد شدة التفاعل مع الحالة الطبية
        $criticalCombinations = [
            'kidney_disease' => ['metformin'],
            'liver_disease' => ['acetaminophen']
        ];

        if (isset($criticalCombinations[$condition]) && 
            in_array($drug, $criticalCombinations[$condition])) {
            return 'critical';
        }

        return 'moderate';
    }

    private function getConditionRecommendation($condition, $drug)
    {
        $recommendations = [
            'kidney_disease' => 'تعديل الجرعة حسب وظائف الكلى',
            'liver_disease' => 'مراقبة وظائف الكبد',
            'heart_failure' => 'مراقبة أعراض تدهور القلب',
            'diabetes' => 'مراقبة مستوى السكر في الدم',
            'hypertension' => 'مراقبة ضغط الدم'
        ];

        return $recommendations[$condition] ?? 'مراقبة عامة مطلوبة';
    }

    private function validateDosage($medication)
    {
        $warnings = [];
        
        // فحص الجرعة
        if (isset($medication['dosage'])) {
            $dosage = $medication['dosage'];
            // إضافة منطق فحص الجرعة هنا
        }

        return $warnings;
    }

    private function getCurrentMedications(Patient $patient)
    {
        return Prescription::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->with('prescriptionItems.medication')
            ->get()
            ->pluck('prescriptionItems')
            ->flatten();
    }

    private function generateComprehensiveRecommendations($report)
    {
        $recommendations = [];

        // توصيات بناء على التفاعلات الدوائية
        if (!$report['drug_interactions']['safe_to_prescribe']) {
            $recommendations[] = 'مراجعة التفاعلات الدوائية قبل الوصف';
        }

        // توصيات بناء على الحساسية
        if (!$report['allergy_check']['safe']) {
            $recommendations[] = 'تجنب الأدوية المسببة للحساسية';
        }

        // توصيات بناء على الحالات الطبية
        if (!$report['condition_check']['safe']) {
            $recommendations[] = 'مراعاة الحالات الطبية عند الوصف';
        }

        return $recommendations;
    }
}