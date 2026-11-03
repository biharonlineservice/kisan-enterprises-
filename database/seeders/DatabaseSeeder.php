<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Production-safe base seed: roles & permissions only.
 * Create the first Admin with:  php artisan kisan:create-admin
 * Development sample data:      php artisan db:seed --class=DevelopmentSeeder
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
    }
}
