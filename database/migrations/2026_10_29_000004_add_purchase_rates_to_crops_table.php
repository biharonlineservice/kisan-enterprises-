<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crops', function (Blueprint $table) {
            $table->decimal('current_purchase_rate', 10, 2)->nullable()->after('hsn_code');
            $table->decimal('max_purchase_rate', 10, 2)->nullable()->after('current_purchase_rate');
            $table->string('rate_note', 120)->nullable()->after('max_purchase_rate');
            $table->boolean('show_rate_on_website')->default(true)->after('rate_note');
            $table->timestamp('rate_updated_at')->nullable()->after('show_rate_on_website');
        });

        // Seed sensible initial purchase prices for existing standard crops if not set yet
        $defaults = [
            'PADDY' => ['min' => 2300.00, 'max' => 2450.00, 'note' => 'FAQ / Grade A Clean'],
            'WHEAT' => ['min' => 2425.00, 'max' => 2600.00, 'note' => 'Milling & Sharbati'],
            'MAIZE' => ['min' => 2150.00, 'max' => 2250.00, 'note' => 'Moisture <= 14%'],
            'MUSTARD' => ['min' => 5650.00, 'max' => 5850.00, 'note' => 'High Oil Grade'],
        ];

        $now = now();
        foreach ($defaults as $code => $rate) {
            DB::table('crops')
                ->where('code', $code)
                ->whereNull('current_purchase_rate')
                ->update([
                    'current_purchase_rate' => $rate['min'],
                    'max_purchase_rate' => $rate['max'],
                    'rate_note' => $rate['note'],
                    'show_rate_on_website' => true,
                    'rate_updated_at' => $now,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('crops', function (Blueprint $table) {
            $table->dropColumn([
                'current_purchase_rate',
                'max_purchase_rate',
                'rate_note',
                'show_rate_on_website',
                'rate_updated_at',
            ]);
        });
    }
};

