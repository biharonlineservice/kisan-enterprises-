<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

/**
 * DEVELOPMENT ONLY sample accounts. Refuses to run in production.
 * Password for all sample accounts: Kisan@1234
 */
class DevelopmentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('DevelopmentSeeder must not be run in production.');
        }

        $this->call(RolePermissionSeeder::class);

        $accounts = [
            ['Admin User', 'admin@kisan.local', '9000000001', Role::ADMIN, 'Owner'],
            ['Purchase Manager', 'purchase@kisan.local', '9000000002', Role::PURCHASE_SALES, 'Purchase & Sales Manager'],
            ['Accounts Manager', 'accounts@kisan.local', '9000000003', Role::ACCOUNTS, 'Accounts & Operations Manager'],
        ];

        foreach ($accounts as [$name, $email, $mobile, $role, $designation]) {
            User::updateOrCreate(['email' => $email], [
                'name' => $name,
                'mobile' => $mobile,
                'designation' => $designation,
                'role_id' => Role::where('name', $role)->value('id'),
                'password' => 'Kisan@1234',
                'is_active' => true,
                'must_change_password' => false,
            ]);
        }

        $this->call(MasterDataSeeder::class);
        $this->call(OperationalDataSeeder::class);

        $this->command?->info('Sample mobile logins: 9000000001 (Admin) / 9000000002 (Purchase) / 9000000003 (Accounts) — password Kisan@1234');
    }
}
