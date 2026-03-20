<?php
/**
 * Database Archive Script - Historical Data Management
 * 
 * Archives closed sessions and old records (>6 months) to history tables
 * Run monthly via cron job
 * 
 * Usage: php archive_historical_data.php [--dry-run]
 */

// Load ThinkPHP framework
define('APP_PATH', __DIR__ . '/source/application/');
define('RUNTIME_PATH', __DIR__ . '/source/runtime/');
require __DIR__ . '/source/thinkphp/start.php';

use think\Db;
use think\Cache;

// Configuration
$archiveOlderThan = '-6 months'; // Archive data older than 6 months
$dryRun = in_array('--dry-run', $argv); // Dry run mode

echo "========================================\n";
echo "Database Archive Script\n";
echo "========================================\n";
echo "Archive Date Cutoff: " . date('Y-m-d', strtotime($archiveOlderThan)) . "\n";
echo "Mode: " . ($dryRun ? "DRY RUN (No changes)" : "LIVE") . "\n";
echo "========================================\n\n";

try {
    // Start transaction for safety
    if (!$dryRun) {
        Db::startTrans();
    }
    
    $archivedStats = [];
    
    // ============================================
    // 1. Archive Old Orders (Completed > 6 months)
    // ============================================
    echo "Processing Orders...\n";
    $cutoffDate = date('Y-m-d H:i:s', strtotime($archiveOlderThan));
    
    // Find completed orders older than 6 months
    $oldOrders = Db::name('order')
        ->where('order_status', 4) // Completed status
        ->where('pay_time', '<', $cutoffDate)
        ->where('is_delete', 0)
        ->select();
    
    echo "  Found " . count($oldOrders) . " completed orders to archive\n";
    
    if (count($oldOrders) > 0 && !$dryRun) {
        // Create archive records
        foreach ($oldOrders as $order) {
            Db::name('order_archive')->insert([
                'original_order_id' => $order['order_id'],
                'order_sn' => $order['order_sn'],
                'user_id' => $order['user_id'],
                'order_status' => $order['order_status'],
                'payment' => $order['real_payment'],
                'created_time' => $order['created_time'],
                'pay_time' => $order['pay_time'],
                'shipping_time' => $order['shipping_time'],
                'completed_time' => $order['confirm_time'],
                'archived_time' => date('Y-m-d H:i:s'),
                'wxapp_id' => $order['wxapp_id'],
            ]);
            
            // Archive order goods
            $orderGoods = Db::name('order_goods')
                ->where('order_id', $order['order_id'])
                ->select();
            
            foreach ($orderGoods as $goods) {
                Db::name('order_goods_archive')->insert([
                    'original_order_goods_id' => $goods['rec_id'],
                    'order_id' => $goods['order_id'],
                    'goods_id' => $goods['goods_id'],
                    'goods_name' => $goods['goods_name'],
                    'quantity' => $goods['goods_num'],
                    'price' => $goods['goods_price'],
                    'wxapp_id' => $goods['wxapp_id'],
                    'archived_time' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        
        // Mark original orders as archived
        Db::name('order')
            ->whereIn('order_id', array_column($oldOrders, 'order_id'))
            ->update(['is_archived' => 1]);
    }
    
    $archivedStats['orders'] = count($oldOrders);
    echo "  ✓ Orders processed\n\n";
    
    // ============================================
    // 2. Archive Old Packages (Delivered > 6 months)
    // ============================================
    echo "Processing Packages...\n";
    
    $oldPackages = Db::name('package')
        ->where('status', '>=', 9) // Shipped/Delivered status
        ->where('created_time', '<', $cutoffDate)
        ->where('is_delete', 0)
        ->select();
    
    echo "  Found " . count($oldPackages) . " old packages to archive\n";
    
    if (count($oldPackages) > 0 && !$dryRun) {
        foreach ($oldPackages as $pkg) {
            Db::name('package_archive')->insert([
                'original_package_id' => $pkg['id'],
                'package_code' => $pkg['express_num'],
                'member_id' => $pkg['member_id'],
                'status' => $pkg['status'],
                'weight' => $pkg['weight'],
                'volume' => $pkg['volume'],
                'created_time' => $pkg['created_time'],
                'archived_time' => date('Y-m-d H:i:s'),
                'wxapp_id' => $pkg['wxapp_id'],
            ]);
        }
        
        // Mark as archived or soft delete based on policy
        Db::name('package')
            ->whereIn('id', array_column($oldPackages, 'id'))
            ->update(['is_archived' => 1]);
    }
    
    $archivedStats['packages'] = count($oldPackages);
    echo "  ✓ Packages processed\n\n";
    
    // ============================================
    // 3. Archive Old Platform Account Bindings (Inactive > 6 months)
    // ============================================
    echo "Processing Platform Account Bindings...\n";
    
    $oldBindings = Db::name('platform_account')
        ->where('status', 1)
        ->where('last_verify_time', '<', $cutoffDate)
        ->where('binding_time', '<', $cutoffDate)
        ->select();
    
    echo "  Found " . count($oldBindings) . " inactive bindings to archive\n";
    
    if (count($oldBindings) > 0 && !$dryRun) {
        foreach ($oldBindings as $binding) {
            Db::name('platform_account_archive')->insert([
                'original_id' => $binding['id'],
                'user_id' => $binding['user_id'],
                'customer_id' => $binding['customer_id'],
                'platform_type' => $binding['platform_type'],
                'binding_time' => $binding['binding_time'],
                'last_verify_time' => $binding['last_verify_time'],
                'archived_time' => date('Y-m-d H:i:s'),
                'wxapp_id' => $binding['wxapp_id'],
            ]);
        }
        
        // Deactivate old bindings
        Db::name('platform_account')
            ->whereIn('id', array_column($oldBindings, 'id'))
            ->update(['status' => 0]);
    }
    
    $archivedStats['bindings'] = count($oldBindings);
    echo "  ✓ Bindings processed\n\n";
    
    // ============================================
    // 4. Archive Old Logistics Records (> 6 months)
    // ============================================
    echo "Processing Logistics Records...\n";
    
    $oldLogistics = Db::name('logistics')
        ->where('created_time', '<', $cutoffDate)
        ->limit(10000) // Batch process to avoid memory issues
        ->select();
    
    echo "  Found " . count($oldLogistics) . " logistics records to archive\n";
    
    if (count($oldLogistics) > 0 && !$dryRun) {
        foreach ($oldLogistics as $log) {
            Db::name('logistics_archive')->insert([
                'original_id' => $log['id'],
                'order_sn' => $log['order_sn'],
                'express_num' => $log['express_num'],
                'status' => $log['status'],
                'logistics_describe' => $log['logistics_describe'],
                'created_time' => $log['created_time'],
                'archived_time' => date('Y-m-d H:i:s'),
                'wxapp_id' => $log['wxapp_id'],
            ]);
        }
        
        // Delete old logistics (keep recent ones)
        Db::name('logistics')
            ->where('created_time', '<', $cutoffDate)
            ->delete();
    }
    
    $archivedStats['logistics'] = count($oldLogistics);
    echo "  ✓ Logistics processed\n\n";
    
    // Commit transaction if not dry run
    if (!$dryRun) {
        Db::commit();
        echo "✅ Transaction committed successfully!\n";
    } else {
        echo "⚠️  DRY RUN MODE - No changes made to database\n";
    }
    
    // Print summary
    echo "\n========================================\n";
    echo "Archive Summary:\n";
    echo "========================================\n";
    echo "Orders Archived:      " . $archivedStats['orders'] . "\n";
    echo "Packages Archived:    " . $archivedStats['packages'] . "\n";
    echo "Bindings Archived:    " . $archivedStats['bindings'] . "\n";
    echo "Logistics Archived:   " . $archivedStats['logistics'] . "\n";
    echo "========================================\n";
    
    // Cache statistics for monitoring
    Cache::set('last_archive_stats', [
        'time' => date('Y-m-d H:i:s'),
        'stats' => $archivedStats,
        'cutoff_date' => $cutoffDate,
    ], 86400 * 30); // Cache for 30 days
    
    echo "\n✅ Archive completed successfully at " . date('Y-m-d H:i:s') . "\n";
    
} catch (\Exception $e) {
    if (!$dryRun) {
        Db::rollback();
    }
    
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    
    // Log error
    \think\Log::record('[DB Archive] Error: ' . $e->getMessage(), 'error');
    
    exit(1);
}
