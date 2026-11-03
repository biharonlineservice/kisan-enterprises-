<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MaintenanceController extends Controller
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (! File::exists($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true);
        }
    }

    public function index(Request $request): View
    {
        $this->authorizePermission('backup.view');

        $tab = $request->query('tab', 'backups');
        if (! in_array($tab, ['backups', 'recovery', 'automation', 'telemetry', 'all'], true)) {
            $tab = 'backups';
        }

        $backupFiles = [];
        $totalBytes = 0;
        if (File::exists($this->backupDir)) {
            foreach (File::files($this->backupDir) as $file) {
                if (in_array($file->getExtension(), ['sql', 'zip', 'gz'])) {
                    $size = $file->getSize();
                    $totalBytes += $size;
                    $backupFiles[] = [
                        'filename' => $file->getFilename(),
                        'size_bytes' => $size,
                        'size_formatted' => round($size / 1024 / 1024, 2).' MB',
                        'created_at' => \Carbon\Carbon::createFromTimestamp($file->getMTime()),
                    ];
                }
            }
        }

        // Sort descending by created_at
        usort($backupFiles, fn ($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        $totalSizeFormatted = $totalBytes >= 1024 * 1024 
            ? round($totalBytes / 1024 / 1024, 2).' MB' 
            : round($totalBytes / 1024, 2).' KB';

        // System telemetry
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'os' => PHP_OS,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
            'database_driver' => config('database.default'),
            'database_name' => config('database.connections.mysql.database', 'kisan_erp'),
            'disk_free_space' => @disk_free_space(base_path()) ? round(disk_free_space(base_path()) / (1024 * 1024 * 1024), 2).' GB' : 'N/A',
        ];

        $metrics = [
            'totalBackups' => count($backupFiles),
            'totalSizeFormatted' => $totalSizeFormatted,
            'databaseName' => $systemInfo['database_name'],
            'databaseDriver' => ucfirst($systemInfo['database_driver']),
            'diskFreeSpace' => $systemInfo['disk_free_space'],
            'phpVersion' => $systemInfo['php_version'],
        ];

        return view('admin.maintenance.index', compact('backupFiles', 'systemInfo', 'metrics', 'tab'));
    }

    public function createBackup(Request $request): RedirectResponse
    {
        $this->authorizePermission('backup.view');

        $isSqlite = config('database.default') === 'sqlite';
        $dbName = config('database.connections.mysql.database', 'kisan_erp');

        $dump = "-- Kisan Enterprises ERP Database Backup\n";
        $dump .= "-- Generated on: " . now()->toDateTimeString() . "\n";
        $dump .= "-- Host: " . config('database.connections.mysql.host', 'localhost') . "\n";
        $dump .= "-- Database: {$dbName}\n\n";

        if ($isSqlite) {
            $tables = DB::select("SELECT name as tbl FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($tables as $t) {
                $table = $t->tbl;
                $rows = DB::table($table)->get();
                $dump .= "\n-- Table structure and data for `{$table}`\n";
                if ($rows->isNotEmpty()) {
                    foreach ($rows->chunk(100) as $chunk) {
                        $values = [];
                        foreach ($chunk as $row) {
                            $escapedValues = array_map(function ($val) {
                                if ($val === null) {
                                    return 'NULL';
                                }
                                return "'" . addslashes((string) $val) . "'";
                            }, (array) $row);
                            $values[] = '(' . implode(', ', $escapedValues) . ')';
                        }
                        $columns = array_keys((array) $chunk->first());
                        $dump .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES\n" . implode(",\n", $values) . ";\n";
                    }
                }
            }
        } else {
            $dump .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            $tables = DB::select('SHOW TABLES');
            $prop = "Tables_in_{$dbName}";

            foreach ($tables as $t) {
                $table = $t->$prop;
                $createTable = DB::select("SHOW CREATE TABLE `{$table}`");
                $dump .= "\n-- Table structure for `{$table}`\n";
                $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
                $dump .= $createTable[0]->{'Create Table'} . ";\n\n";

                $rows = DB::table($table)->get();
                if ($rows->isNotEmpty()) {
                    $dump .= "-- Dumping data for `{$table}`\n";
                    foreach ($rows->chunk(100) as $chunk) {
                        $values = [];
                        foreach ($chunk as $row) {
                            $escapedValues = array_map(function ($val) {
                                if ($val === null) {
                                    return 'NULL';
                                }
                                return "'" . addslashes((string) $val) . "'";
                            }, (array) $row);
                            $values[] = '(' . implode(', ', $escapedValues) . ')';
                        }
                        $columns = array_keys((array) $chunk->first());
                        $dump .= "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES\n" . implode(",\n", $values) . ";\n";
                    }
                    $dump .= "\n";
                }
            }
            $dump .= "SET FOREIGN_KEY_CHECKS=1;\n";
        }

        $filename = "kisan_erp_backup_" . now()->format('Y_m_d_His') . ".sql";
        $filepath = $this->backupDir . DIRECTORY_SEPARATOR . $filename;
        File::put($filepath, $dump);

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => 'backup.create',
            'auditable_type' => 'Database',
            'auditable_id' => 1,
            'description' => "Created on-demand database backup: {$filename}",
            'new_values' => ['filename' => $filename, 'size_bytes' => strlen($dump)],
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', "Database backup created successfully: {$filename} (" . round(strlen($dump) / 1024, 2) . " KB)");
    }

    public function downloadBackup(string $filename): BinaryFileResponse
    {
        $this->authorizePermission('backup.view');

        $filepath = $this->backupDir . DIRECTORY_SEPARATOR . basename($filename);
        if (! File::exists($filepath)) {
            abort(404, 'Backup file not found.');
        }

        return response()->download($filepath);
    }

    public function deleteBackup(Request $request, string $filename): RedirectResponse
    {
        $this->authorizePermission('backup.view');

        $filepath = $this->backupDir . DIRECTORY_SEPARATOR . basename($filename);
        if (File::exists($filepath)) {
            File::delete($filepath);

            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'backup.delete',
                'auditable_type' => 'Database',
                'auditable_id' => 1,
                'description' => "Deleted database backup: {$filename}",
                'old_values' => ['filename' => $filename],
                'ip_address' => $request->ip(),
            ]);

            return back()->with('success', "Backup file {$filename} has been deleted.");
        }

        return back()->with('error', 'Backup file not found.');
    }

    protected function authorizePermission(string $permission): void
    {
        if (! auth()->user()->hasPermission($permission)) {
            abort(403, 'Unauthorized access to maintenance and backup tools.');
        }
    }
}
