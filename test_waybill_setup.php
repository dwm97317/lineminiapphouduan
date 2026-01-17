<?php
/**
 * 测试面单打印功能基础设置
 * 验证 Phase 1 和 Phase 2 的文件创建
 */

// 检查文件是否存在
$files = [
    // Phase 1
    'database/migrations/20260117_create_waybill_record_table.sql',
    'database/migrations/20260117_init_waybill_config.sql',
    
    // Phase 2
    'source/application/common/library/express/ExpressInterface.php',
    'source/application/common/library/express/ZhongtongExpress.php',
    'source/application/common/library/express/ShunfengExpress.php',
    'source/application/common/model/WaybillRecord.php',
    'source/application/store/model/WaybillRecord.php',
    'source/application/common/service/WaybillService.php',
    'source/application/common/service/WaybillConfigService.php',
];

echo "=== 面单打印功能 - 基础文件检查 ===\n\n";

$allExists = true;
foreach ($files as $file) {
    $exists = file_exists($file);
    $status = $exists ? '✓' : '✗';
    echo "$status $file\n";
    if (!$exists) {
        $allExists = false;
    }
}

echo "\n";

if ($allExists) {
    echo "✓ 所有基础文件已创建成功！\n\n";
    echo "下一步操作：\n";
    echo "1. 执行数据库迁移脚本：\n";
    echo "   - database/migrations/20260117_create_waybill_record_table.sql\n";
    echo "   - database/migrations/20260117_init_waybill_config.sql\n\n";
    echo "2. 配置快递API密钥（在后台设置中）\n\n";
    echo "3. 继续 Phase 3：添加打印按钮到订单列表页面\n";
} else {
    echo "✗ 部分文件缺失，请检查创建过程\n";
}

echo "\n=== Phase 1 & 2 完成 ===\n";
