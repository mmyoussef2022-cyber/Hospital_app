<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DepartmentFinancialTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_financial_account_id',
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
    public function departmentFinancialAccount(): BelongsTo
    {
        return $this->belongsTo(DepartmentFinancialAccount::class);
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
                case 'invoice':
                    return $this->belongsTo(Invoice::class, 'reference_id');
                case 'appointment':
                    return $this->belongsTo(Appointment::class, 'reference_id');
                case 'lab_order':
                    return $this->belongsTo(LabOrder::class, 'reference_id');
                case 'radiology_order':
                    return $this->belongsTo(RadiologyOrder::class, 'reference_id');
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function scopeRevenue($query)
    {
        return $query->where('transaction_type', 'revenue');
    }

    public function scopeExpense($query)
    {
        return $query->where('transaction_type', 'expense');
    }

    public function scopeBudgetAllocation($query)
    {
        return $query->where('transaction_type', 'budget_allocation');
    }

    public function scopeTransfer($query)
    {
        return $query->where('transaction_type', 'transfer');
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
            'completed' => 'info',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getStatusDisplayAttribute(): string
    {
        $statuses = [
            'pending' => app()->getLocale() === 'ar' ? 'في الانتظار' : 'Pending',
            'approved' => app()->getLocale() === 'ar' ? 'معتمد' : 'Approved',
            'completed' => app()->getLocale() === 'ar' ? 'مكتمل' : 'Completed',
            'cancelled' => app()->getLocale() === 'ar' ? 'ملغي' : 'Cancelled'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getIsIncomeAttribute(): bool
    {
        return $this->amount > 0;
    }

    public function getIsExpenseAttribute(): bool
    {
        return $this->amount < 0;
    }

    public function getTransactionCategoryAttribute(): string
    {
        if ($this->amount > 0) {
            return app()->getLocale() === 'ar' ? 'إيراد' : 'Income';
        } else {
            return app()->getLocale() === 'ar' ? 'مصروف' : 'Expense';
        }
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

        return true;
    }

    public function complete(): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $this->status = 'completed';
        $this->save();

        return true;
    }

    public function cancel(): bool
    {
        if ($this->status === 'completed') {
            return false; // لا يمكن إلغاء معاملة مكتملة
        }

        $this->status = 'cancelled';
        $this->save();

        return true;
    }

    // إنشاء معاملة إيراد من فاتورة
    public static function createRevenueFromInvoice(Invoice $invoice): ?self
    {
        $department = $invoice->appointment?->doctor?->department;
        if (!$department) {
            return null;
        }

        $departmentAccount = $department->financialAccount ?? DepartmentFinancialAccount::createForDepartment($department);

        return $departmentAccount->addRevenue(
            $invoice->total_amount,
            "Revenue from invoice #{$invoice->invoice_number} - Patient: {$invoice->patient->name}",
            'invoice',
            $invoice->id
        );
    }

    // إنشاء معاملة إيراد من طلب مختبر
    public static function createRevenueFromLabOrder(LabOrder $labOrder): ?self
    {
        $department = Department::where('name', 'Laboratory')->first();
        if (!$department) {
            return null;
        }

        $departmentAccount = $department->financialAccount ?? DepartmentFinancialAccount::createForDepartment($department);

        return $departmentAccount->addRevenue(
            $labOrder->total_cost ?? 0,
            "Revenue from lab order #{$labOrder->id} - Patient: {$labOrder->patient->name}",
            'lab_order',
            $labOrder->id
        );
    }

    // إنشاء معاملة إيراد من طلب أشعة
    public static function createRevenueFromRadiologyOrder(RadiologyOrder $radiologyOrder): ?self
    {
        $department = Department::where('name', 'Radiology')->first();
        if (!$department) {
            return null;
        }

        $departmentAccount = $department->financialAccount ?? DepartmentFinancialAccount::createForDepartment($department);

        return $departmentAccount->addRevenue(
            $radiologyOrder->total_cost ?? 0,
            "Revenue from radiology order #{$radiologyOrder->id} - Patient: {$radiologyOrder->patient->name}",
            'radiology_order',
            $radiologyOrder->id
        );
    }

    // إنشاء معاملة إيراد من عملية جراحية
    public static function createRevenueFromSurgery(Surgery $surgery): ?self
    {
        $department = $surgery->surgeon?->department;
        if (!$department) {
            return null;
        }

        $departmentAccount = $department->financialAccount ?? DepartmentFinancialAccount::createForDepartment($department);

        return $departmentAccount->addRevenue(
            $surgery->cost ?? 0,
            "Revenue from surgery #{$surgery->id} - {$surgery->surgery_name}",
            'surgery',
            $surgery->id
        );
    }
}