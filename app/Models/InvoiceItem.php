<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_type',
        'item_code',
        'item_name',
        'item_description',
        'itemable_type',
        'itemable_id',
        'unit_price',
        'quantity',
        'discount_percentage',
        'discount_amount',
        'tax_percentage',
        'tax_amount',
        'total_amount',
        'covered_by_insurance',
        'insurance_coverage_percentage',
        'insurance_covered_amount',
        'patient_responsibility',
        'item_details',
        'notes'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'insurance_coverage_percentage' => 'decimal:2',
        'insurance_covered_amount' => 'decimal:2',
        'patient_responsibility' => 'decimal:2',
        'item_details' => 'array',
        'covered_by_insurance' => 'boolean'
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }

    // Accessors
    public function getItemTypeDisplayAttribute(): string
    {
        $types = [
            'service' => 'خدمة طبية',
            'medication' => 'دواء',
            'lab_test' => 'فحص مختبري',
            'radiology' => 'فحص أشعة',
            'room' => 'إقامة',
            'surgery' => 'عملية جراحية',
            'consultation' => 'استشارة',
            'emergency' => 'طوارئ',
            'dental' => 'أسنان',
            'other' => 'أخرى'
        ];

        return $types[$this->item_type] ?? $this->item_type;
    }

    public function getSubtotalAttribute(): float
    {
        return $this->unit_price * $this->quantity;
    }

    public function getDiscountedPriceAttribute(): float
    {
        return $this->subtotal - $this->discount_amount;
    }

    public function getFinalPriceAttribute(): float
    {
        return $this->discounted_price + $this->tax_amount;
    }

    // Business Logic Methods
    public function calculateAmounts(): void
    {
        $subtotal = $this->unit_price * $this->quantity;
        
        // Calculate discount
        if ($this->discount_percentage > 0) {
            $this->discount_amount = ($subtotal * $this->discount_percentage) / 100;
        }
        
        $discounted_amount = $subtotal - $this->discount_amount;
        
        // Calculate tax
        if ($this->tax_percentage > 0) {
            $this->tax_amount = ($discounted_amount * $this->tax_percentage) / 100;
        }
        
        $this->total_amount = $discounted_amount + $this->tax_amount;
        
        // Calculate insurance coverage
        if ($this->covered_by_insurance && $this->insurance_coverage_percentage > 0) {
            $this->insurance_covered_amount = ($this->total_amount * $this->insurance_coverage_percentage) / 100;
            $this->patient_responsibility = $this->total_amount - $this->insurance_covered_amount;
        } else {
            $this->insurance_covered_amount = 0;
            $this->patient_responsibility = $this->total_amount;
        }
    }

    public function applyDiscount(float $percentage = null, float $amount = null): void
    {
        if ($percentage !== null) {
            $this->discount_percentage = $percentage;
            $this->discount_amount = ($this->subtotal * $percentage) / 100;
        } elseif ($amount !== null) {
            $this->discount_amount = $amount;
            $this->discount_percentage = ($amount / $this->subtotal) * 100;
        }
        
        $this->calculateAmounts();
    }

    public function applyTax(float $percentage): void
    {
        $this->tax_percentage = $percentage;
        $this->calculateAmounts();
    }

    public function applyInsuranceCoverage(float $percentage): void
    {
        $this->covered_by_insurance = true;
        $this->insurance_coverage_percentage = $percentage;
        $this->calculateAmounts();
    }

    // Static Methods
    public static function createFromService($service, int $quantity = 1): self
    {
        return new static([
            'item_type' => 'service',
            'item_code' => $service->code ?? null,
            'item_name' => $service->name,
            'item_description' => $service->description,
            'itemable_type' => get_class($service),
            'itemable_id' => $service->id,
            'unit_price' => $service->price,
            'quantity' => $quantity,
            'tax_percentage' => config('billing.default_tax_rate', 15)
        ]);
    }

    public static function createFromLabTest($labTest, int $quantity = 1): self
    {
        return new static([
            'item_type' => 'lab_test',
            'item_code' => $labTest->code,
            'item_name' => $labTest->name,
            'item_description' => $labTest->description,
            'itemable_type' => get_class($labTest),
            'itemable_id' => $labTest->id,
            'unit_price' => $labTest->price,
            'quantity' => $quantity,
            'tax_percentage' => config('billing.default_tax_rate', 15)
        ]);
    }

    public static function createFromRadiologyStudy($radiologyStudy, int $quantity = 1): self
    {
        return new static([
            'item_type' => 'radiology',
            'item_code' => $radiologyStudy->code,
            'item_name' => $radiologyStudy->name,
            'item_description' => $radiologyStudy->description,
            'itemable_type' => get_class($radiologyStudy),
            'itemable_id' => $radiologyStudy->id,
            'unit_price' => $radiologyStudy->price,
            'quantity' => $quantity,
            'tax_percentage' => config('billing.default_tax_rate', 15)
        ]);
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($item) {
            $item->calculateAmounts();
        });

        static::saved(function ($item) {
            // Update invoice totals when item is saved
            if ($item->invoice) {
                $item->invoice->calculateTotals();
                $item->invoice->save();
            }
        });

        static::deleted(function ($item) {
            // Update invoice totals when item is deleted
            if ($item->invoice) {
                $item->invoice->calculateTotals();
                $item->invoice->save();
            }
        });
    }
}