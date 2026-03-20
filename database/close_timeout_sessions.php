<?php
/**
 * Auto-Close Timeout Sessions Script
 * 
 * Automatically closes sessions/packages that have been in 'ready' status for >14 days
 * Run daily via cron job
 * 
 * Usage: php close_timeout_sessions.php [--dry-run]
 */

// Load ThinkPHP framework
define('APP_PATH', __DIR__ . '/source/application/');
define('RUNTIME_PATH', __DIR__ . '/source/runtime/');
require __DIR__ . '/source/thinkphp/start.php';

use think\Db;
use think\Cache;

// Configuration
$timeoutDays = 14; // Close sessions ready for more than 14 days
$dryRun = in_array('--dry-run', $argv);

echo "========================================\n";
echo "Auto-Close Timeout Sessions\n";
echo "========================================\n";
echo "Timeout Period: {$timeoutDays} days\n";
echo "Mode: " . ($dryRun ? "DRY RUN (No changes)" : "LIVE") . "\n";
echo "========================================\n\n";

try {
    if (!$dryRun) {
        Db::startTrans();
    }
    
    $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$timeoutDays} days"));
    $closedStats = [];
    
    // ============================================
    // 1. Close Timeout Packages (Ready Status > 14 days)
    // ============================================
    echo "Processing Timeout Packages...\n";
    
    // Find packages in 'ready' status (assuming status 5 = ready/pending payment)
    $timeoutPackages = Db::name('package')
        ->where('status', 5) // Ready/Pending status
        ->where('updated_time', '<', $cutoffDate)
        ->where('is_delete', 0)
        ->select();
    
    echo "  Found " . count($timeoutPackages) . " timeout packages\n";
    
    if (count($timeoutPackages) > 0 && !$dryRun) {
        $packageIds = array_column($timeoutPackages, 'id');
        
        // Update package status to cancelled/timeout
        Db::name('package')
            ->whereIn('id', $packageIds)
            ->update([
                'status' => 11, // Cancelled status
                'is_timeout' => 1,
                'timeout_time' => date('Y-m-d H:i:s'),
                'updated_time' => date('Y-m-d H:i:s'),
            ]);
        
        // Log the timeout action
        foreach ($timeoutPackages as $pkg) {
            \think\Log::record(sprintf(
                '[Package Timeout] Package ID: %d, Code: %s, Ready Since: %s',
                $pkg['id'],
                $pkg['express_num'],
                $pkg['updated_time']
            ), 'notice');
        }
        
        echo "  ✓ Closed " . count($timeoutPackages) . " timeout packages\n";
    }
    
    $closedStats['packages'] = count($timeoutPackages);
    
    // ============================================
    // 2. Close Timeout Orders (Pending Payment > 14 days)
    // ============================================
    echo "\nProcessing Timeout Orders...\n";
    
    $timeoutOrders = Db::name('order')
        ->where('order_status', 1) // Pending payment
        ->where('created_time', '<', $cutoffDate)
        ->where('is_delete', 0)
        ->select();
    
    echo "  Found " . count($timeoutOrders) . " timeout orders\n";
    
    if (count($timeoutOrders) > 0 && !$dryRun) {
        $orderIds = array_column($timeoutOrders, 'order_id');
        
        // Update order status to cancelled
        Db::name('order')
            ->whereIn('order_id', $orderIds)
            ->update([
                'order_status' => 5, // Cancelled
                'close_time' => date('Y-m-d H:i:s'),
                'close_reason' => 'Timeout - Payment not received',
            ]);
        
        // Restore inventory if needed
        foreach ($timeoutOrders as $order) {
            $orderGoods = Db::name('order_goods')
                ->where('order_id', $order['order_id'])
                ->select();
            
            foreach ($orderGoods as $goods) {
                // Restore stock
                Db::name('goods_spec')
                    ->where('spec_id', $goods['spec_id'])
                    ->setInc('stock', $goods['goods_num']);
            }
        }
        
        echo "  ✓ Closed " . count($timeoutOrders) . " timeout orders\n";
    }
    
    $closedStats['orders'] = count($timeoutOrders);
    
    // ============================================
    // 3. Close Timeout Inpack Records (Pending > 14 days)
    // ============================================
    echo "\nProcessing Timeout Inpack Records...\n";
    
    $timeoutInpacks = Db::name('inpack')
        ->where('status', 1) // Pending verification
        ->where('created_time', '<', $cutoffDate)
        ->where('is_delete', 0)
        ->select();
    
    echo "  Found " . count($timeoutInpacks) . " timeout inpack records\n";
    
    if (count($timeoutInpacks) > 0 && !$dryRun) {
        $inpackIds = array_column($timeoutInpacks, 'id');
        
        Db::name('inpack')
            ->whereIn('id', $inpackIds)
            ->update([
                'status' => 0, // Cancelled/Invalid
                'updated_time' => date('Y-m-d H:i:s'),
            ]);
        
        echo "  ✓ Closed " . count($timeoutInpacks) . " timeout inpack records\n";
    }
    
    $closedStats['inpacks'] = count($timeoutInpacks);
    
    // Commit transaction
    if (!$dryRun) {
        Db::commit();
        echo "\n✅ Transaction committed successfully!\n";
    } else {
        echo "\n⚠️  DRY RUN MODE - No changes made to database\n";
    }
    
    // Print summary
    echo "\n========================================\n";
    echo "Timeout Closure Summary:\n";
    echo "========================================\n";
    echo "Packages Closed:    " . $closedStats['packages'] . "\n";
    echo "Orders Closed:      " . $closedStats['orders'] . "\n";
    echo "Inpacks Closed:     " . $closedStats['inpacks'] . "\n";
    echo "Total Closed:       " . array_sum($closedStats) . "\n";
    echo "========================================\n";
    
    // Cache statistics
    Cache::set('last_timeout_closure', [
        'time' => date('Y-m-d H:i:s'),
        'stats' => $closedStats,
        'cutoff_date' => $cutoffDate,
    ], 86400); // Cache for 1 day
    
    echo "\n✅ Timeout closure completed at " . date('Y-m-d H:i:s') . "\n";
    
} catch (\Exception $e) {
    if (!$dryRun) {
        Db::rollback();
    }
    
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    
    // Log error
    \think\Log::record('[Timeout Closure] Error: ' . $e->getMessage(), 'error');
    
    exit(1);
}
