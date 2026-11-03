<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One snapshot of a crop's public purchase price. A new row is written every
 * time Administration changes the buying rate, so farmers can compare today's
 * price against earlier prices and see the trend.
 */
class CropRateHistory extends Model
{
    protected $fillable = [
        'crop_id',
        'purchase_rate',
        'max_purchase_rate',
        'rate_note',
        'user_id',
        'effective_at',
    ];

    protected $casts = [
        'purchase_rate' => 'decimal:2',
        'max_purchase_rate' => 'decimal:2',
        'effective_at' => 'datetime',
    ];

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

