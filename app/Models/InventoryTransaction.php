<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only real-time inventory ledger.
 * Updates and deletes are disabled to maintain strict audit integrity.
 */
class InventoryTransaction extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'warehouse_id', 'crop_id', 'crop_variety_id', 'transaction_date',
        'quantity_kg', 'quantity_qtl', 'source_type', 'source_id',
        'reference_number', 'direction', 'description', 'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'quantity_kg' => 'decimal:3',
        'quantity_qtl' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }

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

    public function variety(): BelongsTo
    {
        return $this->belongsTo(CropVariety::class, 'crop_variety_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Helper to calculate current available stock in a warehouse for a crop (in quintals). */
    public static function getStock(int $warehouseId, int $cropId, ?int $varietyId = null): string
    {
        $sum = static::query()
            ->where('warehouse_id', $warehouseId)
            ->where('crop_id', $cropId)
            ->when($varietyId, fn ($q) => $q->where('crop_variety_id', $varietyId))
            ->sum('quantity_qtl');

        return bc_round((string) $sum, 3);
    }
}
