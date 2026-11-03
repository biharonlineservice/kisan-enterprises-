<?php

namespace Tests\Feature;

use App\Mail\ContactInquiryAdminMail;
use App\Models\Crop;
use App\Models\CropRateHistory;
use App\Models\Farmer;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WebsiteContent;
use App\Models\WebsiteInquiry;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WebsiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);
    }

    public function test_all_public_website_pages_render_successfully(): void
    {
        $urls = [
            '/',
            '/about',
            '/crop-procurement',
            '/products',
            '/farmer-registration',
            '/contact',
            '/privacy-policy',
            '/terms-of-trade',
        ];

        foreach ($urls as $url) {
            $response = $this->get($url);
            $response->assertStatus(200);
            $response->assertSee('public-mobile-bottom-nav', false);
            $response->assertSee('publicNavOffcanvas', false);
        }
    }

    public function test_farmer_can_register_online_and_receives_auto_generated_id(): void
    {
        $response = $this->post('/farmer-registration', [
            'name' => 'Rameshwar Singh',
            'father_name' => 'Dharamraj Singh',
            'mobile' => '9876543210',
            'alt_mobile' => '9876543211',
            'village' => 'Kalyanpur',
            'post_office' => 'Kalyanpur',
            'tehsil' => 'Chandauli',
            'district' => 'Chandauli',
            'state' => 'Uttar Pradesh',
            'pincode' => '232104',
            'aadhaar_number' => '123456789012',
            'bank_name' => 'State Bank of India',
            'bank_account_number' => '112233445566',
            'bank_ifsc' => 'SBIN0001234',
            'crops_grown' => 'Paddy (1509), Wheat',
            'estimated_acreage' => '8.5',
        ]);

        $response->assertRedirect('/farmer-registration');
        $response->assertSessionHas('success');

        $farmer = Farmer::where('mobile', '9876543210')->first();
        $this->assertNotNull($farmer);
        $this->assertStringStartsWith('FRM', $farmer->farmer_id);
        $this->assertSame('Rameshwar Singh', $farmer->name);
        $this->assertSame('Kalyanpur', $farmer->village);
    }

    public function test_user_can_submit_contact_inquiry(): void
    {
        Mail::fake();

        $response = $this->post('/contact', [
            'name' => 'M/s Golden Rice Mills',
            'phone' => '9812345678',
            'email' => 'purchase@goldenrice.com',
            'inquiry_type' => 'bulk_buyer',
            'subject' => 'Requirement of 500 MT Paddy 1509',
            'message' => 'We are interested in purchasing 500 MT of machine-cleaned Paddy 1509 variety. Please share best ex-godown quote.',
        ]);

        $response->assertRedirect('/contact');
        $response->assertSessionHas('success');

        $inquiry = WebsiteInquiry::where('phone', '9812345678')->first();
        $this->assertNotNull($inquiry);
        $this->assertSame('bulk_buyer', $inquiry->inquiry_type);
        $this->assertSame(WebsiteInquiry::STATUS_NEW, $inquiry->status);

        Mail::assertSent(ContactInquiryAdminMail::class, function ($mail) {
            return $mail->inquiry->name === 'M/s Golden Rice Mills';
        });
    }

    public function test_admin_can_update_website_content(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put('/erp/website-content', [
            'hero_headline' => 'Direct Procurement From 5,000+ Verified Farmers',
            'hero_subtitle' => 'Modern digital weighbridges, immediate payment vouchers.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertSame(
            'Direct Procurement From 5,000+ Verified Farmers',
            WebsiteContent::get('hero_headline')
        );
    }

    public function test_admin_can_view_and_toggle_inquiry_status(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $inquiry = WebsiteInquiry::create([
            'name' => 'Shyam Lal',
            'phone' => '9988776655',
            'inquiry_type' => 'farmer',
            'message' => 'When is the weighbridge opening for wheat procurement this week?',
            'status' => WebsiteInquiry::STATUS_NEW,
        ]);

        // View index
        $indexResponse = $this->actingAs($admin)->get('/erp/website-inquiries');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Shyam Lal');

        // Toggle to responded
        $toggleResponse = $this->actingAs($admin)->patch("/erp/website-inquiries/{$inquiry->id}/toggle");
        $toggleResponse->assertRedirect();

        $inquiry->refresh();
        $this->assertSame(WebsiteInquiry::STATUS_RESPONDED, $inquiry->status);
    }

    public function test_admin_can_update_crop_purchase_prices_and_hero_displays_live_rates(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $paddy = Crop::where('code', 'PADDY')->firstOrFail();
        $wheat = Crop::where('code', 'WHEAT')->firstOrFail();

        // Admin updates live crop purchase rates from Website CMS
        $cmsResponse = $this->actingAs($admin)->put('/erp/website-crop-rates', [
            'rates' => [
                $paddy->id => [
                    'current_purchase_rate' => '2380',
                    'max_purchase_rate' => '2520',
                    'rate_note' => 'Special Grade A Clean',
                    'show_rate_on_website' => '1',
                ],
                $wheat->id => [
                    'current_purchase_rate' => '2490',
                    'max_purchase_rate' => '',
                    'rate_note' => 'Hidden Internal Rate',
                    // Omitted show_rate_on_website => hidden on public Hero
                ],
            ],
        ]);

        $cmsResponse->assertRedirect();
        $cmsResponse->assertSessionHas('success');

        $paddy->refresh();
        $wheat->refresh();

        $this->assertSame('2380.00', $paddy->current_purchase_rate);
        $this->assertSame('2520.00', $paddy->max_purchase_rate);
        $this->assertTrue($paddy->show_rate_on_website);
        $this->assertNotNull($paddy->rate_updated_at);
        $this->assertFalse($wheat->show_rate_on_website);

        // Visiting public homepage shows live Paddy rate in Hero section & hides Wheat's hidden rate note
        $homeHi = $this->get('/');
        $homeHi->assertStatus(200);
        $homeHi->assertSee('hero-crop-price-board', false);
        $homeHi->assertSee('2,380');
        $homeHi->assertSee('2,520');
        $homeHi->assertSee('Special Grade A Clean');
        $homeHi->assertDontSee('Hidden Internal Rate');

        // Also check English locale rendering of Hero price board
        $homeEn = $this->withSession(['locale' => 'en'])->get('/');
        $homeEn->assertStatus(200);
        $homeEn->assertSee("Today's Crop Purchase Prices");
        $homeEn->assertSee('Updated by Admin:');

        // Admin can also update crop purchase rate from Crops Master edit form
        $cropUpdateResponse = $this->actingAs($admin)->put("/erp/crops/{$wheat->id}", [
            'name' => $wheat->name,
            'code' => $wheat->code,
            'unit' => $wheat->unit,
            'hsn_code' => $wheat->hsn_code,
            'rate_section_present' => '1',
            'current_purchase_rate' => '2555',
            'max_purchase_rate' => '2675',
            'rate_note' => 'Sharbati Bold Clean',
            'show_rate_on_website' => '1',
            'is_active' => '1',
        ]);
        $cropUpdateResponse->assertRedirect();

        $homeAfterMasterUpdate = $this->withSession(['locale' => 'en'])->get('/');
        $homeAfterMasterUpdate->assertStatus(200);
        $homeAfterMasterUpdate->assertSee('2,555');
        $homeAfterMasterUpdate->assertSee('2,675');
        $homeAfterMasterUpdate->assertSee('Sharbati Bold Clean');
    }

    public function test_crop_price_updates_record_history_and_display_price_difference_and_graph(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $paddy = Crop::where('code', 'PADDY')->firstOrFail();
        $maize = Crop::where('code', 'MAIZE')->firstOrFail();

        $initialPaddySnapshots = CropRateHistory::where('crop_id', $paddy->id)->count();
        $initialMaizeSnapshots = CropRateHistory::where('crop_id', $maize->id)->count();

        // Update 1: Paddy rises from 2300 -> 2410 (+110), Maize stays unchanged at 2150
        $this->actingAs($admin)->put('/erp/website-crop-rates', [
            'rates' => [
                $paddy->id => [
                    'current_purchase_rate' => '2410',
                    'max_purchase_rate' => '2540',
                    'rate_note' => 'Grade A Clean',
                    'show_rate_on_website' => '1',
                ],
                $maize->id => [
                    'current_purchase_rate' => $maize->current_purchase_rate,
                    'max_purchase_rate' => $maize->max_purchase_rate,
                    'rate_note' => $maize->rate_note,
                    'show_rate_on_website' => '1',
                ],
            ],
        ])->assertRedirect();

        $this->assertSame($initialPaddySnapshots + 1, CropRateHistory::where('crop_id', $paddy->id)->count());
        // Unchanged crop must NOT get duplicate history rows
        $this->assertSame($initialMaizeSnapshots, CropRateHistory::where('crop_id', $maize->id)->count());

        $homeUp = $this->withSession(['locale' => 'en'])->get('/');
        $homeUp->assertStatus(200);
        $homeUp->assertSee('cropPriceTrendChart', false);
        $homeUp->assertSee('rate-up', false);
        $homeUp->assertSee('₹110');
        $homeUp->assertSee('Prev:');
        $homeUp->assertSee('2,300');

        // Update 2: Paddy adjusts from 2410 -> 2365 (-45)
        $this->actingAs($admin)->put('/erp/website-crop-rates', [
            'rates' => [
                $paddy->id => [
                    'current_purchase_rate' => '2365',
                    'max_purchase_rate' => '2500',
                    'rate_note' => 'Grade A Clean',
                    'show_rate_on_website' => '1',
                ],
            ],
        ])->assertRedirect();

        $this->assertSame($initialPaddySnapshots + 2, CropRateHistory::where('crop_id', $paddy->id)->count());

        $homeDown = $this->withSession(['locale' => 'en'])->get('/');
        $homeDown->assertStatus(200);
        $homeDown->assertSee('rate-down', false);
        $homeDown->assertSee('₹45');
        $homeDown->assertSee('2,410');

        // Admin CMS also renders previous rate & difference summary cleanly
        $cmsPage = $this->actingAs($admin)->get('/erp/website-content');
        $cmsPage->assertStatus(200);
        $cmsPage->assertSee('2,410');
    }

    public function test_website_content_kpis_tabs_and_localization(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        WebsiteInquiry::create([
            'name' => 'Kisan Trader Ltd',
            'phone' => '9876500001',
            'email' => 'contact@kisantrader.in',
            'inquiry_type' => 'bulk_buyer',
            'message' => 'Need 200 bags of Sarson.',
            'status' => WebsiteInquiry::STATUS_NEW,
        ]);

        // 1. Rates tab in English
        $ratesRes = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/website-content?tab=rates');
        $ratesRes->assertStatus(200);
        $ratesRes->assertSee('Hero Live Rates');
        $ratesRes->assertSee('Active Commodities');
        $ratesRes->assertSee('Total Inquiries');
        $ratesRes->assertSee('Unread Leads');
        $ratesRes->assertSee('Publish Live Crop Prices');

        // 2. CMS tab
        $cmsRes = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/website-content?tab=cms');
        $cmsRes->assertStatus(200);
        $cmsRes->assertSee('Homepage Hero Headline');
        $cmsRes->assertSee('Procurement Banner Announcement');
        $cmsRes->assertSee('Trade Desk Helpline');
        $cmsRes->assertSee('Save Content Changes');

        // 3. Inquiries tab
        $inqRes = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/website-content?tab=inquiries');
        $inqRes->assertStatus(200);
        $inqRes->assertSee('Recent Website Inquiries');
        $inqRes->assertSee('Kisan Trader Ltd');
        $inqRes->assertSee('Need 200 bags of Sarson.');

        // 4. All modules overview tab
        $allRes = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/website-content?tab=all');
        $allRes->assertStatus(200);
        $allRes->assertSee('Publish Live Crop Prices');
        $allRes->assertSee('Save Content Changes');
        $allRes->assertSee('Kisan Trader Ltd');

        // 5. Hindi localization
        $hiRes = $this->actingAs($admin)->withSession(['locale' => 'hi'])->get('/erp/website-content?tab=rates');
        $hiRes->assertStatus(200);
        $hiRes->assertSee('लाइव मंडी दरें');
        $hiRes->assertSee('सक्रिय फसलें');
        $hiRes->assertSee('कुल व्यापारिक पूछताछ');
        $hiRes->assertSee('अपठित पूछताछ संदेश');
        $hiRes->assertSee('लाइव खरीद दरें प्रकाशित करें');
    }
}
