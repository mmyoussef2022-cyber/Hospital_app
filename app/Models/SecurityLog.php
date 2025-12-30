<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SecurityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_type',
        'description',
        'level',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'additional_data',
        'created_at'
    ];

    protected $casts = [
        'additional_data' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope للأحداث الحرجة
     */
    public function scopeCritical($query)
    {
        return $query->where('level', 'critical');
    }

    /**
     * Scope للأحداث التحذيرية
     */
    public function scopeWarning($query)
    {
        return $query->where('level', 'warning');
    }

    /**
     * Scope للأحداث في فترة زمنية
     */
    public function scopeInTimeRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope للأحداث حسب نوع الحدث
     */
    public function scopeEventType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    /**
     * Scope للأحداث حسب IP
     */
    public function scopeFromIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * الحصول على اسم المستخدم أو "غير معروف"
     */
    public function getUserNameAttribute()
    {
        return $this->user ? $this->user->name : 'غير معروف';
    }

    /**
     * الحصول على وصف مختصر للحدث
     */
    public function getShortDescriptionAttribute()
    {
        return strlen($this->description) > 100 
            ? substr($this->description, 0, 100) . '...' 
            : $this->description;
    }

    /**
     * الحصول على لون المستوى
     */
    public function getLevelColorAttribute()
    {
        return match($this->level) {
            'critical' => 'danger',
            'warning' => 'warning',
            'info' => 'info',
            'debug' => 'secondary',
            default => 'primary'
        };
    }

    /**
     * الحصول على أيقونة المستوى
     */
    public function getLevelIconAttribute()
    {
        return match($this->level) {
            'critical' => 'bi-exclamation-triangle-fill',
            'warning' => 'bi-exclamation-triangle',
            'info' => 'bi-info-circle',
            'debug' => 'bi-bug',
            default => 'bi-circle'
        };
    }

    /**
     * الحصول على وصف نوع الحدث
     */
    public function getEventTypeDescriptionAttribute()
    {
        return match($this->event_type) {
            'login_success' => 'تسجيل دخول ناجح',
            'login_failure' => 'فشل تسجيل الدخول',
            'logout' => 'تسجيل خروج',
            'password_change' => 'تغيير كلمة المرور',
            'permission_change' => 'تغيير الصلاحيات',
            'data_access' => 'الوصول للبيانات',
            'data_modification' => 'تعديل البيانات',
            'suspicious_activity' => 'نشاط مشبوه',
            'security_breach' => 'خرق أمني',
            'backup_created' => 'إنشاء نسخة احتياطية',
            'system_maintenance' => 'صيانة النظام',
            default => 'حدث غير معروف'
        };
    }
}