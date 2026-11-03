<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Crop extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'unit',
        'hsn_code',
        'current_purchase_rate',
        'max_purchase_rate',
        'rate_note',
        'show_rate_on_website',
        'rate_updated_at',
        'description',
        'is_active',
    ];

    protected $casts = [
        'current_purchase_rate' => 'decimal:2',
        'max_purchase_rate' => 'decimal:2',
        'show_rate_on_website' => 'boolean',
        'rate_updated_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function varieties(): HasMany
    {
        return $this->hasMany(CropVariety::class);
    }

    public function rateHistories(): HasMany
    {
        return $this->hasMany(CropRateHistory::class)->orderBy('effective_at')->orderBy('id');
    }

    /**
     * Every time the public buying price changes, write a history snapshot so
     * farmers can compare today's price with earlier prices.
     */
    protected static function booted(): void
    {
        static::saved(function (Crop $crop) {
            if ($crop->current_purchase_rate === null || (float) $crop->current_purchase_rate <= 0) {
                return;
            }

            $priceChanged = $crop->wasRecentlyCreated
                || $crop->wasChanged(['current_purchase_rate', 'max_purchase_rate']);

            if (! $priceChanged) {
                return;
            }

            $lastHistory = CropRateHistory::where('crop_id', $crop->id)
                ->orderByDesc('effective_at')
                ->orderByDesc('id')
                ->first();

            if ($lastHistory
                && (float) $lastHistory->purchase_rate === (float) $crop->current_purchase_rate
                && (float) ($lastHistory->max_purchase_rate ?? 0) === (float) ($crop->max_purchase_rate ?? 0)
            ) {
                return;
            }

            CropRateHistory::create([
                'crop_id' => $crop->id,
                'purchase_rate' => $crop->current_purchase_rate,
                'max_purchase_rate' => $crop->max_purchase_rate,
                'rate_note' => $crop->rate_note,
                'user_id' => auth()->id(),
                'effective_at' => $crop->rate_updated_at ?? now(),
            ]);
        });
    }

    public function shortUnitLabel(?string $locale = null): string
    {
        $isHindi = ($locale ?? app()->getLocale()) === 'hi';

        return match (strtolower((string) $this->unit)) {
            'quintal' => $isHindi ? 'क्विंटल' : 'Qtl',
            'kg' => $isHindi ? 'किग्रा' : 'Kg',
            'ton' => $isHindi ? 'टन' : 'MT',
            default => ucfirst((string) $this->unit),
        };
    }

    public function formattedPurchaseRate(bool $includeUnit = true): ?string
    {
        if ($this->current_purchase_rate === null || (float) $this->current_purchase_rate <= 0) {
            return null;
        }

        $sym = setting('locale.currency_symbol', '₹');
        $formatPrice = static function (string|float $val) use ($sym): string {
            $num = (float) $val;
            $decimals = ($num != floor($num)) ? 2 : 0;

            return $sym . indian_number($val, $decimals);
        };

        $minFormatted = $formatPrice($this->current_purchase_rate);
        if ($this->max_purchase_rate !== null && (float) $this->max_purchase_rate > (float) $this->current_purchase_rate) {
            $priceText = $minFormatted . ' – ' . $formatPrice($this->max_purchase_rate);
        } else {
            $priceText = $minFormatted;
        }

        return $includeUnit ? "{$priceText} / {$this->shortUnitLabel('en')}" : $priceText;
    }
}
