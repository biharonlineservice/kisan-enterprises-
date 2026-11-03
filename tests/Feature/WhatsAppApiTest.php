<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Farmer;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\SettingsService;
use App\Services\WhatsAppService;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppApiTest extends TestCase
{
    use RefreshDatabase;

    private WhatsAppService $whatsApp;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);
        $this->whatsApp = app(WhatsAppService::class);
    }

    public function test_mobile_normalization_for_indian_numbers(): void
    {
        $this->assertSame('919876543210', $this->whatsApp->normalizeMobile('9876543210'));
        $this->assertSame('919876543210', $this->whatsApp->normalizeMobile('+91 98765-43210'));
        $this->assertSame('919876543210', $this->whatsApp->normalizeMobile('09876543210'));
        $this->assertSame('919876543210', $this->whatsApp->normalizeMobile('919876543210'));
        $this->assertNull($this->whatsApp->normalizeMobile('12345'));
        $this->assertNull($this->whatsApp->normalizeMobile(null));
    }

    public function test_click_to_chat_url_generation(): void
    {
        $url = $this->whatsApp->formatClickToChatUrl('9876543210', 'नमस्ते किसान भाई');
        $this->assertStringStartsWith('https://wa.me/919876543210?text=', $url);
        $this->assertStringContainsString(rawurlencode('नमस्ते किसान भाई'), $url);
    }

    public function test_purchase_message_formatting(): void
    {
        $farmer = Farmer::create([
            'farmer_id' => 'FRM/2026-27/00001',
            'name' => 'Shyam Lal',
            'mobile' => '9876543210',
            'village' => 'Kalyanpur',
            'tehsil' => 'Chandauli',
            'district' => 'Chandauli',
            'state' => 'Uttar Pradesh',
            'is_active' => true,
        ]);
        $user = User::factory()->create();
        $crop = Crop::first();
        $warehouse = Warehouse::first();

        $purchase = Purchase::create([
            'purchase_number' => 'PUR/2026-27/00001',
            'purchase_date' => now(),
            'farmer_id' => $farmer->id,
            'crop_id' => $crop->id,
            'warehouse_id' => $warehouse->id,
            'created_by' => $user->id,
            'gross_weight_kg' => '5000',
            'tare_weight_kg' => '500',
            'net_weight_kg' => '4500',
            'net_weight_qtl' => '45.000',
            'rate_per_qtl' => '2200.00',
            'base_amount' => '99000.00',
            'deductions_amount' => '0.00',
            'additions_amount' => '0.00',
            'net_payable_amount' => '99000.00',
            'paid_amount' => '0.00',
            'balance_due' => '99000.00',
            'status' => Purchase::STATUS_FINALIZED,
        ]);

        $message = $this->whatsApp->formatPurchaseMessage($purchase);

        $this->assertStringContainsString('Shyam Lal', $message);
        $this->assertStringContainsString('PUR/2026-27/00001', $message);
        $this->assertStringContainsString('45.00', $message);
        $this->assertStringContainsString('99,000.00', $message);
    }

    public function test_generic_gateway_dispatch_via_http(): void
    {
        $settings = app(SettingsService::class);
        $settings->setMany([
            'whatsapp.enabled' => '1',
            'whatsapp.provider' => 'generic',
            'whatsapp.generic_endpoint' => 'https://api.gateway.local/v1/send',
            'whatsapp.generic_api_key' => 'secret_api_key_xyz',
            'whatsapp.sender_phone' => '919876543210',
        ]);

        Http::fake([
            'https://api.gateway.local/v1/send' => Http::response(['status' => 'success', 'msg_id' => '12345'], 200),
        ]);

        $result = $this->whatsApp->send('9876543210', 'Test message to farmer');

        $this->assertTrue($result['success']);
        $this->assertSame('generic', $result['provider']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.gateway.local/v1/send'
                && $request->hasHeader('Authorization', 'Bearer secret_api_key_xyz')
                && $request['to'] === '919876543210'
                && $request['message'] === 'Test message to farmer';
        });
    }
}
