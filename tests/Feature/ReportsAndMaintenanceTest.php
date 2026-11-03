<?php

namespace Tests\Feature;

use App\Models\Crop;
use App\Models\Customer;
use App\Models\Farmer;
use App\Models\Role;
use App\Models\User;
use App\Models\Warehouse;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ReportsAndMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $purchaseManager;
    protected User $accountsManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(MasterDataSeeder::class);

        $this->admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $this->purchaseManager = User::factory()->create([
            'role_id' => Role::where('name', Role::PURCHASE_SALES)->value('id'),
            'is_active' => true,
        ]);

        $this->accountsManager = User::factory()->create([
            'role_id' => Role::where('name', Role::ACCOUNTS)->value('id'),
            'is_active' => true,
        ]);
    }

    public function test_reports_hub_renders_successfully(): void
    {
        $response = $this->actingAs($this->admin)->get('/erp/reports');
        $response->assertStatus(200);
        $response->assertSee('Reports & Business Intelligence Hub', false);
    }

    public function test_purchase_reports_and_csv_export(): void
    {
        $response = $this->actingAs($this->admin)->get('/erp/reports/purchases');
        $response->assertStatus(200);
        $response->assertSee('Procurement & Purchases Report', false);

        // CSV export
        $exportResponse = $this->actingAs($this->admin)->get('/erp/reports/purchases?export=csv');
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('Purchase ID', $exportResponse->streamedContent());
    }

    public function test_sales_reports_and_csv_export(): void
    {
        $response = $this->actingAs($this->admin)->get('/erp/reports/sales');
        $response->assertStatus(200);
        $response->assertSee('Sales & Commercial Dispatch Report', false);

        // CSV export
        $exportResponse = $this->actingAs($this->admin)->get('/erp/reports/sales?export=csv');
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $exportResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('Invoice No', $exportResponse->streamedContent());
    }

    public function test_inventory_reports_tabs(): void
    {
        $tabs = ['balance', 'movements', 'capacity', 'low_stock'];

        foreach ($tabs as $tab) {
            $response = $this->actingAs($this->admin)->get("/erp/reports/inventory?tab={$tab}");
            $response->assertStatus(200);
        }

        // Export balance CSV
        $exportResponse = $this->actingAs($this->admin)->get('/erp/reports/inventory?export=csv&tab=balance');
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('Stock Bags', $exportResponse->streamedContent());
    }

    public function test_financial_reports_tabs(): void
    {
        $tabs = ['outstanding', 'customer_outstanding', 'expenses', 'statement', 'profitability'];

        foreach ($tabs as $tab) {
            $response = $this->actingAs($this->admin)->get("/erp/reports/finance?tab={$tab}");
            $response->assertStatus(200);
        }

        // Export farmer outstanding CSV
        $exportResponse = $this->actingAs($this->admin)->get('/erp/reports/finance?export=csv&tab=outstanding');
        $exportResponse->assertStatus(200);
        $this->assertStringContainsString('Total Outstanding', $exportResponse->streamedContent());
    }

    public function test_activity_and_audit_reports(): void
    {
        $response = $this->actingAs($this->admin)->get('/erp/reports/audit');
        $response->assertStatus(200);
        $response->assertSee('Cancellations, Growth & Audit Trail', false);
    }

    public function test_report_permission_enforcement(): void
    {
        // Purchase Manager does not have reports.activity permission
        $response = $this->actingAs($this->purchaseManager)->get('/erp/reports/audit');
        $response->assertStatus(403);
    }

    public function test_database_backup_generation_download_and_deletion(): void
    {
        $backupDir = storage_path('app/backups');
        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // 1. Visit maintenance index
        $indexResponse = $this->actingAs($this->admin)->get('/erp/maintenance');
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Database Backup & System Maintenance', false);

        // 2. Trigger on-demand backup
        $createResponse = $this->actingAs($this->admin)->post('/erp/maintenance/backup');
        $createResponse->assertRedirect();
        $createResponse->assertSessionHas('success');

        // Check file exists
        $files = File::files($backupDir);
        $this->assertNotEmpty($files);
        $backupFile = $files[0];
        $filename = $backupFile->getFilename();

        // 3. Download backup
        $downloadResponse = $this->actingAs($this->admin)->get("/erp/maintenance/backup/{$filename}");
        $downloadResponse->assertStatus(200);

        // 4. Delete backup
        $deleteResponse = $this->actingAs($this->admin)->delete("/erp/maintenance/backup/{$filename}");
        $deleteResponse->assertRedirect();
        $deleteResponse->assertSessionHas('success');

        $this->assertFileDoesNotExist($backupFile->getPathname());
    }

    public function test_non_admin_cannot_access_backup_maintenance(): void
    {
        $response = $this->actingAs($this->purchaseManager)->get('/erp/maintenance');
        $response->assertStatus(403);

        $postResponse = $this->actingAs($this->purchaseManager)->post('/erp/maintenance/backup');
        $postResponse->assertStatus(403);
    }
}
