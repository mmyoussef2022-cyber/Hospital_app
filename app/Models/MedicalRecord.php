<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'visit_date',
        'chief_complaint',
        'chief_complaint_ar',
        'diagnosis',
        'diagnosis_ar',
        'treatment',
        'treatment_ar',
        'medications',
        'vital_signs',
        'notes',
        'notes_ar',
        'attachments',
        'follow_up_date',
        'is_emergency',
        'visit_type',
        'status'
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'follow_up_date' => 'date',
        'diagnosis' => 'array',
        'diagnosis_ar' => 'array',
        'medications' => 'array',
        'vital_signs' => 'array',
        'attachments' => 'array',
        'is_emergency' => 'boolean',
    ];

    /**
     * Get the patient that owns the medical record
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the doctor that created the medical record
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    /**
     * Get the prescriptions for this medical record
     */
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }

    /**
     * Get follow-up appointments for this medical record
     */
    public function followUpAppointments()
    {
        return $this->hasMany(Appointment::class, 'parent_appointment_id', 'appointment_id');
    }

    /**
     * Get the next follow-up appointment
     */
    public function nextFollowUpAppointment()
    {
        return $this->hasOne(Appointment::class, 'parent_appointment_id', 'appointment_id')
            ->where('type', 'follow_up')
            ->where('appointment_date', '>', now())
            ->orderBy('appointment_date');
    }

    /**
     * Check if this medical record has completed follow-up
     */
    public function hasCompletedFollowUp()
    {
        return $this->followUpAppointments()
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Get the original appointment for this medical record
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Get the attachments for this medical record
     */
    public function attachments()
    {
        return $this->hasMany(MedicalRecordAttachment::class);
    }

    /**
     * Get the audit logs for this medical record
     */
    public function audits()
    {
        return $this->hasMany(MedicalRecordAudit::class);
    }

    /**
     * Get localized chief complaint
     */
    public function getChiefComplaintLocalizedAttribute()
    {
        return app()->getLocale() === 'ar' && $this->chief_complaint_ar 
            ? $this->chief_complaint_ar 
            : $this->chief_complaint;
    }

    /**
     * Get localized diagnosis
     */
    public function getDiagnosisLocalizedAttribute()
    {
        return app()->getLocale() === 'ar' && $this->diagnosis_ar 
            ? $this->diagnosis_ar 
            : $this->diagnosis;
    }

    /**
     * Get localized treatment
     */
    public function getTreatmentLocalizedAttribute()
    {
        return app()->getLocale() === 'ar' && $this->treatment_ar 
            ? $this->treatment_ar 
            : $this->treatment;
    }

    /**
     * Get localized notes
     */
    public function getNotesLocalizedAttribute()
    {
        return app()->getLocale() === 'ar' && $this->notes_ar 
            ? $this->notes_ar 
            : $this->notes;
    }

    /**
     * Get localized visit type
     */
    public function getVisitTypeLocalizedAttribute()
    {
        $types = [
            'consultation' => [
                'en' => 'Consultation',
                'ar' => 'استشارة'
            ],
            'follow_up' => [
                'en' => 'Follow-up',
                'ar' => 'متابعة'
            ],
            'emergency' => [
                'en' => 'Emergency',
                'ar' => 'طوارئ'
            ],
            'routine_checkup' => [
                'en' => 'Routine Checkup',
                'ar' => 'فحص روتيني'
            ],
            'procedure' => [
                'en' => 'Procedure',
                'ar' => 'إجراء'
            ]
        ];

        $locale = app()->getLocale();
        return $types[$this->visit_type][$locale] ?? $this->visit_type;
    }

    /**
     * Scope to get records for a specific patient
     */
    public function scopeForPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope to get records by a specific doctor
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope to get recent records
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('visit_date', '>=', now()->subDays($days));
    }

    /**
     * Scope for emergency records
     */
    public function scopeEmergency($query)
    {
        return $query->where('is_emergency', true);
    }

    /**
     * Scope for specific visit type
     */
    public function scopeVisitType($query, $type)
    {
        return $query->where('visit_type', $type);
    }

    /**
     * Log access to this medical record
     */
    public function logAccess($action = 'viewed', $notes = null)
    {
        MedicalRecordAudit::logAction($this->id, $action, null, null, $notes);
    }

    /**
     * Log changes to this medical record
     */
    public function logChanges($action, $oldValues, $newValues, $notes = null)
    {
        MedicalRecordAudit::logAction($this->id, $action, $oldValues, $newValues, $notes);
    }

    /**
     * Check if user has access to this record
     */
    public function hasAccess($user = null, $action = 'view')
    {
        $user = $user ?? auth()->user();
        
        if (!$user) {
            return false;
        }

        // Doctor who created the record has access to all actions
        if ($this->doctor_id === $user->id) {
            return true;
        }

        // Patient has access to their own records (view only)
        if ($this->patient->user_id === $user->id && $action === 'view') {
            return true;
        }

        // Check permissions based on action
        switch ($action) {
            case 'view':
                return $user->can('view_medical_records') || 
                       $user->can('view_all_medical_records') ||
                       $user->can('medical-records.view');
                       
            case 'edit':
            case 'update':
                return $user->can('edit_medical_records') || 
                       $user->can('edit_all_medical_records') ||
                       $user->can('medical-records.edit') ||
                       $user->can('medical-records.update');
                       
            case 'delete':
                return $user->can('delete_medical_records') || 
                       $user->can('delete_all_medical_records') ||
                       $user->can('medical-records.delete');
                       
            case 'create':
                return $user->can('create_medical_records') ||
                       $user->can('medical-records.create');
                       
            default:
                return $user->can('view_medical_records') || 
                       $user->can('view_all_medical_records');
        }
    }

    /**
     * Check if user can edit this record
     */
    public function canEdit($user = null)
    {
        return $this->hasAccess($user, 'edit');
    }

    /**
     * Check if user can delete this record
     */
    public function canDelete($user = null)
    {
        return $this->hasAccess($user, 'delete');
    }

    /**
     * Boot method to add model events
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($record) {
            $record->logAccess('created', 'Medical record created');
        });

        static::updated(function ($record) {
            $record->logChanges('updated', $record->getOriginal(), $record->getAttributes());
        });

        static::deleted(function ($record) {
            $record->logAccess('deleted', 'Medical record deleted');
        });
    }
}