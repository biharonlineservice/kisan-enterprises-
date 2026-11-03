<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sales Orders
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->date('order_date')->index();
            $table->foreignId('customer_id')->constrained('customers');
            $table->string('status', 20)->default('draft')->index(); // draft, confirmed, dispatched, completed, cancelled
            $table->decimal('total_qtl', 12, 3)->default(0.000);
            $table->decimal('total_amount', 14, 2)->default(0.00);
            $table->text('delivery_address')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // 2. Sales Order Items
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained('sales_orders')->cascadeOnDelete();
            $table->foreignId('crop_id')->constrained('crops');
            $table->foreignId('crop_variety_id')->nullable()->constrained('crop_varieties');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->decimal('quantity_qtl', 12, 3);
            $table->decimal('rate_per_qtl', 10, 2);
            $table->decimal('amount', 14, 2);
            $table->timestamps();
        });

        // 3. Sales Invoices
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number', 50)->unique();
            $table->date('invoice_date')->index();
            $table->date('due_date')->nullable();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('sales_order_id')->nullable()->constrained('sales_orders');
            $table->string('status', 20)->default('draft')->index(); // draft, finalized, reversed
            $table->decimal('subtotal_amount', 14, 2)->default(0.00);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 14, 2)->default(0.00);
            $table->decimal('discount_amount', 14, 2)->default(0.00);
            $table->decimal('round_off', 8, 2)->default(0.00);
            $table->decimal('total_amount', 14, 2)->default(0.00);
            $table->decimal('paid_amount', 14, 2)->default(0.00);
            $table->decimal('balance_due', 14, 2)->default(0.00)->index();

            // Dispatch / logistics
            $table->string('vehicle_number', 30)->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->string('transporter_name', 100)->nullable();
            $table->string('eway_bill_number', 50)->nullable();
            $table->string('delivery_destination', 255)->nullable();

            // Reversal
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users');
            $table->string('reversal_reason', 255)->nullable();

            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        // 4. Sales Invoice Items
        Schema::create('sales_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices')->cascadeOnDelete();
            $table->foreignId('crop_id')->constrained('crops');
            $table->foreignId('crop_variety_id')->nullable()->constrained('crop_varieties');
            $table->foreignId('warehouse_id')->constrained('warehouses');
            $table->decimal('quantity_kg', 12, 3);
            $table->decimal('quantity_qtl', 12, 3);
            $table->decimal('rate_per_qtl', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('tax_amount', 14, 2)->default(0.00);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();
        });

        // 5. Customer Receipts
        Schema::create('customer_receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number', 50)->unique();
            $table->date('receipt_date')->index();
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('sales_invoice_id')->constrained('sales_invoices');
            $table->decimal('amount', 14, 2);
            $table->string('payment_method', 30); // cash, bank_transfer, neft_rtgs, upi, cheque
            $table->string('transaction_reference', 100)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users');
            $table->string('reversal_reason', 255)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_receipts');
        Schema::dropIfExists('sales_invoice_items');
        Schema::dropIfExists('sales_invoices');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};
