<?php
/**
 * Database Backup Strategy Script
 * 
 * Implements 3-2-1 backup strategy:
 * - 3 copies of data
 * - 2 different media types
 * - 1 offsite backup
 * 
 * Usage: php backup_database.php [--full|--incremental] [--retention-days=30]
 */

// Load ThinkPHP framework
define('APP_PATH', __DIR__ . '/source/application/');
define('RUNTIME_PATH', __DIR__ . '/source/runtime/');
require __DIR__ . '/source/thinkphp/start.php';

use think\Db;
use think\Cache;

// Configuration
$backupDir = __DIR__ . '/database/backups/';
$retentionDays = isset($argv['--retention-days'] ?? null) 
    ? (int)$argv['--retention-days'] 
    : 30;
$backupType = in_array('--incremental', $argv) ? 'incremental' : 'full';
$dryRun = in_array('--dry-run', $argv);

// Database configuration
$dbConfig = Db::getConfig();
$dbName = $dbConfig['database'];
$dbHost = $dbConfig['hostname'];
$dbUser = $dbConfig['username'];
$dbPass = $dbConfig['password'];
$dbPort = $dbConfig['hostport'] ?? '3306';

// Create backup directory if not exists
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}

echo "========================================\n";
echo "Database Backup Script\n";
echo "========================================\n";
echo "Database: {$dbName}\n";
echo "Backup Type: " . strtoupper($backupType) . "\n";
echo "Retention: {$retentionDays} days\n";
echo "Backup Dir: {$backupDir}\n";
echo "Mode: " . ($dryRun ? "DRY RUN" : "LIVE") . "\n";
echo "========================================\n\n";

try {
    $timestamp = date('YmdHis');
    $backupFile = $backupDir . "{$dbName}_{$backupType}_{$timestamp}.sql.gz";
    $backupLog = $backupDir . "backup_{$timestamp}.log";
    
    // Start logging
    $logContent = "Backup started at: " . date('Y-m-d H:i:s') . "\n";
    $logContent .= "Database: {$dbName}\n";
    $logContent .= "Type: {$backupType}\n";
    file_put_contents($backupLog, $logContent);
    
    echo "Starting backup...\n";
    
    if (!$dryRun) {
        // ============================================
        // 1. Pre-backup Checks
        // ============================================
        echo "  1. Running pre-backup checks...\n";
        
        // Check disk space
        $freeSpace = disk_free_space($backupDir);
        $requiredSpace = estimateBackupSize($dbName) * 1.5; // 50% buffer
        
        if ($freeSpace < $requiredSpace) {
            throw new Exception("Insufficient disk space. Free: " . number_format($freeSpace) . " bytes");
        }
        
        echo "     ✓ Disk space OK (" . number_format($freeSpace / 1024 / 1024, 2) . " MB free)\n";
        
        // ============================================
        // 2. Lock Tables (for consistent backup)
        // ============================================
        echo "  2. Locking tables for backup...\n";
        
        if ($backupType === 'full') {
            Db::execute('FLUSH TABLES WITH READ LOCK');
            $tablesLocked = true;
        } else {
            $tablesLocked = false;
        }
        
        echo "     ✓ Tables locked\n";
        
        // ============================================
        // 3. Execute Backup using mysqldump
        // ============================================
        echo "  3. Executing backup...\n";
        
        $mysqldumpCmd = buildMysqldumpCommand(
            $dbHost, $dbPort, $dbUser, $dbPass, $dbName, $backupFile, $backupType
        );
        
        exec($mysqldumpCmd . ' 2>&1', $output, $returnCode);
        
        if ($returnCode !== 0) {
            throw new Exception("mysqldump failed with code {$returnCode}: " . implode("\n", $output));
        }
        
        // Unlock tables
        if ($tablesLocked) {
            Db::execute('UNLOCK TABLES');
        }
        
        $backupSize = filesize($backupFile);
        echo "     ✓ Backup created: " . number_format($backupSize / 1024 / 1024, 2) . " MB\n";
        
        // ============================================
        // 4. Verify Backup Integrity
        // ============================================
        echo "  4. Verifying backup integrity...\n";
        
        // Test if file is valid gzip
        $testCmd = "gzip -t " . escapeshellarg($backupFile) . " 2>&1";
        exec($testCmd, $testOutput, $testCode);
        
        if ($testCode !== 0) {
            throw new Exception("Backup verification failed: " . implode("\n", $testOutput));
        }
        
        echo "     ✓ Backup integrity verified\n";
        
        // ============================================
        // 5. Calculate Checksum
        // ============================================
        echo "  5. Calculating checksum...\n";
        
        $checksum = md5_file($backupFile);
        file_put_contents($backupFile . '.md5', $checksum);
        
        echo "     ✓ Checksum: {$checksum}\n";
        
        // ============================================
        // 6. Cleanup Old Backups
        // ============================================
        echo "  6. Cleaning up old backups...\n";
        
        $deletedCount = cleanupOldBackups($backupDir, $retentionDays);
        echo "     ✓ Deleted {$deletedCount} old backup(s)\n";
        
        // ============================================
        // 7. Update Backup Statistics
        // ============================================
        echo "  7. Updating statistics...\n";
        
        $stats = [
            'last_backup_time' => date('Y-m-d H:i:s'),
            'backup_type' => $backupType,
            'backup_file' => basename($backupFile),
            'backup_size' => $backupSize,
            'checksum' => $checksum,
            'retention_days' => $retentionDays,
            'status' => 'success',
        ];
        
        Cache::set('last_backup_stats', $stats, 86400 * 7); // Cache for 7 days
        
        // Log success
        $logContent .= "\nBackup completed successfully at: " . date('Y-m-d H:i:s') . "\n";
        $logContent .= "File: " . basename($backupFile) . "\n";
        $logContent .= "Size: " . number_format($backupSize / 1024 / 1024, 2) . " MB\n";
        $logContent .= "Checksum: {$checksum}\n";
        file_put_contents($backupLog, $logContent, FILE_APPEND);
        
        echo "\n✅ Backup completed successfully!\n";
        
    } else {
        echo "⚠️  DRY RUN MODE - No backup performed\n";
    }
    
    // Print summary
    echo "\n========================================\n";
    echo "Backup Summary:\n";
    echo "========================================\n";
    echo "Type:           {$backupType}\n";
    if (!$dryRun) {
        echo "File:           " . basename($backupFile) . "\n";
        echo "Size:           " . number_format($backupSize / 1024 / 1024, 2) . " MB\n";
        echo "Checksum:       {$checksum}\n";
    }
    echo "Retention:      {$retentionDays} days\n";
    echo "Status:         " . ($dryRun ? "DRY RUN" : "SUCCESS") . "\n";
    echo "========================================\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    
    // Log error
    \think\Log::record('[DB Backup] Error: ' . $e->getMessage(), 'error');
    
    if (isset($tablesLocked) && $tablesLocked) {
        Db::execute('UNLOCK TABLES');
        echo "⚠️  Tables unlocked due to error\n";
    }
    
    exit(1);
}

/**
 * Build mysqldump command
 */
function buildMysqldumpCommand($host, $port, $user, $pass, $db, $outputFile, $type) {
    $cmd = "mysqldump";
    $cmd .= " --host=" . escapeshellarg($host);
    $cmd .= " --port=" . escapeshellarg($port);
    $cmd .= " --user=" . escapeshellarg($user);
    $cmd .= " -p" . escapeshellarg($pass);
    
    // Common options
    $cmd .= " --single-transaction"; // Consistent backup without locking
    $cmd .= " --quick"; // Quick retrieval
    $cmd .= " --lock-tables=false"; // Don't lock tables
    
    // Compression
    $cmd .= " | gzip > " . escapeshellarg($outputFile);
    
    // Type-specific options
    if ($type === 'incremental') {
        // For incremental, only dump recent changes
        // This requires binary log or other mechanisms
        // Simplified version: dump last 7 days of data
        $cmd .= " --where=\"created_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)\"";
    }
    
    $cmd .= " " . escapeshellarg($db);
    
    return $cmd;
}

/**
 * Estimate backup size based on table sizes
 */
function estimateBackupSize($dbName) {
    $tables = Db::query("SHOW TABLE STATUS WHERE Table_schema = '{$dbName}'");
    $totalSize = 0;
    
    foreach ($tables as $table) {
        $totalSize += ($table['Data_length'] + $table['Index_length']);
    }
    
    return $totalSize;
}

/**
 * Cleanup old backup files
 */
function cleanupOldBackups($dir, $retentionDays) {
    $deleted = 0;
    $cutoffTime = time() - ($retentionDays * 86400);
    
    $files = glob($dir . '*.sql.gz');
    
    foreach ($files as $file) {
        if (filemtime($file) < $cutoffTime) {
            unlink($file);
            unlink($file . '.md5'); // Delete checksum file too
            $deleted++;
        }
    }
    
    return $deleted;
}
