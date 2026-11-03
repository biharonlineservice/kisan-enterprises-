<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\CropVariety;
use App\Models\Customer;
use App\Models\CustomerReceipt;
use App\Models\Farmer;
use App\Models\InventoryTransaction;
use App\Models\Role;
use App\Models\SalesInvoice;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\ProcurementService;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesAndDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $purchaseSalesManager;
    protected User $accountsManager;

    protected Customer $customer;
    protected Farmer $farmer;
    protected Crop $wheat;
    protected CropVariety $variety;
    protected Warehouse $godown;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);

        $adminRole = Role::where('name', Role::ADMIN)->first();
        $pmRole = Role::where('name', Role::PURCHASE_SALES)->first();
        $accRole = Role::where('name', Role::ACCOUNTS)->first();

        $this->admin = User::factory()->create(['role_id' => $adminRole->id, 'email' => 'admin@kisan.test']);
        $this->purchaseSalesManager = User::factory()->create(['role_id' => $pmRole->id, 'email' => 'pm@kisan.test']);
        $this->accountsManager = User::factory()->create(['role_id' => $accRole->id, 'email' => 'acc@kisan.test']);

        $this->wheat = Crop::create([
            'name' => 'Wheat (Gehun)',
            'code' => 'WHT',
            'unit' => 'Qtl',
            'is_active' => true,
        ]);

        $this->variety = CropVariety::create([
            'crop_id' => $this->wheat->id,
            'name' => 'Sharbati',
            'code' => 'SHAR',
            'is_active' => true,
        ]);

        $this->godown = Warehouse::create([
            'name' => 'Central Godown',
            'code' => 'CG01',
            'capacity_qtl' => 10000.000,
            'is_active' => true,
        ]);

        $this->farmer = Farmer::create([
            'farmer_id' => 'FRM00001',
            'name' => 'Sohan Lal',
            'mobile' => '9876543210',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);

        $this->customer = Customer::create([
            'customer_id' => 'CUS00001',
            'name' => 'Shree Ram Rice & Flour Mills',
            'customer_type' => 'rice_mill',
            'phone' => '9123456789',
            'city' => 'Bareilly',
            'is_active' => true,
            'created_by' => $this->admin->id,
        ]);
    }

    public function test_sales_service_calculation(): void
    {
        // 20 Qtl at 2500 = 50,000.00
        // Discount 1,000.00 -> Net 49,000.00
        $items = [
            [
                'crop_id' => $this->wheat->id,
                'warehouse_id' => $this->godown->id,
                'quantity_qtl' => '20.000',
                'rate_per_qtl' => '2500.00',
            ],
        ];

        $calc = SalesService::calculate($items, discount: '1000.00', globalTaxRate: '0.00');

        $this->assertSame('50000.00', $calc['subtotal_amount']);
        $this->assertSame('1000.00', $calc['discount_amount']);
        $this->assertSame('49000.00', $calc['total_amount']);
        $this->assertSame('20.000', $calc['items'][0]['quantity_qtl']);
        $this->assertSame('2000.000', $calc['items'][0]['quantity_kg']);
    }

    public function test_create_sales_order(): void
    {
        $this->actingAs($this->purchaseSalesManager);

        $response = $this->post(route('sales-orders.store'), [
            '_form_token' => 'token-so-1',
            'order_date' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'delivery_address' => 'Industrial Area Phase 2, Bareilly',
            'items' => [
                [
                    'crop_id' => $this->wheat->id,
                    'crop_variety_id' => $this->variety->id,
                    'warehouse_id' => $this->godown->id,
                    'quantity_qtl' => '100.000',
                    'rate_per_qtl' => '2600.00',
                ],
            ],
        ]);

        $order = SalesOrder::first();
        $this->assertNotNull($order);
        $response->assertRedirect(route('sales-orders.show', $order));

        $this->assertSame(SalesOrder::STATUS_CONFIRMED, $order->status);
        $this->assertSame('100.000', $order->total_qtl);
        $this->assertSame('260000.00', $order->total_amount);
    }

    public function test_finalize_sales_invoice_dispatches_stock_from_inventory(): void
    {
        $this->actingAs($this->purchaseSalesManager);

        // Inward stock first via procurement: 50 Qtl
        (new ProcurementService)->create([
            'purchase_date' => now(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->wheat->id,
            'crop_variety_id' => $this->variety->id,
            'warehouse_id' => $this->godown->id,
            'gross_weight_kg' => '5200.000',
            'tare_weight_kg' => '200.000', // 5000 kg net = 50 Qtl
            'rate_per_qtl' => '2300.00',
        ], $this->purchaseSalesManager->id, finalize: true);

        $this->assertSame('50.000', InventoryTransaction::getStock($this->godown->id, $this->wheat->id, $this->variety->id));

        // Create and finalize sales invoice for 30 Qtl with 10,000 initial payment
        $response = $this->post(route('sales-invoices.store'), [
            '_form_token' => 'token-si-1',
            'invoice_date' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'discount_amount' => '0.00',
            'vehicle_number' => 'UP 32 EF 1122',
            'driver_name' => 'Mukesh',
            'initial_paid_amount' => '10000.00',
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'UTR11223344',
            'action' => 'finalize',
            'items' => [
                [
                    'crop_id' => $this->wheat->id,
                    'crop_variety_id' => $this->variety->id,
                    'warehouse_id' => $this->godown->id,
                    'quantity_qtl' => '30.000',
                    'rate_per_qtl' => '2700.00', // 30 * 2700 = 81,000.00
                ],
            ],
        ]);

        $invoice = SalesInvoice::first();
        $this->assertNotNull($invoice);
        $response->assertRedirect(route('sales-invoices.show', $invoice));

        $this->assertSame(SalesInvoice::STATUS_FINALIZED, $invoice->status);
        $this->assertSame('81000.00', $invoice->total_amount);
        $this->assertSame('10000.00', $invoice->paid_amount);
        $this->assertSame('71000.00', $invoice->balance_due);

        // Inventory check: 50 - 30 = 20 Qtl remaining
        $remainingStock = InventoryTransaction::getStock($this->godown->id, $this->wheat->id, $this->variety->id);
        $this->assertSame('20.000', $remainingStock);
    }

    public function test_sales_invoice_blocked_when_insufficient_stock(): void
    {
        $this->actingAs($this->purchaseSalesManager);

        // Currently godown has 0 Qtl of wheat. Attempting to finalize a sale of 25 Qtl should fail.
        $response = $this->post(route('sales-invoices.store'), [
            '_form_token' => 'token-si-2',
            'invoice_date' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'action' => 'finalize',
            'items' => [
                [
                    'crop_id' => $this->wheat->id,
                    'warehouse_id' => $this->godown->id,
                    'quantity_qtl' => '25.000',
                    'rate_per_qtl' => '2700.00',
                ],
            ],
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(0, SalesInvoice::count());
    }

    public function test_customer_receipt_recording(): void
    {
        // 1. Stock inward 50 Qtl
        (new ProcurementService)->create([
            'purchase_date' => now(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->wheat->id,
            'warehouse_id' => $this->godown->id,
            'gross_weight_kg' => '5000.000',
            'tare_weight_kg' => '0.000',
            'rate_per_qtl' => '2300.00',
        ], $this->admin->id, finalize: true);

        // 2. Finalize sales invoice of 10 Qtl @ 2800 = 28,000.00
        $invoice = (new SalesService)->createInvoice([
            'invoice_date' => now(),
            'customer_id' => $this->customer->id,
        ], [
            [
                'crop_id' => $this->wheat->id,
                'warehouse_id' => $this->godown->id,
                'quantity_qtl' => '10.000',
                'rate_per_qtl' => '2800.00',
            ],
        ], $this->admin->id, finalize: true);

        $this->assertSame('28000.00', $invoice->balance_due);

        // 3. Record customer receipt of 20,000.00
        $this->flushSession();
        $this->actingAs($this->accountsManager);

        $response = $this->post(route('customer-receipts.store'), [
            '_form_token' => 'token-cr-1',
            'sales_invoice_id' => $invoice->id,
            'amount' => '20000.00',
            'payment_method' => 'bank_transfer',
            'transaction_reference' => 'RTGS00192837',
            'bank_name' => 'State Bank of India',
        ]);

        $receipt = CustomerReceipt::where('sales_invoice_id', $invoice->id)->first();
        $this->assertNotNull($receipt);
        $response->assertRedirect(route('customer-receipts.show', $receipt));

        $invoice->refresh();
        $this->assertSame('20000.00', $invoice->paid_amount);
        $this->assertSame('8000.00', $invoice->balance_due);
    }

    public function test_reversing_sales_invoice_restores_inventory(): void
    {
        $this->actingAs($this->admin);

        // Stock inward 50 Qtl
        (new ProcurementService)->create([
            'purchase_date' => now(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->wheat->id,
            'warehouse_id' => $this->godown->id,
            'gross_weight_kg' => '5000.000',
            'tare_weight_kg' => '0.000',
            'rate_per_qtl' => '2300.00',
        ], $this->admin->id, finalize: true);

        // Finalize invoice of 15 Qtl -> stock becomes 35 Qtl
        $invoice = (new SalesService)->createInvoice([
            'invoice_date' => now(),
            'customer_id' => $this->customer->id,
        ], [
            [
                'crop_id' => $this->wheat->id,
                'warehouse_id' => $this->godown->id,
                'quantity_qtl' => '15.000',
                'rate_per_qtl' => '2800.00',
            ],
        ], $this->admin->id, finalize: true);

        $this->assertSame('35.000', InventoryTransaction::getStock($this->godown->id, $this->wheat->id));

        // Reverse the invoice
        $response = $this->post(route('sales-invoices.reverse', $invoice), [
            '_form_token' => 'token-si-rev-1',
            'reason' => 'Truck breakdown and buyer cancellation before gate exit',
        ]);

        $response->assertRedirect(route('sales-invoices.show', $invoice));

        $invoice->refresh();
        $this->assertSame(SalesInvoice::STATUS_REVERSED, $invoice->status);
        $this->assertSame('Truck breakdown and buyer cancellation before gate exit', $invoice->reversal_reason);

        // Stock restored back to 50 Qtl
        $this->assertSame('50.000', InventoryTransaction::getStock($this->godown->id, $this->wheat->id));
    }

    public function test_transport_challan_view_and_pdf_generation(): void
    {
        $this->actingAs($this->purchaseSalesManager);

        // First inward 50 Qtl via Purchase
        (new ProcurementService)->create([
            'purchase_date' => now(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->wheat->id,
            'crop_variety_id' => $this->variety->id,
            'warehouse_id' => $this->godown->id,
            'gross_weight_kg' => '5200.000',
            'tare_weight_kg' => '200.000',
            'rate_per_qtl' => '2300.00',
        ], $this->admin->id, finalize: true);

        $invoice = (new SalesService)->createInvoice([
            'invoice_date' => now(),
            'customer_id' => $this->customer->id,
            'vehicle_number' => 'UP 32 AB 9999',
            'driver_name' => 'Mohan Lal',
            'transporter_name' => 'Kisan Express Lines',
            'eway_bill_number' => 'EWB-888777666',
            'delivery_destination' => 'Flour Mill Gate 2, Lucknow',
        ], [
            [
                'crop_id' => $this->wheat->id,
                'crop_variety_id' => $this->variety->id,
                'warehouse_id' => $this->godown->id,
                'quantity_qtl' => '25.000',
                'rate_per_qtl' => '2800.00',
            ],
        ], $this->admin->id, finalize: true);

        // Test web view
        $viewResponse = $this->get(route('sales-invoices.challan', $invoice));
        $viewResponse->assertOk();
        $viewResponse->assertSee('DELIVERY CHALLAN');
        $viewResponse->assertSee('UP 32 AB 9999');
        $viewResponse->assertSee('Mohan Lal');
        $viewResponse->assertSee('25.000 Qtl');

        // Test PDF download
        $pdfResponse = $this->get(route('sales-invoices.challan.pdf', $invoice));
        $pdfResponse->assertOk();
        $this->assertStringContainsString('application/pdf', $pdfResponse->headers->get('Content-Type'));
    }

    public function test_customers_index_kpis_filters_and_mobile_cards(): void
    {
        $this->actingAs($this->admin);

        // Seed diverse customer network
        $mill = Customer::create([
            'customer_id' => 'CUS00091',
            'name' => 'Agrawal Modern Rice Mill',
            'customer_type' => 'rice_mill',
            'city' => 'Patna',
            'state' => 'Bihar',
            'credit_limit' => 500000.00,
            'opening_balance' => 45000.00,
            'payment_terms_days' => 15,
            'is_active' => true,
            'mobile' => '9876543210',
            'gstin' => '10AABCA1234A1Z5',
            'created_by' => $this->admin->id,
        ]);

        $trader = Customer::create([
            'customer_id' => 'CUS00092',
            'name' => 'Gupta Grain Trading Co',
            'customer_type' => 'trader',
            'city' => 'Sasaram',
            'state' => 'Bihar',
            'credit_limit' => 200000.00,
            'opening_balance' => 0.00,
            'payment_terms_days' => 7,
            'is_active' => false,
            'mobile' => '9123456780',
            'created_by' => $this->admin->id,
        ]);

        // 1. Index page loads with Botanical Banner, KPIs & Tabs
        $response = $this->get(route('customers.index'));
        $response->assertOk();
        $response->assertViewHas('kpis');
        $response->assertViewHas('tabCounts');
        $response->assertViewHas('cities');
        $response->assertSee('Agrawal Modern Rice Mill');
        $response->assertSee('Gupta Grain Trading Co');
        $response->assertSee(__('sales.customers.tab_rice_mills'));
        $response->assertSee('Patna');

        // 2. Tab Filter: Rice Mills
        $millTab = $this->get(route('customers.index', ['tab' => 'rice_mills']));
        $millTab->assertOk();
        $millTab->assertSee('Agrawal Modern Rice Mill');
        $millTab->assertDontSee('Gupta Grain Trading Co');

        // 3. Tab Filter: Inactive
        $inactiveTab = $this->get(route('customers.index', ['tab' => 'inactive']));
        $inactiveTab->assertOk();
        $inactiveTab->assertSee('Gupta Grain Trading Co');
        $inactiveTab->assertDontSee('Agrawal Modern Rice Mill');

        // 4. City Filter
        $cityFilter = $this->get(route('customers.index', ['city' => 'Patna']));
        $cityFilter->assertOk();
        $cityFilter->assertSee('Agrawal Modern Rice Mill');
        $cityFilter->assertDontSee('Gupta Grain Trading Co');

        // 5. Customer Show View
        $showResponse = $this->get(route('customers.show', $mill));
        $showResponse->assertOk();
        $showResponse->assertSee('Agrawal Modern Rice Mill');
        $showResponse->assertSee('10AABCA1234A1Z5');
        $showResponse->assertSee(__('sales.customers.card_credit_exposure'));

        // 6. Customer Print View
        $printResponse = $this->get(route('customers.print', $mill));
        $printResponse->assertOk();
        $printResponse->assertSee('BUYER & MILL PROFILE', false);
        $printResponse->assertSee('Agrawal Modern Rice Mill');

        // 7. Customer Export CSV
        $exportResponse = $this->get(route('customers.export'));
        $exportResponse->assertOk();
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
    }

    public function test_sales_orders_index_kpis_tabs_filters_and_export(): void
    {
        $this->actingAs($this->admin);

        // 1. Create Confirmed Order
        $order1 = SalesOrder::create([
            'order_number' => 'SO/2026-27/00001',
            'order_date' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'status' => SalesOrder::STATUS_CONFIRMED,
            'total_qtl' => '150.000',
            'total_amount' => '390000.00',
            'delivery_address' => 'Mill Gate 1, Patna',
            'created_by' => $this->admin->id,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $order1->id,
            'crop_id' => $this->wheat->id,
            'crop_variety_id' => $this->variety->id,
            'warehouse_id' => $this->godown->id,
            'quantity_qtl' => '150.000',
            'rate_per_qtl' => '2600.00',
            'amount' => '390000.00',
        ]);

        // 2. Create Completed Order
        $order2 = SalesOrder::create([
            'order_number' => 'SO/2026-27/00002',
            'order_date' => now()->subDays(5)->toDateString(),
            'customer_id' => $this->customer->id,
            'status' => SalesOrder::STATUS_COMPLETED,
            'total_qtl' => '80.000',
            'total_amount' => '208000.00',
            'delivery_address' => 'Mandi Warehouse 2',
            'created_by' => $this->admin->id,
        ]);

        // 3. Index Page asserts
        $indexResponse = $this->get(route('sales-orders.index'));
        $indexResponse->assertOk();
        $indexResponse->assertViewHas('kpis');
        $indexResponse->assertViewHas('tabCounts');
        $indexResponse->assertSee('SO/2026-27/00001');
        $indexResponse->assertSee('SO/2026-27/00002');
        $indexResponse->assertSee('150.000');

        // 4. Tab: Confirmed
        $confirmedTab = $this->get(route('sales-orders.index', ['tab' => 'confirmed']));
        $confirmedTab->assertOk();
        $confirmedTab->assertSee('SO/2026-27/00001');
        $confirmedTab->assertDontSee('SO/2026-27/00002');

        // 5. Tab: Completed
        $completedTab = $this->get(route('sales-orders.index', ['tab' => 'completed']));
        $completedTab->assertOk();
        $completedTab->assertSee('SO/2026-27/00002');
        $completedTab->assertDontSee('SO/2026-27/00001');

        // 6. Show Order Details & Tracker
        $showResponse = $this->get(route('sales-orders.show', $order1));
        $showResponse->assertOk();
        $showResponse->assertSee('SO/2026-27/00001');
        $showResponse->assertSee($this->customer->name);
        $showResponse->assertSee('3,90,000.00');

        // 7. Print Sales Order Agreement
        $printResponse = $this->get(route('sales-orders.print', $order1));
        $printResponse->assertOk();
        $printResponse->assertSee('SALES ORDER AGREEMENT');
        $printResponse->assertSee('SO/2026-27/00001');

        // 8. Export CSV
        $exportResponse = $this->get(route('sales-orders.export'));
        $exportResponse->assertOk();
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
    }

    public function test_sales_invoices_index_kpis_tabs_filters_and_export(): void
    {
        $this->actingAs($this->admin);

        // 1. Inward stock via Purchase
        (new ProcurementService)->create([
            'purchase_date' => now(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->wheat->id,
            'crop_variety_id' => $this->variety->id,
            'warehouse_id' => $this->godown->id,
            'gross_weight_kg' => '8000.000',
            'tare_weight_kg' => '0.000',
            'rate_per_qtl' => '2200.00',
        ], $this->admin->id, finalize: true);

        // 2. Create Finalized Invoice
        $invFinalized = (new SalesService)->createInvoice([
            'invoice_date' => now(),
            'customer_id' => $this->customer->id,
            'vehicle_number' => 'UP 32 XY 1234',
            'driver_name' => 'Rajesh Kumar',
            'transporter_name' => 'Kisan Logistics',
            'eway_bill_number' => 'EWB-99887766',
            'delivery_destination' => 'Mandi Hub 1',
        ], [
            [
                'crop_id' => $this->wheat->id,
                'crop_variety_id' => $this->variety->id,
                'warehouse_id' => $this->godown->id,
                'quantity_qtl' => '30.000',
                'rate_per_qtl' => '2600.00',
            ],
        ], $this->admin->id, finalize: true);

        // 3. Create Draft Invoice
        $invDraft = (new SalesService)->createInvoice([
            'invoice_date' => now()->subDay(),
            'customer_id' => $this->customer->id,
            'vehicle_number' => 'UP 32 DRAFT 01',
        ], [
            [
                'crop_id' => $this->wheat->id,
                'crop_variety_id' => $this->variety->id,
                'warehouse_id' => $this->godown->id,
                'quantity_qtl' => '15.000',
                'rate_per_qtl' => '2550.00',
            ],
        ], $this->admin->id, finalize: false);

        // 4. Index View asserts
        $indexResponse = $this->get(route('sales-invoices.index'));
        $indexResponse->assertOk();
        $indexResponse->assertViewHas('kpis');
        $indexResponse->assertViewHas('tabCounts');
        $indexResponse->assertSee($invFinalized->invoice_number);
        $indexResponse->assertSee($invDraft->invoice_number);
        $indexResponse->assertSee('UP 32 XY 1234');

        // 5. Tab: Finalized
        $finalizedTab = $this->get(route('sales-invoices.index', ['tab' => 'finalized']));
        $finalizedTab->assertOk();
        $finalizedTab->assertSee($invFinalized->invoice_number);
        $finalizedTab->assertDontSee($invDraft->invoice_number);

        // 6. Tab: Draft
        $draftTab = $this->get(route('sales-invoices.index', ['tab' => 'draft']));
        $draftTab->assertOk();
        $draftTab->assertSee($invDraft->invoice_number);
        $draftTab->assertDontSee($invFinalized->invoice_number);

        // 7. Show Invoice
        $showResponse = $this->get(route('sales-invoices.show', $invFinalized));
        $showResponse->assertOk();
        $showResponse->assertSee($invFinalized->invoice_number);
        $showResponse->assertSee('Rajesh Kumar');
        $showResponse->assertSee('Kisan Logistics');

        // 8. Export CSV
        $exportResponse = $this->get(route('sales-invoices.export'));
        $exportResponse->assertOk();
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
    }

    public function test_customer_receipts_index_kpis_tabs_filters_and_export(): void
    {
        $this->actingAs($this->admin);

        // 1. Inward stock via Purchase
        (new ProcurementService)->create([
            'purchase_date' => now(),
            'farmer_id' => $this->farmer->id,
            'crop_id' => $this->wheat->id,
            'crop_variety_id' => $this->variety->id,
            'warehouse_id' => $this->godown->id,
            'gross_weight_kg' => '5000.000',
            'tare_weight_kg' => '0.000',
            'rate_per_qtl' => '2200.00',
        ], $this->admin->id, finalize: true);

        // 2. Create Finalized Invoice
        $invoice = (new SalesService)->createInvoice([
            'invoice_date' => now(),
            'customer_id' => $this->customer->id,
            'vehicle_number' => 'UP 32 CR 5555',
        ], [
            [
                'crop_id' => $this->wheat->id,
                'crop_variety_id' => $this->variety->id,
                'warehouse_id' => $this->godown->id,
                'quantity_qtl' => '20.000',
                'rate_per_qtl' => '2500.00',
            ],
        ], $this->admin->id, finalize: true);

        // 3. Record Bank Receipt
        $receiptBank = (new SalesService)->recordReceipt(
            $invoice,
            '25000.00',
            'bank_transfer',
            'UTR-987654321',
            'State Bank of India',
            'Part payment via NEFT',
            $this->admin->id
        );

        // 4. Record Cash Receipt
        $receiptCash = (new SalesService)->recordReceipt(
            $invoice,
            '10000.00',
            'cash',
            null,
            null,
            'Cash counter payment',
            $this->admin->id
        );

        // 5. Index View asserts
        $indexResponse = $this->get(route('customer-receipts.index'));
        $indexResponse->assertOk();
        $indexResponse->assertViewHas('kpis');
        $indexResponse->assertViewHas('tabCounts');
        $indexResponse->assertSee($receiptBank->receipt_number);
        $indexResponse->assertSee($receiptCash->receipt_number);
        $indexResponse->assertSee('UTR-987654321');
        $indexResponse->assertSee('State Bank of India');

        // 6. Tab: Bank
        $bankTab = $this->get(route('customer-receipts.index', ['tab' => 'bank']));
        $bankTab->assertOk();
        $bankTab->assertSee($receiptBank->receipt_number);
        $bankTab->assertDontSee($receiptCash->receipt_number);

        // 7. Tab: Cash
        $cashTab = $this->get(route('customer-receipts.index', ['tab' => 'cash']));
        $cashTab->assertOk();
        $cashTab->assertSee($receiptCash->receipt_number);
        $cashTab->assertDontSee($receiptBank->receipt_number);

        // 8. Show View
        $showResponse = $this->get(route('customer-receipts.show', $receiptBank));
        $showResponse->assertOk();
        $showResponse->assertSee($receiptBank->receipt_number);
        $showResponse->assertSee('State Bank of India');
        $showResponse->assertSee($this->customer->name);

        // 9. Print View
        $printResponse = $this->get(route('customer-receipts.print', $receiptBank));
        $printResponse->assertOk();
        $printResponse->assertSee($receiptBank->receipt_number);

        // 10. Export CSV
        $exportResponse = $this->get(route('customer-receipts.export'));
        $exportResponse->assertOk();
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
    }

    public function test_buyer_delivered_mark_and_revert_workflow(): void
    {
        $this->actingAs($this->admin);

        // 1. Stock godown
        (new ProcurementService)->create([
            'farmer_id' => $this->farmer->id,
            'warehouse_id' => $this->godown->id,
            'purchase_date' => now(),
            'crop_id' => $this->wheat->id,
            'crop_variety_id' => $this->variety->id,
            'gross_weight_kg' => '10200.000',
            'tare_weight_kg' => '200.000', // 10000 kg net = 100 Qtl
            'rate_per_qtl' => '2200.00',
        ], $this->admin->id, finalize: true);

        // 2. Create Sales Order
        $order = SalesOrder::create([
            'order_number' => 'SO/2026-27/00099',
            'order_date' => now()->toDateString(),
            'customer_id' => $this->customer->id,
            'status' => SalesOrder::STATUS_CONFIRMED,
            'total_qtl' => '50.000',
            'total_amount' => '125000.00',
            'delivery_address' => 'Bareilly Industrial Area, Gate 3',
            'created_by' => $this->admin->id,
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $order->id,
            'crop_id' => $this->wheat->id,
            'crop_variety_id' => $this->variety->id,
            'warehouse_id' => $this->godown->id,
            'quantity_qtl' => '50.000',
            'rate_per_qtl' => '2500.00',
            'amount' => '125000.00',
        ]);

        // 3. Create Finalized Invoice linked to Order
        $invoice = (new SalesService)->createInvoice([
            'sales_order_id' => $order->id,
            'invoice_date' => now(),
            'customer_id' => $this->customer->id,
            'vehicle_number' => 'UP 25 T 9999',
            'delivery_destination' => 'Bareilly Industrial Area, Gate 3',
        ], [
            [
                'crop_id' => $this->wheat->id,
                'crop_variety_id' => $this->variety->id,
                'warehouse_id' => $this->godown->id,
                'quantity_qtl' => '50.000',
                'rate_per_qtl' => '2500.00',
            ],
        ], $this->admin->id, finalize: true);

        $order->refresh();
        $invoice->refresh();
        $this->assertEquals(SalesOrder::STATUS_DISPATCHED, $order->status);
        $this->assertEquals(SalesInvoice::DELIVERY_IN_TRANSIT, $invoice->delivery_status);
        $this->assertTrue($invoice->isDeliveryPending());

        // 4. Mark Buyer Delivered via Controller Route
        $deliveryTimestamp = now()->format('Y-m-d H:i:s');
        $response = $this->post(route('sales-invoices.mark-delivered', $invoice), [
            'delivered_at' => $deliveryTimestamp,
            'received_by' => 'Ramesh Incharge',
            'delivery_notes' => 'Received 500 bags intact at Mill Silo.',
        ]);

        $response->assertRedirect(route('sales-invoices.show', $invoice));
        $response->assertSessionHas('success');

        $invoice->refresh();
        $order->refresh();

        $this->assertEquals(SalesInvoice::DELIVERY_DELIVERED, $invoice->delivery_status);
        $this->assertTrue($invoice->isDelivered());
        $this->assertEquals('Ramesh Incharge', $invoice->received_by);
        $this->assertEquals('Received 500 bags intact at Mill Silo.', $invoice->delivery_notes);
        $this->assertEquals($this->admin->id, $invoice->delivered_by);

        // Order should auto-complete as all invoices are delivered
        $this->assertEquals(SalesOrder::STATUS_COMPLETED, $order->status);

        // Verify index view displays delivered status and tab filter works
        $indexResponse = $this->get(route('sales-invoices.index', ['tab' => 'delivered']));
        $indexResponse->assertOk();
        $indexResponse->assertSee($invoice->invoice_number);
        $indexResponse->assertSee(__('sales.invoices.status_delivered'));

        $transitTabResponse = $this->get(route('sales-invoices.index', ['tab' => 'in_transit']));
        $transitTabResponse->assertOk();
        $transitTabResponse->assertDontSee($invoice->invoice_number);

        // Verify challan view shows delivery confirmation
        $challanResponse = $this->get(route('sales-invoices.challan', $invoice));
        $challanResponse->assertOk();
        $challanResponse->assertSee('Ramesh Incharge');
        $challanResponse->assertSee('DELIVERY CONFIRMED');

        // 5. Test Revert Delivery
        $revertResponse = $this->post(route('sales-invoices.revert-delivery', $invoice));
        $revertResponse->assertRedirect(route('sales-invoices.show', $invoice));
        $revertResponse->assertSessionHas('success');

        $invoice->refresh();
        $order->refresh();

        $this->assertEquals(SalesInvoice::DELIVERY_IN_TRANSIT, $invoice->delivery_status);
        $this->assertNull($invoice->delivered_at);
        $this->assertEquals(SalesOrder::STATUS_DISPATCHED, $order->status);

        // 6. Test manual Order Mark Completed
        $orderCompleteResponse = $this->post(route('sales-orders.mark-completed', $order));
        $orderCompleteResponse->assertRedirect(route('sales-orders.show', $order));
        $order->refresh();
        $this->assertEquals(SalesOrder::STATUS_COMPLETED, $order->status);
    }
}
