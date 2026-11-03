<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expense Categories
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 30)->unique();
            $table->string('description', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Cash and Bank Accounts
        Schema::create('cash_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('account_type', 20)->default('bank'); // cash, bank
            $table->string('account_number', 50)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('ifsc_code', 20)->nullable();
            $table->decimal('opening_balance', 14, 2)->default(0.00);
            $table->decimal('current_balance', 14, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Expenses
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number', 50)->unique();
            $table->date('expense_date')->index();
            $table->foreignId('expense_category_id')->constrained('expense_categories');
            $table->foreignId('cash_account_id')->nullable()->constrained('cash_accounts');
            $table->decimal('amount', 14, 2);
            $table->string('payment_method', 30)->default('cash');
            $table->string('paid_to', 150);
            $table->string('transaction_reference', 100)->nullable();
            $table->string('status', 20)->default('approved')->index(); // approved, reversed
            $table->timestamp('reversed_at')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users');
            $table->string('reversal_reason', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // 4. Contra / Account Transfers
        Schema::create('account_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_number', 50)->unique();
            $table->date('transfer_date')->index();
            $table->foreignId('from_account_id')->constrained('cash_accounts');
            $table->foreignId('to_account_id')->constrained('cash_accounts');
            $table->decimal('amount', 14, 2);
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transfers');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('cash_accounts');
        Schema::dropIfExists('expense_categories');
    }
};
