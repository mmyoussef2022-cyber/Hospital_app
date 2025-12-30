<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurgicalTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'surgery_id',
        'user_id',
        'role',
        'role_description',
        'is_primary',
        'is_required',
        'is_confirmed',
        'assigned_at',
        'confirmed_at',
        'notes',
        'fee',
        'commission_percentage'
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'is_required' => 'boolean',
        'is_confirmed' => 'boolean',
        'assigned_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'fee' => 'decimal:2',
        'commission_percentage' => 'decimal:2'
    ];

    // Relationships
    public function surgery(): BelongsTo
    {
        return $this->belongsTo(Surgery::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('is_confirmed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_confirmed', false);
    }

    // Accessors
    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'primary_surgeon' => 'الجراح الرئيسي',
            'assistant_surgeon' => 'الجراح المساعد',
            'anesthesiologist' => 'طبيب التخدير',
            'scrub_nurse' => 'ممرض العمليات',
            'circulating_nurse' => 'الممرض المتنقل',
            'surgical_technician' => 'فني العمليات',
            'resident' => 'طبيب مقيم',
            'medical_student' => 'طالب طب',
            'perfusionist' => 'أخصائي القلب الصناعي',
            'other' => 'أخرى',
            default => $this->role
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        if ($this->is_confirmed) {
            return 'مؤكد';
        }
        return 'في الانتظار';
    }

    public function getStatusColorAttribute(): string
    {
        if ($this->is_confirmed) {
            return 'success';
        }
        return 'warning';
    }

    public function getRoleColorAttribute(): string
    {
        return match($this->role) {
            'primary_surgeon' => 'primary',
            'assistant_surgeon' => 'info',
            'anesthesiologist' => 'warning',
            'scrub_nurse' => 'success',
            'circulating_nurse' => 'success',
            'surgical_technician' => 'secondary',
            'resident' => 'light',
            'medical_student' => 'light',
            'perfusionist' => 'danger',
            'other' => 'dark',
            default => 'secondary'
        };
    }

    // Business Logic Methods
    public function confirm(): bool
    {
        if ($this->is_confirmed) {
            return false;
        }

        $this->update([
            'is_confirmed' => true,
            'confirmed_at' => now()
        ]);

        return true;
    }

    public function unconfirm(): bool
    {
        if (!$this->is_confirmed) {
            return false;
        }

        $this->update([
            'is_confirmed' => false,
            'confirmed_at' => null
        ]);

        return true;
    }

    public function canBeRemoved(): bool
    {
        // Cannot remove if surgery is in progress or completed
        return !in_array($this->surgery->status, ['in_progress', 'completed']);
    }

    public function canBeConfirmed(): bool
    {
        // Can confirm if not already confirmed and surgery is not completed/cancelled
        return !$this->is_confirmed && 
               !in_array($this->surgery->status, ['completed', 'cancelled']);
    }

    public function calculateCommission(): float
    {
        if ($this->commission_percentage > 0 && $this->surgery->actual_cost > 0) {
            return ($this->surgery->actual_cost * $this->commission_percentage) / 100;
        }
        
        return $this->fee;
    }

    // Static Methods
    public static function getAvailableRoles(): array
    {
        return [
            'primary_surgeon' => 'الجراح الرئيسي',
            'assistant_surgeon' => 'الجراح المساعد',
            'anesthesiologist' => 'طبيب التخدير',
            'scrub_nurse' => 'ممرض العمليات',
            'circulating_nurse' => 'الممرض المتنقل',
            'surgical_technician' => 'فني العمليات',
            'resident' => 'طبيب مقيم',
            'medical_student' => 'طالب طب',
            'perfusionist' => 'أخصائي القلب الصناعي',
            'other' => 'أخرى'
        ];
    }

    public static function getRequiredRoles(): array
    {
        return [
            'primary_surgeon',
            'anesthesiologist',
            'scrub_nurse'
        ];
    }

    public static function getOptionalRoles(): array
    {
        return [
            'assistant_surgeon',
            'circulating_nurse',
            'surgical_technician',
            'resident',
            'medical_student',
            'perfusionist',
            'other'
        ];
    }

    public static function getUsersForRole(string $role): \Illuminate\Database\Eloquent\Collection
    {
        $query = User::where('is_active', true);

        switch ($role) {
            case 'primary_surgeon':
            case 'assistant_surgeon':
                $query->whereHas('doctor', function($q) {
                    $q->where('specialization', 'like', '%surgery%')
                      ->orWhere('specialization', 'like', '%جراحة%');
                });
                break;
                
            case 'anesthesiologist':
                $query->whereHas('doctor', function($q) {
                    $q->where('specialization', 'like', '%anesthesia%')
                      ->orWhere('specialization', 'like', '%تخدير%');
                });
                break;
                
            case 'scrub_nurse':
            case 'circulating_nurse':
                $query->whereHas('roles', function($q) {
                    $q->where('name', 'nurse');
                });
                break;
                
            case 'surgical_technician':
                $query->whereHas('roles', function($q) {
                    $q->where('name', 'technician');
                });
                break;
                
            case 'resident':
                $query->whereHas('roles', function($q) {
                    $q->where('name', 'resident');
                });
                break;
                
            case 'medical_student':
                $query->whereHas('roles', function($q) {
                    $q->where('name', 'student');
                });
                break;
        }

        return $query->get();
    }
}
