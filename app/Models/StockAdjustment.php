<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory;

    public const TYPES = [
        'physical_reconciliation' => 'Physical Stock Count Reconciliation',
        'damage' => 'Damaged / Spoiled Stock',
        'shrinkage' => 'Natural Moisture Shrinkage',
        'correction' => 'Weight Error Correction',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'adjustment_number', 'adjustment_date', 'warehouse_id', 'crop_id',
        'crop_variety_id', 'adjustment_type', 'quantity_kg', 'quantity_qtl',
        'status', 'reason', 'created_by', 'approved_by', 'approved_at', 'rejection_reason',
    ];

    protected $casts = [
        'adjustment_date' => 'datetime',
        'approved_at' => 'datetime',
        'quantity_kg' => 'decimal:3',
        'quantity_qtl' => 'decimal:3',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function cropVariety(): BelongsTo
    {
        return $this->belongsTo(CropVariety::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isIncrease(): bool
    {
        return bccomp((string) $this->quantity_kg, '0', 3) > 0;
    }

    public function isDecrease(): bool
    {
        return bccomp((string) $this->quantity_kg, '0', 3) < 0;
    }

    public function getEstBagsAttribute(): int
    {
        $kg = abs((float) $this->quantity_kg);
        return $kg > 0 ? (int) round($kg / 50) : 0;
    }

    public function getTypeNameAttribute(): string
    {
        return self::TYPES[$this->adjustment_type] ?? ucfirst(str_replace('_', ' ', $this->adjustment_type));
    }
}
