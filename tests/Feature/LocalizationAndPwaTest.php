<?php

namespace Tests\Feature;

use App\Http\Middleware\SetLocale;
use App\Models\Crop;
use App\Models\Farmer;
use App\Models\Purchase;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LocalizationAndPwaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_default_locale_is_hindi_for_first_time_visitors(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('<html lang="hi"', false);
        $response->assertSee(__('auth.login_title'));
        $this->assertEquals('hi', App::getLocale());
    }

    public function test_guest_can_switch_to_english_and_back_to_hindi(): void
    {
        // 1. Switch to English
        $response = $this->get('/locale/en');
        $response->assertRedirect();
        $response->assertSessionHas('locale', 'en');
        $response->assertCookie('kisan_locale', 'en');

        // 2. Visit login with English cookie
        $response = $this->withCookie('kisan_locale', 'en')->get('/login');
        $response->assertOk();
        $response->assertSee('<html lang="en"', false);
        $response->assertSee('Staff Sign In');
        $this->assertEquals('en', App::getLocale());

        // 3. Switch back to Hindi
        $response = $this->get('/locale/hi');
        $response->assertRedirect();
        $response->assertSessionHas('locale', 'hi');
        $response->assertCookie('kisan_locale', 'hi');

        // 4. Visit login with Hindi cookie
        $response = $this->withCookie('kisan_locale', 'hi')->get('/login');
        $response->assertOk();
        $response->assertSee('<html lang="hi"', false);
        $response->assertSee('कर्मचारी लॉगिन');
    }

    public function test_authenticated_user_locale_preference_persists_in_database(): void
    {
        $adminRole = Role::where('name', Role::ADMIN)->first();
        $user = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
            'locale' => 'hi',
        ]);

        $this->actingAs($user);

        // Switch to English
        $this->get('/locale/en');
        $this->assertEquals('en', $user->fresh()->locale);

        // Switch to Hindi
        $this->get('/locale/hi');
        $this->assertEquals('hi', $user->fresh()->locale);
    }

    public function test_unsupported_locale_defaults_to_hindi(): void
    {
        $response = $this->get('/locale/fr');
        $response->assertRedirect();
        $response->assertSessionHas('locale', SetLocale::DEFAULT_LOCALE);
        $this->assertEquals(SetLocale::DEFAULT_LOCALE, 'hi');
    }

    public function test_pwa_manifest_is_valid_and_accessible(): void
    {
        $manifestPath = public_path('manifest.json');
        $this->assertFileExists($manifestPath);

        $json = json_decode(file_get_contents($manifestPath), true);
        $this->assertIsArray($json);
        $this->assertEquals('Kisan Enterprises', $json['name']);
        $this->assertEquals('Kisan ERP', $json['short_name']);
        $this->assertEquals('/erp/dashboard', $json['start_url']);
        $this->assertEquals('standalone', $json['display']);
        $this->assertEquals('#21864B', $json['theme_color']);
        $this->assertEquals('#F6F8F6', $json['background_color']);

        // Check required icons exist on disk
        $this->assertFileExists(public_path('icons/icon-192.png'));
        $this->assertFileExists(public_path('icons/icon-512.png'));
        $this->assertFileExists(public_path('icons/icon-maskable-192.png'));
        $this->assertFileExists(public_path('icons/icon-maskable-512.png'));
    }

    public function test_service_worker_file_exists(): void
    {
        $this->assertFileExists(public_path('sw.js'));
    }

    public function test_offline_page_renders_successfully(): void
    {
        $response = $this->get('/offline');
        $response->assertOk();
        $response->assertSee('Kisan Enterprises');
        $response->assertSee('offline-card', false);
    }

    public function test_mobile_bottom_bar_renders_with_authorized_modules(): void
    {
        $adminRole = Role::where('name', Role::ADMIN)->first();
        $user = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->withSession(['locale' => 'hi'])
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Mobile Safari/537.36',
            ])
            ->get('/erp/dashboard');

        $response->assertOk();
        $response->assertSee('mobile-bottom-nav', false);
        $response->assertSee('होम');
        $response->assertSee('खरीद');
        $response->assertSee('बिक्री');
        $response->assertSee('स्टॉक');
        $response->assertSee('अन्य');
    }

    public function test_mobile_bottom_bar_in_english(): void
    {
        $adminRole = Role::where('name', Role::ADMIN)->first();
        $user = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
            'locale' => 'en',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['locale' => 'en'])
            ->withHeaders([
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 Mobile/15E148 Safari/604.1',
            ])
            ->get('/erp/dashboard');

        $response->assertOk();
        $response->assertSee('mobile-bottom-nav', false);
        $response->assertSee('Home');
        $response->assertSee('Purchase');
        $response->assertSee('Sales');
        $response->assertSee('Stock');
        $response->assertSee('More');
    }

    public function test_dynamic_farmer_and_customer_data_is_never_altered_by_localization(): void
    {
        $farmerHindi = Farmer::create([
            'farmer_id' => 'FRM-TEST-001',
            'name' => 'रामेश्वर प्रसाद यादव',
            'mobile' => '9876543210',
            'village' => 'बिसवां खुर्द',
            'district' => 'सीतापुर',
            'state' => 'उत्तर प्रदेश',
        ]);

        $farmerEnglish = Farmer::create([
            'farmer_id' => 'FRM-TEST-002',
            'name' => 'Gurpreet Singh Dhillon',
            'mobile' => '9876543211',
            'village' => 'Amargarh',
            'district' => 'Ludhiana',
            'state' => 'Punjab',
        ]);

        $adminRole = Role::where('name', Role::ADMIN)->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);

        // Check in Hindi
        $responseHi = $this->actingAs($admin)->withSession(['locale' => 'hi'])->get('/erp/farmers');
        $responseHi->assertOk();
        $responseHi->assertSee('रामेश्वर प्रसाद यादव');
        $responseHi->assertSee('Gurpreet Singh Dhillon');

        // Check in English
        $responseEn = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/farmers');
        $responseEn->assertOk();
        $responseEn->assertSee('रामेश्वर प्रसाद यादव');
        $responseEn->assertSee('Gurpreet Singh Dhillon');
    }

    public function test_hindi_pdf_receipt_renders_without_error(): void
    {
        $adminRole = Role::where('name', Role::ADMIN)->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);

        $farmer = Farmer::first() ?? Farmer::create([
            'farmer_id' => 'FRM-TEST-003',
            'name' => 'कमलेश पटेल',
            'mobile' => '9876543219',
            'village' => 'हरदोई रोड',
        ]);

        $crop = Crop::first() ?? Crop::create([
            'name' => 'Wheat (Gehun)',
            'code' => 'WHEAT',
            'unit' => 'quintal',
            'hsn_code' => '1001',
            'is_active' => true,
        ]);

        $warehouse = Warehouse::first() ?? Warehouse::create([
            'name' => 'Main Mandi Godown',
            'code' => 'WH-MAIN',
            'capacity_qtl' => 50000,
            'is_active' => true,
        ]);

        $purchase = Purchase::create([
            'purchase_number' => 'PUR-TEST-001',
            'purchase_date' => now(),
            'farmer_id' => $farmer->id,
            'crop_id' => $crop->id,
            'warehouse_id' => $warehouse->id,
            'gross_weight_kg' => '5000.000',
            'tare_weight_kg' => '500.000',
            'net_weight_kg' => '4500.000',
            'net_weight_qtl' => '45.000',
            'rate_per_qtl' => '2400.00',
            'base_amount' => '108000.00',
            'deductions_amount' => '0.00',
            'additions_amount' => '0.00',
            'net_payable_amount' => '108000.00',
            'paid_amount' => '0.00',
            'balance_due' => '108000.00',
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['locale' => 'hi'])
            ->get("/erp/purchases/{$purchase->id}/pdf");

        $response->assertOk();
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertNotEmpty($response->getContent());
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_mobile_app_view_elements_on_indexes_and_forms(): void
    {
        $adminRole = Role::where('name', Role::ADMIN)->first();
        $admin = User::factory()->create(['role_id' => $adminRole->id, 'is_active' => true]);

        // 1. Verify Purchase index has mobile card feed, mobile FAB, and mobile back button in header
        $response = $this->actingAs($admin)->get('/erp/purchases');
        $response->assertOk();
        $response->assertSee('mobile-card-list', false);
        $response->assertSee('mobile-fab', false);
        $response->assertSee('bi-chevron-left', false);

        // 2. Verify Daybook renders mobile layout and back navigation
        $daybookResp = $this->actingAs($admin)->get(route('accounts.daybook'));
        $daybookResp->assertOk();
        $daybookResp->assertSee('bi-chevron-left', false);

        // 3. Verify Farmers index has mobile card feed and FAB
        $farmerResp = $this->actingAs($admin)->get('/erp/farmers');
        $farmerResp->assertOk();
        $farmerResp->assertSee('mobile-card-list', false);
        $farmerResp->assertSee('mobile-fab', false);

        // 4. Verify Purchase Create form has sticky mobile action bar and decimal inputmode
        $formResp = $this->actingAs($admin)->get(route('purchases.create'));
        $formResp->assertOk();
        $formResp->assertSee('mobile-sticky-actions', false);
        $formResp->assertSee('inputmode="decimal"', false);
    }
}
