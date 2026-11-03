<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Customer;
use App\Models\ExpenseCategory;
use App\Models\Farmer;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_manage_crops_and_varieties(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->post('/erp/crops', [
            'name' => 'Barley (Jau)',
            'code' => 'BARLEY',
            'unit' => 'quintal',
            'hsn_code' => '1003',
            'is_active' => '1',
        ]);

        $crop = Crop::where('code', 'BARLEY')->first();
        $this->assertNotNull($crop);
        $response->assertRedirect("/erp/crops/{$crop->id}");

        // Add variety
        $varResponse = $this->actingAs($admin)->post("/erp/crops/{$crop->id}/varieties", [
            'name' => 'HBL 391',
            'code' => 'HBL-391',
            'standard_moisture_pct' => 12.5,
            'default_grade' => 'Grade A',
        ]);

        $varResponse->assertSessionHas('success');
        $this->assertDatabaseHas('crop_varieties', [
            'crop_id' => $crop->id,
            'name' => 'HBL 391',
        ]);
    }

    public function test_farmer_registration_generates_auto_id(): void
    {
        $pm = User::factory()->create([
            'role_id' => Role::where('name', Role::PURCHASE_SALES)->value('id'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($pm)->post('/erp/farmers', [
            'name' => 'Bishwanath Rai',
            'mobile' => '9835099999',
            'village' => 'Dumraon',
            'block' => 'Dumraon',
            'district' => 'Buxar',
            'state' => 'Bihar',
            'is_active' => '1',
        ]);

        $farmer = Farmer::where('name', 'Bishwanath Rai')->first();
        $this->assertNotNull($farmer);
        $this->assertStringStartsWith('FRM', $farmer->farmer_id);
        $response->assertRedirect("/erp/farmers/{$farmer->id}");
    }

    public function test_bank_details_are_ignored_if_user_lacks_farmers_bank_permission(): void
    {
        // Purchase Manager does NOT have farmers.bank by default
        $pm = User::factory()->create([
            'role_id' => Role::where('name', Role::PURCHASE_SALES)->value('id'),
            'is_active' => true,
        ]);

        $this->actingAs($pm)->post('/erp/farmers', [
            'name' => 'Govind Sharma',
            'mobile' => '9835088888',
            'village' => 'Nawada',
            'bank_name' => 'HDFC Bank',
            'bank_account_number' => '50100012345678',
            'bank_ifsc' => 'HDFC0001234',
            'is_active' => '1',
        ]);

        $farmer = Farmer::where('name', 'Govind Sharma')->first();
        $this->assertNotNull($farmer);
        // Bank details must NOT be saved because user lacks farmers.bank permission
        $this->assertNull($farmer->bank_name);
        $this->assertNull($farmer->bank_account_number);

        // User with both farmers.edit and farmers.bank (like Admin) can save bank details
        $this->flushSession();
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $this->actingAs($admin)->put("/erp/farmers/{$farmer->id}", [
            'name' => 'Govind Sharma',
            'village' => 'Nawada',
            'bank_name' => 'State Bank of India',
            'bank_account_number' => '12345678901',
            'bank_ifsc' => 'SBIN0001234',
            'is_active' => '1',
        ]);

        $this->assertEquals('State Bank of India', $farmer->fresh()->bank_name);
        $this->assertEquals('12345678901', $farmer->fresh()->bank_account_number);
    }

    public function test_farmer_export_permission_enforcement(): void
    {
        // Accounts manager has no farmers.export permission by default
        $acc = User::factory()->create([
            'role_id' => Role::where('name', Role::ACCOUNTS)->value('id'),
            'is_active' => true,
        ]);

        $this->actingAs($acc)->get('/erp/farmers/export')->assertForbidden();

        // Purchase manager has farmers.export permission
        $this->flushSession();
        $pm = User::factory()->create([
            'role_id' => Role::where('name', Role::PURCHASE_SALES)->value('id'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($pm)->get('/erp/farmers/export');
        $response->assertOk();
        $this->assertEquals('text/csv; charset=utf-8', $response->headers->get('Content-Type'));
    }

    public function test_customer_creation_supports_unregistered_and_registered_gstin(): void
    {
        $pm = User::factory()->create([
            'role_id' => Role::where('name', Role::PURCHASE_SALES)->value('id'),
            'is_active' => true,
        ]);

        // 1. Unregistered customer (no GSTIN)
        $this->actingAs($pm)->post('/erp/customers', [
            'name' => 'Kisan Mandi Local Buyer',
            'customer_type' => 'market_buyer',
            'mobile' => '9835077777',
            'city' => 'Arrah',
            'payment_terms_days' => 0,
            'is_active' => '1',
        ])->assertRedirect();

        $c1 = Customer::where('name', 'Kisan Mandi Local Buyer')->first();
        $this->assertNotNull($c1);
        $this->assertNull($c1->gstin);

        // 2. GST registered mill
        $this->actingAs($pm)->post('/erp/customers', [
            'name' => 'Patna Agro Mills',
            'customer_type' => 'rice_mill',
            'mobile' => '9835066666',
            'city' => 'Patna',
            'gstin' => '10AABCP1234G1Z3',
            'credit_limit' => 2000000,
            'payment_terms_days' => 15,
            'is_active' => '1',
        ])->assertRedirect();

        $c2 = Customer::where('name', 'Patna Agro Mills')->first();
        $this->assertNotNull($c2);
        $this->assertEquals('10AABCP1234G1Z3', $c2->gstin);
    }

    public function test_warehouse_creation_and_capacity(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $this->actingAs($admin)->post('/erp/warehouses', [
            'name' => 'South Mandi Godown No 4',
            'code' => 'WH-04',
            'capacity_quintals' => 12500.50,
            'address' => 'Station Road Mandi',
            'is_active' => '1',
        ])->assertRedirect('/erp/warehouses');

        $wh = Warehouse::where('code', 'WH-04')->first();
        $this->assertNotNull($wh);
        $this->assertEquals('12500.50', $wh->capacity_quintals);
    }

    public function test_admin_can_configure_farmer_by_farmer_delayed_bonus_and_non_admin_cannot(): void
    {
        $pm = User::factory()->create([
            'role_id' => Role::where('name', Role::PURCHASE_SALES)->value('id'),
            'is_active' => true,
        ]);

        // Non-admin does NOT see the Admin Only bonus section on /erp/farmers-create
        $pmCreateView = $this->actingAs($pm)->get('/erp/farmers-create');
        $pmCreateView->assertOk();
        $pmCreateView->assertDontSee('delayed_bonus_rate', false);

        // Non-admin attempting to submit custom bonus fields is ignored
        $this->actingAs($pm)->post('/erp/farmers', [
            'name' => 'Suresh Yadav',
            'mobile' => '9835011111',
            'village' => 'Sasaram',
            'farmer_bonus_section_present' => '1',
            'delayed_bonus_enabled' => '0',
            'delayed_bonus_rate' => '5.00',
            'delayed_bonus_mode' => 'flat',
            'is_active' => '1',
        ])->assertRedirect();

        $farmer = Farmer::where('name', 'Suresh Yadav')->first();
        $this->assertNotNull($farmer);
        $this->assertTrue($farmer->delayed_bonus_enabled);
        $this->assertNull($farmer->delayed_bonus_rate);
        $this->assertNull($farmer->delayed_bonus_mode);

        // Admin DOES see the bonus card on /erp/farmers-create and can set custom farmer-by-farmer rate & mode
        $this->flushSession();
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $adminCreateView = $this->actingAs($admin)->get('/erp/farmers-create');
        $adminCreateView->assertOk();
        $adminCreateView->assertSee('Farmer Delayed Payment Bonus');
        $adminCreateView->assertSee('delayed_bonus_rate', false);

        $this->actingAs($admin)->post('/erp/farmers', [
            'name' => 'Mahendra Pratap',
            'mobile' => '9835022222',
            'village' => 'Dehri',
            'farmer_bonus_section_present' => '1',
            'delayed_bonus_enabled' => '1',
            'delayed_bonus_rate' => '2.50',
            'delayed_bonus_mode' => 'flat',
            'is_active' => '1',
        ])->assertRedirect();

        $customFarmer = Farmer::where('name', 'Mahendra Pratap')->first();
        $this->assertNotNull($customFarmer);
        $this->assertTrue($customFarmer->isDelayedBonusEnabled());
        $this->assertSame('2.50', $customFarmer->effective_bonus_rate);
        $this->assertSame('flat', $customFarmer->effective_bonus_mode);

        // Farmer show view displays custom rate
        $showView = $this->actingAs($admin)->get("/erp/farmers/{$customFarmer->id}");
        $showView->assertOk();
        $showView->assertSee('2.50% (Flat)');

        // Payment form pre-populates this farmer's custom bonus rate & mode
        $crop = Crop::create(['name' => 'Wheat', 'code' => 'WHT', 'unit' => 'Qtl', 'is_active' => true]);
        $wh = Warehouse::create(['name' => 'Depot 1', 'code' => 'DP1', 'is_active' => true]);

        $purchase = (new \App\Services\ProcurementService)->create([
            'purchase_date' => now()->subDays(20)->toDateString(),
            'farmer_id' => $customFarmer->id,
            'crop_id' => $crop->id,
            'warehouse_id' => $wh->id,
            'gross_weight_kg' => '1000.000',
            'tare_weight_kg' => '0.000',
            'rate_per_qtl' => '2000.00', // 20,000.00
        ], $admin->id, finalize: true);

        $pmtCreate = $this->actingAs($admin)->get(route('farmer-payments.create', ['purchase_id' => $purchase->id]));
        $pmtCreate->assertOk();
        $pmtCreate->assertSee('data-bonus-rate="2.50"', false);
        $pmtCreate->assertSee('data-bonus-mode="flat"', false);

        // Recording payment applies farmer's 2.50% flat bonus -> 20,000 * 2.50% = 500.00
        $this->actingAs($admin)->post(route('farmer-payments.store'), [
            'purchase_id' => $purchase->id,
            'amount' => '20000.00',
            'payment_method' => 'cash',
        ])->assertRedirect();

        $payment = \App\Models\PurchasePayment::where('purchase_id', $purchase->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame('2.50', $payment->bonus_percentage);
        $this->assertSame('flat', $payment->bonus_calculation_mode);
        $this->assertSame('500.00', $payment->bonus_amount);
        $this->assertSame('20500.00', $payment->total_payout);
    }

    public function test_farmer_directory_live_ledger_aggregates_filters_and_profile_tabs(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $farmerDue = Farmer::create([
            'farmer_id' => 'FRM261001',
            'name' => 'Rajendra Prasad Verma',
            'mobile' => '9835099991',
            'village' => 'Sasaram',
            'opening_balance' => '5000.00',
            'is_active' => true,
        ]);

        $farmerSettled = Farmer::create([
            'farmer_id' => 'FRM261002',
            'name' => 'Suresh Kumar Mahto',
            'mobile' => '9835099992',
            'village' => 'Bikramganj',
            'opening_balance' => '0.00',
            'is_active' => true,
        ]);

        $crop = Crop::create(['name' => 'Paddy (Dhan)', 'code' => 'PADDY', 'unit' => 'Qtl', 'is_active' => true]);
        $wh = Warehouse::create(['name' => 'Main Godown', 'code' => 'WH-01', 'is_active' => true]);

        // 15.000 Qtl @ ₹2,200 = ₹33,000 unpaid finalized purchase -> Net Payable = 5,000 + 33,000 = ₹38,000
        $purchase = (new \App\Services\ProcurementService)->create([
            'purchase_date' => now()->subDays(10)->toDateString(),
            'farmer_id' => $farmerDue->id,
            'crop_id' => $crop->id,
            'warehouse_id' => $wh->id,
            'gross_weight_kg' => '1550.000',
            'tare_weight_kg' => '50.000',
            'rate_per_qtl' => '2200.00',
        ], $admin->id, finalize: true);

        $indexResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/farmers');
        $indexResp->assertOk();
        $indexResp->assertSee('Rajendra Prasad Verma');
        $indexResp->assertSee('38,000.00');
        $indexResp->assertSee('15.00');

        // Pending dues filter shows only farmerDue
        $pendingResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/farmers?due=pending');
        $pendingResp->assertOk();
        $pendingResp->assertSee('Rajendra Prasad Verma');
        $pendingResp->assertDontSee('Suresh Kumar Mahto');

        // Settled filter shows only farmerSettled
        $settledResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/farmers?due=settled');
        $settledResp->assertOk();
        $settledResp->assertSee('Suresh Kumar Mahto');
        $settledResp->assertDontSee('Rajendra Prasad Verma');

        // Quick Pay shortcut (?farmer_id=) pre-selects oldest unpaid purchase
        $quickPayResp = $this->actingAs($admin)->get(route('farmer-payments.create', ['farmer_id' => $farmerDue->id]));
        $quickPayResp->assertOk();
        $quickPayResp->assertSee($purchase->purchase_number);

        // Farmer Profile displays live purchase receipt in ledger tab
        $profileResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get("/erp/farmers/{$farmerDue->id}");
        $profileResp->assertOk();
        $profileResp->assertSee($purchase->purchase_number);
        $profileResp->assertSee('38,000.00');
    }

    public function test_crops_index_kpis_tabs_search_and_export(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $activeCrop = Crop::create([
            'name' => 'Premium Basmati Paddy',
            'code' => 'BASMATI',
            'unit' => 'quintal',
            'hsn_code' => '1006',
            'current_purchase_rate' => 3400.00,
            'max_purchase_rate' => 3600.00,
            'rate_note' => 'Max 12% moisture',
            'show_rate_on_website' => true,
            'is_active' => true,
        ]);

        $activeCrop->varieties()->create([
            'name' => 'Pusa 1121',
            'code' => 'PUSA-1121',
            'standard_moisture_pct' => 12.0,
            'default_grade' => 'Grade A',
            'is_active' => true,
        ]);

        $inactiveCrop = Crop::create([
            'name' => 'Old Mustard Seed',
            'code' => 'MUSTARD_OLD',
            'unit' => 'quintal',
            'hsn_code' => '1207',
            'current_purchase_rate' => null,
            'show_rate_on_website' => false,
            'is_active' => false,
        ]);

        // 1. Index renders with KPIs and Banner
        $indexResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/crops');
        $indexResp->assertOk();
        $indexResp->assertSee('Crops &amp; Varieties', false);
        $indexResp->assertSee('Premium Basmati Paddy');
        $indexResp->assertSee('BASMATI');
        $indexResp->assertSee('1 Varieties');

        // 1b. Show page renders variety details
        $showResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get("/erp/crops/{$activeCrop->id}");
        $showResp->assertOk();
        $showResp->assertSee('Pusa 1121');
        $showResp->assertSee('PUSA-1121');

        // 2. Tab: active only
        $activeTabResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/crops?tab=active');
        $activeTabResp->assertOk();
        $activeTabResp->assertSee('Premium Basmati Paddy');
        $activeTabResp->assertDontSee('Old Mustard Seed');

        // 3. Tab: inactive only
        $inactiveTabResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/crops?tab=inactive');
        $inactiveTabResp->assertOk();
        $inactiveTabResp->assertSee('Old Mustard Seed');
        $inactiveTabResp->assertDontSee('Premium Basmati Paddy');

        // 4. Tab: rates_live only
        $ratesLiveResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/crops?tab=rates_live');
        $ratesLiveResp->assertOk();
        $ratesLiveResp->assertSee('Premium Basmati Paddy');
        $ratesLiveResp->assertDontSee('Old Mustard Seed');

        // 5. Search query
        $searchResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/crops?q=Basmati');
        $searchResp->assertOk();
        $searchResp->assertSee('Premium Basmati Paddy');
        $searchResp->assertDontSee('Old Mustard Seed');

        // 6. Toggle crop active state
        $toggleResp = $this->actingAs($admin)->patch("/erp/crops/{$activeCrop->id}/toggle");
        $toggleResp->assertRedirect();
        $this->assertFalse($activeCrop->fresh()->is_active);

        // 7. CSV Export
        $exportResp = $this->actingAs($admin)->get('/erp/crops/export');
        $exportResp->assertOk();
        $this->assertStringContainsString('text/csv', $exportResp->headers->get('content-type'));
        $content = $exportResp->streamedContent();
        $this->assertStringContainsString('Crop Name', $content);
        $this->assertStringContainsString('Premium Basmati Paddy', $content);
        $this->assertStringContainsString('BASMATI', $content);
    }

    public function test_warehouses_index_kpis_tabs_search_and_export(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $supervisor = User::factory()->create([
            'role_id' => Role::where('name', Role::PURCHASE_SALES)->value('id'),
            'name' => 'Alok Kumar Supervisor',
            'is_active' => true,
        ]);

        $whActive = Warehouse::create([
            'name' => 'Central Mandi Complex Godown',
            'code' => 'WH-CENTRAL',
            'address' => 'Plot 5A Mandi Complex',
            'capacity_quintals' => 15000.00,
            'supervisor_id' => $supervisor->id,
            'phone' => '9876500001',
            'is_active' => true,
        ]);

        $whInactive = Warehouse::create([
            'name' => 'Old Temporary Tin Shed',
            'code' => 'WH-SHED',
            'address' => 'Old By-pass Yard',
            'capacity_quintals' => 2000.00,
            'is_active' => false,
        ]);

        // 1. Index renders successfully with KPIs
        $indexResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/warehouses');
        $indexResp->assertOk();
        $indexResp->assertSee('Warehouses &amp; Godowns', false);
        $indexResp->assertSee('Central Mandi Complex Godown');
        $indexResp->assertSee('WH-CENTRAL');
        $indexResp->assertSee('Alok Kumar Supervisor');

        // 2. Tab: active only
        $activeTabResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/warehouses?tab=active');
        $activeTabResp->assertOk();
        $activeTabResp->assertSee('Central Mandi Complex Godown');
        $activeTabResp->assertDontSee('Old Temporary Tin Shed');

        // 3. Tab: inactive only
        $inactiveTabResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/warehouses?tab=inactive');
        $inactiveTabResp->assertOk();
        $inactiveTabResp->assertSee('Old Temporary Tin Shed');
        $inactiveTabResp->assertDontSee('Central Mandi Complex Godown');

        // 4. Search query
        $searchResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/warehouses?q=Central');
        $searchResp->assertOk();
        $searchResp->assertSee('Central Mandi Complex Godown');
        $searchResp->assertDontSee('Old Temporary Tin Shed');

        // 5. Toggle warehouse status
        $toggleResp = $this->actingAs($admin)->patch("/erp/warehouses/{$whActive->id}/toggle");
        $toggleResp->assertRedirect();
        $this->assertFalse($whActive->fresh()->is_active);

        // 6. CSV Export
        $exportResp = $this->actingAs($admin)->get('/erp/warehouses/export');
        $exportResp->assertOk();
        $this->assertStringContainsString('text/csv', $exportResp->headers->get('content-type'));
        $content = $exportResp->streamedContent();
        $this->assertStringContainsString('Warehouse Name', $content);
        $this->assertStringContainsString('Central Mandi Complex Godown', $content);
        $this->assertStringContainsString('WH-CENTRAL', $content);
    }

    public function test_expense_categories_index_kpis_tabs_search_edit_and_export(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $catActive = ExpenseCategory::create([
            'name' => 'Labor and Hamali Charges',
            'code' => 'LABOR',
            'description' => 'Unloading and bagging laborers',
            'is_active' => true,
        ]);

        $catInactive = ExpenseCategory::create([
            'name' => 'Obsolete Discontinued Head',
            'code' => 'OBSOLETE',
            'description' => 'Inactive legacy expense head',
            'is_active' => false,
        ]);

        // 1. Index renders successfully with KPIs
        $indexResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/expense-categories');
        $indexResp->assertOk();
        $indexResp->assertSee('Expense Categories');
        $indexResp->assertSee('Labor and Hamali Charges');
        $indexResp->assertSee('LABOR');

        // 2. Tab: active only
        $activeTabResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/expense-categories?tab=active');
        $activeTabResp->assertOk();
        $activeTabResp->assertSee('Labor and Hamali Charges');
        $activeTabResp->assertDontSee('Obsolete Discontinued Head');

        // 3. Tab: inactive only
        $inactiveTabResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/expense-categories?tab=inactive');
        $inactiveTabResp->assertOk();
        $inactiveTabResp->assertSee('Obsolete Discontinued Head');
        $inactiveTabResp->assertDontSee('Labor and Hamali Charges');

        // 4. Search query
        $searchResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/expense-categories?q=Hamali');
        $searchResp->assertOk();
        $searchResp->assertSee('Labor and Hamali Charges');
        $searchResp->assertDontSee('Obsolete Discontinued Head');

        // 5. Store a new category via POST
        $storeResp = $this->actingAs($admin)->post('/erp/expense-categories', [
            'name' => 'Weighbridge Fees',
            'code' => 'WEIGH',
            'description' => 'Mandi dharam kanta charges',
        ]);
        $storeResp->assertRedirect();
        $this->assertDatabaseHas('expense_categories', [
            'code' => 'WEIGH',
            'name' => 'Weighbridge Fees',
        ]);

        // 6. Update existing category via PUT
        $updateResp = $this->actingAs($admin)->put("/erp/expense-categories/{$catActive->id}", [
            'name' => 'Labor and Hamali Outlays',
            'code' => 'LABOR',
            'description' => 'Updated labor charges',
        ]);
        $updateResp->assertRedirect();
        $this->assertSame('Labor and Hamali Outlays', $catActive->fresh()->name);

        // 7. Toggle status
        $toggleResp = $this->actingAs($admin)->patch("/erp/expense-categories/{$catActive->id}/toggle");
        $toggleResp->assertRedirect();
        $this->assertFalse($catActive->fresh()->is_active);

        // 8. CSV Export
        $exportResp = $this->actingAs($admin)->get('/erp/expense-categories/export');
        $exportResp->assertOk();
        $this->assertStringContainsString('text/csv', $exportResp->headers->get('content-type'));
        $content = $exportResp->streamedContent();
        $this->assertStringContainsString('Category Name', $content);
        $this->assertStringContainsString('Labor and Hamali Outlays', $content);
        $this->assertStringContainsString('LABOR', $content);
    }
}
