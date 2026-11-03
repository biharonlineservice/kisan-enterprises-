<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->decimal('bonus_percentage', 5, 2)->default(0.00)->after('amount');
            $table->decimal('bonus_amount', 14, 2)->default(0.00)->after('bonus_percentage');
            $table->decimal('total_payout', 14, 2)->default(0.00)->after('bonus_amount');
            $table->unsignedInteger('days_delayed')->default(0)->after('total_payout');
            $table->string('bonus_calculation_mode', 20)->default('monthly')->after('days_delayed');
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('delayed_payment_bonus_rate', 5, 2)->nullable()->after('rate_per_qtl');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_payments', function (Blueprint $table) {
            $table->dropColumn(['bonus_percentage', 'bonus_amount', 'total_payout', 'days_delayed', 'bonus_calculation_mode']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('delayed_payment_bonus_rate');
        });
    }
};

