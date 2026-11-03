<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\Farmer;
use App\Models\InventoryTransaction;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\Role;
use App\Models\StockAdjustment;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\InventoryService;
use App\Services\ProcurementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProcurementAndInventoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $purchaseManager;
    protected User $accountsManager;

    protected Farmer $farmer;
    protected Crop $paddy;
    protected CropVariety $variety;
    protected Warehouse $mainWh;
    protected Warehouse $branchWh;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $adminRole = Role::where('name', Role::ADMIN)->first();
        $pmRole = Role::where('name', Role::PURCHASE_SALES)->first();
        $accRole = Role::where('name', Role::ACCOUNTS)->first();

        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'email' => 'admin@kisan.test']);
        $this->purchaseManager = User::factory()->create(['role_id' => $pmRole->id, 'email' => 'pm@kisan.test']);
        $this->accountsManager = User::factory()->create(['role_id' => $accRole->id, 'email' => 'acc@kisan.test']);

        $this->paddy = Crop::create([
            'name' => 'Paddy (Dhan)',
            'code' => 'PAD',
            'unit' => 'Qtl',
            'standard_moisture_pct' => 14.00,
            'is_active' => true,
        ]);

        $this->variety = CropVariety::create([
            'crop_id' => $this->paddy->id,
            'name' => 'Basmati 1121',
            'code' => 'B1121',
            'is_active' => true,
        ]);

        $this->mainWh = Warehouse::create([
            'name' => 'Main Godown',
            'code' => 'WH01',
            'capacity_qtl' => 5000.000,
            'is_active' => true,
        ]);

        $this->branchWh = Warehouse::create([
            'name' => 'Station Depot',
            'code' => 'WH02',
            'capacity_qtl' => 2500.000,
            'is_active' => true,
        ]);

        $this->farmer = Farmer::create([
            'farmer_id' => 'FRM00001',
            'name' => 'Ram Kumar',
            'mobile' => '9876543210',
            'village' => 'Kisanpur',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_purchase_calculation_service(): void
    {
        // 5500 kg gross - 500 kg tare = 5000 kg net = 50 Qtl
        // Rate: 2200 per Qtl -> Base: 50 * 2200 = 110,000.00
        // Deductions: 1000.00, Additions: 500.00 -> Net Payable = 109,500.00
        $calc = ProcurementService::calculate(
            grossKg: '5500.000',
            tareKg: '500.000',
            ratePerQtl: '2200.00',
            deductions: '1000.00',
            additions: '500.00',
            paidAmount: '20000.00'
        );

        $this->assertSame('5000.000', $calc['net_weight_kg']);
        $this->assertSame('50.000', $calc['net_weight_qtl']);
        $this->assertSame('110000.00', $calc['base_amount']);
        $this->assertSame('109500.00', $calc['net_payable_amount']);
        $this->assertSame('89500.00', $calc['balance_due']);
    }

    public function test_purchase_manager_can_create_draft_purchase(): void
    {
        $this->actingAs($this->purchaseManager);

        $response = $this->post(route('purchases.store'), [
            '_form_token' => 'token-test-1',
            'purchase_date' => now()->toDateString(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'crop_variety_id' => $this->variety->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '3250.000',
            'tare_weight_kg' => '250.000',
            'rate_per_qtl' => '2400.00',
            'quality_grade' => 'Grade A',
            'deductions_amount' => '0.00',
            'additions_amount' => '0.00',
            'action' => 'draft',
        ]);

        $purchase = Purchase::first();
        $this->assertNotNull($purchase);
        $response->assertRedirect(route('purchases.show', $purchase));

        $this->assertSame(Purchase::STATUS_DRAFT, $purchase->status);
        $this->assertSame('3000.000', $purchase->net_weight_kg);
        $this->assertSame('30.000', $purchase->net_weight_qtl);
        $this->assertSame('72000.00', $purchase->net_payable_amount);
        $this->assertSame('72000.00', $purchase->balance_due);

        // Draft does NOT create inventory transactions
        $this->assertSame(0, InventoryTransaction::count());
    }

    public function test_finalizing_purchase_posts_inventory_ledger_entry(): void
    {
        $this->actingAs($this->purchaseManager);

        $purchase = (new ProcurementService)->create([
            'purchase_date' => now(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'crop_variety_id' => $this->variety->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '5200.000',
            'tare_weight_kg' => '200.000',
            'rate_per_qtl' => '2500.00',
        ], $this->purchaseManager->id);

        $this->assertSame(Purchase::STATUS_DRAFT, $purchase->status);

        // Finalize with initial payment of 25,000.00
        $response = $this->post(route('purchases.finalize', $purchase), [
            '_form_token' => 'token-test-2',
            'payment_amount' => '25000.00',
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('purchases.show', $purchase));

        $purchase->refresh();
        $this->assertSame(Purchase::STATUS_FINALIZED, $purchase->status);
        $this->assertSame('50.000', $purchase->net_weight_qtl);
        $this->assertSame('125000.00', $purchase->net_payable_amount);
        $this->assertSame('25000.00', $purchase->paid_amount);
        $this->assertSame('100000.00', $purchase->balance_due);

        // Inventory ledger check
        $stockTx = InventoryTransaction::where('source_id', $purchase->id)->first();
        $this->assertNotNull($stockTx);
        $this->assertSame('in', $stockTx->direction);
        $this->assertSame('50.000', $stockTx->quantity_qtl);
        $this->assertSame($this->mainWh->id, $stockTx->warehouse_id);

        // Real-time stock balance check
        $currentStock = InventoryTransaction::getStock($this->mainWh->id, $this->paddy->id, $this->variety->id);
        $this->assertSame('50.000', $currentStock);
    }

    public function test_recording_subsequent_farmer_payment(): void
    {
        $purchase = (new ProcurementService)->create([
            'purchase_date' => now(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '1100.000',
            'tare_weight_kg' => '100.000',
            'rate_per_qtl' => '2000.00', // 10 Qtl * 2000 = 20,000.00
        ], $this->admin->id, finalize: true);

        $this->flushSession();
        $this->actingAs($this->accountsManager);

        $response = $this->post(route('farmer-payments.store'), [
            '_form_token' => 'token-test-3',
            'purchase_id' => $purchase->id,
            'amount' => '15000.00',
            'payment_method' => 'bank_transfer',
            'transaction_reference' => 'UTR99887766',
        ]);

        $payment = PurchasePayment::where('purchase_id', $purchase->id)->first();
        $this->assertNotNull($payment);
        $response->assertRedirect(route('farmer-payments.show', $payment));

        $purchase->refresh();
        $this->assertSame('15000.00', $purchase->paid_amount);
        $this->assertSame('5000.00', $purchase->balance_due);
    }

    public function test_reversing_finalized_purchase_reverses_inventory(): void
    {
        $this->actingAs($this->admin);

        $purchase = (new ProcurementService)->create([
            'purchase_date' => now(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '2100.000',
            'tare_weight_kg' => '100.000',
            'rate_per_qtl' => '2000.00',
        ], $this->admin->id, finalize: true);

        // Pre-reversal stock is 20 Qtl
        $this->assertSame('20.000', InventoryTransaction::getStock($this->mainWh->id, $this->paddy->id));

        $response = $this->post(route('purchases.reverse', $purchase), [
            '_form_token' => 'token-test-4',
            'reason' => 'Truck weighment duplicate entry corrected',
        ]);

        $response->assertRedirect(route('purchases.show', $purchase));

        $purchase->refresh();
        $this->assertSame(Purchase::STATUS_REVERSED, $purchase->status);
        $this->assertSame('Truck weighment duplicate entry corrected', $purchase->reversal_reason);

        // Stock must now be zero
        $this->assertSame('0.000', InventoryTransaction::getStock($this->mainWh->id, $this->paddy->id));
    }

    public function test_stock_transfer_between_warehouses(): void
    {
        $this->actingAs($this->purchaseManager);

        // First stock 40 Qtl into Main Warehouse
        (new ProcurementService)->create([
            'purchase_date' => now(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '4100.000',
            'tare_weight_kg' => '100.000',
            'rate_per_qtl' => '2000.00',
        ], $this->purchaseManager->id, finalize: true);

        // Transfer 15 Qtl (1500 kg) from Main Godown to Station Depot
        $response = $this->post(route('stock-transfers.store'), [
            '_form_token' => 'token-test-5',
            'transfer_date' => now()->toDateString(),
            'from_warehouse_id' => $this->mainWh->id,
            'to_warehouse_id' => $this->branchWh->id,
            'crop_id' => $this->paddy->id,
            'quantity_kg' => '1500.000',
            'vehicle_number' => 'UP 32 CD 9999',
            'notes' => 'Transfer to branch depot for processing',
        ]);

        $transfer = StockTransfer::first();
        $this->assertNotNull($transfer);
        $response->assertRedirect(route('stock-transfers.show', $transfer));

        // Source warehouse stock: 40 - 15 = 25 Qtl
        $this->assertSame('25.000', InventoryTransaction::getStock($this->mainWh->id, $this->paddy->id));

        // Destination warehouse stock: 15 Qtl
        $this->assertSame('15.000', InventoryTransaction::getStock($this->branchWh->id, $this->paddy->id));

        // Test stock transfer challan view & pdf
        $challanView = $this->get(route('stock-transfers.challan', $transfer));
        $challanView->assertOk();
        $challanView->assertSee('STOCK TRANSFER CHALLAN');
        $challanView->assertSee('UP 32 CD 9999');

        $challanPdf = $this->get(route('stock-transfers.challan.pdf', $transfer));
        $challanPdf->assertOk();
        $this->assertStringContainsString('application/pdf', $challanPdf->headers->get('Content-Type'));
    }

    public function test_negative_stock_transfer_is_blocked(): void
    {
        $this->actingAs($this->purchaseManager);

        // Main Godown currently has 0 Qtl. Attempting to transfer 1000 kg (10 Qtl) should fail.
        $response = $this->post(route('stock-transfers.store'), [
            '_form_token' => 'token-test-6',
            'transfer_date' => now()->toDateString(),
            'from_warehouse_id' => $this->mainWh->id,
            'to_warehouse_id' => $this->branchWh->id,
            'crop_id' => $this->paddy->id,
            'quantity_kg' => '1000.000',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, StockTransfer::count());
    }

    public function test_stock_adjustment_approval_workflow(): void
    {
        $this->actingAs($this->purchaseManager);

        // Request adjustment: -100 kg shrinkage
        $response = $this->post(route('stock-adjustments.store'), [
            '_form_token' => 'token-test-7',
            'adjustment_date' => now()->toDateString(),
            'warehouse_id' => $this->mainWh->id,
            'crop_id' => $this->paddy->id,
            'adjustment_type' => 'shrinkage',
            'quantity_kg' => '-100.000',
            'reason' => 'Moisture shrinkage during summer storage',
        ]);

        $adj = StockAdjustment::first();
        $this->assertNotNull($adj);
        $response->assertRedirect(route('stock-adjustments.show', $adj));
        $this->assertSame(StockAdjustment::STATUS_PENDING, $adj->status);

        // Pending adjustment has NOT affected inventory
        $this->assertSame('0.000', InventoryTransaction::getStock($this->mainWh->id, $this->paddy->id));

        // Admin approves the adjustment
        $this->flushSession();
        $this->actingAs($this->admin);

        $approveResponse = $this->post(route('stock-adjustments.approve', $adj), [
            '_form_token' => 'token-test-8',
        ]);

        $approveResponse->assertRedirect(route('stock-adjustments.show', $adj));
        $adj->refresh();
        $this->assertSame(StockAdjustment::STATUS_APPROVED, $adj->status);

        // Inventory transaction posted
        $adjTx = InventoryTransaction::where('source_type', 'stock_adjustment')->where('source_id', $adj->id)->first();
        $this->assertNotNull($adjTx);
        $this->assertSame('-1.000', $adjTx->quantity_qtl);
    }

    public function test_finalize_and_sell_redirects_to_sales_invoice_with_prefilled_purchase_data(): void
    {
        $this->actingAs($this->purchaseManager);

        $response = $this->post(route('purchases.store'), [
            '_form_token' => 'token-test-direct-sale',
            'action' => 'finalize_sell',
            'purchase_date' => now()->format('Y-m-d'),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'crop_variety_id' => $this->variety->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '5200.000',
            'tare_weight_kg' => '200.000',
            'rate_per_qtl' => '2100.00',
        ]);

        $purchase = Purchase::first();
        $this->assertNotNull($purchase);
        $this->assertTrue($purchase->isFinalized());
        $this->assertSame('50.000', $purchase->net_weight_qtl);

        $response->assertRedirect(route('sales-invoices.create', ['purchase_id' => $purchase->id]));

        // Visiting sales invoice create with purchase_id renders pre-filled purchase details
        $formResponse = $this->get(route('sales-invoices.create', ['purchase_id' => $purchase->id]));
        $formResponse->assertOk();
        $formResponse->assertSee($purchase->purchase_number);
        $formResponse->assertSee('50.000');
    }

    public function test_delayed_payment_bonus_calculation_and_voucher_rendering(): void
    {
        $purchaseDate = now()->subDays(45)->toDateString();

        // 25 Qtl @ 2000 = 50,000.00 payable, 0 paid initially
        $purchase = (new ProcurementService)->create([
            'purchase_date' => $purchaseDate,
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '2600.000',
            'tare_weight_kg' => '100.000',
            'rate_per_qtl' => '2000.00',
        ], $this->admin->id, finalize: true);

        $this->flushSession();
        $this->actingAs($this->accountsManager);

        // Create form renders with purchase date metadata and bonus section
        $createView = $this->get(route('farmer-payments.create', ['purchase_id' => $purchase->id]));
        $createView->assertOk();
        $createView->assertSee('data-purchase-date="' . $purchaseDate . '"', false);
        $createView->assertSee('bonus_calculation_mode', false);

        // Record delayed payment: 50,000.00 @ 2% monthly for 45 days -> 50,000 * 2% * (45/30) = 1,500.00 bonus
        $response = $this->post(route('farmer-payments.store'), [
            '_form_token' => 'token-test-bonus-1',
            'purchase_id' => $purchase->id,
            'amount' => '50000.00',
            'apply_bonus_present' => '1',
            'apply_bonus' => '1',
            'bonus_calculation_mode' => 'monthly',
            'bonus_percentage' => '2.00',
            'payment_method' => 'bank_transfer',
            'transaction_reference' => 'UTR-BONUS-45D',
        ]);

        $payment = PurchasePayment::where('purchase_id', $purchase->id)->first();
        $this->assertNotNull($payment);
        $response->assertRedirect(route('farmer-payments.show', $payment));

        $this->assertSame(45, $payment->days_delayed);
        $this->assertSame('2.00', $payment->bonus_percentage);
        $this->assertSame('1500.00', $payment->bonus_amount);
        $this->assertSame('51500.00', $payment->total_payout);
        $this->assertSame('51500.00', $payment->effective_payout);

        // Purchase principal balance reduced to 0.00
        $purchase->refresh();
        $this->assertSame('50000.00', $purchase->paid_amount);
        $this->assertSame('0.00', $purchase->balance_due);

        // Show & Print views display bonus & total payout
        $showView = $this->get(route('farmer-payments.show', $payment));
        $showView->assertOk();
        $showView->assertSee('1,500.00');
        $showView->assertSee('51,500.00');

        $printView = $this->get(route('farmer-payments.print', $payment));
        $printView->assertOk();
        $printView->assertSee('Delayed Payment Bonus');
        $printView->assertSee('51,500.00');

        // Daybook reflects actual cash/bank outflow of 51,500.00
        $daybook = (new \App\Services\FinanceService)->getDaybook(now()->toDateString());
        $this->assertSame('51500.00', $daybook['total_outflow']);
    }

    public function test_same_day_payment_has_zero_delayed_bonus(): void
    {
        $purchase = (new ProcurementService)->create([
            'purchase_date' => now()->toDateString(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '1100.000',
            'tare_weight_kg' => '100.000',
            'rate_per_qtl' => '2000.00',
        ], $this->admin->id, finalize: true);

        $this->flushSession();
        $this->actingAs($this->accountsManager);

        $response = $this->post(route('farmer-payments.store'), [
            '_form_token' => 'token-test-bonus-instant',
            'purchase_id' => $purchase->id,
            'amount' => '20000.00',
            'apply_bonus_present' => '1',
            // apply_bonus unchecked for same-day payment
            'bonus_calculation_mode' => 'monthly',
            'bonus_percentage' => '1.00',
            'payment_method' => 'cash',
        ]);

        $payment = PurchasePayment::where('purchase_id', $purchase->id)->first();
        $this->assertNotNull($payment);
        $response->assertRedirect(route('farmer-payments.show', $payment));

        $this->assertSame(0, $payment->days_delayed);
        $this->assertSame('0.00', $payment->bonus_amount);
        $this->assertSame('20000.00', $payment->total_payout);
    }

    public function test_purchases_index_kpis_payment_filters_and_live_mandi_rates(): void
    {
        $this->paddy->update([
            'current_purchase_rate' => '2320.00',
            'rate_updated_at' => now(),
        ]);

        // Unpaid finalized purchase (20 Qtl @ 2320 = 46,400)
        $unpaidPurchase = (new ProcurementService)->create([
            'purchase_date' => now()->subDays(5)->toDateString(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '2100.000',
            'tare_weight_kg' => '100.000',
            'rate_per_qtl' => '2320.00',
            'vehicle_number' => 'BR24UNPAID',
        ], $this->admin->id, finalize: true);

        // Draft purchase with matching farmer name to verify grouped search + status filter
        $draftPurchase = (new ProcurementService)->create([
            'purchase_date' => now()->toDateString(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '1100.000',
            'tare_weight_kg' => '100.000',
            'rate_per_qtl' => '2320.00',
            'vehicle_number' => 'BR24DRAFT',
        ], $this->admin->id, finalize: false);

        $indexResp = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/purchases');
        $indexResp->assertOk();
        $indexResp->assertSee('46,400.00');
        $indexResp->assertSee('2,320');
        $indexResp->assertSee($unpaidPurchase->purchase_number);
        $indexResp->assertSee($draftPurchase->purchase_number);

        // Grouped search + status=finalized must exclude draftPurchase even when farmer name matches
        $filteredResp = $this->actingAs($this->admin)
            ->withSession(['locale' => 'en'])
            ->get('/erp/purchases?q=Ram+Kumar&status=finalized');
        $filteredResp->assertOk();
        $filteredResp->assertSee($unpaidPurchase->purchase_number);
        $filteredResp->assertDontSee($draftPurchase->purchase_number);

        // Payment=due filter shows unpaid finalized purchase and excludes draft
        $dueResp = $this->actingAs($this->admin)
            ->withSession(['locale' => 'en'])
            ->get('/erp/purchases?payment=due');
        $dueResp->assertOk();
        $dueResp->assertSee($unpaidPurchase->purchase_number);
        $dueResp->assertDontSee($draftPurchase->purchase_number);

        // Create form embeds live mandi rate on crop option
        $createResp = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/purchases-create');
        $createResp->assertOk();
        $createResp->assertSee('data-rate="2320.00"', false);
    }

    public function test_farmer_payments_index_kpis_filters_and_mobile_cards(): void
    {
        $purchase = (new ProcurementService)->create([
            'purchase_date' => now()->subDays(45)->toDateString(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '2100.000',
            'tare_weight_kg' => '100.000',
            'rate_per_qtl' => '2500.00',
            'vehicle_number' => 'BR24PAY01',
        ], $this->admin->id, finalize: true);

        // Record payment with delayed bonus
        $postResp = $this->actingAs($this->admin)->post('/erp/farmer-payments', [
            'purchase_id' => $purchase->id,
            'amount' => '50000.00',
            'apply_bonus_present' => 1,
            'apply_bonus' => 1,
            'bonus_calculation_mode' => 'monthly',
            'bonus_percentage' => '2.00',
            'bonus_amount' => '1500.00',
            'payment_method' => 'bank_transfer',
            'transaction_reference' => 'UTR99887766',
        ]);
        $postResp->assertSessionHasNoErrors();

        // 1. English index verification
        $enResp = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/farmer-payments');
        $enResp->assertOk();
        $enResp->assertSee('Farmer Payments');
        $enResp->assertSee('Total Payout Disbursed');
        $enResp->assertSee('Bonus Incentives Paid');
        $enResp->assertSee('51,500.00');
        $enResp->assertSee('1,500.00');
        $enResp->assertSee('UTR99887766');
        $enResp->assertSee($this->farmer->name);
        $enResp->assertSee('Bank Transfer (NEFT/RTGS)');
        $enResp->assertSee('mobile-record-card');

        // 2. Bonus tab filter
        $bonusTab = $this->actingAs($this->admin)->get('/erp/farmer-payments?bonus=with_bonus');
        $bonusTab->assertOk();
        $bonusTab->assertSee('51,500.00');

        // 3. Search query filter
        $searchResp = $this->actingAs($this->admin)->get('/erp/farmer-payments?q=UTR99887766');
        $searchResp->assertOk();
        $searchResp->assertSee('51,500.00');

        // 4. Hindi index localization
        $hiResp = $this->actingAs($this->admin)->withSession(['locale' => 'hi'])->get('/erp/farmer-payments');
        $hiResp->assertOk();
        $hiResp->assertSee('किसान भुगतान');
        $hiResp->assertSee('कुल संचित भुगतान');
        $hiResp->assertSee('विलंब भुगतान बोनस');
        $hiResp->assertSee('51,500.00');
    }

    public function test_inventory_index_kpis_godown_tabs_and_localization(): void
    {
        // Finalize a purchase to create stock in Main Warehouse (20 Qtl = 2000 Kg)
        (new ProcurementService)->create([
            'purchase_date' => now()->toDateString(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '2100.000',
            'tare_weight_kg' => '100.000',
            'rate_per_qtl' => '2400.00',
            'vehicle_number' => 'BR24STOCK01',
        ], $this->admin->id, finalize: true);

        // 1. English view
        $enResp = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/inventory');
        $enResp->assertOk();
        $enResp->assertSee('Inventory & Stock Management');
        $enResp->assertSee('Total Physical Stock');
        $enResp->assertSee('Estimated Valuation');
        $enResp->assertSee('Active Godowns');
        $enResp->assertSee('20.00'); // 20 Qtl
        $enResp->assertSee('2000.0'); // 2000 Kg
        $enResp->assertSee($this->mainWh->name);
        $enResp->assertSee($this->paddy->name);
        $enResp->assertSee('mobile-record-card');

        // 2. Filter by warehouse
        $whResp = $this->actingAs($this->admin)
            ->withSession(['locale' => 'en'])
            ->get('/erp/inventory?warehouse_id=' . $this->mainWh->id);
        $whResp->assertOk();
        $whResp->assertSee('20.00');

        // 3. Search query filter
        $searchResp = $this->actingAs($this->admin)
            ->withSession(['locale' => 'en'])
            ->get('/erp/inventory?q=' . urlencode($this->paddy->name));
        $searchResp->assertOk();
        $searchResp->assertSee('20.00');

        // 4. Hindi localization
        $hiResp = $this->actingAs($this->admin)->withSession(['locale' => 'hi'])->get('/erp/inventory');
        $hiResp->assertOk();
        $hiResp->assertSee('स्टॉक एवं गोदाम प्रबंधन');
        $hiResp->assertSee('कुल भौतिक स्टॉक');
        $hiResp->assertSee('सक्रिय गोदाम');
        $hiResp->assertSee('20.00');
    }

    public function test_inventory_movements_kpis_filters_and_mobile_cards(): void
    {
        // 1. Create a finalized purchase (Inward: 20 Qtl Paddy)
        $purchase = (new ProcurementService)->create([
            'purchase_date' => now()->subDay()->toDateString(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '2100.000',
            'tare_weight_kg' => '100.000',
            'rate_per_qtl' => '2400.00',
            'vehicle_number' => 'BR24MOV01',
        ], $this->admin->id, finalize: true);

        // 2. English view assertions
        $enResp = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/inventory/movements');
        $enResp->assertOk();
        $enResp->assertSee('Stock Movements & Audit Ledger');
        $enResp->assertSee('Total Inward Stock');
        $enResp->assertSee('Total Outward Stock');
        $enResp->assertSee('Net Movement Flow');
        $enResp->assertSee('+20.00');
        $enResp->assertSee($purchase->purchase_number);
        $enResp->assertSee($this->mainWh->name);
        $enResp->assertSee($this->paddy->name);
        $enResp->assertSee('INWARD');
        $enResp->assertSee('mobile-record-card');

        // 3. Direction tab filter (Inward)
        $inResp = $this->actingAs($this->admin)
            ->withSession(['locale' => 'en'])
            ->get('/erp/inventory/movements?direction=in');
        $inResp->assertOk();
        $inResp->assertSee($purchase->purchase_number);

        // 4. Source type tab filter (Purchase)
        $purchResp = $this->actingAs($this->admin)
            ->withSession(['locale' => 'en'])
            ->get('/erp/inventory/movements?source_type=purchase');
        $purchResp->assertOk();
        $purchResp->assertSee($purchase->purchase_number);

        // 5. Search query filter
        $searchResp = $this->actingAs($this->admin)
            ->withSession(['locale' => 'en'])
            ->get('/erp/inventory/movements?q=' . urlencode($purchase->purchase_number));
        $searchResp->assertOk();
        $searchResp->assertSee($purchase->purchase_number);

        // 6. Hindi localization assertions
        $hiResp = $this->actingAs($this->admin)->withSession(['locale' => 'hi'])->get('/erp/inventory/movements');
        $hiResp->assertOk();
        $hiResp->assertSee('स्टॉक आवक-जावक एवं ऑडिट लेजर');
        $hiResp->assertSee('कुल आवक स्टॉक');
        $hiResp->assertSee('शुद्ध स्टॉक प्रवाह');
        $hiResp->assertSee('आवक');
        $hiResp->assertSee('+20.00');
    }

    public function test_stock_transfers_index_kpis_filters_and_mobile_cards(): void
    {
        // Stock 40 Qtl in main warehouse
        (new ProcurementService)->create([
            'purchase_date' => now(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '4100.000',
            'tare_weight_kg' => '100.000',
            'rate_per_qtl' => '2000.00',
        ], $this->admin->id, finalize: true);

        // Process a transfer: 15 Qtl (1500 kg) from Main to Branch
        $transfer = (new \App\Services\InventoryService)->transfer([
            'transfer_date' => now()->toDateString(),
            'from_warehouse_id' => $this->mainWh->id,
            'to_warehouse_id' => $this->branchWh->id,
            'crop_id' => $this->paddy->id,
            'quantity_kg' => '1500.000',
            'vehicle_number' => 'BR 01 TR 8888',
            'notes' => 'Inter-godown balancing transfer',
        ], $this->admin->id);

        // 1. English index view
        $enResp = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/stock-transfers');
        $enResp->assertOk();
        $enResp->assertSee('Warehouse Stock Transfers');
        $enResp->assertSee('Total Transferred Stock');
        $enResp->assertSee('15.00');
        $enResp->assertSee('BR 01 TR 8888');
        $enResp->assertSee($transfer->transfer_number);
        $enResp->assertSee($this->mainWh->name);
        $enResp->assertSee($this->branchWh->name);
        $enResp->assertSee('mobile-record-card');

        // 2. Search query filter
        $searchResp = $this->actingAs($this->admin)
            ->withSession(['locale' => 'en'])
            ->get('/erp/stock-transfers?q=' . urlencode('BR 01 TR 8888'));
        $searchResp->assertOk();
        $searchResp->assertSee($transfer->transfer_number);

        // 3. Segment filter (today)
        $todayResp = $this->actingAs($this->admin)
            ->withSession(['locale' => 'en'])
            ->get('/erp/stock-transfers?segment=today');
        $todayResp->assertOk();
        $todayResp->assertSee($transfer->transfer_number);

        // 4. Hindi localization
        $hiResp = $this->actingAs($this->admin)->withSession(['locale' => 'hi'])->get('/erp/stock-transfers');
        $hiResp->assertOk();
        $hiResp->assertSee('गोदाम स्टॉक ट्रांसफर');
        $hiResp->assertSee('कुल स्थानांतरित स्टॉक');
        $hiResp->assertSee('15.00');

        // 5. Show page
        $showResp = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/stock-transfers/' . $transfer->id);
        $showResp->assertOk();
        $showResp->assertSee('Inter-Warehouse Transfer Particulars');
        $showResp->assertSee($transfer->challanNumber());

        // 6. Create form page
        $createResp = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/stock-transfers-create');
        $createResp->assertOk();
        $createResp->assertSee('Initiate Stock Transfer');
    }

    public function test_stock_adjustments_index_kpis_filters_and_mobile_cards(): void
    {
        $invService = new InventoryService();

        // 1. Initial stock so we have some base inventory
        (new ProcurementService)->create([
            'purchase_date' => now(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->paddy->id,
            'warehouse_id' => $this->mainWh->id,
            'gross_weight_kg' => '5100.000',
            'tare_weight_kg' => '100.000',
            'rate_per_qtl' => '2200.00',
        ], $this->admin->id, finalize: true);

        // 2. Request and approve an adjustment: -250 kg (-2.500 Qtl)
        $adj1 = $invService->requestAdjustment([
            'adjustment_date' => now()->toDateString(),
            'warehouse_id' => $this->mainWh->id,
            'crop_id' => $this->paddy->id,
            'adjustment_type' => 'shrinkage',
            'quantity_kg' => '-250.000',
            'reason' => 'Storage moisture shrinkage 5 bags write-off',
        ], $this->admin->id);
        $invService->approveAdjustment($adj1, $this->admin->id);

        // 3. Request a pending surplus adjustment: +100 kg (+1.000 Qtl)
        $adj2 = $invService->requestAdjustment([
            'adjustment_date' => now()->toDateString(),
            'warehouse_id' => $this->mainWh->id,
            'crop_id' => $this->paddy->id,
            'adjustment_type' => 'physical_reconciliation',
            'quantity_kg' => '100.000',
            'reason' => 'Physical stock count surplus discovered',
        ], $this->admin->id);

        // 4. Request and reject a damage adjustment: -50 kg (-0.500 Qtl)
        $adj3 = $invService->requestAdjustment([
            'adjustment_date' => now()->toDateString(),
            'warehouse_id' => $this->mainWh->id,
            'crop_id' => $this->paddy->id,
            'adjustment_type' => 'damage',
            'quantity_kg' => '-50.000',
            'reason' => 'Rainwater leak damage',
        ], $this->admin->id);
        $invService->rejectAdjustment($adj3, 'Damage was covered by insurance claim', $this->admin->id);

        // 5. English index view
        $enResp = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/stock-adjustments');
        $enResp->assertOk();
        $enResp->assertSee('Stock Adjustments & Reconciliation');
        $enResp->assertSee('Net Stock Adjustment');
        $enResp->assertSee('-2.50');
        $enResp->assertSee('Pending Approvals');
        $enResp->assertSee($adj1->adjustment_number);
        $enResp->assertSee($adj2->adjustment_number);
        $enResp->assertSee($adj3->adjustment_number);
        $enResp->assertSee('mobile-record-card');

        // 6. Search filter by adjustment number
        $searchResp = $this->actingAs($this->admin)
            ->withSession(['locale' => 'en'])
            ->get('/erp/stock-adjustments?q=' . urlencode($adj2->adjustment_number));
        $searchResp->assertOk();
        $searchResp->assertSee($adj2->adjustment_number);
        $searchResp->assertDontSee($adj1->adjustment_number);

        // 7. Status filter (pending)
        $pendingResp = $this->actingAs($this->admin)
            ->withSession(['locale' => 'en'])
            ->get('/erp/stock-adjustments?status=pending');
        $pendingResp->assertOk();
        $pendingResp->assertSee($adj2->adjustment_number);
        $pendingResp->assertDontSee($adj1->adjustment_number);

        // 8. Hindi localization
        $hiResp = $this->actingAs($this->admin)->withSession(['locale' => 'hi'])->get('/erp/stock-adjustments');
        $hiResp->assertOk();
        $hiResp->assertSee('स्टॉक समायोजन एवं मिलान');
        $hiResp->assertSee('शुद्ध स्टॉक समायोजन');
        $hiResp->assertSee('लंबित सत्यापन');

        // 9. Show page for pending adjustment (shows approve button and reject modal)
        $showPending = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/stock-adjustments/' . $adj2->id);
        $showPending->assertOk();
        $showPending->assertSee($adj2->adjustment_number);
        $showPending->assertSee('Approve Adjustment');
        $showPending->assertSee('Reject');

        // 10. Show page for rejected adjustment (shows rejection alert)
        $showRejected = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/stock-adjustments/' . $adj3->id);
        $showRejected->assertOk();
        $showRejected->assertSee($adj3->adjustment_number);
        $showRejected->assertSee('Damage was covered by insurance claim');

        // 11. Create form page
        $createResp = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/stock-adjustments-create');
        $createResp->assertOk();
        $createResp->assertSee('Request Stock Adjustment');
        $createResp->assertSee('Deficit (-)');
        $createResp->assertSee('Surplus (+)');
    }
}
