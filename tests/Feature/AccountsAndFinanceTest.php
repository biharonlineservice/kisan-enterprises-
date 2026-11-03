<?php

namespace Tests\Feature;

use App\Models\CashAccount;
use App\Models\Crop;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Farmer;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\FinanceService;
use App\Services\ProcurementService;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountsAndFinanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $accountsManager;

    protected ExpenseCategory $laborCat;
    protected ExpenseCategory $freightCat;
    protected CashAccount $cashTill;
    protected CashAccount $bankAccount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $adminRole = Role::where('name', Role::ADMIN)->first();
        $accRole = Role::where('name', Role::ACCOUNTS)->first();

        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'email' => 'admin@kisan.test']);
        $this->accountsManager = User::factory()->create(['role_id' => $accRole->id, 'email' => 'acc@kisan.test']);

        $this->laborCat = ExpenseCategory::create([
            'name' => 'Labor & Hamali',
            'code' => 'LAB',
            'is_active' => true,
        ]);

        $this->freightCat = ExpenseCategory::create([
            'name' => 'Freight & Transport',
            'code' => 'FRT',
            'is_active' => true,
        ]);

        $this->cashTill = CashAccount::create([
            'name' => 'Main Cash Till',
            'account_type' => 'cash',
            'opening_balance' => '50000.00',
            'current_balance' => '50000.00',
            'is_active' => true,
        ]);

        $this->bankAccount = CashAccount::create([
            'name' => 'SBI Current Account',
            'account_type' => 'bank',
            'account_number' => '12345678901',
            'bank_name' => 'State Bank of India',
            'opening_balance' => '200000.00',
            'current_balance' => '200000.00',
            'is_active' => true,
        ]);
    }

    public function test_record_expense_deducts_cash_account_balance(): void
    {
        $this->actingAs($this->accountsManager);

        $response = $this->post(route('expenses.store'), [
            '_form_token' => 'token-exp-1',
            'expense_date' => now()->toDateString(),
            'expense_category_id' => $this->laborCat->id,
            'cash_account_id' => $this->cashTill->id,
            'amount' => '3500.00',
            'payment_method' => 'cash',
            'paid_to' => 'Babu Lal & Team',
            'remarks' => 'Unloading 500 bags of paddy',
        ]);

        $expense = Expense::first();
        $this->assertNotNull($expense);
        $response->assertRedirect(route('expenses.show', $expense));

        $this->assertSame('3500.00', $expense->amount);
        $this->assertSame(Expense::STATUS_APPROVED, $expense->status);

        // Account balance deducted: 50,000 - 3,500 = 46,500
        $this->cashTill->refresh();
        $this->assertSame('46500.00', $this->cashTill->current_balance);
    }

    public function test_reverse_expense_restores_account_balance(): void
    {
        $expense = (new FinanceService)->recordExpense([
            'expense_date' => now(),
            'expense_category_id' => $this->laborCat->id,
            'cash_account_id' => $this->cashTill->id,
            'amount' => '2000.00',
            'payment_method' => 'cash',
            'paid_to' => 'Test Vendor',
        ], $this->admin->id);

        $this->cashTill->refresh();
        $this->assertSame('48000.00', $this->cashTill->current_balance);

        $this->actingAs($this->admin);

        $response = $this->post(route('expenses.reverse', $expense), [
            '_form_token' => 'token-exp-rev-1',
            'reason' => 'Duplicate expense bill voucher entered by error',
        ]);

        $response->assertRedirect(route('expenses.show', $expense));

        $expense->refresh();
        $this->assertSame(Expense::STATUS_REVERSED, $expense->status);
        $this->assertSame('Duplicate expense bill voucher entered by error', $expense->reversal_reason);

        // Account balance restored back to 50,000.00
        $this->cashTill->refresh();
        $this->assertSame('50000.00', $this->cashTill->current_balance);
    }

    public function test_contra_fund_transfer_between_accounts(): void
    {
        $this->actingAs($this->accountsManager);

        // Transfer 25,000 from SBI Bank to Cash Till (cash withdrawal)
        $response = $this->post(route('accounts.transfer'), [
            '_form_token' => 'token-trf-1',
            'transfer_date' => now()->toDateString(),
            'from_account_id' => $this->bankAccount->id,
            'to_account_id' => $this->cashTill->id,
            'amount' => '25000.00',
            'reference_number' => 'CHQ-889900',
            'notes' => 'Cash withdrawal from bank for daily farmer disbursements',
        ]);

        $response->assertRedirect();

        $this->bankAccount->refresh();
        $this->cashTill->refresh();

        // Bank: 200,000 - 25,000 = 175,000.00
        $this->assertSame('175000.00', $this->bankAccount->current_balance);
        // Cash: 50,000 + 25,000 = 75,000.00
        $this->assertSame('75000.00', $this->cashTill->current_balance);
    }

    public function test_daybook_calculation(): void
    {
        $today = now()->toDateString();

        // Farmer & Warehouse setup
        $wh = Warehouse::create(['name' => 'Godown 1', 'code' => 'G1', 'is_active' => true]);
        $crop = Crop::create(['name' => 'Maize', 'code' => 'MZ', 'unit' => 'Qtl', 'is_active' => true]);
        $farmer = Farmer::create(['farmer_id' => 'FRM01', 'name' => 'Farmer 1', 'is_active' => true, 'created_by' => $this->admin->id]);
        $customer = Customer::create(['customer_id' => 'CUS01', 'name' => 'Buyer 1', 'customer_type' => 'trader', 'is_active' => true, 'created_by' => $this->admin->id]);

        // Inflow 1: Customer Receipt of 40,000.00
        // (Create stock & finalized invoice first)
        (new ProcurementService)->create([
            'purchase_date' => $today,
            'farmer_id' => $farmer->id,
            'crop_id' => $crop->id,
            'warehouse_id' => $wh->id,
            'gross_weight_kg' => '3000.000',
            'tare_weight_kg' => '0.000',
            'rate_per_qtl' => '1500.00',
        ], $this->admin->id, finalize: true);

        $invoice = (new SalesService)->createInvoice([
            'invoice_date' => $today,
            'customer_id' => $customer->id,
        ], [
            [
                'crop_id' => $crop->id,
                'warehouse_id' => $wh->id,
                'quantity_qtl' => '20.000',
                'rate_per_qtl' => '2000.00',
            ],
        ], $this->admin->id, finalize: true);

        (new SalesService)->recordReceipt($invoice, '40000.00', 'bank_transfer', userId: $this->admin->id);

        // Outflow 1: Expense of 1,500.00
        (new FinanceService)->recordExpense([
            'expense_date' => $today,
            'expense_category_id' => $this->laborCat->id,
            'amount' => '1500.00',
            'paid_to' => 'Labor group',
        ], $this->admin->id);

        // Daybook check
        $daybook = (new FinanceService)->getDaybook($today);

        $this->assertSame('40000.00', $daybook['total_inflow']);
        $this->assertSame('1500.00', $daybook['total_outflow']);
        $this->assertSame('38500.00', $daybook['net_flow']);
    }

    public function test_profit_and_loss_calculation(): void
    {
        $today = now()->toDateString();
        $wh = Warehouse::create(['name' => 'Godown 2', 'code' => 'G2', 'is_active' => true]);
        $crop = Crop::create(['name' => 'Barley', 'code' => 'BAR', 'unit' => 'Qtl', 'is_active' => true]);
        $farmer = Farmer::create(['farmer_id' => 'FRM02', 'name' => 'Farmer 2', 'is_active' => true, 'created_by' => $this->admin->id]);
        $customer = Customer::create(['customer_id' => 'CUS02', 'name' => 'Buyer 2', 'customer_type' => 'wholesaler', 'is_active' => true, 'created_by' => $this->admin->id]);

        // Purchase: 100 Qtl @ 1800 = 180,000.00 (COGS)
        (new ProcurementService)->create([
            'purchase_date' => $today,
            'farmer_id' => $farmer->id,
            'crop_id' => $crop->id,
            'warehouse_id' => $wh->id,
            'gross_weight_kg' => '10000.000',
            'tare_weight_kg' => '0.000',
            'rate_per_qtl' => '1800.00',
        ], $this->admin->id, finalize: true);

        // Sale: 80 Qtl @ 2400 = 192,000.00 (Revenue)
        (new SalesService)->createInvoice([
            'invoice_date' => $today,
            'customer_id' => $customer->id,
        ], [
            [
                'crop_id' => $crop->id,
                'warehouse_id' => $wh->id,
                'quantity_qtl' => '80.000',
                'rate_per_qtl' => '2400.00',
            ],
        ], $this->admin->id, finalize: true);

        // Expenses: Labor 2,000.00 + Freight 3,000.00 = 5,000.00
        (new FinanceService)->recordExpense([
            'expense_date' => $today,
            'expense_category_id' => $this->laborCat->id,
            'amount' => '2000.00',
            'paid_to' => 'Laborers',
        ], $this->admin->id);

        (new FinanceService)->recordExpense([
            'expense_date' => $today,
            'expense_category_id' => $this->freightCat->id,
            'amount' => '3000.00',
            'paid_to' => 'Truck agency',
        ], $this->admin->id);

        // Gross Profit = 192,000 - 180,000 = 12,000.00
        // Operating Expenses = 5,000.00
        // Net Profit = 12,000 - 5,000 = 7,000.00
        $pnl = (new FinanceService)->getProfitAndLoss($today, $today);

        $this->assertSame('192000.00', $pnl['total_sales']);
        $this->assertSame('180000.00', $pnl['total_purchases']);
        $this->assertSame('12000.00', $pnl['gross_profit']);
        $this->assertSame('5000.00', $pnl['total_expenses']);
        $this->assertSame('7000.00', $pnl['net_profit']);
    }

    public function test_expenses_index_kpis_tabs_filters_and_export(): void
    {
        $this->actingAs($this->accountsManager);

        // 1. Record Cash Expense
        $expCash = (new FinanceService)->recordExpense([
            'expense_date' => now()->toDateString(),
            'expense_category_id' => $this->laborCat->id,
            'cash_account_id' => $this->cashTill->id,
            'amount' => '4000.00',
            'payment_method' => 'cash',
            'paid_to' => 'Kalu Mandi Labor',
            'remarks' => 'Wheat unloading charges',
        ], $this->admin->id);

        // 2. Record Bank Expense
        $expBank = (new FinanceService)->recordExpense([
            'expense_date' => now()->subDay()->toDateString(),
            'expense_category_id' => $this->freightCat->id,
            'cash_account_id' => $this->bankAccount->id,
            'amount' => '8500.00',
            'payment_method' => 'bank_transfer',
            'paid_to' => 'National Highway Transport',
            'transaction_reference' => 'NEFT-EXP-776655',
            'remarks' => 'Interstate trucking freight',
        ], $this->admin->id);

        // 3. Index View asserts
        $indexResponse = $this->get(route('expenses.index'));
        $indexResponse->assertOk();
        $indexResponse->assertViewHas('kpis');
        $indexResponse->assertViewHas('tabCounts');
        $indexResponse->assertSee($expCash->voucher_number);
        $indexResponse->assertSee($expBank->voucher_number);
        $indexResponse->assertSee('Kalu Mandi Labor');
        $indexResponse->assertSee('National Highway Transport');

        // 4. Tab: Cash
        $cashTab = $this->get(route('expenses.index', ['tab' => 'cash']));
        $cashTab->assertOk();
        $cashTab->assertSee($expCash->voucher_number);
        $cashTab->assertDontSee($expBank->voucher_number);

        // 5. Tab: Bank
        $bankTab = $this->get(route('expenses.index', ['tab' => 'bank']));
        $bankTab->assertOk();
        $bankTab->assertSee($expBank->voucher_number);
        $bankTab->assertDontSee($expCash->voucher_number);

        // 6. Show View
        $showResponse = $this->get(route('expenses.show', $expCash));
        $showResponse->assertOk();
        $showResponse->assertSee($expCash->voucher_number);
        $showResponse->assertSee('Kalu Mandi Labor');

        // 7. Export CSV
        $exportResponse = $this->get(route('expenses.export'));
        $exportResponse->assertOk();
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
    }

    public function test_cash_accounts_index_kpis_tabs_search_and_export(): void
    {
        $this->actingAs($this->accountsManager);

        // 1. Index View asserts (in English)
        $response = $this->withSession(['locale' => 'en'])->get(route('accounts.index'));
        $response->assertOk();
        $response->assertViewHas('totalCash');
        $response->assertViewHas('totalBank');
        $response->assertViewHas('totalLiquidity');
        $response->assertViewHas('counts');
        $response->assertSee('Main Cash Till');
        $response->assertSee('SBI Current Account');
        $response->assertSee('Total Liquid Funds');
        $response->assertSee('Cash in Hand (Galla)');
        $response->assertSee('Bank Account Balances');

        // 2. Tab: Cash
        $cashTab = $this->withSession(['locale' => 'en'])->get(route('accounts.index', ['tab' => 'cash']));
        $cashTab->assertOk();
        $this->assertCount(1, $cashTab->viewData('accounts'));
        $this->assertSame('Main Cash Till', $cashTab->viewData('accounts')->first()->name);

        // 3. Tab: Bank
        $bankTab = $this->withSession(['locale' => 'en'])->get(route('accounts.index', ['tab' => 'bank']));
        $bankTab->assertOk();
        $this->assertCount(1, $bankTab->viewData('accounts'));
        $this->assertSame('SBI Current Account', $bankTab->viewData('accounts')->first()->name);

        // 4. Search Filter
        $searchResponse = $this->withSession(['locale' => 'en'])->get(route('accounts.index', ['q' => 'SBI']));
        $searchResponse->assertOk();
        $this->assertCount(1, $searchResponse->viewData('accounts'));
        $this->assertSame('SBI Current Account', $searchResponse->viewData('accounts')->first()->name);

        // 5. Export CSV
        $exportResponse = $this->withSession(['locale' => 'en'])->get(route('accounts.export'));
        $exportResponse->assertOk();
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
    }

    public function test_daybook_index_kpis_tabs_search_and_export(): void
    {
        $this->actingAs($this->accountsManager);
        $today = now()->toDateString();

        // Setup master entities for transactions
        $wh = Warehouse::create(['name' => 'Daybook Godown', 'code' => 'DBG1', 'is_active' => true]);
        $crop = Crop::create(['name' => 'Wheat', 'code' => 'WHT', 'unit' => 'Qtl', 'is_active' => true]);
        $farmer = Farmer::create(['farmer_id' => 'FRM_DB', 'name' => 'Ramesh Kisan', 'is_active' => true, 'created_by' => $this->admin->id]);
        $customer = Customer::create(['customer_id' => 'CUS_DB', 'name' => 'Shyam Trading Co', 'customer_type' => 'trader', 'is_active' => true, 'created_by' => $this->admin->id]);

        // 1. Purchase & Payment
        $purchase = (new ProcurementService)->create([
            'purchase_date' => $today,
            'farmer_id' => $farmer->id,
            'crop_id' => $crop->id,
            'warehouse_id' => $wh->id,
            'gross_weight_kg' => '2000.000',
            'tare_weight_kg' => '0.000',
            'rate_per_qtl' => '2000.00',
        ], $this->admin->id, finalize: true);

        (new ProcurementService)->recordPayment(
            $purchase,
            '10000.00',
            'cash',
            'TXN-FRM-01',
            'Farmer partial payout',
            $this->admin->id
        );

        // 2. Sale Invoice & Customer Receipt
        $invoice = (new SalesService)->createInvoice([
            'invoice_date' => $today,
            'customer_id' => $customer->id,
        ], [
            [
                'crop_id' => $crop->id,
                'warehouse_id' => $wh->id,
                'quantity_qtl' => '10.000',
                'rate_per_qtl' => '2500.00',
            ],
        ], $this->admin->id, finalize: true);

        (new SalesService)->recordReceipt(
            $invoice,
            '15000.00',
            'cash',
            'TXN-CUS-01',
            null,
            'Customer advance receipt',
            $this->admin->id
        );

        // 3. Operating Expense
        $cat = ExpenseCategory::create(['name' => 'Mandi Loading', 'code' => 'ML01', 'is_active' => true]);
        (new FinanceService)->recordExpense([
            'expense_date' => $today,
            'expense_category_id' => $cat->id,
            'cash_account_id' => $this->cashTill->id,
            'amount' => '2000.00',
            'payment_method' => 'cash',
            'paid_to' => 'Palledar Union',
            'remarks' => 'Loading charges',
        ], $this->admin->id);

        // 4. Index View check (English)
        $response = $this->withSession(['locale' => 'en'])->get(route('accounts.daybook', ['date' => $today]));
        $response->assertOk();
        $response->assertViewHas('daybook');
        $response->assertViewHas('counts');
        $response->assertViewHas('totalReceipts');
        $response->assertViewHas('totalPayments');
        $response->assertViewHas('totalExpenses');
        $response->assertSee('Daily Financial Daybook (Roznamcha)');
        $response->assertSee('Daily Inflows (Receipts)');
        $response->assertSee('Farmer Payouts');
        $response->assertSee('Operating Expenses');
        $response->assertSee('Net Daily Cash Flow');
        $response->assertSee('Shyam Trading Co');
        $response->assertSee('Ramesh Kisan');
        $response->assertSee('Palledar Union');

        // 5. Tab: Customer Receipts
        $receiptsTab = $this->withSession(['locale' => 'en'])->get(route('accounts.daybook', ['date' => $today, 'tab' => 'receipts']));
        $receiptsTab->assertOk();
        $this->assertCount(1, $receiptsTab->viewData('daybook')['entries']);
        $this->assertSame('Customer Receipt', $receiptsTab->viewData('daybook')['entries']->first()['type']);

        // 6. Tab: Farmer Payments
        $paymentsTab = $this->withSession(['locale' => 'en'])->get(route('accounts.daybook', ['date' => $today, 'tab' => 'payments']));
        $paymentsTab->assertOk();
        $this->assertCount(1, $paymentsTab->viewData('daybook')['entries']);
        $this->assertSame('Farmer Payment', $paymentsTab->viewData('daybook')['entries']->first()['type']);

        // 7. Tab: Expenses
        $expensesTab = $this->withSession(['locale' => 'en'])->get(route('accounts.daybook', ['date' => $today, 'tab' => 'expenses']));
        $expensesTab->assertOk();
        $this->assertCount(1, $expensesTab->viewData('daybook')['entries']);
        $this->assertSame('Operating Expense', $expensesTab->viewData('daybook')['entries']->first()['type']);

        // 8. Search query
        $searchResponse = $this->withSession(['locale' => 'en'])->get(route('accounts.daybook', ['date' => $today, 'q' => 'Palledar']));
        $searchResponse->assertOk();
        $this->assertCount(1, $searchResponse->viewData('daybook')['entries']);
        $this->assertSame('Palledar Union', $searchResponse->viewData('daybook')['entries']->first()['party']);

        // 9. Export CSV
        $exportResponse = $this->withSession(['locale' => 'en'])->get(route('accounts.daybook.export', ['date' => $today]));
        $exportResponse->assertOk();
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
        $content = $exportResponse->streamedContent();
        $this->assertStringContainsString('Shyam Trading Co', $content);
        $this->assertStringContainsString('Ramesh Kisan', $content);
        $this->assertStringContainsString('Palledar Union', $content);
    }

    public function test_profit_and_loss_index_kpis_presets_and_export(): void
    {
        $this->actingAs($this->accountsManager);
        $today = now()->toDateString();

        // 1. Setup sample purchase and sale to generate revenue and COGS
        $wh = Warehouse::create(['name' => 'P&L Godown', 'code' => 'PL01', 'is_active' => true]);
        $crop = Crop::create(['name' => 'Soybean', 'code' => 'SOY', 'unit' => 'Qtl', 'is_active' => true]);
        $farmer = Farmer::create(['farmer_id' => 'FRM_PL', 'name' => 'Kishan Lal', 'is_active' => true, 'created_by' => $this->admin->id]);
        $customer = Customer::create(['customer_id' => 'CUS_PL', 'name' => 'Kisan Solvent Extractions', 'customer_type' => 'mill', 'is_active' => true, 'created_by' => $this->admin->id]);

        // Purchase: 10 Qtl @ 3000 = 30,000.00
        (new ProcurementService)->create([
            'purchase_date' => $today,
            'farmer_id' => $farmer->id,
            'crop_id' => $crop->id,
            'warehouse_id' => $wh->id,
            'gross_weight_kg' => '1000.000',
            'tare_weight_kg' => '0.000',
            'rate_per_qtl' => '3000.00',
        ], $this->admin->id, finalize: true);

        // Sale: 10 Qtl @ 4000 = 40,000.00
        (new SalesService)->createInvoice([
            'invoice_date' => $today,
            'customer_id' => $customer->id,
        ], [
            [
                'crop_id' => $crop->id,
                'warehouse_id' => $wh->id,
                'quantity_qtl' => '10.000',
                'rate_per_qtl' => '4000.00',
            ],
        ], $this->admin->id, finalize: true);

        // Expense: 2,500.00
        $cat = ExpenseCategory::create(['name' => 'Factory Power', 'code' => 'PWR', 'is_active' => true]);
        (new FinanceService)->recordExpense([
            'expense_date' => $today,
            'expense_category_id' => $cat->id,
            'cash_account_id' => $this->cashTill->id,
            'amount' => '2500.00',
            'payment_method' => 'cash',
            'paid_to' => 'Electricity Board',
            'remarks' => 'Monthly meter bill',
        ], $this->admin->id);

        // 2. Index View check (English)
        $response = $this->withSession(['locale' => 'en'])->get(route('accounts.profit_and_loss'));
        $response->assertOk();
        $response->assertViewHas('report');
        $response->assertViewHas('from');
        $response->assertViewHas('to');
        $response->assertViewHas('period');
        $response->assertViewHas('grossMarginPct');
        $response->assertViewHas('netMarginPct');
        $response->assertSee('Profit & Loss Statement');
        $response->assertSee('Total Revenue (Sales)');
        $response->assertSee('Cost of Goods Sold (COGS)');
        $response->assertSee('Gross Trading Profit');
        $response->assertSee('Net Operating Profit / (Loss)');
        $response->assertSee('Factory Power');

        // 3. Preset periods
        $fyResponse = $this->withSession(['locale' => 'en'])->get(route('accounts.profit_and_loss', ['period' => 'this_fy']));
        $fyResponse->assertOk();
        $this->assertSame('this_fy', $fyResponse->viewData('period'));

        $lastMonthResponse = $this->withSession(['locale' => 'en'])->get(route('accounts.profit_and_loss', ['period' => 'last_month']));
        $lastMonthResponse->assertOk();
        $this->assertSame('last_month', $lastMonthResponse->viewData('period'));

        // 4. Export CSV
        $exportResponse = $this->withSession(['locale' => 'en'])->get(route('accounts.profit_and_loss.export', ['period' => 'this_month']));
        $exportResponse->assertOk();
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
        $content = $exportResponse->streamedContent();
        $this->assertStringContainsString('Profit & Loss Statement', $content);
        $this->assertStringContainsString('1. REVENUE FROM OPERATIONS', $content);
        $this->assertStringContainsString('2. COST OF GOODS SOLD (COGS)', $content);
        $this->assertStringContainsString('Factory Power', $content);
    }
}
