<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PaymentTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'days',
        'discount_percentage',
        'discount_days',
        'late_fee_percentage',
        'late_fee_days',
        'is_active',
        'is_default'
    ];

    protected $casts = [
        'days' => 'integer',
        'discount_percentage' => 'decimal:2',
        'discount_days' => 'integer',
        'late_fee_percentage' => 'decimal:2',
        'late_fee_days' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean'
    ];

    // Relationships
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' && $this->name_ar ? $this->name_ar : $this->name;
    }

    public function getDisplayDescriptionAttribute(): string
    {
        $locale = app()->getLocale();
        return $locale === 'ar' && $this->description_ar ? $this->description_ar : $this->description;
    }

    // Business Logic Methods
    public function calculateDueDate(Carbon $invoiceDate): Carbon
    {
        return $invoiceDate->copy()->addDays($this->days);
    }

    public function calculateDiscountAmount(float $amount, Carbon $paymentDate, Carbon $invoiceDate): float
    {
        if (!$this->discount_percentage || !$this->discount_days) {
            return 0;
        }

        $daysSinceInvoice = $invoiceDate->diffInDays($paymentDate);
        
        if ($daysSinceInvoice <= $this->discount_days) {
            return $amount * ($this->discount_percentage / 100);
        }

        return 0;
    }

    public function calculateLateFee(float $amount, Carbon $dueDate, Carbon $currentDate = null): float
    {
        $currentDate = $currentDate ?? now();
        
        if (!$this->late_fee_percentage || !$this->late_fee_days) {
            return 0;
        }

        if ($currentDate->lte($dueDate)) {
            return 0;
        }

        $daysOverdue = $dueDate->diffInDays($currentDate);
        
        if ($daysOverdue >= $this->late_fee_days) {
            return $amount * ($this->late_fee_percentage / 100);
        }

        return 0;
    }

    public function isEligibleForDiscount(Carbon $paymentDate, Carbon $invoiceDate): bool
    {
        if (!$this->discount_percentage || !$this->discount_days) {
            return false;
        }

        $daysSinceInvoice = $invoiceDate->diffInDays($paymentDate);
        return $daysSinceInvoice <= $this->discount_days;
    }

    public function isOverdue(Carbon $dueDate, Carbon $currentDate = null): bool
    {
        $currentDate = $currentDate ?? now();
        return $currentDate->gt($dueDate);
    }

    // Static Methods
    public static function getDefault(): ?self
    {
        return static::default()->active()->first();
    }

    public static function createStandardTerms(): void
    {
        $terms = [
            [
                'name' => 'Net 30',
                'name_ar' => 'صافي 30 يوم',
                'description' => 'Payment due within 30 days',
                'description_ar' => 'الدفع مستحق خلال 30 يوم',
                'days' => 30,
                'is_active' => true,
                'is_default' => true
            ],
            [
                'name' => 'Net 15',
                'name_ar' => 'صافي 15 يوم',
                'description' => 'Payment due within 15 days',
                'description_ar' => 'الدفع مستحق خلال 15 يوم',
                'days' => 15,
                'is_active' => true,
                'is_default' => false
            ],
            [
                'name' => '2/10 Net 30',
                'name_ar' => '2% خصم خلال 10 أيام أو صافي 30',
                'description' => '2% discount if paid within 10 days, otherwise net 30',
                'description_ar' => 'خصم 2% إذا تم الدفع خلال 10 أيام، وإلا صافي 30 يوم',
                'days' => 30,
                'discount_percentage' => 2.00,
                'discount_days' => 10,
                'is_active' => true,
                'is_default' => false
            ],
            [
                'name' => 'Due on Receipt',
                'name_ar' => 'مستحق عند الاستلام',
                'description' => 'Payment due immediately upon receipt',
                'description_ar' => 'الدفع مستحق فور الاستلام',
                'days' => 0,
                'is_active' => true,
                'is_default' => false
            ],
            [
                'name' => 'Net 60 with Late Fee',
                'name_ar' => 'صافي 60 يوم مع رسوم تأخير',
                'description' => 'Payment due within 60 days, 1.5% monthly late fee after 5 days',
                'description_ar' => 'الدفع مستحق خلال 60 يوم، رسوم تأخير 1.5% شهرياً بعد 5 أيام',
                'days' => 60,
                'late_fee_percentage' => 1.50,
                'late_fee_days' => 5,
                'is_active' => true,
                'is_default' => false
            ]
        ];

        foreach ($terms as $term) {
            static::create($term);
        }
    }
}