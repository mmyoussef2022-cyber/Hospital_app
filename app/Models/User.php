<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'national_id',
        'phone',
        'mobile',
        'gender',
        'date_of_birth',
        'address',
        'employee_id',
        'department_id',
        'job_title',
        'specialization',
        'license_number',
        'hire_date',
        'employment_status',
        'salary',
        'emergency_contact',
        'qualifications',
        'profile_photo',
        'is_active',
        'last_login_at',
        'preferred_language',
        'notification_preferences'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'date_of_birth' => 'date',
        'hire_date' => 'date',
        'last_login_at' => 'datetime',
        'emergency_contact' => 'array',
        'qualifications' => 'array',
        'notification_preferences' => 'array',
        'is_active' => 'boolean',
        'salary' => 'decimal:2'
    ];

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    // علاقات الأدوار والصلاحيات - استخدام Spatie Permissions
    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    // دوال مساعدة للأدوار
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isDepartmentManager(): bool
    {
        return $this->hasRole('department_manager');
    }

    public function isDoctor(): bool
    {
        return $this->hasRole('doctor');
    }

    public function isReceptionStaff(): bool
    {
        return $this->hasRole('reception_staff');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }

    public function getDashboardRoute(): string
    {
        if ($this->isSuperAdmin()) {
            return 'admin.dashboard';
        } elseif ($this->isDepartmentManager()) {
            return 'department.dashboard';
        } elseif ($this->isDoctor()) {
            return 'doctor.dashboard';
        } elseif ($this->isReceptionStaff()) {
            return 'reception.dashboard';
        } elseif ($this->isCashier()) {
            return 'cashier.dashboard';
        }
        
        return 'dashboard';
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

    /**
     * Get user's full name with title
     */
    public function getFullNameAttribute(): string
    {
        $title = '';
        if ($this->hasRole('Doctor')) {
            $title = 'د. ';
        } elseif ($this->hasRole('Nurse')) {
            $title = $this->gender === 'female' ? 'أ. ' : 'أ. ';
        }
        
        return $title . $this->name;
    }

    // Appointment relationships (for doctors)
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function todayAppointments()
    {
        return $this->appointments()->today()->orderBy('appointment_time');
    }

    public function upcomingAppointments()
    {
        return $this->appointments()->upcoming()->orderBy('appointment_date')->orderBy('appointment_time');
    }

    public function appointmentsByDate($date)
    {
        return $this->appointments()->whereDate('appointment_date', $date)->orderBy('appointment_time');
    }

    // Doctor profile relationship
    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    /**
     * Check if user has a specific permission
     * This is a wrapper for Spatie's hasPermissionTo method
     */
    public function hasPermission(string $permission): bool
    {
        return $this->hasPermissionTo($permission);
    }
}