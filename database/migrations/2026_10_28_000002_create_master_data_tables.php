<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Crops (e.g. Paddy / Dhan, Wheat, Maize, Mustard, Pulses)
        Schema::create('crops', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->string('unit', 20)->default('quintal');       // quintal = 100 kg
            $table->string('hsn_code', 20)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // Crop varieties (e.g. Sona Masoori, Katarni, Basmati 1121, Sharbati)
        Schema::create('crop_varieties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crop_id')->constrained('crops')->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('code', 30)->nullable();
            $table->decimal('standard_moisture_pct', 5, 2)->default(14.00); // benchmark moisture %
            $table->string('default_grade', 20)->default('Grade A');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['crop_id', 'name']);
        });

        // Storage warehouses and godowns
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->string('address', 255)->nullable();
            $table->decimal('capacity_quintals', 12, 2)->default(0.00);
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        // Farmer registry
        Schema::create('farmers', function (Blueprint $table) {
            $table->id();
            $table->string('farmer_id', 30)->unique();             // e.g. FRM00001
            $table->string('name', 120);
            $table->string('mobile', 15)->nullable()->index();
            $table->string('address', 255)->nullable();
            $table->string('village', 100)->nullable()->index();
            $table->string('panchayat', 100)->nullable();
            $table->string('block', 100)->nullable()->index();
            $table->string('district', 100)->nullable();
            $table->string('state', 100)->nullable();
            
            // Restricted bank & ID details (collected only when required)
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_branch', 100)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_ifsc', 20)->nullable();
            $table->string('id_type', 30)->nullable();             // Aadhaar, Voter ID, Kisan Credit Card
            $table->string('id_number', 50)->nullable();

            $table->date('registration_date')->nullable();
            $table->decimal('opening_balance', 14, 2)->default(0.00); // (+) payable to farmer, (-) advance
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Customers: rice mills, grain wholesalers, traders, market buyers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_id', 30)->unique();           // e.g. CUS00001
            $table->string('name', 150);                           // Business or individual name
            $table->string('customer_type', 40)->default('rice_mill'); // rice_mill, wholesaler, trader, market_buyer, other
            $table->string('contact_person', 100)->nullable();
            $table->string('mobile', 15)->nullable()->index();
            $table->string('alt_phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('state', 100)->nullable();
            $table->string('gstin', 20)->nullable();               // Do not assume registered
            $table->string('pan', 20)->nullable();
            $table->decimal('credit_limit', 14, 2)->default(0.00);
            $table->unsignedInteger('payment_terms_days')->default(15);
            $table->decimal('opening_balance', 14, 2)->default(0.00); // (+) receivable from customer, (-) customer deposit
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
        Schema::dropIfExists('farmers');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('crop_varieties');
        Schema::dropIfExists('crops');
    }
};
