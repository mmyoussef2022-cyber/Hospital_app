<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LandingPageOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'discount_type',
        'discount_value',
        'discount_badge_text',
        'valid_from',
        'valid_until',
        'is_active',
        'is_featured',
        'cta_text',
        'cta_url',
        'terms_conditions',
        'max_uses',
        'current_uses',
        'sort_order',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'discount_value' => 'decimal:2',
        'max_uses' => 'integer',
        'current_uses' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Scope for active offers
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where('valid_from', '<=', now())
                    ->where('valid_until', '>=', now());
    }

    /**
     * Scope for featured offers
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Check if offer is currently valid
     */
    public function getIsValidAttribute()
    {
        return $this->is_active && 
               $this->valid_from <= now() && 
               $this->valid_until >= now();
    }

    /**
     * Check if offer has usage limit
     */
    public function getHasUsageLimitAttribute()
    {
        return !is_null($this->max_uses);
    }

    /**
     * Check if offer is available for use
     */
    public function getIsAvailableAttribute()
    {
        if (!$this->is_valid) {
            return false;
        }

        if ($this->has_usage_limit) {
            return $this->current_uses < $this->max_uses;
        }

        return true;
    }

    /**
     * Get formatted discount text
     */
    public function getDiscountTextAttribute()
    {
        if ($this->discount_badge_text) {
            return $this->discount_badge_text;
        }

        switch ($this->discount_type) {
            case 'percentage':
                return $this->discount_value . '% خصم';
            case 'fixed':
                return 'خصم ' . $this->discount_value . ' ريال';
            case 'free':
                return 'مجاناً';
            default:
                return '';
        }
    }
}
