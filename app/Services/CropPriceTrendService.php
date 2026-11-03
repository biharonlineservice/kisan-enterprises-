<?php

namespace App\Services;

use App\Models\Crop;
use App\Models\CropRateHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the public "price trend" data for crops: current vs previous buying
 * price, the difference (amount and %), period high/low, chart points, and a
 * short list of recent price changes.
 */
class CropPriceTrendService
{
    /**
     * @param  Collection<int, Crop>  $crops  Crops whose rates are public.
     * @return array<int, array<string, mixed>>
     */
    public function build(Collection $crops, int $days = 90): array
    {
        if ($crops->isEmpty()) {
            return [];
        }

        $histories = CropRateHistory::whereIn('crop_id', $crops->pluck('id'))
            ->orderBy('effective_at')
            ->orderBy('id')
            ->get()
            ->groupBy('crop_id');

        $windowStart = now()->subDays($days)->startOfDay();
        $tz = config('app.timezone');

        return $crops->map(function (Crop $crop) use ($histories, $windowStart, $tz) {
            $rows = $histories->get($crop->id, collect())->values();

            $current = (float) $crop->current_purchase_rate;
            $currentMax = $crop->max_purchase_rate !== null && (float) $crop->max_purchase_rate > 0
                ? (float) $crop->max_purchase_rate
                : null;

            // Previous price = the snapshot immediately before the latest one
            $previous = $rows->count() >= 2 ? $rows[$rows->count() - 2] : null;
            $previousRate = $previous ? (float) $previous->purchase_rate : null;
            $change = $previousRate !== null ? round($current - $previousRate, 2) : null;
            $changePct = ($previousRate !== null && $previousRate > 0) ? round(($change / $previousRate) * 100, 2) : null;
            $direction = $change === null ? 'new' : ($change > 0 ? 'up' : ($change < 0 ? 'down' : 'same'));

            // Keep every distinct update inside the window (so multiple updates on the same day
            // all show up on the farmer's graph!), anchored by the last pre-window rate if any.
            $inWindow = $rows->filter(fn ($r) => $r->effective_at->gte($windowStart))->values();
            $beforeWindow = $rows->filter(fn ($r) => $r->effective_at->lt($windowStart))->last();

            $dateCounts = $inWindow
                ->countBy(fn ($r) => $r->effective_at->copy()->timezone($tz)->toDateString());

            $points = collect();
            if ($beforeWindow) {
                $points->push($this->point($windowStart, $beforeWindow, false));
            }
            foreach ($inWindow as $row) {
                $dateKey = $row->effective_at->copy()->timezone($tz)->toDateString();
                $includeTime = ($dateCounts[$dateKey] ?? 0) > 1;
                $points->push($this->point($row->effective_at, $row, $includeTime));
            }

            // Extend the line up to today if the last recorded change was on an earlier date
            $today = now()->timezone($tz)->toDateString();
            if ($points->isEmpty() || $points->last()['date'] !== $today) {
                $points->push([
                    'date' => $today,
                    'days_ago' => 0,
                    'label' => now()->timezone($tz)->format('d M'),
                    'rate' => $current,
                    'max' => $currentMax,
                ]);
            }

            $rates = $points->pluck('rate');

            // Recent price changes, newest first, each compared with the one before it
            $recent = collect();
            $ordered = $rows->values();
            for ($i = $ordered->count() - 1; $i >= 0 && $recent->count() < 6; $i--) {
                $row = $ordered[$i];
                $prior = $i > 0 ? $ordered[$i - 1] : null;
                $diff = $prior ? round((float) $row->purchase_rate - (float) $prior->purchase_rate, 2) : null;
                $recent->push([
                    'date' => $row->effective_at,
                    'rate' => (float) $row->purchase_rate,
                    'max' => $row->max_purchase_rate !== null && (float) $row->max_purchase_rate > 0 ? (float) $row->max_purchase_rate : null,
                    'change' => $diff,
                    'direction' => $diff === null ? 'new' : ($diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'same')),
                ]);
            }

            return [
                'id' => $crop->id,
                'name' => $crop->name,
                'code' => $crop->code,
                'unit_en' => $crop->shortUnitLabel('en'),
                'unit_hi' => $crop->shortUnitLabel('hi'),
                'current' => $current,
                'current_max' => $currentMax,
                'previous' => $previousRate,
                'previous_at' => $previous?->effective_at,
                'change' => $change,
                'change_pct' => $changePct,
                'direction' => $direction,
                'high' => $rates->max(),
                'low' => $rates->min(),
                'updates_count' => $rows->count(),
                'points' => $points->values()->all(),
                'recent' => $recent->all(),
                'sparkline' => $this->sparkline($rates->all()),
            ];
        })->values()->all();
    }

    private function point(Carbon $date, CropRateHistory $row, bool $includeTime = false): array
    {
        $local = $date->copy()->timezone(config('app.timezone'));
        $daysAgo = (int) max(0, $local->copy()->startOfDay()->diffInDays(now()->timezone(config('app.timezone'))->startOfDay(), false));

        return [
            'date' => $local->toDateString(),
            'days_ago' => $daysAgo,
            'label' => $local->format($includeTime ? 'd M, h:i A' : 'd M'),
            'rate' => (float) $row->purchase_rate,
            'max' => $row->max_purchase_rate !== null && (float) $row->max_purchase_rate > 0
                ? (float) $row->max_purchase_rate
                : null,
        ];
    }

    /** SVG polyline points (100 x 28 box) for a tiny inline sparkline. */
    private function sparkline(array $rates, int $width = 100, int $height = 28): string
    {
        $count = count($rates);
        if ($count === 0) {
            return '';
        }
        if ($count === 1) {
            $rates[] = $rates[0];
            $count = 2;
        }

        $min = min($rates);
        $max = max($rates);
        $span = $max - $min;
        $pad = 3;

        $coords = [];
        foreach (array_values($rates) as $i => $rate) {
            $x = round(($i / ($count - 1)) * $width, 1);
            $y = $span > 0
                ? round($pad + (1 - ($rate - $min) / $span) * ($height - 2 * $pad), 1)
                : round($height / 2, 1);
            $coords[] = "{$x},{$y}";
        }

        return implode(' ', $coords);
    }
}
