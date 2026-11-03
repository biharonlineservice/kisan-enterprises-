<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class MaintenanceTest extends TestCase
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

    public function test_admin_can_access_maintenance_index_with_kpis_and_tabs(): void
    {
        $response = $this->actingAs($this->admin)->get('/erp/maintenance');
        $response->assertStatus(200);
        $response->assertSee('erp-hero-banner', false);
        $response->assertSee('erp-kpi-card', false);
        $response->assertSee('Database Backup & System Maintenance', false);
    }

    public function test_maintenance_tabs_render_properly(): void
    {
        $tabs = ['backups', 'recovery', 'automation', 'telemetry', 'all'];

        foreach ($tabs as $tab) {
            $response = $this->actingAs($this->admin)->get("/erp/maintenance?tab={$tab}");
            $response->assertStatus(200);
            $response->assertSee('Database Backup & System Maintenance', false);
        }
    }

    public function test_admin_can_create_download_and_delete_backup(): void
    {
        $backupDir = storage_path('app/backups');
        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // 1. Create on-demand backup
        $createResponse = $this->actingAs($this->admin)->post('/erp/maintenance/backup');
        $createResponse->assertRedirect();
        $createResponse->assertSessionHas('success');

        $files = File::files($backupDir);
        $this->assertNotEmpty($files);
        $backupFile = $files[0];
        $filename = $backupFile->getFilename();

        // 2. Download backup
        $downloadResponse = $this->actingAs($this->admin)->get("/erp/maintenance/backup/{$filename}");
        $downloadResponse->assertStatus(200);

        // 3. Delete backup
        $deleteResponse = $this->actingAs($this->admin)->delete("/erp/maintenance/backup/{$filename}");
        $deleteResponse->assertRedirect();
        $deleteResponse->assertSessionHas('success');

        $this->assertFileDoesNotExist($backupFile->getPathname());
    }

    public function test_unauthorized_staff_cannot_access_or_trigger_backups(): void
    {
        $this->actingAs($this->staff)->get('/erp/maintenance')->assertForbidden();
        $this->actingAs($this->staff)->post('/erp/maintenance/backup')->assertForbidden();
    }

    public function test_maintenance_bilingual_localization(): void
    {
        // English
        $enRes = $this->actingAs($this->admin)->withSession(['locale' => 'en'])->get('/erp/maintenance');
        $enRes->assertStatus(200);
        $enRes->assertSee('Database Backup & System Maintenance');
        $enRes->assertSee('Stored SQL Snapshots');
        $enRes->assertSee('Backup Storage Footprint');

        // Hindi
        $hiRes = $this->actingAs($this->admin)->withSession(['locale' => 'hi'])->get('/erp/maintenance');
        $hiRes->assertStatus(200);
        $hiRes->assertSee('डेटाबेस बैकअप व सिस्टम रखरखाव');
        $hiRes->assertSee('संग्रहीत SQL स्नैपशॉट');
        $hiRes->assertSee('बैकअप स्टोरेज स्पेस');
    }
}
