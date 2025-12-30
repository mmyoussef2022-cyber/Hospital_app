<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_number',
        'national_id',
        'name',
        'name_en',
        'gender',
        'date_of_birth',
        'phone',
        'mobile',
        'email',
        'address',
        'city',
        'country',
        'nationality',
        'marital_status',
        'occupation',
        'blood_type',
        'emergency_contact',
        'insurance_info',
        'allergies',
        'chronic_conditions',
        'medical_notes',
        'family_code',
        'family_head_id',
        'family_relation',
        'barcode',
        'profile_photo',
        'is_active',
        'patient_type',
        'first_visit_date',
        'last_visit_date',
        'outstanding_balance',
        'preferences'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'emergency_contact' => 'array',
        'insurance_info' => 'array',
        'allergies' => 'array',
        'chronic_conditions' => 'array',
        'first_visit_date' => 'datetime',
        'last_visit_date' => 'datetime',
        'preferences' => 'array',
        'is_active' => 'boolean',
        'outstanding_balance' => 'decimal:2'
    ];

    public function familyHead()
    {
        return $this->belongsTo(Patient::class, 'family_head_id');
    }

    public function familyMembers()
    {
        return $this->hasMany(Patient::class, 'family_head_id');
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    /**
     * Get the decrypted national ID
     */
    public function getNationalIdAttribute($value)
    {
        if (!$value) {
            return null;
        }
        
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            // If decryption fails, return the value as is (probably not encrypted)
            return $value;
        }
    }

    /**
     * Set the encrypted national ID
     */
    public function setNationalIdAttribute($value)
    {
        if (!$value) {
            $this->attributes['national_id'] = null;
            return;
        }
        
        try {
            // Try to decrypt first to see if it's already encrypted
            decrypt($value);
            // If successful, it's already encrypted
            $this->attributes['national_id'] = $value;
        } catch (\Exception $e) {
            // If decryption fails, encrypt it
            $this->attributes['national_id'] = encrypt($value);
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            if (empty($patient->patient_number)) {
                $patient->patient_number = 'P' . str_pad(Patient::count() + 1, 6, '0', STR_PAD_LEFT);
            }
            
            if (empty($patient->barcode)) {
                $patient->barcode = 'BC' . $patient->patient_number . rand(1000, 9999);
            }
        });
    }

    // Relationships
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function upcomingAppointments()
    {
        return $this->appointments()->upcoming()->orderBy('appointment_date')->orderBy('appointment_time');
    }

    public function todayAppointments()
    {
        return $this->appointments()->today()->orderBy('appointment_time');
    }

    /**
     * Get localized gender
     */
    public function getGenderLocalizedAttribute()
    {
        $genders = [
            'male' => __('app.male'),
            'female' => __('app.female')
        ];
        
        return $genders[$this->gender] ?? $this->gender;
    }

    /**
     * Medical records relationship
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * Prescriptions relationship
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Patient insurance relationships
     */
    public function patientInsurances()
    {
        return $this->hasMany(PatientInsurance::class);
    }

    /**
     * Primary insurance policy relationship
     */
    public function insurancePolicy()
    {
        return $this->hasOneThrough(
            InsurancePolicy::class,
            PatientInsurance::class,
            'patient_id', // Foreign key on PatientInsurance table
            'id', // Foreign key on InsurancePolicy table
            'id', // Local key on Patient table
            'insurance_policy_id' // Local key on PatientInsurance table
        )->select('insurance_policies.id as policy_id', 'insurance_policies.insurance_company_id', 'insurance_policies.policy_number', 'insurance_policies.coverage_percentage')
         ->where('patient_insurance.is_primary', true)
         ->where('patient_insurance.status', 'active');
    }

    /**
     * Primary insurance company relationship
     */
    public function insuranceCompany()
    {
        return $this->hasOneThrough(
            InsuranceCompany::class,
            PatientInsurance::class,
            'patient_id', // Foreign key on PatientInsurance table
            'id', // Foreign key on InsuranceCompany table
            'id', // Local key on Patient table
            'insurance_company_id' // Local key on PatientInsurance table
        )->select('insurance_companies.id as company_id', 'insurance_companies.name_ar', 'insurance_companies.name_en')
         ->where('patient_insurance.is_primary', true)
         ->where('patient_insurance.status', 'active');
    }

    /**
     * All insurance claims for this patient
     */
    public function insuranceClaims()
    {
        return $this->hasMany(InsuranceClaim::class);
    }

    // Advanced Patient Profile Methods

    /**
     * Get patient classification (cash/insurance)
     */
    public function getPatientClassificationAttribute(): string
    {
        $activeInsurance = $this->patientInsurances()->active()->primary()->first();
        return $activeInsurance ? 'insurance' : 'cash';
    }

    /**
     * Get patient classification display in Arabic
     */
    public function getPatientClassificationDisplayAttribute(): string
    {
        return $this->patient_classification === 'insurance' ? 'تأمين' : 'نقدي';
    }

    /**
     * Get patient classification color for UI
     */
    public function getPatientClassificationColorAttribute(): string
    {
        return $this->patient_classification === 'insurance' ? 'success' : 'primary';
    }

    /**
     * Get primary insurance details
     */
    public function getPrimaryInsuranceDetailsAttribute(): ?array
    {
        $primaryInsurance = $this->patientInsurances()->active()->primary()->with(['insuranceCompany', 'insurancePolicy'])->first();
        
        if (!$primaryInsurance) {
            return null;
        }

        return [
            'company_name' => $primaryInsurance->insuranceCompany->name ?? 'غير محدد',
            'policy_number' => $primaryInsurance->insurancePolicy->policy_number ?? 'غير محدد',
            'coverage_percentage' => $primaryInsurance->insurancePolicy->coverage_percentage ?? 0,
            'member_id' => $primaryInsurance->member_id ?? 'غير محدد',
            'card_number' => $primaryInsurance->card_number ?? 'غير محدد',
            'status' => $primaryInsurance->status_display,
            'expiry_date' => $primaryInsurance->coverage_end_date?->format('Y-m-d'),
            'annual_limit_used' => $primaryInsurance->annual_limit_used ?? 0,
            'annual_limit_remaining' => $primaryInsurance->annual_limit_remaining ?? 0,
            'is_expiring_soon' => $primaryInsurance->is_expiring_soon
        ];
    }

    /**
     * Get all active insurances
     */
    public function getActiveInsurancesAttribute()
    {
        return $this->patientInsurances()->active()->with(['insuranceCompany', 'insurancePolicy'])->get();
    }

    /**
     * Check if patient has active insurance
     */
    public function hasActiveInsurance(): bool
    {
        return $this->patientInsurances()->active()->exists();
    }

    /**
     * Get insurance coverage for a specific amount
     */
    public function calculateInsuranceCoverage(float $amount, string $serviceType = null): array
    {
        $primaryInsurance = $this->patientInsurances()->active()->primary()->first();
        
        if (!$primaryInsurance) {
            return [
                'covered_amount' => 0,
                'patient_responsibility' => $amount,
                'coverage_percentage' => 0,
                'reason' => 'No active insurance coverage'
            ];
        }

        return $primaryInsurance->calculateCoverage($amount, $serviceType);
    }

    /**
     * Get medical alerts (allergies, chronic conditions, drug interactions)
     */
    public function getMedicalAlertsAttribute(): array
    {
        $alerts = [];

        // Allergy alerts
        if ($this->allergies && count($this->allergies) > 0) {
            foreach ($this->allergies as $allergy) {
                $alerts[] = [
                    'type' => 'allergy',
                    'severity' => 'high',
                    'message' => "تحذير: المريض لديه حساسية من {$allergy}",
                    'icon' => 'bi-exclamation-triangle-fill',
                    'color' => 'danger'
                ];
            }
        }

        // Chronic condition alerts
        if ($this->chronic_conditions && count($this->chronic_conditions) > 0) {
            foreach ($this->chronic_conditions as $condition) {
                $alerts[] = [
                    'type' => 'chronic_condition',
                    'severity' => 'medium',
                    'message' => "تنبيه: المريض يعاني من {$condition}",
                    'icon' => 'bi-heart-pulse-fill',
                    'color' => 'warning'
                ];
            }
        }

        // Insurance expiry alert
        $primaryInsurance = $this->primary_insurance_details;
        if ($primaryInsurance && $primaryInsurance['is_expiring_soon']) {
            $alerts[] = [
                'type' => 'insurance_expiry',
                'severity' => 'medium',
                'message' => "تنبيه: التأمين ينتهي قريباً في {$primaryInsurance['expiry_date']}",
                'icon' => 'bi-shield-exclamation',
                'color' => 'warning'
            ];
        }

        return $alerts;
    }

    /**
     * Get complete medical history summary
     */
    public function getMedicalHistorySummaryAttribute(): array
    {
        return [
            'total_visits' => $this->appointments()->count(),
            'last_visit' => $this->last_visit_date?->format('Y-m-d'),
            'total_prescriptions' => $this->prescriptions()->count(),
            'total_medical_records' => $this->medicalRecords()->count(),
            'allergies_count' => $this->allergies ? count($this->allergies) : 0,
            'chronic_conditions_count' => $this->chronic_conditions ? count($this->chronic_conditions) : 0,
            'outstanding_balance' => $this->outstanding_balance ?? 0
        ];
    }

    /**
     * Get billing and payment history
     */
    public function getBillingHistoryAttribute(): array
    {
        // This would need to be implemented based on your billing system
        // For now, returning basic structure
        return [
            'total_invoices' => 0, // Count from invoices table
            'total_paid' => 0, // Sum of paid amounts
            'total_pending' => $this->outstanding_balance ?? 0,
            'last_payment_date' => null, // Last payment date
            'payment_method_preference' => 'cash' // Based on history
        ];
    }

    /**
     * Get family insurance status
     */
    public function getFamilyInsuranceStatusAttribute(): ?array
    {
        if (!$this->family_code) {
            return null;
        }

        $familyMembers = Patient::where('family_code', $this->family_code)
                               ->where('is_active', true)
                               ->with(['patientInsurances' => function($q) {
                                   $q->active()->primary();
                               }])
                               ->get();

        $insuredMembers = $familyMembers->filter(function($member) {
            return $member->hasActiveInsurance();
        });

        return [
            'total_members' => $familyMembers->count(),
            'insured_members' => $insuredMembers->count(),
            'uninsured_members' => $familyMembers->count() - $insuredMembers->count(),
            'family_policy_exists' => $insuredMembers->first()?->patientInsurances->first()?->isFamilyPolicy() ?? false
        ];
    }
}
