<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LoginAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'success',
        'failure_reason',
        'attempted_at'
    ];

    protected $casts = [
        'success' => 'boolean',
        'attempted_at' => 'datetime'
    ];

    /**
     * العلاقة مع المستخدم
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'email', 'email');
    }

    /**
     * Scope للمحاولات الناجحة
     */
    public function scopeSuccessful($query)
    {
        return $query->where('success', true);
    }

    /**
     * Scope للمحاولات الفاشلة
     */
    public function scopeFailed($query)
    {
        return $query->where('success', false);
    }

    /**
     * Scope للمحاولات في فترة زمنية
     */
    public function scopeInTimeRange($query, $start, $end)
    {
        return $query->whereBetween('attempted_at', [$start, $end]);
    }

    /**
     * Scope للمحاولات من IP معين
     */
    public function scopeFromIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }

    /**
     * Scope للمحاولات لبريد إلكتروني معين
     */
    public function scopeForEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Scope للمحاولات الأخيرة
     */
    public function scopeRecent($query, $minutes = 15)
    {
        return $query->where('attempted_at', '>=', now()->subMinutes($minutes));
    }

    /**
     * الحصول على حالة المحاولة
     */
    public function getStatusAttribute()
    {
        return $this->success ? 'نجح' : 'فشل';
    }

    /**
     * الحصول على لون الحالة
     */
    public function getStatusColorAttribute()
    {
        return $this->success ? 'success' : 'danger';
    }

    /**
     * الحصول على أيقونة الحالة
     */
    public function getStatusIconAttribute()
    {
        return $this->success ? 'bi-check-circle-fill' : 'bi-x-circle-fill';
    }

    /**
     * الحصول على وصف سبب الفشل
     */
    public function getFailureReasonDescriptionAttribute()
    {
        if ($this->success) {
            return null;
        }

        return match($this->failure_reason) {
            'invalid_credentials' => 'بيانات اعتماد غير صحيحة',
            'account_locked' => 'الحساب مقفل',
            'account_disabled' => 'الحساب معطل',
            'too_many_attempts' => 'محاولات كثيرة جداً',
            'invalid_email' => 'بريد إلكتروني غير صحيح',
            'password_expired' => 'كلمة المرور منتهية الصلاحية',
            default => $this->failure_reason ?? 'سبب غير معروف'
        };
    }

    /**
     * الحصول على معلومات المتصفح
     */
    public function getBrowserInfoAttribute()
    {
        $agent = new \Jenssegers\Agent\Agent();
        $agent->setUserAgent($this->user_agent);
        
        return [
            'browser' => $agent->browser(),
            'version' => $agent->version($agent->browser()),
            'platform' => $agent->platform(),
            'device' => $agent->device()
        ];
    }

    /**
     * فحص إذا كانت المحاولة مشبوهة
     */
    public function isSuspicious()
    {
        // فحص إذا كان هناك محاولات متعددة من نفس IP
        $recentAttempts = static::fromIp($this->ip_address)
            ->recent(60)
            ->failed()
            ->count();

        return $recentAttempts >= 5;
    }

    /**
     * الحصول على الموقع الجغرافي للـ IP (تقريبي)
     */
    public function getLocationAttribute()
    {
        // يمكن استخدام خدمة خارجية للحصول على الموقع
        // هنا مثال بسيط
        if ($this->ip_address === '127.0.0.1' || $this->ip_address === '::1') {
            return 'محلي';
        }

        // يمكن تطوير هذا باستخدام خدمات مثل GeoIP
        return 'غير معروف';
    }
}