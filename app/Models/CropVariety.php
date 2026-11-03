<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CropVariety extends Model
{
    use HasFactory;

    protected $fillable = [
        'crop_id', 'name', 'code', 'standard_moisture_pct', 'default_grade', 'description', 'is_active',
    ];

    protected $casts = [
        'standard_moisture_pct' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function crop(): BelongsTo
    {
        return $this->belongsTo(Crop::class);
    }
}
