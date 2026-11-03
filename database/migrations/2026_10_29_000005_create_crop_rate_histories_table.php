<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crop_rate_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained('crops')->cascadeOnDelete();
            $table->decimal('purchase_rate', 10, 2);
            $table->decimal('max_purchase_rate', 10, 2)->nullable();
            $table->string('rate_note', 120)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('effective_at');
            $table->timestamps();

            $table->index(['crop_id', 'effective_at']);
        });

        // Backfill historical rate progression ending at each crop's current buying price
        // so farmers can immediately inspect past mandi rate trends on the graph.
        $now = now();
        $historicalDeltasByCode = [
            'PADDY' => [-120, -75, -40],
            'WHEAT' => [-95, -50, -35],
            'MAIZE' => [-60, +25, +30],
            'MUSTARD' => [-180, -110, -65],
        ];
        $daysAgoSteps = [24, 15, 6];

        DB::table('crops')
            ->whereNotNull('current_purchase_rate')
            ->where('current_purchase_rate', '>', 0)
            ->orderBy('id')
            ->get(['id', 'code', 'current_purchase_rate', 'max_purchase_rate', 'rate_note', 'rate_updated_at'])
            ->each(function ($crop) use ($now, $historicalDeltasByCode, $daysAgoSteps) {
                $baseRate = (float) $crop->current_purchase_rate;
                $maxRate = $crop->max_purchase_rate !== null ? (float) $crop->max_purchase_rate : null;
                $deltas = $historicalDeltasByCode[$crop->code] ?? null;

                if ($deltas) {
                    foreach ($deltas as $idx => $delta) {
                        $pastMin = max(100, round($baseRate + $delta, 2));
                        $pastMax = $maxRate !== null ? max($pastMin, round($maxRate + $delta, 2)) : null;
                        $pastDate = $now->copy()->subDays($daysAgoSteps[$idx])->setTime(10, 30);

                        DB::table('crop_rate_histories')->insert([
                            'crop_id' => $crop->id,
                            'purchase_rate' => $pastMin,
                            'max_purchase_rate' => $pastMax,
                            'rate_note' => $crop->rate_note,
                            'user_id' => null,
                            'effective_at' => $pastDate,
                            'created_at' => $pastDate,
                            'updated_at' => $pastDate,
                        ]);
                    }
                }

                $effectiveNow = $crop->rate_updated_at ?? $now;
                DB::table('crop_rate_histories')->insert([
                    'crop_id' => $crop->id,
                    'purchase_rate' => $crop->current_purchase_rate,
                    'max_purchase_rate' => $crop->max_purchase_rate,
                    'rate_note' => $crop->rate_note,
                    'user_id' => null,
                    'effective_at' => $effectiveNow,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('crop_rate_histories');
    }
};
