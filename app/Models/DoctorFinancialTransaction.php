<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorFinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_financial_account_id',
        'transaction_number',
        'transaction_type',
        'transaction_type_ar',
        'amount',
        'description',
        'description_ar',
        'reference_type',
        'reference_id',
        'transaction_date',
        'status',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'approved_at' => 'datetime'
    ];

    // العلاقات
    public function doctorFinancialAccount(): BelongsTo
    {
        return $this->belongsTo(DoctorFinancialAccount::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function reference()
    {
        if ($this->reference_type && $this->reference_id) {
            switch ($this->reference_type) {
                case 'appointment':
                    return $this->belongsTo(Appointment::class, 'reference_id');
                case 'invoice':
                    return $this->belongsTo(Invoice::class, 'reference_id');
                case 'surgery':
                    return $this->belongsTo(Surgery::class, 'reference_id');
                default:
                    return null;
            }
        }
        return null;
    }

    // النطاقات
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeCommissions($query)
    {
        return $query->where('transaction_type', 'commission');
    }

    public function scopeBonuses($query)
    {
        return $query->where('transaction_type', 'bonus');
    }

    public function scopeDeductions($query)
    {
        return $query->where('transaction_type', 'deduction');
    }

    public function scopeWithdrawals($query)
    {
        return $query->where('transaction_type', 'withdrawal');
    }

    // الخصائص المحسوبة
    public function getDescriptionAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->description_ar : $this->attributes['description'];
    }

    public function getTransactionTypeDisplayAttribute(): string
    {
        return app()->getLocale() === 'ar' ? $this->transaction_type_ar : $this->transaction_type;
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format(abs($this->amount), 2) . ' ريال';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'paid' => 'info',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'pending' => app()->getLocale() === 'ar' ? 'في الانتظار' : 'Pending',
            'approved' => app()->getLocale() === 'ar' ? 'معتمد' : 'Approved',
            'paid' => app()->getLocale() === 'ar' ? 'مدفوع' : 'Paid',
            'cancelled' => app()->getLocale() === 'ar' ? 'ملغي' : 'Cancelled'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getIsPositiveAttribute(): bool
    {
        return $this->amount > 0;
    }

    public function getIsNegativeAttribute(): bool
    {
        return $this->amount < 0;
    }

    // الدوال المساعدة
    public function approve(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->status = 'approved';
        $this->approved_by = auth()->id();
        $this->approved_at = now();
        $this->save();

        // تحديث رصيد الطبيب
        $this->doctorFinancialAccount->approveTransaction($this);

        return true;
    }

    public function cancel(): bool
    {
        if ($this->status === 'paid') {
            return false; // لا يمكن إلغاء معاملة مدفوعة
        }

        $this->status = 'cancelled';
        $this->save();

        return true;
    }

    public function markAsPaid(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $this->status = 'paid';
        $this->save();

        return true;
    }

    // إنشاء معاملة عمولة من موعد
    public static function createCommissionFromAppointment(Appointment $appointment): ?self
    {
        $doctor = $appointment->doctor;
        $doctorAccount = $doctor->doctorFinancialAccount ?? DoctorFinancialAccount::createForDoctor($doctor);

        if (!$doctorAccount->is_active) {
            return null;
        }

        $serviceAmount = $appointment->invoice ? $appointment->invoice->total_amount : 0;
        $commissionAmount = $doctorAccount->calculateCommission($serviceAmount);

        return $doctorAccount->addCommission(
            $commissionAmount,
            "Commission for appointment #{$appointment->id} - Patient: {$appointment->patient->name}",
            'appointment',
            $appointment->id
        );
    }

    // إنشاء معاملة عمولة من عملية جراحية
    public static function createCommissionFromSurgery(Surgery $surgery): ?self
    {
        $doctor = $surgery->surgeon;
        $doctorAccount = $doctor->doctorFinancialAccount ?? DoctorFinancialAccount::createForDoctor($doctor);

        if (!$doctorAccount->is_active) {
            return null;
        }

        $surgeryAmount = $surgery->cost ?? 0;
        $commissionAmount = $doctorAccount->calculateCommission($surgeryAmount);

        return $doctorAccount->addCommission(
            $commissionAmount,
            "Commission for surgery #{$surgery->id} - {$surgery->surgery_name}",
            'surgery',
            $surgery->id
        );
    }
}