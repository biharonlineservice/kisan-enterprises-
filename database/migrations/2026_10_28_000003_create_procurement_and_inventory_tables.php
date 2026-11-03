<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Crop Purchases
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number', 40)->unique();          // e.g. PUR/2026-27/00001
            $table->dateTime('purchase_date');
            $table->foreignId('farmer_id')->constrained('farmers')->restrictOnDelete();
            $table->foreignId('crop_id')->constrained('crops')->restrictOnDelete();
            $table->foreignId('crop_variety_id')->nullable()->constrained('crop_varieties')->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();

            // Weights (in kg to 3 decimal places)
            $table->decimal('gross_weight_kg', 12, 3);
            $table->decimal('tare_weight_kg', 12, 3)->default(0.000);
            $table->decimal('net_weight_kg', 12, 3);                  // gross - tare
            $table->decimal('net_weight_qtl', 12, 3);                 // net_weight_kg / 100

            // Quality & Price
            $table->decimal('moisture_pct', 5, 2)->nullable();
            $table->string('quality_grade', 30)->default('Standard');
            $table->decimal('rate_per_qtl', 12, 2);                   // ₹ per quintal

            // Amounts
            $table->decimal('base_amount', 14, 2);                    // net_weight_qtl * rate_per_qtl
            $table->decimal('deductions_amount', 14, 2)->default(0.00); // moisture cut, dirt/refraction, handling
            $table->string('deduction_reason', 150)->nullable();
            $table->decimal('additions_amount', 14, 2)->default(0.00);  // bonus / incentive
            $table->decimal('net_payable_amount', 14, 2);             // base + additions - deductions
            $table->decimal('paid_amount', 14, 2)->default(0.00);     // total payments recorded
            $table->decimal('balance_due', 14, 2);                    // net_payable - paid_amount

            // Logistics & Status
            $table->string('vehicle_number', 30)->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->string('status', 20)->default('draft')->index();  // draft, finalized, reversed
            $table->text('remarks')->nullable();

            // Reversal / Cancellation audit
            $table->dateTime('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reversal_reason', 255)->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['purchase_date', 'status']);
        });

        // Payments made against a purchase (can be multiple partial payments)
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number', 40)->unique();           // e.g. PV/2026-27/00001
            $table->foreignId('purchase_id')->constrained('purchases')->restrictOnDelete();
            $table->foreignId('farmer_id')->constrained('farmers')->restrictOnDelete();
            $table->dateTime('payment_date');
            $table->decimal('amount', 14, 2);
            $table->string('payment_method', 30)->default('cash');    // cash, bank_transfer, upi, cheque
            $table->string('transaction_reference', 100)->nullable(); // UTR / Cheque No
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->dateTime('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reversal_reason', 255)->nullable();
            $table->timestamps();

            $table->index(['payment_date', 'farmer_id']);
        });

        // Unified Inventory Ledger (Append-only stock movements)
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('crop_id')->constrained('crops')->restrictOnDelete();
            $table->foreignId('crop_variety_id')->nullable()->constrained('crop_varieties')->nullOnDelete();
            $table->dateTime('transaction_date');

            // Quantity in Kg (positive for inward, negative for outward)
            $table->decimal('quantity_kg', 14, 3);
            $table->decimal('quantity_qtl', 14, 3);                   // quantity_kg / 100

            // Source linkage (e.g. Purchase, Sale, Transfer, Adjustment, Reversal)
            $table->string('source_type', 50);                        // purchase, sales_invoice, stock_transfer, stock_adjustment, reversal
            $table->unsignedBigInteger('source_id');
            $table->string('reference_number', 50)->nullable();       // e.g. PUR/2026-27/00001
            $table->string('direction', 10);                          // in, out
            $table->text('description')->nullable();

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['warehouse_id', 'crop_id', 'transaction_date'], 'inv_wh_crop_date_idx');
            $table->index(['source_type', 'source_id']);
            // Prevent duplicate posting for the exact same source document
            $table->unique(['source_type', 'source_id', 'direction'], 'inv_unique_source_direction');
        });

        // Warehouse Stock Transfers
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 40)->unique();          // e.g. TRF/2026-27/00001
            $table->dateTime('transfer_date');
            $table->foreignId('from_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('crop_id')->constrained('crops')->restrictOnDelete();
            $table->foreignId('crop_variety_id')->nullable()->constrained('crop_varieties')->nullOnDelete();
            $table->decimal('quantity_kg', 12, 3);
            $table->decimal('quantity_qtl', 12, 3);
            $table->string('vehicle_number', 30)->nullable();
            $table->string('status', 20)->default('completed');       // completed, cancelled
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });

        // Stock Adjustments & Physical Reconciliation
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number', 40)->unique();        // e.g. ADJ/2026-27/00001
            $table->dateTime('adjustment_date');
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->foreignId('crop_id')->constrained('crops')->restrictOnDelete();
            $table->foreignId('crop_variety_id')->nullable()->constrained('crop_varieties')->nullOnDelete();
            $table->string('adjustment_type', 30);                    // damage, shrinkage, physical_reconciliation, correction
            $table->decimal('quantity_kg', 12, 3);                    // signed: + found, - damaged/lost
            $table->decimal('quantity_qtl', 12, 3);
            $table->string('status', 20)->default('pending')->index(); // pending, approved, rejected
            $table->text('reason');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('inventory_transactions');
        Schema::dropIfExists('purchase_payments');
        Schema::dropIfExists('purchases');
    }
};
