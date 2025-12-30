<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'message',
        'type',
        'priority',
        'channels',
        'recipient_type',
        'recipient_id',
        'sender_id',
        'data',
        'scheduled_at',
        'sent_at',
        'read_at',
        'status',
        'delivery_status',
        'retry_count',
        'max_retries',
        'escalation_level',
        'escalated_at',
        'escalated_to',
        'reference_type',
        'reference_id'
    ];

    protected $casts = [
        'channels' => 'array',
        'data' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'read_at' => 'datetime',
        'escalated_at' => 'datetime',
        'delivery_status' => 'array'
    ];

    // العلاقات
    public function recipient()
    {
        return $this->morphTo();
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function escalatedTo()
    {
        return $this->belongsTo(User::class, 'escalated_to');
    }

    public function reference()
    {
        return $this->morphTo();
    }

    // النطاقات
    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }

    public function scopeHigh($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('recipient_type', User::class)
                    ->where('recipient_id', $userId);
    }

    public function scopeForPatient($query, $patientId)
    {
        return $query->where('recipient_type', Patient::class)
                    ->where('recipient_id', $patientId);
    }

    // الطرق المساعدة
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    public function markAsSent()
    {
        $this->update([
            'sent_at' => now(),
            'status' => 'sent'
        ]);
    }

    public function markAsFailed($reason = null)
    {
        $this->update([
            'status' => 'failed',
            'data' => array_merge($this->data ?? [], ['failure_reason' => $reason])
        ]);
    }

    public function incrementRetry()
    {
        $this->increment('retry_count');
        
        if ($this->retry_count >= $this->max_retries) {
            $this->escalate();
        }
    }

    public function escalate()
    {
        $escalationUser = $this->determineEscalationUser();
        
        $this->update([
            'escalation_level' => $this->escalation_level + 1,
            'escalated_at' => now(),
            'escalated_to' => $escalationUser?->id
        ]);

        // إنشاء إشعار تصعيد
        if ($escalationUser) {
            static::create([
                'title' => 'تصعيد إشعار: ' . $this->title,
                'message' => 'تم تصعيد الإشعار التالي بسبب عدم الاستجابة: ' . $this->message,
                'type' => 'escalation',
                'priority' => 'critical',
                'channels' => ['whatsapp', 'sms', 'push', 'in_app'],
                'recipient_type' => User::class,
                'recipient_id' => $escalationUser->id,
                'sender_id' => $this->sender_id,
                'reference_type' => static::class,
                'reference_id' => $this->id,
                'data' => [
                    'original_notification_id' => $this->id,
                    'escalation_level' => $this->escalation_level
                ]
            ]);
        }
    }

    private function determineEscalationUser()
    {
        // تحديد المستخدم المناسب للتصعيد بناءً على نوع الإشعار والقسم
        switch ($this->type) {
            case 'medical_critical':
                return User::role('chief_medical_officer')->first();
            case 'financial_alert':
                return User::role('financial_manager')->first();
            case 'lab_critical':
                return User::role('lab_supervisor')->first();
            case 'radiology_urgent':
                return User::role('radiology_supervisor')->first();
            default:
                return User::role('admin')->first();
        }
    }

    public function shouldEscalate()
    {
        if ($this->priority !== 'critical') {
            return false;
        }

        $timeThreshold = match($this->type) {
            'medical_critical' => 5, // 5 دقائق
            'lab_critical' => 15,    // 15 دقيقة
            'radiology_urgent' => 30, // 30 دقيقة
            default => 60            // ساعة واحدة
        };

        return $this->sent_at && 
               $this->sent_at->diffInMinutes(now()) > $timeThreshold && 
               !$this->read_at;
    }

    // الثوابت
    const PRIORITIES = [
        'low' => 'منخفض',
        'normal' => 'عادي', 
        'high' => 'عالي',
        'critical' => 'حرج'
    ];

    const TYPES = [
        'appointment_reminder' => 'تذكير موعد',
        'appointment_confirmation' => 'تأكيد موعد',
        'lab_result_ready' => 'نتيجة مختبر جاهزة',
        'lab_critical' => 'نتيجة مختبر حرجة',
        'radiology_result_ready' => 'نتيجة أشعة جاهزة',
        'radiology_urgent' => 'نتيجة أشعة عاجلة',
        'payment_reminder' => 'تذكير دفع',
        'payment_overdue' => 'دفعة متأخرة',
        'medical_critical' => 'حالة طبية حرجة',
        'system_alert' => 'تنبيه نظام',
        'escalation' => 'تصعيد',
        'general' => 'عام'
    ];

    const CHANNELS = [
        'in_app' => 'داخل التطبيق',
        'email' => 'بريد إلكتروني',
        'sms' => 'رسالة نصية',
        'whatsapp' => 'واتساب',
        'push' => 'إشعار فوري'
    ];

    const STATUSES = [
        'pending' => 'في الانتظار',
        'sent' => 'تم الإرسال',
        'delivered' => 'تم التسليم',
        'read' => 'تم القراءة',
        'failed' => 'فشل',
        'cancelled' => 'ملغي'
    ];
}