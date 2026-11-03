<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'transfer_number', 'transfer_date', 'from_warehouse_id', 'to_warehouse_id',
        'crop_id', 'crop_variety_id', 'quantity_kg', 'quantity_qtl',
        'vehicle_number', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'transfer_date' => 'datetime',
        'quantity_kg' => 'decimal:3',
        'quantity_qtl' => 'decimal:3',
    ];

    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
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

    public function challanNumber(): string
    {
        return 'TC/' . $this->transfer_number;
    }
}
