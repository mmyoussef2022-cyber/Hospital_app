<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecordAudit extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_record_id',
        'user_id',
        'action',
        'action_ar',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'notes',
        'notes_ar',
        'performed_at'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * Get the medical record that owns the audit
     */
    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * Get the user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get localized action description
     */
    public function getActionLocalizedAttribute()
    {
        return app()->getLocale() === 'ar' && $this->action_ar 
            ? $this->action_ar 
            : $this->action;
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
     * Get formatted changes
     */
    public function getFormattedChangesAttribute()
    {
        $changes = [];
        
        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $field => $newValue) {
                $oldValue = $this->old_values[$field] ?? null;
                if ($oldValue !== $newValue) {
                    $changes[$field] = [
                        'old' => $oldValue,
                        'new' => $newValue
                    ];
                }
            }
        }
        
        return $changes;
    }

    /**
     * Scope for specific action
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope for recent audits
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('performed_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Create audit log entry
     */
    public static function logAction($medicalRecordId, $action, $oldValues = null, $newValues = null, $notes = null)
    {
        // Skip audit logging if no authenticated user (e.g., during seeding)
        if (!auth()->check()) {
            return null;
        }

        return static::create([
            'medical_record_id' => $medicalRecordId,
            'user_id' => auth()->id(),
            'action' => $action,
            'action_ar' => static::getArabicAction($action),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'notes' => $notes,
            'notes_ar' => static::getArabicNotes($notes),
            'performed_at' => now()
        ]);
    }

    /**
     * Get Arabic translation for action
     */
    private static function getArabicAction($action)
    {
        $actions = [
            'created' => 'تم الإنشاء',
            'updated' => 'تم التحديث',
            'viewed' => 'تم العرض',
            'deleted' => 'تم الحذف',
            'restored' => 'تم الاستعادة',
            'prescription_added' => 'تم إضافة وصفة طبية',
            'prescription_updated' => 'تم تحديث وصفة طبية',
            'attachment_added' => 'تم إضافة مرفق',
            'attachment_removed' => 'تم حذف مرفق',
            'access_granted' => 'تم منح الوصول',
            'access_denied' => 'تم رفض الوصول'
        ];

        return $actions[$action] ?? $action;
    }

    /**
     * Get Arabic translation for notes (if needed)
     */
    private static function getArabicNotes($notes)
    {
        // This could be enhanced with automatic translation or predefined translations
        return $notes;
    }
}
