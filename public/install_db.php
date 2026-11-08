<?php
/**
 * One-time Database Migration & Seeder Runner for Kisan Enterprises ERP on Hostinger
 * Access via: https://kisanhub.in/install_db.php?key=kisan2026_setup
 */

$secretKey = 'kisan2026_setup';

if (!isset($_GET['key']) || $_GET['key'] !== $secretKey) {
    http_response_code(403);
    die('<h2 style="color:red;font-family:sans-serif;">403 Forbidden: Invalid or missing setup key.</h2>');
}

// Disable time limits for migrations
@set_time_limit(300);
@ini_set('memory_limit', '512M');

echo '<!DOCTYPE html><html><head><title>Kisan Enterprises Database Setup</title>';
echo '<style>body{font-family:system-ui,sans-serif;background:#0f172a;color:#f8fafc;padding:30px;} pre{background:#1e293b;padding:16px;border-radius:8px;color:#38bdf8;white-space:pre-wrap;}</style></head><body>';
echo '<h1>🌾 Kisan Enterprises ERP - Database Initialization</h1>';

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';

    // Bootstrap Laravel console kernel
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo '<h3>1. Testing Database Connection...</h3>';
    \Illuminate\Support\Facades\DB::connection()->getPdo();
    $dbName = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
    echo "<p style='color:#4ade80;'>✅ Successfully connected to MySQL database: <strong>{$dbName}</strong></p>";

    echo '<h3>2. Running Migrations...</h3>';
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo '<pre>' . htmlspecialchars(\Illuminate\Support\Facades\Artisan::output()) . '</pre>';

    echo '<h3>3. Seeding Roles and Permissions...</h3>';
    \Illuminate\Support\Facades\Artisan::call('db:seed', [
        '--class' => 'RolePermissionSeeder',
        '--force' => true
    ]);
    echo '<pre>' . htmlspecialchars(\Illuminate\Support\Facades\Artisan::output()) . '</pre>';

    echo '<h3>4. Seeding Master Data (Crops, Rates, Warehouses, Accounts)...</h3>';
    \Illuminate\Support\Facades\Artisan::call('db:seed', [
        '--class' => 'MasterDataSeeder',
        '--force' => true
    ]);
    echo '<pre>' . htmlspecialchars(\Illuminate\Support\Facades\Artisan::output()) . '</pre>';

    echo '<h3>5. Clearing Application Cache...</h3>';
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    echo '<pre>' . htmlspecialchars(\Illuminate\Support\Facades\Artisan::output()) . '</pre>';

    echo '<div style="background:#065f46;padding:20px;border-radius:8px;margin-top:20px;">';
    echo '<h2 style="color:#a7f3d0;margin:0 0 10px 0;">🎉 Setup Completed Successfully!</h2>';
    echo '<p style="color:#ecfdf5;margin:0 0 15px 0;">Your ERP is fully initialized with roles, permissions, crop master data, and admin accounts.</p>';
    echo '<a href="/" style="display:inline-block;background:#10b981;color:#fff;padding:10px 20px;text-decoration:none;border-radius:6px;font-weight:bold;">Go to Homepage</a> ';
    echo '<a href="/login" style="display:inline-block;background:#3b82f6;color:#fff;padding:10px 20px;text-decoration:none;border-radius:6px;font-weight:bold;margin-left:10px;">Go to ERP Login</a>';
    echo '</div>';

} catch (\Throwable $e) {
    echo '<div style="background:#7f1d1d;padding:20px;border-radius:8px;margin-top:20px;">';
    echo '<h2 style="color:#fecaca;margin:0 0 10px 0;">❌ Setup Error</h2>';
    echo '<pre style="background:#450a0a;color:#fca5a5;">' . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</div>';
}

echo '</body></html>';

