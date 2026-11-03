<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_admin_can_access_administrative_modules(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $this->actingAs($admin)->get('/erp/users')->assertOk();
        $this->actingAs($admin)->get('/erp/roles')->assertOk();
        $this->actingAs($admin)->get('/erp/settings')->assertOk();
        $this->actingAs($admin)->get('/erp/audit-logs')->assertOk();
    }

    public function test_purchase_manager_is_forbidden_from_administrative_modules(): void
    {
        $pm = User::factory()->create([
            'role_id' => Role::where('name', Role::PURCHASE_SALES)->value('id'),
            'is_active' => true,
        ]);

        $this->actingAs($pm)->get('/erp/users')->assertForbidden();
        $this->actingAs($pm)->get('/erp/roles')->assertForbidden();
        $this->actingAs($pm)->get('/erp/settings')->assertForbidden();
        $this->actingAs($pm)->get('/erp/audit-logs')->assertForbidden();
    }

    public function test_accounts_manager_is_forbidden_from_user_and_settings_management(): void
    {
        $acc = User::factory()->create([
            'role_id' => Role::where('name', Role::ACCOUNTS)->value('id'),
            'is_active' => true,
        ]);

        $this->actingAs($acc)->get('/erp/users')->assertForbidden();
        $this->actingAs($acc)->get('/erp/roles')->assertForbidden();
        $this->actingAs($acc)->get('/erp/settings')->assertForbidden();
    }

    public function test_admin_can_create_new_staff_user(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $pmRole = Role::where('name', Role::PURCHASE_SALES)->first();

        $response = $this->actingAs($admin)->post('/erp/users', [
            'name' => 'Mohan Lal',
            'email' => 'mohan@kisan.local',
            'mobile' => '9812345678',
            'designation' => 'Procurement Officer',
            'role_id' => $pmRole->id,
            'is_active' => '1',
            'password' => 'Mohan@1234',
            'password_confirmation' => 'Mohan@1234',
        ]);

        $response->assertRedirect('/erp/users');
        $this->assertDatabaseHas('users', [
            'email' => 'mohan@kisan.local',
            'role_id' => $pmRole->id,
            'must_change_password' => true,
        ]);
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->patch("/erp/users/{$admin->id}/toggle");
        $response->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_cannot_deactivate_the_last_active_admin(): void
    {
        // Only one admin exists
        $admin1 = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);
        // Another admin created
        $admin2 = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        // admin1 deactivates admin2 -> allowed since admin1 is still active
        $response = $this->actingAs($admin1)->patch("/erp/users/{$admin2->id}/toggle");
        $response->assertSessionHas('success');
        $this->assertFalse($admin2->fresh()->is_active);

        // Now admin2 is inactive. If admin2 was the only one, another active admin exists.
        // If someone tries to edit admin1 to inactive, it must be blocked.
        $editResponse = $this->actingAs($admin1)->put("/erp/users/{$admin1->id}", [
            'name' => $admin1->name,
            'email' => $admin1->email,
            'role_id' => $admin1->role_id,
            'is_active' => '0',
        ]);
        $editResponse->assertSessionHas('error');
        $this->assertTrue($admin1->fresh()->is_active);
    }

    public function test_users_index_kpis_tabs_search_and_export(): void
    {
        $admin = User::factory()->create([
            'name' => 'Super Administrator User',
            'mobile' => '9876501111',
            'email' => 'superadmin@kisan.local',
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $pmUser = User::factory()->create([
            'name' => 'Rajesh Mandi Operator',
            'mobile' => '9876502222',
            'email' => 'rajesh@kisan.local',
            'role_id' => Role::where('name', Role::PURCHASE_SALES)->value('id'),
            'designation' => 'Weighbridge Supervisor',
            'is_active' => true,
            'must_change_password' => true,
        ]);

        $inactiveUser = User::factory()->create([
            'name' => 'Former Inactive Clerk',
            'mobile' => '9876503333',
            'email' => 'former@kisan.local',
            'role_id' => Role::where('name', Role::ACCOUNTS)->value('id'),
            'is_active' => false,
        ]);

        // 1. Index renders successfully with KPIs
        $indexResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/users');
        $indexResp->assertOk();
        $indexResp->assertSee('User &amp; Staff Management', false);
        $indexResp->assertSee('Super Administrator User');
        $indexResp->assertSee('Rajesh Mandi Operator');

        // 2. Tab: active only
        $activeTabResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/users?tab=active');
        $activeTabResp->assertOk();
        $activeTabResp->assertSee('Rajesh Mandi Operator');
        $activeTabResp->assertDontSee('Former Inactive Clerk');

        // 3. Tab: inactive only
        $inactiveTabResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/users?tab=inactive');
        $inactiveTabResp->assertOk();
        $inactiveTabResp->assertSee('Former Inactive Clerk');
        $inactiveTabResp->assertDontSee('Rajesh Mandi Operator');

        // 4. Tab: temp_pwd
        $tempPwdTabResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/users?tab=temp_pwd');
        $tempPwdTabResp->assertOk();
        $tempPwdTabResp->assertSee('Rajesh Mandi Operator');

        // 5. Search query
        $searchResp = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/users?q=Rajesh');
        $searchResp->assertOk();
        $searchResp->assertSee('Rajesh Mandi Operator');
        $searchResp->assertDontSee('Former Inactive Clerk');

        // 6. CSV Export
        $exportResp = $this->actingAs($admin)->get('/erp/users/export');
        $exportResp->assertOk();
        $this->assertStringContainsString('text/csv', $exportResp->headers->get('content-type'));
        $content = $exportResp->streamedContent();
        $this->assertStringContainsString('Full Name', $content);
        $this->assertStringContainsString('Rajesh Mandi Operator', $content);
        $this->assertStringContainsString('9876502222', $content);
    }

    public function test_roles_index_kpis_tabs_and_permission_update(): void
    {
        $admin = User::factory()->create([
            'role_id' => Role::where('name', Role::ADMIN)->value('id'),
            'is_active' => true,
        ]);

        $pmRole = Role::where('name', Role::PURCHASE_SALES)->firstOrFail();
        $adminRole = Role::where('name', Role::ADMIN)->firstOrFail();

        // 1. Roles index renders overview tab
        $response = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/roles');
        $response->assertOk();
        $response->assertSee('Roles &amp; Permission Matrix', false);
        $response->assertSee('Purchase &amp; Sales Manager', false);

        // 2. Tab for specific role
        $tabResponse = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/roles?tab=purchase_sales');
        $tabResponse->assertOk();
        $tabResponse->assertSee('Save Permissions');
        $tabResponse->assertSee('Quick Select:');

        // 3. Tab for all roles stacked
        $allTabResponse = $this->actingAs($admin)->withSession(['locale' => 'en'])->get('/erp/roles?tab=all');
        $allTabResponse->assertOk();

        // 4. Update permissions for Purchase & Sales role
        $permIds = \App\Models\Permission::whereIn('key', ['dashboard.view', 'farmers.view', 'farmers.create'])->pluck('id')->all();
        $updateResp = $this->actingAs($admin)->put("/erp/roles/{$pmRole->id}", [
            'permissions' => $permIds,
        ]);
        $updateResp->assertRedirect();
        $this->assertEqualsCanonicalizing($permIds, $pmRole->fresh()->permissions->pluck('id')->all());

        // 5. Admin role cannot be modified
        $adminUpdateResp = $this->actingAs($admin)->put("/erp/roles/{$adminRole->id}", [
            'permissions' => $permIds,
        ]);
        $adminUpdateResp->assertSessionHas('error');
    }
}
