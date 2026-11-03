<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->string('delivery_status', 30)->default('in_transit')->index()->after('delivery_destination');
            $table->timestamp('delivered_at')->nullable()->after('delivery_status');
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete()->after('delivered_at');
            $table->string('received_by', 100)->nullable()->after('delivered_by');
            $table->text('delivery_notes')->nullable()->after('received_by');
        });
    }

    public function down(): void
    {
        Schema::table('sales_invoices', function (Blueprint $table) {
            $table->dropForeign(['delivered_by']);
            $table->dropColumn([
                'delivery_status',
                'delivered_at',
                'delivered_by',
                'received_by',
                'delivery_notes',
            ]);
        });
    }
};

