<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Farmer extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id', 'name', 'mobile', 'address', 'village', 'panchayat',
        'block', 'district', 'state', 'bank_name', 'bank_branch',
        'bank_account_number', 'bank_ifsc', 'id_type', 'id_number',
        'registration_date', 'opening_balance',
        'delayed_bonus_enabled', 'delayed_bonus_rate', 'delayed_bonus_mode',
        'is_active', 'notes', 'created_by',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'opening_balance' => 'decimal:2',
        'delayed_bonus_enabled' => 'boolean',
        'delayed_bonus_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function isDelayedBonusEnabled(): bool
    {
        if (setting('procurement.delayed_bonus_enabled', '1') != '1') {
            return false;
        }

        return (bool) ($this->delayed_bonus_enabled ?? true);
    }

    public function hasCustomBonusSetting(): bool
    {
        return $this->delayed_bonus_enabled === false
            || $this->delayed_bonus_rate !== null
            || ! empty($this->delayed_bonus_mode);
    }

    public function getEffectiveBonusRateAttribute(): string
    {
        return $this->delayed_bonus_rate !== null
            ? bc_round((string) $this->delayed_bonus_rate, 2)
            : (string) setting('procurement.delayed_bonus_rate', '1.00');
    }

    public function getEffectiveBonusModeAttribute(): string
    {
        return ! empty($this->delayed_bonus_mode)
            ? (string) $this->delayed_bonus_mode
            : (string) setting('procurement.delayed_bonus_mode', 'monthly');
    }

    public function formattedMobile(): ?string
    {
        if (empty($this->mobile)) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $this->mobile) ?? '';
        if (strlen($digits) === 10) {
            return '+91 ' . substr($digits, 0, 5) . ' ' . substr($digits, 5);
        }

        return $this->mobile;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
              ->orWhere('farmer_id', 'like', $term)
              ->orWhere('mobile', 'like', $term)
              ->orWhere('village', 'like', $term)
              ->orWhere('block', 'like', $term)
              ->orWhere('district', 'like', $term);
        });
    }

    /** Formatted address string. */
    public function getFullLocationAttribute(): string
    {
        return collect([$this->village, $this->panchayat, $this->block, $this->district, $this->state])
            ->filter()
            ->join(', ');
    }
}
