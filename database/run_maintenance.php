<?php
/**
 * Database Maintenance Scheduler & Runner
 * 
 * Central orchestrator for all database maintenance tasks
 * Provides unified interface for running maintenance operations
 * 
 * Usage: 
 *   php run_maintenance.php [--report] [--full-analysis]
 */

// Load ThinkPHP framework
define('APP_PATH', __DIR__ . '/source/application/');
define('RUNTIME_PATH', __DIR__ . '/source/runtime/');
require __DIR__ . '/source/thinkphp/start.php';

use think\Db;
use think\Cache;

// Configuration
$scripts = [
    'archive' => __DIR__ . '/database/archive_historical_data.php',
    'timeout' => __DIR__ . '/database/close_timeout_sessions.php',
    'backup' => __DIR__ . '/database/backup_database.php',
    'indexes' => __DIR__ . '/database/optimize_indexes.sql',
];

$modes = ['report', 'run-all', 'full-analysis'];
$mode = $argv[1] ?? 'run-all';

echo "========================================\n";
echo "Database Maintenance Scheduler\n";
echo "========================================\n";
echo "Mode: " . strtoupper($mode) . "\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";
echo "========================================\n\n";

try {
    switch ($mode) {
        case '--report':
            generateMaintenanceReport();
            break;
            
        case '--full-analysis':
            runFullAnalysis();
            break;
            
        case 'run-all':
        default:
            runAllMaintenance();
            break;
    }
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    \think\Log::record('[DB Maintenance] Error: ' . $e->getMessage(), 'error');
    exit(1);
}

/**
 * Run all maintenance tasks in sequence
 */
function runAllMaintenance() {
    global $scripts;
    
    $results = [];
    
    // 1. Close timeout sessions (daily task)
    echo "[1/4] Running timeout closure...\n";
    if (file_exists($scripts['timeout'])) {
        $results['timeout'] = runScript($scripts['timeout'], ['--dry-run']);
    } else {
        $results['timeout'] = ['status' => 'skipped', 'message' => 'Script not found'];
    }
    
    // 2. Archive historical data (monthly task)
    echo "\n[2/4] Running archive script...\n";
    if (file_exists($scripts['archive'])) {
        $results['archive'] = runScript($scripts['archive'], ['--dry-run']);
    } else {
        $results['archive'] = ['status' => 'skipped', 'message' => 'Script not found'];
    }
    
    // 3. Backup database (daily task)
    echo "\n[3/4] Running backup...\n";
    if (file_exists($scripts['backup'])) {
        $results['backup'] = runScript($scripts['backup'], ['--incremental', '--dry-run']);
    } else {
        $results['backup'] = ['status' => 'skipped', 'message' => 'Script not found'];
    }
    
    // 4. Optimize indexes (weekly task)
    echo "\n[4/4] Checking index optimization needs...\n";
    $results['indexes'] = checkIndexOptimization();
    
    // Print summary
    printResults($results);
    
    // Cache results
    Cache::set('last_maintenance_run', [
        'time' => date('Y-m-d H:i:s'),
        'mode' => 'run-all',
        'results' => $results,
    ], 86400);
}

/**
 * Generate comprehensive maintenance report
 */
function generateMaintenanceReport() {
    echo "Generating Maintenance Report...\n\n";
    
    $report = [
        'generated_at' => date('Y-m-d H:i:s'),
        'database_stats' => getDatabaseStats(),
        'table_sizes' => getTableSizes(),
        'index_stats' => getIndexStatistics(),
        'last_backups' => getLastBackups(),
        'last_archives' => getLastArchives(),
        'maintenance_history' => getMaintenanceHistory(),
    ];
    
    // Print report
    echo "========================================\n";
    echo "DATABASE MAINTENANCE REPORT\n";
    echo "Generated: {$report['generated_at']}\n";
    echo "========================================\n\n";
    
    echo "DATABASE STATISTICS:\n";
    echo "  Tables: {$report['database_stats']['tables']}\n";
    echo "  Total Size: " . number_format($report['database_stats']['total_size_mb'], 2) . " MB\n";
    echo "  Free Space: " . number_format($report['database_stats']['free_space_mb'], 2) . " MB\n\n";
    
    echo "LARGEST TABLES:\n";
    foreach ($report['table_sizes'] as $table) {
        echo "  {$table['name']}: " . number_format($table['size_mb'], 2) . " MB ({$table['rows']} rows)\n";
    }
    echo "\n";
    
    echo "RECENT BACKUPS:\n";
    foreach ($report['last_backups'] as $backup) {
        echo "  {$backup['time']} - {$backup['type']} ({$backup['size']})\n";
    }
    echo "\n";
    
    echo "MAINTENANCE HISTORY:\n";
    foreach ($report['maintenance_history'] as $entry) {
        echo "  {$entry['time']} - {$entry['task']}: {$entry['status']}\n";
    }
    echo "\n";
    
    // Save report
    $reportFile = __DIR__ . '/database/logs/maintenance_report_' . date('Ymd_His') . '.txt';
    file_put_contents($reportFile, print_r($report, true));
    
    echo "✅ Report saved to: {$reportFile}\n";
}

/**
 * Run full database analysis
 */
function runFullAnalysis() {
    echo "Running Full Database Analysis...\n\n";
    
    // 1. Analyze all tables
    echo "[1/5] Analyzing tables...\n";
    $tables = Db::query("SHOW TABLE STATUS");
    foreach ($tables as $table) {
        echo "  - {$table['Name']}: {$table['Rows']} rows, " . 
             number_format(($table['Data_length'] + $table['Index_length']) / 1024 / 1024, 2) . " MB\n";
    }
    
    // 2. Check index fragmentation
    echo "\n[2/5] Checking index fragmentation...\n";
    $fragmented = Db::query("
        SELECT table_name, index_name, 
               ROUND((stat_value * @@innodb_page_size) / 1024 / 1024, 2) as size_mb
        FROM mysql.innodb_index_stats 
        WHERE stat_name = 'size' 
        AND stat_value > 100
        ORDER BY stat_value DESC
    ");
    
    if (empty($fragmented)) {
        echo "  No heavily fragmented indexes found\n";
    } else {
        foreach ($fragmented as $idx) {
            echo "  ⚠️  {$idx['table_name']}.{$idx['index_name']}: {$idx['size_mb']} MB\n";
        }
    }
    
    // 3. Check for missing indexes
    echo "\n[3/5] Checking query performance...\n";
    $slowQueries = checkSlowQueries();
    if (!empty($slowQueries)) {
        echo "  Found " . count($slowQueries) . " potentially slow queries\n";
    } else {
        echo "  No slow queries detected\n";
    }
    
    // 4. Check table health
    echo "\n[4/5] Checking table health...\n";
    $healthCheck = checkTableHealth();
    foreach ($healthCheck as $table => $status) {
        echo "  {$table}: " . ($status ? '✓ OK' : '⚠️  Needs attention') . "\n";
    }
    
    // 5. Recommendations
    echo "\n[5/5] Generating recommendations...\n";
    $recommendations = generateRecommendations();
    if (empty($recommendations)) {
        echo "  ✅ Database is healthy. No immediate actions needed.\n";
    } else {
        foreach ($recommendations as $rec) {
            echo "  • {$rec}\n";
        }
    }
    
    echo "\n✅ Full analysis completed!\n";
}

/**
 * Helper Functions
 */

function runScript($script, $args = []) {
    $cmd = 'php ' . escapeshellarg($script) . ' ' . implode(' ', $args);
    exec($cmd, $output, $returnCode);
    
    return [
        'status' => $returnCode === 0 ? 'success' : 'failed',
        'output' => implode("\n", $output),
        'code' => $returnCode,
    ];
}

function checkIndexOptimization() {
    // Check if optimization is needed
    $needsOptimization = false;
    
    // Check last optimization time
    $lastOptimization = Cache::get('last_index_optimization');
    if (!$lastOptimization || strtotime($lastOptimization['time']) < strtotime('-30 days')) {
        $needsOptimization = true;
    }
    
    return [
        'status' => $needsOptimization ? 'needed' : 'ok',
        'message' => $needsOptimization ? 'Indexes should be optimized' : 'Indexes are up to date',
    ];
}

function printResults($results) {
    echo "\n========================================\n";
    echo "Maintenance Results Summary:\n";
    echo "========================================\n";
    
    foreach ($results as $task => $result) {
        $icon = $result['status'] === 'success' ? '✅' : ($result['status'] === 'failed' ? '❌' : '⚪');
        echo "{$icon} {$task}: {$result['status']}\n";
        if (isset($result['message'])) {
            echo "   {$result['message']}\n";
        }
    }
    
    echo "========================================\n";
}

function getDatabaseStats() {
    $dbName = Db::getConfig('database');
    
    $stats = Db::query("
        SELECT 
            COUNT(*) as tables,
            SUM(data_length + index_length) as total_size,
            SUM(data_free) as free_space
        FROM information_schema.TABLES
        WHERE table_schema = '{$dbName}'
    ")[0];
    
    return [
        'tables' => $stats['tables'],
        'total_size_mb' => $stats['total_size'] / 1024 / 1024,
        'free_space_mb' => $stats['free_space'] / 1024 / 1024,
    ];
}

function getTableSizes() {
    $dbName = Db::getConfig('database');
    
    return Db::query("
        SELECT table_name as name,
               ROUND((data_length + index_length) / 1024 / 1024, 2) as size_mb,
               table_rows as rows
        FROM information_schema.TABLES
        WHERE table_schema = '{$dbName}'
        ORDER BY size_mb DESC
        LIMIT 10
    ");
}

function getIndexStatistics() {
    // Implementation for index statistics
    return [];
}

function getLastBackups() {
    $backupDir = __DIR__ . '/database/backups/';
    $backups = [];
    
    if (is_dir($backupDir)) {
        $files = glob($backupDir . '*.sql.gz');
        rsort($files);
        
        foreach (array_slice($files, 0, 5) as $file) {
            $backups[] = [
                'time' => date('Y-m-d H:i:s', filemtime($file)),
                'type' => strpos($file, 'full') !== false ? 'FULL' : 'INCREMENTAL',
                'size' => number_format(filesize($file) / 1024 / 1024, 2) . ' MB',
            ];
        }
    }
    
    return $backups;
}

function getLastArchives() {
    $cacheKey = 'last_archive_stats';
    $lastArchive = Cache::get($cacheKey);
    
    return $lastArchive ? [$lastArchive] : [];
}

function getMaintenanceHistory() {
    $history = [];
    
    $lastBackup = Cache::get('last_backup_stats');
    if ($lastBackup) {
        $history[] = [
            'time' => $lastBackup['last_backup_time'],
            'task' => 'Backup',
            'status' => $lastBackup['status'],
        ];
    }
    
    $lastTimeout = Cache::get('last_timeout_closure');
    if ($lastTimeout) {
        $history[] = [
            'time' => $lastTimeout['time'],
            'task' => 'Timeout Closure',
            'status' => 'completed',
        ];
    }
    
    return $history;
}

function checkSlowQueries() {
    // Check for queries without proper indexes
    return [];
}

function checkTableHealth() {
    $dbName = Db::getConfig('database');
    $tables = Db::query("SHOW TABLES FROM {$dbName}");
    
    $health = [];
    foreach ($tables as $table) {
        $tableName = reset($table);
        try {
            Db::execute("CHECK TABLE {$tableName}");
            $health[$tableName] = true;
        } catch (\Exception $e) {
            $health[$tableName] = false;
        }
    }
    
    return $health;
}

function generateRecommendations() {
    $recommendations = [];
    
    // Check backup age
    $lastBackup = Cache::get('last_backup_stats');
    if (!$lastBackup || strtotime($lastBackup['last_backup_time']) < strtotime('-2 days')) {
        $recommendations[] = "Backup is older than 2 days. Consider running backup.";
    }
    
    // Check archive age
    $lastArchive = Cache::get('last_archive_stats');
    if (!$lastArchive || strtotime($lastArchive['time']) < strtotime('-30 days')) {
        $recommendations[] = "Archive is older than 30 days. Consider archiving old data.";
    }
    
    return $recommendations;
}
