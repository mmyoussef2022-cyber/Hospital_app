<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PaymentReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'patient_id',
        'reminder_type',
        'reminder_method',
        'scheduled_date',
        'sent_date',
        'status',
        'message',
        'response_received',
        'response_date',
        'response_details',
        'escalation_level',
        'created_by',
        'sent_by'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'sent_date' => 'datetime',
        'response_date' => 'datetime',
        'response_received' => 'boolean',
        'response_details' => 'array',
        'escalation_level' => 'integer'
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sentBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeDue($query)
    {
        return $query->where('status', 'pending')
                    ->where('scheduled_date', '<=', now());
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('reminder_method', $method);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('reminder_type', $type);
    }

    // Accessors
    public function getReminderTypeDisplayAttribute(): string
    {
        $types = [
            'gentle' => 'تذكير لطيف',
            'standard' => 'تذكير عادي',
            'urgent' => 'تذكير عاجل',
            'final_notice' => 'إشعار نهائي',
            'collection' => 'تحصيل'
        ];

        return $types[$this->reminder_type] ?? $this->reminder_type;
    }

    public function getReminderMethodDisplayAttribute(): string
    {
        $methods = [
            'sms' => 'رسالة نصية',
            'email' => 'بريد إلكتروني',
            'whatsapp' => 'واتساب',
            'phone_call' => 'مكالمة هاتفية',
            'letter' => 'خطاب'
        ];

        return $methods[$this->reminder_method] ?? $this->reminder_method;
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'pending' => 'معلق',
            'sent' => 'مرسل',
            'delivered' => 'تم التسليم',
            'failed' => 'فشل',
            'responded' => 'تم الرد'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' && $this->scheduled_date->isPast();
    }

    public function getDaysOverdueAttribute(): int
    {
        if (!$this->is_overdue) {
            return 0;
        }

        return $this->scheduled_date->diffInDays(now());
    }

    // Business Logic Methods
    public function markAsSent(User $sentBy = null): void
    {
        $this->update([
            'status' => 'sent',
            'sent_date' => now(),
            'sent_by' => $sentBy ? $sentBy->id : auth()->id()
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered'
        ]);
    }

    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => 'failed',
            'response_details' => array_merge($this->response_details ?? [], [
                'failure_reason' => $reason,
                'failed_at' => now()
            ])
        ]);
    }

    public function recordResponse(array $responseDetails = []): void
    {
        $this->update([
            'status' => 'responded',
            'response_received' => true,
            'response_date' => now(),
            'response_details' => $responseDetails
        ]);
    }

    public function escalate(): void
    {
        $this->increment('escalation_level');
        
        // Create next level reminder
        $nextReminderType = $this->getNextEscalationLevel();
        
        if ($nextReminderType) {
            static::create([
                'invoice_id' => $this->invoice_id,
                'patient_id' => $this->patient_id,
                'reminder_type' => $nextReminderType,
                'reminder_method' => $this->reminder_method,
                'scheduled_date' => now()->addDays($this->getEscalationDays()),
                'status' => 'pending',
                'message' => $this->generateEscalatedMessage($nextReminderType),
                'escalation_level' => $this->escalation_level + 1,
                'created_by' => auth()->id()
            ]);
        }
    }

    private function getNextEscalationLevel(): ?string
    {
        $escalationPath = [
            'gentle' => 'standard',
            'standard' => 'urgent',
            'urgent' => 'final_notice',
            'final_notice' => 'collection',
            'collection' => null
        ];

        return $escalationPath[$this->reminder_type] ?? null;
    }

    private function getEscalationDays(): int
    {
        $escalationDays = [
            'gentle' => 7,
            'standard' => 5,
            'urgent' => 3,
            'final_notice' => 7,
            'collection' => 14
        ];

        return $escalationDays[$this->reminder_type] ?? 7;
    }

    private function generateEscalatedMessage(string $type): string
    {
        $invoice = $this->invoice;
        $patient = $this->patient;
        
        $messages = [
            'gentle' => "عزيزي/عزيزتي {$patient->name}، نذكركم بأن الفاتورة رقم {$invoice->invoice_number} بمبلغ {$invoice->remaining_amount} ريال مستحقة الدفع. نرجو منكم التكرم بسداد المبلغ في أقرب وقت ممكن.",
            
            'standard' => "السيد/السيدة {$patient->name}، الفاتورة رقم {$invoice->invoice_number} بمبلغ {$invoice->remaining_amount} ريال متأخرة عن موعد الاستحقاق. يرجى سداد المبلغ خلال 5 أيام لتجنب الرسوم الإضافية.",
            
            'urgent' => "تنبيه عاجل: السيد/السيدة {$patient->name}، الفاتورة رقم {$invoice->invoice_number} بمبلغ {$invoice->remaining_amount} ريال متأخرة بشكل كبير. يجب سداد المبلغ خلال 3 أيام وإلا سيتم اتخاذ إجراءات قانونية.",
            
            'final_notice' => "إشعار نهائي: السيد/السيدة {$patient->name}، هذا آخر تنبيه بخصوص الفاتورة رقم {$invoice->invoice_number} بمبلغ {$invoice->remaining_amount} ريال. في حالة عدم السداد خلال 7 أيام سيتم تحويل الملف لشركة التحصيل.",
            
            'collection' => "تم تحويل ملفكم للتحصيل: السيد/السيدة {$patient->name}، تم تحويل الفاتورة رقم {$invoice->invoice_number} بمبلغ {$invoice->remaining_amount} ريال لشركة التحصيل. للتسوية الفورية يرجى الاتصال على الرقم المرفق."
        ];

        return $messages[$type] ?? $messages['standard'];
    }

    // Static Methods
    public static function createAutomaticReminders(Invoice $invoice): void
    {
        if ($invoice->type !== 'credit' || $invoice->status !== 'pending') {
            return;
        }

        $reminderSchedule = [
            ['type' => 'gentle', 'days_after_due' => 3],
            ['type' => 'standard', 'days_after_due' => 10],
            ['type' => 'urgent', 'days_after_due' => 20],
            ['type' => 'final_notice', 'days_after_due' => 30]
        ];

        foreach ($reminderSchedule as $reminder) {
            static::create([
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'reminder_type' => $reminder['type'],
                'reminder_method' => 'sms', // Default method
                'scheduled_date' => $invoice->due_date->addDays($reminder['days_after_due']),
                'status' => 'pending',
                'message' => (new static)->generateEscalatedMessage($reminder['type']),
                'escalation_level' => 1,
                'created_by' => $invoice->created_by
            ]);
        }
    }

    public static function processPendingReminders(): int
    {
        $pendingReminders = static::due()->get();
        $processed = 0;

        foreach ($pendingReminders as $reminder) {
            try {
                // TODO: Implement actual sending logic based on reminder_method
                // This would integrate with SMS, Email, WhatsApp services
                
                $reminder->markAsSent();
                $processed++;
                
            } catch (\Exception $e) {
                $reminder->markAsFailed($e->getMessage());
            }
        }

        return $processed;
    }
}