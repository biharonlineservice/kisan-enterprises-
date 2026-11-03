<?php

namespace Tests\Feature;

use App\Mail\TestSmtpMail;
use App\Models\Role;
use App\Models\User;
use App\Services\FinancialYear;
use App\Services\SettingsService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role_id' => Role::where('name', Role::PURCHASE_SALES)->value('id'),
            'is_active' => true,
        ]);
    }

    public function test_admin_can_access_all_settings_tabs_with_kpis(): void
    {
        $tabs = ['business', 'documents', 'numbering', 'system', 'mail', 'whatsapp'];

        foreach ($tabs as $tab) {
            $response = $this->actingAs($this->admin)->get("/erp/settings?tab={$tab}");
            $response->assertStatus(200);
            $response->assertSee('erp-hero-banner', false);
            $response->assertSee('erp-kpi-card', false);
            $response->assertSee(FinancialYear::label());
        }
    }

    public function test_admin_can_update_business_settings(): void
    {
        $response = $this->actingAs($this->admin)->put('/erp/settings/business', [
            'business_name' => 'Kisan Agri Enterprises Pvt Ltd',
            'business_tagline' => 'Certified Grain Procurement & Modern Milling',
            'business_state' => 'Uttar Pradesh',
            'business_address' => 'Plot 45, Mandi Samiti Campus, Chandauli, UP - 232104',
            'business_phone' => '9876543210',
            'business_alt_phone' => '05412234567',
            'business_email' => 'admin@kisanagri.com',
            'business_gstin' => '09AAACK1234M1Z5',
            'business_pan' => 'AAACK1234M',
        ]);

        $response->assertRedirect('/erp/settings?tab=business');
        $response->assertSessionHas('success');

        $settings = app(SettingsService::class);
        $this->assertSame('Kisan Agri Enterprises Pvt Ltd', $settings->get('business.name'));
        $this->assertSame('09AAACK1234M1Z5', $settings->get('business.gstin'));
    }

    public function test_admin_can_update_documents_settings(): void
    {
        $response = $this->actingAs($this->admin)->put('/erp/settings/documents', [
            'invoice_header' => 'M/s Kisan Enterprises &bull; Wholesale Grain Trading',
            'invoice_footer' => 'All disputes subject to Chandauli jurisdiction only.',
            'receipt_footer' => 'Payment will be credited to registered farmer bank account within 24 hours.',
            'show_signature' => '1',
            'tax_enabled' => '1',
            'tax_label' => 'GST',
            'tax_default_rate' => '5.00',
        ]);

        $response->assertRedirect('/erp/settings?tab=documents');
        $response->assertSessionHas('success');

        $settings = app(SettingsService::class);
        $this->assertSame('M/s Kisan Enterprises &bull; Wholesale Grain Trading', $settings->get('documents.invoice_header'));
        $this->assertSame('1', $settings->get('tax.enabled'));
        $this->assertSame('5.00', $settings->get('tax.default_rate'));
    }

    public function test_admin_can_update_numbering_settings(): void
    {
        $response = $this->actingAs($this->admin)->put('/erp/settings/numbering', [
            'purchase' => 'PUR',
            'sales_order' => 'SO',
            'sales_invoice' => 'INV',
            'farmer_payment' => 'PAY',
            'customer_receipt' => 'REC',
            'expense' => 'EXP',
            'farmer' => 'FRM',
            'customer' => 'CUST',
            'padding' => '6',
        ]);

        $response->assertRedirect('/erp/settings?tab=numbering');
        $response->assertSessionHas('success');

        $settings = app(SettingsService::class);
        $this->assertSame('6', $settings->get('numbering.padding'));
    }

    public function test_admin_can_update_system_settings(): void
    {
        $response = $this->actingAs($this->admin)->put('/erp/settings/system', [
            'date_format' => 'd-m-Y',
            'financial_year_start_month' => '4',
            'session_timeout_minutes' => '60',
            'low_stock_threshold_qtl' => '15.50',
            'allow_negative_stock' => '0',
            'delayed_bonus_section_present' => '1',
            'delayed_bonus_enabled' => '1',
            'delayed_bonus_rate' => '1.50',
            'delayed_bonus_mode' => 'monthly',
        ]);

        $response->assertRedirect('/erp/settings?tab=system');
        $response->assertSessionHas('success');

        $settings = app(SettingsService::class);
        $this->assertSame('60', $settings->get('security.session_timeout_minutes'));
        $this->assertSame('15.50', $settings->get('inventory.low_stock_threshold_qtl'));
        $this->assertSame('1.50', $settings->get('procurement.delayed_bonus_rate'));
    }

    public function test_admin_can_update_mail_settings(): void
    {
        $response = $this->actingAs($this->admin)->put('/erp/settings/mail', [
            'mailer' => 'smtp',
            'host' => 'smtp.hostinger.com',
            'port' => '465',
            'encryption' => 'ssl',
            'username' => 'info@kisanenterprises.in',
            'password' => 'SecurePass123!',
            'from_address' => 'info@kisanenterprises.in',
            'from_name' => 'Kisan Enterprises',
        ]);

        $response->assertRedirect('/erp/settings?tab=mail');
        $response->assertSessionHas('success');

        $settings = app(SettingsService::class);
        $this->assertSame('smtp', $settings->get('mail.mailer'));
        $this->assertSame('smtp.hostinger.com', $settings->get('mail.host'));
        $this->assertSame('465', $settings->get('mail.port'));
        $this->assertSame('ssl', $settings->get('mail.encryption'));
        $this->assertSame('info@kisanenterprises.in', $settings->get('mail.username'));
        $this->assertSame('SecurePass123!', $settings->get('mail.password'));
        $this->assertSame('info@kisanenterprises.in', $settings->get('mail.from_address'));
        $this->assertSame('Kisan Enterprises', $settings->get('mail.from_name'));
    }

    public function test_admin_can_send_test_email(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)->post('/erp/settings/test-email', [
            'test_email' => 'admin@kisanenterprises.in',
        ]);

        $response->assertRedirect('/erp/settings?tab=mail');
        $response->assertSessionHas('success');

        Mail::assertSent(TestSmtpMail::class, function ($mail) {
            return $mail->hasTo('admin@kisanenterprises.in');
        });
    }

    public function test_admin_can_update_whatsapp_settings(): void
    {
        $response = $this->actingAs($this->admin)->put('/erp/settings/whatsapp', [
            'whatsapp_enabled' => '1',
            'whatsapp_provider' => 'meta',
            'meta_phone_number_id' => '104598214589123',
            'meta_access_token' => 'EAAGtesttoken123',
            'meta_waba_id' => '109823471829341',
            'meta_api_version' => 'v21.0',
            'auto_notify_purchase' => '1',
            'auto_notify_payment' => '1',
            'auto_notify_sale' => '1',
        ]);

        $response->assertRedirect('/erp/settings?tab=whatsapp');
        $response->assertSessionHas('success');

        $settings = app(SettingsService::class);
        $this->assertSame('1', $settings->get('whatsapp.enabled'));
        $this->assertSame('meta', $settings->get('whatsapp.provider'));
        $this->assertSame('104598214589123', $settings->get('whatsapp.meta_phone_number_id'));
        $this->assertSame('EAAGtesttoken123', $settings->get('whatsapp.meta_access_token'));
        $this->assertSame('109823471829341', $settings->get('whatsapp.meta_waba_id'));
        $this->assertSame('1', $settings->get('whatsapp.auto_notify_purchase'));
    }

    public function test_admin_can_send_test_whatsapp_in_log_mode(): void
    {
        $response = $this->actingAs($this->admin)->post('/erp/settings/test-whatsapp', [
            'test_mobile' => '9876543210',
            'test_message' => 'Test Hello from Agri ERP',
        ]);

        $response->assertRedirect('/erp/settings?tab=whatsapp');
        $response->assertSessionHas('success');
    }

    public function test_admin_can_send_test_whatsapp_via_meta_cloud(): void
    {
        $settings = app(SettingsService::class);
        $settings->setMany([
            'whatsapp.enabled' => '1',
            'whatsapp.provider' => 'meta',
            'whatsapp.meta_phone_number_id' => '104598214589123',
            'whatsapp.meta_access_token' => 'EAAGFakeToken',
        ]);

        Http::fake([
            'https://graph.facebook.com/v21.0/104598214589123/messages' => Http::response([
                'messaging_product' => 'whatsapp',
                'contacts' => [['input' => '919876543210', 'wa_id' => '919876543210']],
                'messages' => [['id' => 'wamid.HBgLM...']],
            ], 200),
        ]);

        $response = $this->actingAs($this->admin)->post('/erp/settings/test-whatsapp', [
            'test_mobile' => '9876543210',
        ]);

        $response->assertRedirect('/erp/settings?tab=whatsapp');
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '104598214589123/messages')
                && $request['to'] === '919876543210';
        });
    }

    public function test_unauthorized_staff_cannot_access_or_update_settings(): void
    {
        $this->actingAs($this->staff)->get('/erp/settings')->assertForbidden();
        $this->actingAs($this->staff)->put('/erp/settings/business', [
            'business_name' => 'Hacked Name',
        ])->assertForbidden();
        $this->actingAs($this->staff)->post('/erp/settings/test-email', [
            'test_email' => 'test@hacker.com',
        ])->assertForbidden();
        $this->actingAs($this->staff)->post('/erp/settings/test-whatsapp', [
            'test_mobile' => '9876543210',
        ])->assertForbidden();
    }

    public function test_settings_page_renders_bilingual_strings(): void
    {
        // English
        $enRes = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/settings?tab=business');
        $enRes->assertStatus(200);
        $enRes->assertSee('Business & System Settings');
        $enRes->assertSee('Active Financial Year');
        $enRes->assertSee('Business Profile');

        // Hindi
        $hiRes = $this->actingAs($this->admin)->withSession(['locale' => 'hi'])->get('/erp/settings?tab=business');
        $hiRes->assertStatus(200);
        $hiRes->assertSee('व्यापार व सिस्टम सेटिंग्स');
        $hiRes->assertSee('सक्रिय वित्तीय वर्ष');
        $hiRes->assertSee('व्यापारिक प्रोफाइल');
    }
}
