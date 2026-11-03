<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->boolean('delayed_bonus_enabled')->default(true)->after('opening_balance');
            $table->decimal('delayed_bonus_rate', 5, 2)->nullable()->after('delayed_bonus_enabled');
            $table->string('delayed_bonus_mode', 20)->nullable()->after('delayed_bonus_rate');
        });
    }

    public function down(): void
    {
        Schema::table('farmers', function (Blueprint $table) {
            $table->dropColumn(['delayed_bonus_enabled', 'delayed_bonus_rate', 'delayed_bonus_mode']);
        });
    }
};

