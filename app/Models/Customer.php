<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    public const TYPES = [
        'rice_mill' => 'Rice Mill',
        'wholesaler' => 'Grain Wholesaler',
        'trader' => 'Agricultural Trader',
        'market_buyer' => 'Market / Mandi Buyer',
        'other' => 'Other Buyer',
    ];

    protected $fillable = [
        'customer_id', 'name', 'customer_type', 'contact_person', 'mobile',
        'alt_phone', 'email', 'address', 'city', 'state', 'gstin', 'pan',
        'credit_limit', 'payment_terms_days', 'opening_balance', 'is_active',
        'notes', 'created_by',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'opening_balance' => 'decimal:2',
        'payment_terms_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function salesInvoices(): HasMany
    {
        return $this->hasMany(SalesInvoice::class);
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(CustomerReceipt::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = '%'.trim($term).'%';

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', $term)
              ->orWhere('customer_id', 'like', $term)
              ->orWhere('contact_person', 'like', $term)
              ->orWhere('mobile', 'like', $term)
              ->orWhere('gstin', 'like', $term)
              ->orWhere('city', 'like', $term);
        });
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->customer_type] ?? ucfirst($this->customer_type);
    }

    public function getCustomerTypeLabelAttribute(): string
    {
        return $this->getTypeNameAttribute();
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            trim((string) $this->address),
            trim((string) $this->city),
            trim((string) $this->state),
        ]);

        return implode(', ', $parts);
    }

    public function getFormattedMobileAttribute(): ?string
    {
        if (blank($this->mobile)) {
            return null;
        }
        $clean = preg_replace('/\D/', '', $this->mobile);
        if (strlen($clean) === 10) {
            return '+91 ' . substr($clean, 0, 5) . ' ' . substr($clean, 5);
        }
        return $this->mobile;
    }

    public function getCurrentBalanceAttribute(): float
    {
        $opening = (float) ($this->opening_balance ?? 0);
        $finalizedInvoicesDue = (float) $this->salesInvoices()
            ->where('status', 'finalized')
            ->sum('balance_due');
        return $opening + $finalizedInvoicesDue;
    }

    public function getTotalInvoicedAttribute(): float
    {
        return (float) $this->salesInvoices()
            ->where('status', 'finalized')
            ->sum('total_amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->receipts()
            ->whereNull('reversed_at')
            ->sum('amount');
    }
}
