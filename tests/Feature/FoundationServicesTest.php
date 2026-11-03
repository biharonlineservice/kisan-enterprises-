<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\DocumentNumber;
use App\Services\FinancialYear;
use App\Services\SettingsService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FoundationServicesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_document_numbers_increment_sequentially(): void
    {
        $fy = FinancialYear::label();
        $doc1 = DocumentNumber::next('purchase');
        $doc2 = DocumentNumber::next('purchase');

        $this->assertEquals("PUR/{$fy}/00001", $doc1);
        $this->assertEquals("PUR/{$fy}/00002", $doc2);
    }

    public function test_master_document_numbers_do_not_contain_financial_year(): void
    {
        $farmerId1 = DocumentNumber::next('farmer');
        $farmerId2 = DocumentNumber::next('farmer');

        $this->assertEquals('FRM00001', $farmerId1);
        $this->assertEquals('FRM00002', $farmerId2);
    }

    public function test_audit_logger_redacts_sensitive_fields(): void
    {
        $user = User::factory()->create();

        AuditLogger::log('test.action', $user, [
            'password' => 'secret123',
            'bank_account_number' => '1234567890',
            'public_note' => 'test note',
        ]);

        $log = AuditLog::where('action', 'test.action')->first();
        $this->assertNotNull($log);
        $this->assertEquals('[redacted]', $log->old_values['password']);
        $this->assertEquals('[redacted]', $log->old_values['bank_account_number']);
        $this->assertEquals('test note', $log->old_values['public_note']);
    }

    public function test_settings_service_loads_defaults_and_persists_overrides(): void
    {
        $settings = app(SettingsService::class);

        // Default value from config/kisan.php
        $this->assertEquals('Kisan Enterprises', $settings->get('business.name'));
        $this->assertEquals('5', $settings->get('numbering.padding'));

        // Override
        $settings->setMany(['business.name' => 'Kisan Agri Traders Ltd']);
        $this->assertEquals('Kisan Agri Traders Ltd', $settings->get('business.name'));
    }

    public function test_indian_number_and_money_helpers(): void
    {
        $this->assertEquals('12,34,567.85', indian_number('1234567.85'));
        $this->assertEquals('1,00,000.00', indian_number(100000));
        $this->assertEquals('₹12,34,567.85', money('1234567.85'));
        $this->assertEquals('₹50.00', money(50));
    }

    public function test_indian_fy_helper(): void
    {
        $this->assertEquals(FinancialYear::label(), indian_fy());
        $this->assertEquals('2026-27', indian_fy('2026-05-15'));
        $this->assertEquals('2025-26', indian_fy('2026-02-15'));
    }
}
