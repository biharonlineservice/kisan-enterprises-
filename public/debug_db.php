<?php
header('Content-Type: text/plain');

echo "=== 1. PHP ENV ===\n";
echo "getenv('DB_DATABASE'): " . var_export(getenv('DB_DATABASE'), true) . "\n";
echo "\$_ENV['DB_DATABASE']: " . var_export($_ENV['DB_DATABASE'] ?? null, true) . "\n";
echo "\$_SERVER['DB_DATABASE']: " . var_export($_SERVER['DB_DATABASE'] ?? null, true) . "\n";

echo "\n=== 2. .ENV FILE ON DISK ===\n";
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    echo ".env exists at $envPath (size: " . filesize($envPath) . " bytes)\n";
    echo file_get_contents($envPath) . "\n";
} else {
    echo ".env NOT found at $envPath\n";
}

echo "\n=== 3. CONFIG CACHE ===\n";
$configCache = __DIR__ . '/../bootstrap/cache/config.php';
echo "Config cache exists: " . (file_exists($configCache) ? "YES" : "NO") . "\n";

echo "\n=== 4. LARAVEL BOOTSTRAP TEST ===\n";
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo "config('database.connections.mysql.database'): " . config('database.connections.mysql.database') . "\n";
    echo "config('database.connections.mysql.username'): " . config('database.connections.mysql.username') . "\n";
    echo "config('session.driver'): " . config('session.driver') . "\n";
    
    $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "PDO Connection: SUCCESS!\n";
} catch (\Throwable $e) {
    echo "Laravel error: " . $e->getMessage() . "\n";
}

