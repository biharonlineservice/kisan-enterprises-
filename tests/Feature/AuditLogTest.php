<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
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

    public function test_admin_can_view_audit_logs_index_with_kpis_and_tabs(): void
    {
        AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'settings.updated',
            'description' => 'Updated business settings profile',
            'old_values' => ['business.name' => 'Old Name'],
            'new_values' => ['business.name' => 'New Name'],
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin)->get('/erp/audit-logs');
        $response->assertStatus(200);
        $response->assertSee('erp-hero-banner', false);
        $response->assertSee('erp-kpi-card', false);
        $response->assertSee('settings.updated');
        $response->assertSee('Updated business settings profile');
    }

    public function test_audit_logs_tabs_and_filtering(): void
    {
        AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'user.created',
            'description' => 'Created user Ramesh',
            'old_values' => null,
            'new_values' => ['name' => 'Ramesh'],
            'ip_address' => '192.168.1.10',
        ]);

        AuditLog::create([
            'user_id' => $this->staff->id,
            'action' => 'purchase.created',
            'description' => 'Created purchase draft',
            'old_values' => null,
            'new_values' => ['amount' => 50000],
            'ip_address' => '192.168.1.12',
        ]);

        // Tab: all
        $this->actingAs($this->admin)->get('/erp/audit-logs?tab=all')->assertOk()->assertSee('purchase.created');

        // Tab: today
        $this->actingAs($this->admin)->get('/erp/audit-logs?tab=today')->assertOk()->assertSee('purchase.created');

        // Tab: mutations
        $this->actingAs($this->admin)->get('/erp/audit-logs?tab=mutations')->assertOk()->assertSee('user.created');

        // Tab: security
        $secRes = $this->actingAs($this->admin)->get('/erp/audit-logs?tab=security');
        $secRes->assertOk();
        $secRes->assertSee('Created user Ramesh');
        $secRes->assertDontSee('Created purchase draft');

        // Filter by user
        $userFilter = $this->actingAs($this->admin)->get("/erp/audit-logs?user_id={$this->staff->id}");
        $userFilter->assertOk();
        $userFilter->assertSee('Created purchase draft');
        $userFilter->assertDontSee('Created user Ramesh');
    }

    public function test_admin_can_view_audit_log_show_details(): void
    {
        $log = AuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'role.updated',
            'auditable_type' => 'App\Models\Role',
            'auditable_id' => 1,
            'description' => 'Permissions modified',
            'old_values' => ['permissions' => ['user.view']],
            'new_values' => ['permissions' => ['user.view', 'user.create']],
            'ip_address' => '10.0.0.1',
            'user_agent' => 'Mozilla/5.0 Chrome Test',
        ]);

        $response = $this->actingAs($this->admin)->get("/erp/audit-logs/{$log->id}");
        $response->assertStatus(200);
        $response->assertSee('#' . $log->id);
        $response->assertSee('role.updated');
        $response->assertSee('Permissions modified');
        $response->assertSee('user.create');
        $response->assertSee('10.0.0.1');
    }

    public function test_unauthorized_staff_cannot_access_audit_logs(): void
    {
        $this->actingAs($this->staff)->get('/erp/audit-logs')->assertForbidden();
    }

    public function test_audit_logs_bilingual_localization(): void
    {
        // English
        $enRes = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/audit-logs');
        $enRes->assertStatus(200);
        $enRes->assertSee('System Audit & Security Logs');
        $enRes->assertSee('Total Audit Events');
        $enRes->assertSee('Active Operators');

        // Hindi
        $hiRes = $this->actingAs($this->admin)->withSession(['locale' => 'hi'])->get('/erp/audit-logs');
        $hiRes->assertStatus(200);
        $hiRes->assertSee('सिस्टम ऑडिट व सुरक्षा लॉग्स');
        $hiRes->assertSee('कुल ऑडिट गतिविधियां');
        $hiRes->assertSee('सक्रिय कर्मचारी');
    }
}
