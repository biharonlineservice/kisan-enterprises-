<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/erp/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee(__('auth.login_title'));
    }

    public function test_user_can_login_with_mobile_and_password_and_redirects_to_dashboard(): void
    {
        $adminRole = Role::where('name', Role::ADMIN)->first();
        $user = User::factory()->create([
            'mobile' => '9876543210',
            'role_id' => $adminRole->id,
            'password' => 'Secret123',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'mobile' => '9876543210',
            'password' => 'Secret123',
        ]);

        $response->assertRedirect('/erp/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_normalizes_plus91_and_rejects_email_input(): void
    {
        $adminRole = Role::where('name', Role::ADMIN)->first();
        $user = User::factory()->create([
            'email' => 'admin@kisan.local',
            'mobile' => '9876543211',
            'role_id' => $adminRole->id,
            'password' => 'Secret123',
            'is_active' => true,
        ]);

        // Email login must be rejected now that login uses Mobile + Password
        $emailAttempt = $this->post('/login', [
            'mobile' => 'admin@kisan.local',
            'password' => 'Secret123',
        ]);
        $emailAttempt->assertSessionHasErrors('mobile');
        $this->assertGuest();

        // +91 formatted mobile number is normalized cleanly
        $mobileAttempt = $this->post('/login', [
            'mobile' => '+91 98765 43211',
            'password' => 'Secret123',
        ]);
        $mobileAttempt->assertRedirect('/erp/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $adminRole = Role::where('name', Role::ADMIN)->first();
        User::factory()->create([
            'mobile' => '9876543212',
            'role_id' => $adminRole->id,
            'password' => 'Secret123',
            'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'mobile' => '9876543212',
            'password' => 'Secret123',
        ]);

        $response->assertSessionHasErrors('mobile');
        $this->assertGuest();
    }

    public function test_login_is_throttled_after_consecutive_failures(): void
    {
        RateLimiter::clear('9999999999|127.0.0.1');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'mobile' => '9999999999',
                'password' => 'wrong-pass',
            ]);
        }

        $response = $this->post('/login', [
            'mobile' => '9999999999',
            'password' => 'wrong-pass',
        ]);

        $response->assertSessionHasErrors('mobile');
        $err = session('errors')->first('mobile');
        $this->assertTrue(str_contains($err, 'Too many login attempts') || str_contains($err, 'बहुत अधिक लॉगिन'));
    }

    public function test_user_can_logout(): void
    {
        $adminRole = Role::where('name', Role::ADMIN)->first();
        $user = User::factory()->create([
            'role_id' => $adminRole->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_user_with_must_change_password_is_redirected_to_change_password(): void
    {
        $role = Role::where('name', Role::PURCHASE_SALES)->first();
        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $response = $this->actingAs($user)->get('/erp/dashboard');
        $response->assertRedirect('/erp/change-password');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $adminRole = Role::where('name', Role::ADMIN)->first();
        $user = User::factory()->create([
            'role_id' => $adminRole->id,
            'locale' => 'en',
            'is_active' => true,
        ]);

        // Seed a delayed purchase with custom farmer bonus & a finalized sales dispatch challan
        $wh = \App\Models\Warehouse::create([
            'name' => 'Central Godown',
            'code' => 'WH-MAIN',
            'capacity_quintals' => 1000,
            'is_active' => true,
        ]);
        $crop = \App\Models\Crop::create([
            'name' => 'Wheat',
            'code' => 'WHT',
            'unit' => 'Qtl',
            'is_active' => true,
        ]);
        $farmer = \App\Models\Farmer::create([
            'farmer_id' => 'FRM00010',
            'name' => 'Rajendra Singh',
            'delayed_bonus_enabled' => true,
            'delayed_bonus_rate' => '2.00',
            'delayed_bonus_mode' => 'monthly',
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        $customer = \App\Models\Customer::create([
            'customer_id' => 'CUS00010',
            'name' => 'Ganga Flour Mills',
            'customer_type' => 'flour_mill',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        // Purchase 30 days ago: 20 Qtl @ 2500 = 50,000.00 -> 2% monthly for 30 days = +1,000.00 accrued bonus
        $purchase = (new \App\Services\ProcurementService)->create([
            'purchase_date' => now()->subDays(30)->toDateString(),
            'farmer_id' => $farmer->id,
            'crop_id' => $crop->id,
            'warehouse_id' => $wh->id,
            'gross_weight_kg' => '2000.000',
            'tare_weight_kg' => '0.000',
            'rate_per_qtl' => '2500.00',
        ], $user->id, finalize: true);

        $invoice = (new \App\Services\SalesService)->createInvoice([
            'invoice_date' => now()->toDateString(),
            'customer_id' => $customer->id,
            'vehicle_number' => 'BR 01 GA 5555',
        ], [
            [
                'crop_id' => $crop->id,
                'warehouse_id' => $wh->id,
                'quantity_qtl' => '5.000',
                'rate_per_qtl' => '2800.00',
            ],
        ], $user->id, finalize: true);

        $response = $this->actingAs($user)->get('/erp/dashboard');
        $response->assertOk();
        $response->assertSee(__('app.financial_year'));
        $response->assertSee('Delayed Payment Bonus');
        $response->assertSee('1,000.00'); // Accrued bonus on Rajendra Singh's 30-day pending purchase
        $response->assertSee($purchase->purchase_number);
        $response->assertSee($invoice->challanNumber());
        $response->assertSee('BR 01 GA 5555');
        $response->assertSee('Central Godown');
        $response->assertSee("Today's Live Mandi Crop Purchase Rates");
        $response->assertSee('Rate not set');

        // Verify live mandi crop rate & price change diff badge on dashboard
        $crop->update([
            'current_purchase_rate' => '2400.00',
            'show_rate_on_website' => true,
            'rate_updated_at' => now()->subDay(),
        ]);
        $crop->update([
            'current_purchase_rate' => '2475.00',
            'rate_updated_at' => now(),
        ]);

        $updatedResponse = $this->actingAs($user)->get('/erp/dashboard');
        $updatedResponse->assertOk();
        $updatedResponse->assertSee('₹2,475');
        $updatedResponse->assertSee('+₹75');
        $updatedResponse->assertSee('Prev: ₹2,400');
    }

    public function test_login_page_prevents_browser_cache_and_exposes_csrf_refresh_endpoint(): void
    {
        $loginResponse = $this->get('/login');
        $loginResponse->assertOk();
        $this->assertStringContainsString('no-store', (string) $loginResponse->headers->get('Cache-Control'));

        $csrfResponse = $this->getJson('/csrf-token');
        $csrfResponse->assertOk()->assertJsonStructure(['token']);
        $this->assertNotEmpty($csrfResponse->json('token'));
    }

    public function test_expired_csrf_419_on_web_form_redirects_cleanly_instead_of_error_screen(): void
    {
        \Illuminate\Support\Facades\Route::post('/_test-419-login', function () {
            throw new \Illuminate\Session\TokenMismatchException('CSRF token mismatch.');
        })->middleware('web');

        $response = $this->from('/login')->post('/_test-419-login', [
            'mobile' => '9000000001',
            'password' => 'Secret123',
            '_token' => 'expired-token',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('warning');
        $this->assertSame('9000000001', session('_old_input.mobile'));
        $this->assertNull(session('_old_input.password'));
    }

    public function test_custom_419_error_view_renders_with_botanical_branding_and_countdown(): void
    {
        $view = view('errors.419')->render();
        $this->assertStringContainsString('HTTP 419', $view);
        $this->assertStringContainsString('Kisan Enterprises', $view);
        $this->assertStringContainsString('timerSec', $view);
        $this->assertStringContainsString('btnContinue', $view);
    }
}
