<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Purchase extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINALIZED = 'finalized';
    public const STATUS_REVERSED = 'reversed';
    public const STATUS_CANCELLED = self::STATUS_REVERSED;

    public function getPurchaseIdAttribute(): string
    {
        return $this->purchase_number;
    }

    public function getNetPayableAttribute(): string
    {
        return (string) ($this->attributes['net_payable_amount'] ?? '0.00');
    }

    public function getGrossAmountAttribute(): string
    {
        return (string) $this->base_amount;
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(CropVariety::class, 'crop_variety_id');
    }

    protected $fillable = [
        'purchase_number', 'purchase_date', 'farmer_id', 'crop_id', 'crop_variety_id',
        'warehouse_id', 'gross_weight_kg', 'tare_weight_kg', 'net_weight_kg', 'net_weight_qtl',
        'moisture_pct', 'quality_grade', 'rate_per_qtl', 'delayed_payment_bonus_rate', 'base_amount', 'deductions_amount',
        'deduction_reason', 'additions_amount', 'net_payable_amount', 'paid_amount', 'balance_due',
        'vehicle_number', 'driver_name', 'status', 'remarks', 'reversed_at', 'reversed_by',
        'reversal_reason', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'purchase_date' => 'datetime',
        'reversed_at' => 'datetime',
        'gross_weight_kg' => 'decimal:3',
        'tare_weight_kg' => 'decimal:3',
        'net_weight_kg' => 'decimal:3',
        'net_weight_qtl' => 'decimal:3',
        'moisture_pct' => 'decimal:2',
        'rate_per_qtl' => 'decimal:2',
        'delayed_payment_bonus_rate' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'deductions_amount' => 'decimal:2',
        'additions_amount' => 'decimal:2',
        'net_payable_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(Farmer::class);
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function cropVariety(): BelongsTo
    {
        return $this->belongsTo(CropVariety::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by');
    }

    public function inventoryTransaction(): HasOne
    {
        return $this->hasOne(InventoryTransaction::class, 'source_id')
            ->where('source_type', 'purchase');
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isFinalized(): bool
    {
        return $this->status === self::STATUS_FINALIZED;
    }

    public function isReversed(): bool
    {
        return $this->status === self::STATUS_REVERSED;
    }

    public function getIsReversedAttribute(): bool
    {
        return $this->isReversed();
    }

    public function scopeFinalized(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FINALIZED);
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeReversed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REVERSED);
    }
}
